const express = require('express');
const { v4: uuidv4 } = require('uuid');
const pool = require('../config/database');
const { authenticate, authorize } = require('../middleware/auth');

const router = express.Router();

/**
 * GET /api/bhp
 * List all BHP items with room info.
 */
router.get('/', authenticate, async (req, res) => {
  try {
    const { room_id, low_stock, search } = req.query;

    let sql = `
      SELECT b.*, r.name AS room_name, r.code AS room_code
      FROM bhp_items b
      LEFT JOIN rooms r ON b.room_id = r.id
      WHERE 1=1
    `;
    const params = [];

    if (room_id) { sql += ' AND b.room_id = ?'; params.push(room_id); }
    if (low_stock === '1') { sql += ' AND b.stock <= b.min_stock'; }
    if (search) {
      sql += ' AND b.name LIKE ?';
      params.push(`%${search}%`);
    }

    sql += ' ORDER BY b.name ASC';
    const [rows] = await pool.execute(sql, params);

    return res.json({ data: rows });
  } catch (err) {
    console.error('Get BHP error:', err);
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

/**
 * GET /api/bhp/:id
 * Get single BHP item with transaction history.
 */
router.get('/:id', authenticate, async (req, res) => {
  try {
    const [rows] = await pool.execute(`
      SELECT b.*, r.name AS room_name
      FROM bhp_items b
      LEFT JOIN rooms r ON b.room_id = r.id
      WHERE b.id = ?
    `, [req.params.id]);

    if (rows.length === 0) {
      return res.status(404).json({ error: 'BHP tidak ditemukan.' });
    }

    const [transactions] = await pool.execute(`
      SELECT bt.*, u.name AS user_name
      FROM bhp_transactions bt
      LEFT JOIN users u ON bt.user_id = u.id
      WHERE bt.bhp_item_id = ?
      ORDER BY bt.created_at DESC
      LIMIT 50
    `, [req.params.id]);

    return res.json({ data: rows[0], transactions });
  } catch (err) {
    console.error('Get BHP detail error:', err);
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

/**
 * POST /api/bhp
 * Create new BHP item.
 * Role: staff_lab, staff_admin
 */
router.post('/', authenticate, authorize('staff_lab', 'staff_admin', 'admin'), async (req, res) => {
  try {
    const { name, stock = 0, unit, min_stock = 0, room_id } = req.body;

    if (!name || !unit) {
      return res.status(400).json({ error: 'Nama dan satuan wajib diisi.' });
    }

    const [result] = await pool.execute(
      'INSERT INTO bhp_items (name, stock, unit, min_stock, room_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())',
      [name, stock, unit, min_stock, room_id || null]
    );

    // If stock > 0, create initial transaction
    if (stock > 0) {
      await pool.execute(`
        INSERT INTO bhp_transactions (bhp_item_id, type, quantity, stock_before, stock_after, description, user_id, created_at, updated_at)
        VALUES (?, 'in', ?, 0, ?, 'Stok awal', ?, NOW(), NOW())
      `, [result.insertId, stock, stock, req.user.id]);
    }

    // Audit log
    await pool.execute(`
      INSERT INTO audit_logs (user_id, model_type, model_id, action, new_values, ip_address, created_at, updated_at)
      VALUES (?, 'App\\\\Models\\\\BhpItem', ?, 'created', ?, ?, NOW(), NOW())
    `, [req.user.id, result.insertId, JSON.stringify(req.body), req.ip]);

    return res.status(201).json({
      message: 'BHP berhasil ditambahkan.',
      id: result.insertId,
    });
  } catch (err) {
    console.error('Create BHP error:', err);
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

/**
 * POST /api/bhp/bulk-usage
 * Bulk input BHP usage (multiple items at once).
 * Role: staff_lab
 * Body: { items: [{ bhp_item_id, quantity, description }] }
 */
router.post('/bulk-usage', authenticate, authorize('staff_lab', 'admin'), async (req, res) => {
  const conn = await pool.getConnection();
  try {
    const { items } = req.body;

    if (!items || !Array.isArray(items) || items.length === 0) {
      return res.status(400).json({ error: 'Data items wajib diisi (array).' });
    }

    await conn.beginTransaction();

    const batchId = `BULK-${Date.now()}-${uuidv4().slice(0, 6)}`;
    const results = [];

    for (const entry of items) {
      const { bhp_item_id, quantity, description } = entry;

      if (!bhp_item_id || !quantity || quantity <= 0) {
        await conn.rollback();
        return res.status(400).json({ error: `Data tidak valid untuk bhp_item_id: ${bhp_item_id}` });
      }

      // Get current stock
      const [bhpRows] = await conn.execute('SELECT * FROM bhp_items WHERE id = ? FOR UPDATE', [bhp_item_id]);
      if (bhpRows.length === 0) {
        await conn.rollback();
        return res.status(404).json({ error: `BHP ID ${bhp_item_id} tidak ditemukan.` });
      }

      const bhp = bhpRows[0];
      if (bhp.stock < quantity) {
        await conn.rollback();
        return res.status(400).json({
          error: `Stok "${bhp.name}" tidak cukup. Sisa: ${bhp.stock} ${bhp.unit}, diminta: ${quantity}`,
        });
      }

      const stockBefore = bhp.stock;
      const stockAfter = bhp.stock - quantity;

      // Deduct stock
      await conn.execute('UPDATE bhp_items SET stock = ?, updated_at = NOW() WHERE id = ?', [stockAfter, bhp_item_id]);

      // Record transaction
      await conn.execute(`
        INSERT INTO bhp_transactions (bhp_item_id, type, quantity, stock_before, stock_after, description, batch_id, user_id, created_at, updated_at)
        VALUES (?, 'out', ?, ?, ?, ?, ?, ?, NOW(), NOW())
      `, [bhp_item_id, quantity, stockBefore, stockAfter, description || 'Pemakaian bulk', batchId, req.user.id]);

      results.push({
        bhp_item_id,
        name: bhp.name,
        quantity_used: quantity,
        stock_before: stockBefore,
        stock_after: stockAfter,
      });
    }

    await conn.commit();

    // Audit log
    await pool.execute(`
      INSERT INTO audit_logs (user_id, model_type, model_id, action, new_values, ip_address, created_at, updated_at)
      VALUES (?, 'App\\\\Models\\\\BhpTransaction', 0, 'bulk_usage', ?, ?, NOW(), NOW())
    `, [req.user.id, JSON.stringify({ batch_id: batchId, items: results }), req.ip]);

    return res.json({
      message: `${results.length} item BHP berhasil dicatat pemakaiannya.`,
      batch_id: batchId,
      results,
    });
  } catch (err) {
    await conn.rollback();
    console.error('Bulk usage error:', err);
    return res.status(500).json({ error: 'Internal server error.' });
  } finally {
    conn.release();
  }
});

/**
 * PATCH /api/bhp/:id/stock
 * Update BHP stock (in/out).
 */
router.patch('/:id/stock', authenticate, authorize('staff_lab', 'staff_admin', 'admin'), async (req, res) => {
  try {
    const { type = 'in', quantity, description } = req.body;

    if (!quantity || quantity <= 0) {
      return res.status(400).json({ error: 'Jumlah harus lebih dari 0.' });
    }

    const [rows] = await pool.execute('SELECT * FROM bhp_items WHERE id = ?', [req.params.id]);
    if (rows.length === 0) {
      return res.status(404).json({ error: 'BHP tidak ditemukan.' });
    }

    const bhp = rows[0];
    const stockBefore = bhp.stock;
    const stockAfter = type === 'in' ? bhp.stock + quantity : bhp.stock - quantity;

    if (stockAfter < 0) {
      return res.status(400).json({ error: 'Stok tidak mencukupi.' });
    }

    await pool.execute('UPDATE bhp_items SET stock = ?, updated_at = NOW() WHERE id = ?', [stockAfter, req.params.id]);

    await pool.execute(`
      INSERT INTO bhp_transactions (bhp_item_id, type, quantity, stock_before, stock_after, description, user_id, created_at, updated_at)
      VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    `, [req.params.id, type, quantity, stockBefore, stockAfter, description || null, req.user.id]);

    return res.json({
      message: `Stok ${bhp.name} berhasil diperbarui.`,
      stock_before: stockBefore,
      stock_after: stockAfter,
    });
  } catch (err) {
    console.error('Update stock error:', err);
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

/**
 * GET /api/bhp/transactions/recent
 * List recent BHP transactions.
 */
router.get('/transactions/recent', authenticate, authorize('staff_lab', 'admin'), async (req, res) => {
  try {
    const [rows] = await pool.execute(`
      SELECT bt.*, b.name AS bhp_item_name, b.unit AS bhp_item_unit, u.name AS user_name
      FROM bhp_transactions bt
      LEFT JOIN bhp_items b ON bt.bhp_item_id = b.id
      LEFT JOIN users u ON bt.user_id = u.id
      ORDER BY bt.created_at DESC
      LIMIT 20
    `);
    return res.json({ data: rows });
  } catch (err) {
    console.error('Get recent transactions error:', err);
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

module.exports = router;
