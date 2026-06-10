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

    for (let room of rooms) {
      const [breakdown] = await pool.execute(
        "SELECT name, COUNT(*) as qty FROM inventory_items WHERE room_id = ? AND status = 'active' GROUP BY name",
        [room.id]
      );
      room.item_breakdown = breakdown;
    }

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

/**
 * POST /api/rooms
 * Create new room.
 */
router.post('/', authenticate, authorize('admin'), async (req, res) => {
  try {
    const { name, code, location, capacity } = req.body;
    if (!name || !code || capacity === undefined) {
      return res.status(400).json({ error: 'Nama, kode, dan kapasitas wajib diisi.' });
    }

    const [result] = await pool.execute(
      'INSERT INTO rooms (name, code, location, capacity, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())',
      [name, code, location || null, capacity]
    );

    return res.status(201).json({ message: 'Ruangan berhasil dibuat.', id: result.insertId });
  } catch (err) {
    console.error('Create room error:', err);
    if (err.code === 'ER_DUP_ENTRY') {
      return res.status(409).json({ error: 'Kode ruangan sudah digunakan.' });
    }
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

/**
 * PUT /api/rooms/:id
 * Update room.
 */
router.put('/:id', authenticate, authorize('admin'), async (req, res) => {
  try {
    const { name, code, location, capacity } = req.body;
    if (!name || !code || capacity === undefined) {
      return res.status(400).json({ error: 'Nama, kode, dan kapasitas wajib diisi.' });
    }

    const [result] = await pool.execute(
      'UPDATE rooms SET name = ?, code = ?, location = ?, capacity = ?, updated_at = NOW() WHERE id = ?',
      [name, code, location || null, capacity, req.params.id]
    );

    if (result.affectedRows === 0) {
      return res.status(404).json({ error: 'Ruangan tidak ditemukan.' });
    }

    return res.json({ message: 'Ruangan berhasil diperbarui.' });
  } catch (err) {
    console.error('Update room error:', err);
    if (err.code === 'ER_DUP_ENTRY') {
      return res.status(409).json({ error: 'Kode ruangan sudah digunakan.' });
    }
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

/**
 * DELETE /api/rooms/:id
 * Delete room.
 */
router.delete('/:id', authenticate, authorize('admin'), async (req, res) => {
  try {
    const [result] = await pool.execute('DELETE FROM rooms WHERE id = ?', [req.params.id]);
    if (result.affectedRows === 0) {
      return res.status(404).json({ error: 'Ruangan tidak ditemukan.' });
    }
    return res.json({ message: 'Ruangan berhasil dihapus.' });
  } catch (err) {
    console.error('Delete room error:', err);
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

module.exports = router;
