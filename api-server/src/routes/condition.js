const express = require('express');
const pool = require('../config/database');
const { authenticate, authorize } = require('../middleware/auth');

const router = express.Router();

/**
 * POST /api/condition/:itemId/update
 * Staff Lab updates item condition. If rusak_berat, creates replacement entry.
 */
router.post('/:itemId/update', authenticate, authorize('staff_lab', 'admin'), async (req, res) => {
  const conn = await pool.getConnection();
  try {
    const { condition, description } = req.body;
    const validConditions = ['baik', 'kurang_baik', 'rusak_ringan', 'rusak_berat'];
    if (!validConditions.includes(condition)) {
      return res.status(400).json({ error: 'Kondisi tidak valid.' });
    }

    const [rows] = await conn.execute('SELECT * FROM inventory_items WHERE id = ?', [req.params.itemId]);
    if (rows.length === 0) return res.status(404).json({ error: 'Item tidak ditemukan.' });

    const item = rows[0];
    const condBefore = item.condition;

    await conn.beginTransaction();

    // Log condition change
    await conn.execute(`
      INSERT INTO inventory_condition_logs (inventory_item_id, condition_before, condition_after, description, user_id, created_at, updated_at)
      VALUES (?, ?, ?, ?, ?, NOW(), NOW())
    `, [req.params.itemId, condBefore, condition, description || null, req.user.id]);

    // Update item condition
    await conn.execute(
      'UPDATE inventory_items SET `condition` = ?, updated_at = NOW() WHERE id = ?',
      [condition, req.params.itemId]
    );

    let replacementId = null;

    // If rusak_berat: mark old item inactive, create new replacement entry
    if (condition === 'rusak_berat') {
      await conn.execute(
        "UPDATE inventory_items SET status = 'inactive', updated_at = NOW() WHERE id = ?",
        [req.params.itemId]
      );

      const newLabel = item.label_code ? `${item.label_code}-R` : null;
      const [newItem] = await conn.execute(`
        INSERT INTO inventory_items
          (label_code, name, category, \`condition\`, room_id, price, item_type_id,
           status, approval_status, replaced_from, is_labeled, created_at, updated_at)
        VALUES (?, ?, ?, 'baik', ?, ?, ?, 'active', 'pending', ?, ?, NOW(), NOW())
      `, [
        newLabel, item.name, item.category || 'inventaris',
        item.room_id, item.price, item.item_type_id,
        req.params.itemId, item.is_labeled ? 1 : 0,
      ]);

      replacementId = newItem.insertId;

      // Link old item to new replacement
      await conn.execute(
        'UPDATE inventory_items SET replaced_by = ? WHERE id = ?',
        [replacementId, req.params.itemId]
      );
    }

    await conn.commit();

    // Audit log
    await pool.execute(`
      INSERT INTO audit_logs (user_id, model_type, model_id, action, old_values, new_values, ip_address, created_at, updated_at)
      VALUES (?, 'App\\\\Models\\\\InventoryItem', ?, 'condition_updated', ?, ?, ?, NOW(), NOW())
    `, [
      req.user.id, req.params.itemId,
      JSON.stringify({ condition: condBefore }),
      JSON.stringify({ condition, replacement_id: replacementId }),
      req.ip,
    ]);

    return res.json({
      message: condition === 'rusak_berat'
        ? `Barang ditandai rusak berat. ID lama (#${req.params.itemId}) disimpan sebagai histori. Barang pengganti baru dibuat (#${replacementId}).`
        : 'Kondisi barang berhasil diperbarui.',
      old_item_id: parseInt(req.params.itemId),
      replacement_id: replacementId,
    });
  } catch (err) {
    await conn.rollback();
    console.error('Condition update error:', err);
    return res.status(500).json({ error: 'Internal server error.' });
  } finally {
    conn.release();
  }
});

/**
 * POST /api/condition/maintenance
 * Staff Lab records maintenance log.
 */
router.post('/maintenance', authenticate, authorize('staff_lab', 'admin'), async (req, res) => {
  const conn = await pool.getConnection();
  try {
    const { inventory_item_id, maintenance_date, condition_after, description, uses_bhp, bhp_item_id, bhp_qty_used } = req.body;

    if (!inventory_item_id || !maintenance_date || !condition_after) {
      return res.status(400).json({ error: 'Data tidak lengkap.' });
    }

    await conn.beginTransaction();

    // Check if uses BHP and stock is sufficient
    if (uses_bhp && bhp_item_id) {
      const [bhpRows] = await conn.execute('SELECT * FROM bhp_items WHERE id = ? FOR UPDATE', [bhp_item_id]);
      if (bhpRows.length === 0) {
        await conn.rollback();
        return res.status(404).json({ error: 'BHP tidak ditemukan.' });
      }
      const bhp = bhpRows[0];
      if (bhp.stock < bhp_qty_used) {
        await conn.rollback();
        return res.status(400).json({ error: `Stok BHP tidak mencukupi. Sisa: ${bhp.stock} ${bhp.unit}` });
      }

      const stockBefore = bhp.stock;
      const stockAfter = bhp.stock - bhp_qty_used;

      // Deduct BHP stock
      await conn.execute('UPDATE bhp_items SET stock = ?, updated_at = NOW() WHERE id = ?', [stockAfter, bhp_item_id]);

      // Record BHP transaction
      await conn.execute(`
        INSERT INTO bhp_transactions (bhp_item_id, type, quantity, stock_before, stock_after, description, user_id, created_at, updated_at)
        VALUES (?, 'out', ?, ?, ?, ?, ?, NOW(), NOW())
      `, [bhp_item_id, bhp_qty_used, stockBefore, stockAfter, `Maintenance: ${description || 'Pemeliharaan'}`, req.user.id]);
    }

    // Insert maintenance log
    await conn.execute(`
      INSERT INTO maintenance_logs (inventory_item_id, maintenance_date, condition_after, description, bhp_item_id, bhp_qty_used, user_id, created_at, updated_at)
      VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    `, [
      inventory_item_id,
      maintenance_date,
      condition_after,
      description || null,
      uses_bhp ? bhp_item_id : null,
      uses_bhp ? bhp_qty_used : 0,
      req.user.id
    ]);

    // Check item
    const [itemRows] = await conn.execute('SELECT * FROM inventory_items WHERE id = ? FOR UPDATE', [inventory_item_id]);
    if (itemRows.length === 0) {
      await conn.rollback();
      return res.status(404).json({ error: 'Item tidak ditemukan.' });
    }

    const item = itemRows[0];
    const condBefore = item.condition;

    // Log condition change
    await conn.execute(`
      INSERT INTO inventory_condition_logs (inventory_item_id, condition_before, condition_after, description, user_id, created_at, updated_at)
      VALUES (?, ?, ?, ?, ?, NOW(), NOW())
    `, [inventory_item_id, condBefore, condition_after, `Maintenance: ${description || 'Pemeliharaan'}`, req.user.id]);

    // Update item condition
    await conn.execute(
      'UPDATE inventory_items SET `condition` = ?, updated_at = NOW() WHERE id = ?',
      [condition_after, inventory_item_id]
    );

    let replacementId = null;

    // If condition_after is 'rusak_berat', mark item inactive and create replacement
    if (condition_after === 'rusak_berat') {
      await conn.execute(
        "UPDATE inventory_items SET status = 'inactive', updated_at = NOW() WHERE id = ?",
        [inventory_item_id]
      );

      const newLabel = item.label_code ? `${item.label_code}-R` : null;
      const [newItem] = await conn.execute(`
        INSERT INTO inventory_items
          (label_code, name, category, \`condition\`, room_id, price, item_type_id,
           status, approval_status, replaced_from, is_labeled, created_at, updated_at)
        VALUES (?, ?, ?, 'baik', ?, ?, ?, 'active', 'pending', ?, ?, NOW(), NOW())
      `, [
        newLabel, item.name, item.category || 'inventaris',
        item.room_id, item.price, item.item_type_id,
        inventory_item_id, item.is_labeled ? 1 : 0
      ]);

      replacementId = newItem.insertId;

      await conn.execute(
        'UPDATE inventory_items SET replaced_by = ? WHERE id = ?',
        [replacementId, inventory_item_id]
      );
    }

    await conn.commit();

    // Audit log
    await pool.execute(`
      INSERT INTO audit_logs (user_id, model_type, model_id, action, old_values, new_values, ip_address, created_at, updated_at)
      VALUES (?, 'App\\\\Models\\\\InventoryItem', ?, 'condition_updated_via_maintenance', ?, ?, ?, NOW(), NOW())
    `, [
      req.user.id, inventory_item_id,
      JSON.stringify({ condition: condBefore }),
      JSON.stringify({ condition: condition_after, replacement_id: replacementId }),
      req.ip
    ]);

    return res.json({
      message: condition_after === 'rusak_berat'
        ? `Barang ditandai rusak berat via maintenance. ID lama (#${inventory_item_id}) disimpan sebagai histori. Barang pengganti baru dibuat (#${replacementId}).`
        : 'Log pemeliharaan berhasil disimpan.',
      replacement_id: replacementId
    });
  } catch (err) {
    await conn.rollback();
    console.error('Maintenance log error:', err);
    return res.status(500).json({ error: 'Internal server error.' });
  } finally {
    conn.release();
  }
});

/**
 * GET /api/condition/logs/recent
 * List recent inventory condition logs.
 */
router.get('/logs/recent', authenticate, async (req, res) => {
  try {
    const [rows] = await pool.execute(`
      SELECT cl.*, i.name AS item_name, u.name AS user_name
      FROM inventory_condition_logs cl
      LEFT JOIN inventory_items i ON cl.inventory_item_id = i.id
      LEFT JOIN users u ON cl.user_id = u.id
      ORDER BY cl.created_at DESC
      LIMIT 20
    `);
    return res.json({ data: rows });
  } catch (err) {
    console.error('Get recent condition logs error:', err);
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

module.exports = router;
