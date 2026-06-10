const express = require('express');
const pool = require('../config/database');
const { authenticate, authorize } = require('../middleware/auth');

const router = express.Router();

/**
 * GET /api/rooms
 * List all rooms with auto-counted inventory via COUNT.
 */
router.get('/', authenticate, async (req, res) => {
  try {
    const [rooms] = await pool.execute(`
      SELECT r.*,
        (SELECT COUNT(*) FROM inventory_items i WHERE i.room_id = r.id AND i.status = 'active') AS total_inventory,
        (SELECT COUNT(*) FROM inventory_items i WHERE i.room_id = r.id AND i.status = 'active' AND i.\`condition\` = 'baik') AS inventory_baik,
        (SELECT COUNT(*) FROM inventory_items i WHERE i.room_id = r.id AND i.status = 'active' AND i.\`condition\` != 'baik') AS inventory_rusak,
        (SELECT COUNT(*) FROM bhp_items b WHERE b.room_id = r.id) AS total_bhp,
        (SELECT COALESCE(SUM(i.price), 0) FROM inventory_items i WHERE i.room_id = r.id AND i.status = 'active') AS total_asset_value
      FROM rooms r ORDER BY r.name ASC
    `);
    return res.json({ data: rooms });
  } catch (err) {
    console.error('Get rooms error:', err);
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

/**
 * GET /api/rooms/:id
 * Room detail with inventory list.
 */
router.get('/:id', authenticate, async (req, res) => {
  try {
    const [rooms] = await pool.execute('SELECT * FROM rooms WHERE id = ?', [req.params.id]);
    if (rooms.length === 0) return res.status(404).json({ error: 'Ruangan tidak ditemukan.' });

    const [inv] = await pool.execute(
      "SELECT * FROM inventory_items WHERE room_id = ? AND status = 'active' ORDER BY name", [req.params.id]
    );
    const [bhp] = await pool.execute(
      'SELECT * FROM bhp_items WHERE room_id = ? ORDER BY name', [req.params.id]
    );

    return res.json({
      room: rooms[0],
      summary: { total_inventory: inv.length, total_bhp: bhp.length },
      inventory_items: inv,
      bhp_items: bhp,
    });
  } catch (err) {
    console.error('Room detail error:', err);
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

/**
 * GET /api/rooms/:id/count
 * Real-time COUNT of items in a room.
 */
router.get('/:id/count', authenticate, async (req, res) => {
  try {
    const id = req.params.id;
    const [result] = await pool.execute(`
      SELECT
        (SELECT COUNT(*) FROM inventory_items WHERE room_id = ? AND status = 'active') AS active_items,
        (SELECT COUNT(*) FROM inventory_items WHERE room_id = ? AND status = 'active' AND \`condition\` = 'baik') AS baik,
        (SELECT COUNT(*) FROM inventory_items WHERE room_id = ? AND status = 'active' AND \`condition\` != 'baik') AS rusak,
        (SELECT COUNT(*) FROM bhp_items WHERE room_id = ?) AS total_bhp
    `, [id, id, id, id]);
    return res.json({ counts: result[0] });
  } catch (err) {
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

module.exports = router;
