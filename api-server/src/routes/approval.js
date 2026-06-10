const express = require('express');
const pool = require('../config/database');
const { authenticate, authorize } = require('../middleware/auth');

const router = express.Router();

/**
 * POST /api/approval/:id/approve
 * Kaprodi approves an inventory item — locks it permanently.
 */
router.post('/:id/approve', authenticate, authorize('kaprodi'), async (req, res) => {
  try {
    const [rows] = await pool.execute('SELECT * FROM inventory_items WHERE id = ?', [req.params.id]);
    if (rows.length === 0) {
      return res.status(404).json({ error: 'Item tidak ditemukan.' });
    }

    const item = rows[0];
    if (item.approval_status === 'approved') {
      return res.status(400).json({ error: 'Item sudah disetujui sebelumnya.' });
    }

    await pool.execute(`
      UPDATE inventory_items SET
        approval_status = 'approved',
        approved_by = ?,
        approved_at = NOW(),
        updated_at = NOW()
      WHERE id = ?
    `, [req.user.id, req.params.id]);

    // Audit log
    await pool.execute(`
      INSERT INTO audit_logs (user_id, model_type, model_id, action, old_values, new_values, ip_address, created_at, updated_at)
      VALUES (?, 'App\\\\Models\\\\InventoryItem', ?, 'approved', ?, ?, ?, NOW(), NOW())
    `, [
      req.user.id, req.params.id,
      JSON.stringify({ approval_status: item.approval_status }),
      JSON.stringify({ approval_status: 'approved', approved_by: req.user.id }),
      req.ip,
    ]);

    return res.json({ message: 'Item berhasil disetujui dan dikunci (LOCKED).' });
  } catch (err) {
    console.error('Approve error:', err);
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

/**
 * POST /api/approval/:id/reject
 * Kaprodi rejects an inventory item.
 */
router.post('/:id/reject', authenticate, authorize('kaprodi'), async (req, res) => {
  try {
    const { reason } = req.body;

    const [rows] = await pool.execute('SELECT * FROM inventory_items WHERE id = ?', [req.params.id]);
    if (rows.length === 0) {
      return res.status(404).json({ error: 'Item tidak ditemukan.' });
    }

    const item = rows[0];
    if (item.approval_status === 'approved') {
      return res.status(400).json({ error: 'Item sudah disetujui dan tidak bisa ditolak.' });
    }

    await pool.execute(`
      UPDATE inventory_items SET
        approval_status = 'rejected',
        rejection_reason = ?,
        approved_by = ?,
        approved_at = NOW(),
        updated_at = NOW()
      WHERE id = ?
    `, [reason || null, req.user.id, req.params.id]);

    // Audit log
    await pool.execute(`
      INSERT INTO audit_logs (user_id, model_type, model_id, action, old_values, new_values, ip_address, created_at, updated_at)
      VALUES (?, 'App\\\\Models\\\\InventoryItem', ?, 'rejected', ?, ?, ?, NOW(), NOW())
    `, [
      req.user.id, req.params.id,
      JSON.stringify({ approval_status: item.approval_status }),
      JSON.stringify({ approval_status: 'rejected', reason }),
      req.ip,
    ]);

    return res.json({ message: 'Item ditolak.' });
  } catch (err) {
    console.error('Reject error:', err);
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

/**
 * GET /api/approval/pending
 * Get all items pending approval (for Kaprodi dashboard).
 */
router.get('/pending', authenticate, authorize('kaprodi'), async (req, res) => {
  try {
    const [rows] = await pool.execute(`
      SELECT i.*, r.name AS room_name, r.code AS room_code
      FROM inventory_items i
      LEFT JOIN rooms r ON i.room_id = r.id
      WHERE i.approval_status = 'pending'
      ORDER BY i.created_at DESC
    `);

    return res.json({ data: rows });
  } catch (err) {
    console.error('Get pending approvals error:', err);
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

/**
 * GET /api/approval/history
 * Get approval history.
 */
router.get('/history', authenticate, authorize('kaprodi'), async (req, res) => {
  try {
    const [rows] = await pool.execute(`
      SELECT i.*, r.name AS room_name, u.name AS approved_by_name
      FROM inventory_items i
      LEFT JOIN rooms r ON i.room_id = r.id
      LEFT JOIN users u ON i.approved_by = u.id
      WHERE i.approval_status IN ('approved', 'rejected')
      ORDER BY i.approved_at DESC
      LIMIT 100
    `);

    return res.json({ data: rows });
  } catch (err) {
    console.error('Get approval history error:', err);
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

module.exports = router;
