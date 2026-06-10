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

module.exports = router;
