const express = require('express');
const pool = require('../config/database');
const { authenticate, authorize } = require('../middleware/auth');

const router = express.Router();

/**
 * GET /api/inventory
 * List all inventory items with optional filters.
 * Query params: category, room_id, condition, status, approval_status, search, page, limit
 */
router.get('/', authenticate, async (req, res) => {
  try {
    const {
      category, room_id, condition, status, approval_status,
      search, page = 1, limit = 50,
    } = req.query;

    let sql = `
      SELECT i.*, r.name AS room_name, r.code AS room_code, r.location AS room_location,
             u.name AS approved_by_name
      FROM inventory_items i
      LEFT JOIN rooms r ON i.room_id = r.id
      LEFT JOIN users u ON i.approved_by = u.id
      WHERE 1=1
    `;
    const params = [];

    if (category) { sql += ' AND i.category = ?'; params.push(category); }
    if (room_id) { sql += ' AND i.room_id = ?'; params.push(room_id); }
    if (condition) { sql += ' AND i.condition = ?'; params.push(condition); }
    if (status) { sql += ' AND i.status = ?'; params.push(status); }
    if (approval_status) { sql += ' AND i.approval_status = ?'; params.push(approval_status); }
    if (search) {
      sql += ' AND (i.name LIKE ? OR i.label_code LIKE ? OR i.brand LIKE ?)';
      const term = `%${search}%`;
      params.push(term, term, term);
    }

    // Count query
    const countSql = sql.replace(/SELECT[\s\S]+?FROM/i, 'SELECT COUNT(*) as total FROM');
    const [countResult] = await pool.execute(countSql, params);
    const total = countResult[0].total;

    // Paginate
    const offset = (parseInt(page) - 1) * parseInt(limit);
    sql += ' ORDER BY i.created_at DESC LIMIT ? OFFSET ?';
    params.push(parseInt(limit), offset);

    const [rows] = await pool.execute(sql, params);

    return res.json({
      data: rows,
      pagination: {
        total,
        page: parseInt(page),
        limit: parseInt(limit),
        totalPages: Math.ceil(total / parseInt(limit)),
      },
    });
  } catch (err) {
    console.error('Get inventory error:', err);
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

/**
 * GET /api/inventory/:id
 * Get single inventory item with full details.
 */
router.get('/:id', authenticate, async (req, res) => {
  try {
    const [rows] = await pool.execute(`
      SELECT i.*, r.name AS room_name, r.code AS room_code,
             u.name AS approved_by_name
      FROM inventory_items i
      LEFT JOIN rooms r ON i.room_id = r.id
      LEFT JOIN users u ON i.approved_by = u.id
      WHERE i.id = ?
    `, [req.params.id]);

    if (rows.length === 0) {
      return res.status(404).json({ error: 'Item tidak ditemukan.' });
    }

    // Get condition history
    const [condLogs] = await pool.execute(`
      SELECT cl.*, u.name AS user_name
      FROM inventory_condition_logs cl
      LEFT JOIN users u ON cl.user_id = u.id
      WHERE cl.inventory_item_id = ?
      ORDER BY cl.created_at DESC LIMIT 10
    `, [req.params.id]);

    // Get maintenance logs
    const [maintLogs] = await pool.execute(`
      SELECT ml.*, u.name AS user_name
      FROM maintenance_logs ml
      LEFT JOIN users u ON ml.user_id = u.id
      WHERE ml.inventory_item_id = ?
      ORDER BY ml.maintenance_date DESC LIMIT 10
    `, [req.params.id]);

    return res.json({
      data: rows[0],
      condition_logs: condLogs,
      maintenance_logs: maintLogs,
    });
  } catch (err) {
    console.error('Get inventory detail error:', err);
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

/**
 * POST /api/inventory
 * Create new inventory item.
 * Roles: kepala_lab, staff_admin
 */
router.post('/', authenticate, authorize('kepala_lab', 'staff_admin', 'admin'), async (req, res) => {
  try {
    const {
      label_code, name, category = 'inventaris', description, brand,
      condition = 'baik', room_id, price = 0, purchase_date,
      acquisition_year, photo,
    } = req.body;

    if (!name) {
      return res.status(400).json({ error: 'Nama barang wajib diisi.' });
    }

    const [result] = await pool.execute(`
      INSERT INTO inventory_items
        (label_code, name, category, description, brand, \`condition\`, room_id, price, purchase_date, acquisition_year, photo, is_labeled, approval_status, status, created_at, updated_at)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'active', NOW(), NOW())
    `, [
      label_code || null, name, category, description || null, brand || null,
      condition, room_id || null, price, purchase_date || null,
      acquisition_year || null, photo || null, label_code ? 1 : 0,
    ]);

    // Audit log
    await pool.execute(`
      INSERT INTO audit_logs (user_id, model_type, model_id, action, new_values, ip_address, created_at, updated_at)
      VALUES (?, 'App\\\\Models\\\\InventoryItem', ?, 'created', ?, ?, NOW(), NOW())
    `, [req.user.id, result.insertId, JSON.stringify(req.body), req.ip]);

    return res.status(201).json({
      message: 'Item inventaris berhasil ditambahkan.',
      id: result.insertId,
    });
  } catch (err) {
    console.error('Create inventory error:', err);
    if (err.code === 'ER_DUP_ENTRY') {
      return res.status(409).json({ error: 'Label code sudah digunakan.' });
    }
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

/**
 * PUT /api/inventory/:id
 * Update inventory item (only if not locked/approved by Kaprodi).
 * Roles: kepala_lab, staff_admin
 */
router.put('/:id', authenticate, authorize('kepala_lab', 'staff_admin', 'admin'), async (req, res) => {
  try {
    // Check if item exists and is not locked
    const [existing] = await pool.execute('SELECT * FROM inventory_items WHERE id = ?', [req.params.id]);
    if (existing.length === 0) {
      return res.status(404).json({ error: 'Item tidak ditemukan.' });
    }

    const item = existing[0];

    // If approved by Kaprodi, it's locked
    if (item.approval_status === 'approved') {
      return res.status(403).json({ error: 'Data ini sudah disetujui Kaprodi dan tidak bisa diubah (LOCKED).' });
    }

    const {
      label_code, name, category, description, brand,
      condition, room_id, price, purchase_date, acquisition_year,
    } = req.body;

    const oldValues = JSON.stringify(item);

    await pool.execute(`
      UPDATE inventory_items SET
        label_code = COALESCE(?, label_code),
        name = COALESCE(?, name),
        category = COALESCE(?, category),
        description = COALESCE(?, description),
        brand = COALESCE(?, brand),
        \`condition\` = COALESCE(?, \`condition\`),
        room_id = COALESCE(?, room_id),
        price = COALESCE(?, price),
        purchase_date = COALESCE(?, purchase_date),
        acquisition_year = COALESCE(?, acquisition_year),
        updated_at = NOW()
      WHERE id = ?
    `, [
      label_code !== undefined ? label_code : null,
      name !== undefined ? name : null,
      category !== undefined ? category : null,
      description !== undefined ? description : null,
      brand !== undefined ? brand : null,
      condition !== undefined ? condition : null,
      room_id !== undefined ? room_id : null,
      price !== undefined ? price : null,
      purchase_date !== undefined ? purchase_date : null,
      acquisition_year !== undefined ? acquisition_year : null,
      req.params.id,
    ]);

    // Audit log
    await pool.execute(`
      INSERT INTO audit_logs (user_id, model_type, model_id, action, old_values, new_values, ip_address, created_at, updated_at)
      VALUES (?, 'App\\\\Models\\\\InventoryItem', ?, 'updated', ?, ?, ?, NOW(), NOW())
    `, [req.user.id, req.params.id, oldValues, JSON.stringify(req.body), req.ip]);

    return res.json({ message: 'Item berhasil diperbarui.' });
  } catch (err) {
    console.error('Update inventory error:', err);
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

/**
 * DELETE /api/inventory/:id
 * Soft-delete (set status to inactive).
 */
router.delete('/:id', authenticate, authorize('kepala_lab', 'admin'), async (req, res) => {
  try {
    const [existing] = await pool.execute('SELECT * FROM inventory_items WHERE id = ?', [req.params.id]);
    if (existing.length === 0) {
      return res.status(404).json({ error: 'Item tidak ditemukan.' });
    }

    if (existing[0].approval_status === 'approved') {
      return res.status(403).json({ error: 'Data yang sudah disetujui tidak bisa dihapus.' });
    }

    await pool.execute('UPDATE inventory_items SET status = ?, updated_at = NOW() WHERE id = ?', ['inactive', req.params.id]);

    // Audit log
    await pool.execute(`
      INSERT INTO audit_logs (user_id, model_type, model_id, action, old_values, ip_address, created_at, updated_at)
      VALUES (?, 'App\\\\Models\\\\InventoryItem', ?, 'deleted', ?, ?, NOW(), NOW())
    `, [req.user.id, req.params.id, JSON.stringify(existing[0]), req.ip]);

    return res.json({ message: 'Item berhasil di-nonaktifkan.' });
  } catch (err) {
    console.error('Delete inventory error:', err);
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

module.exports = router;
