const express = require('express');
const pool = require('../config/database');
const { authenticate } = require('../middleware/auth');

const router = express.Router();

/**
 * GET /api/stats/admin
 */
router.get('/admin', authenticate, async (req, res) => {
  try {
    const [[users]] = await pool.execute('SELECT COUNT(*) AS count FROM users');
    const [[rooms]] = await pool.execute('SELECT COUNT(*) AS count FROM rooms');
    const [[inventory]] = await pool.execute('SELECT COUNT(*) AS count FROM inventory_items');
    
    return res.json({
      totalUsers: users.count,
      totalRooms: rooms.count,
      totalInventory: inventory.count,
    });
  } catch (err) {
    console.error('Admin stats error:', err);
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

/**
 * GET /api/stats/kaprodi
 */
router.get('/kaprodi', authenticate, async (req, res) => {
  try {
    const [[pending]] = await pool.execute("SELECT COUNT(*) AS count FROM inventory_items WHERE approval_status = 'pending' AND status = 'active'");
    const [[approved]] = await pool.execute("SELECT COUNT(*) AS count FROM inventory_items WHERE approval_status = 'approved'");
    const [[rejected]] = await pool.execute("SELECT COUNT(*) AS count FROM inventory_items WHERE approval_status = 'rejected'");
    const [[assetVal]] = await pool.execute("SELECT COALESCE(SUM(price), 0) AS value FROM inventory_items WHERE approval_status = 'approved'");
    const [[rooms]] = await pool.execute('SELECT COUNT(*) AS count FROM rooms');

    return res.json({
      pendingCount: pending.count,
      approvedCount: approved.count,
      rejectedCount: rejected.count,
      totalAssetValue: parseFloat(assetVal.value),
      roomCount: rooms.count,
    });
  } catch (err) {
    console.error('Kaprodi stats error:', err);
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

/**
 * GET /api/stats/kepala_lab
 */
router.get('/kepala_lab', authenticate, async (req, res) => {
  try {
    const [[active]] = await pool.execute("SELECT COUNT(*) AS count FROM inventory_items WHERE status = 'active'");
    const [[assetVal]] = await pool.execute("SELECT COALESCE(SUM(price), 0) AS value FROM inventory_items WHERE status = 'active'");
    const [[needsRepair]] = await pool.execute("SELECT COUNT(*) AS count FROM inventory_items WHERE status = 'active' AND `condition` != 'baik'");
    const [[pending]] = await pool.execute("SELECT COUNT(*) AS count FROM inventory_items WHERE approval_status = 'pending'");
    const [[approved]] = await pool.execute("SELECT COUNT(*) AS count FROM inventory_items WHERE approval_status = 'approved'");

    return res.json({
      totalInventory: active.count,
      totalAssetValue: parseFloat(assetVal.value),
      needsRepair: needsRepair.count,
      pendingApproval: pending.count,
      lockedCount: approved.count,
    });
  } catch (err) {
    console.error('Kepala Lab stats error:', err);
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

/**
 * GET /api/stats/staff_lab
 */
router.get('/staff_lab', authenticate, async (req, res) => {
  try {
    const [[active]] = await pool.execute("SELECT COUNT(*) AS count FROM inventory_items WHERE status = 'active'");
    const [[bhpLow]] = await pool.execute("SELECT COUNT(*) AS count FROM bhp_items WHERE stock <= min_stock");

    // Start & End of current week
    const now = new Date();
    const day = now.getDay();
    const diff = now.getDate() - day + (day === 0 ? -6 : 1); // Mon
    const startOfWeek = new Date(now.setDate(diff));
    startOfWeek.setHours(0, 0, 0, 0);

    const endOfWeek = new Date(startOfWeek);
    endOfWeek.setDate(endOfWeek.getDate() + 6);
    endOfWeek.setHours(23, 59, 59, 999);

    const [[maintDue]] = await pool.execute(
      'SELECT COUNT(*) AS count FROM maintenance_logs WHERE maintenance_date >= ? AND maintenance_date <= ?',
      [startOfWeek.toISOString().split('T')[0], endOfWeek.toISOString().split('T')[0]]
    );

    return res.json({
      totalAssets: active.count,
      bhpLow: bhpLow.count,
      maintenanceDue: maintDue.count,
    });
  } catch (err) {
    console.error('Staff Lab stats error:', err);
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

module.exports = router;
