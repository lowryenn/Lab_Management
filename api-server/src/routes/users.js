const express = require('express');
const bcrypt = require('bcryptjs');
const pool = require('../config/database');
const { authenticate, authorize } = require('../middleware/auth');

const router = express.Router();

/**
 * GET /api/users
 * List all users.
 */
router.get('/', authenticate, authorize('admin', 'kaprodi'), async (req, res) => {
  try {
    const [rows] = await pool.execute('SELECT * FROM users ORDER BY name ASC');
    return res.json({ data: rows });
  } catch (err) {
    console.error('Get users error:', err);
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

/**
 * GET /api/users/:id
 * Get user detail.
 */
router.get('/:id', authenticate, authorize('admin'), async (req, res) => {
  try {
    const [rows] = await pool.execute('SELECT * FROM users WHERE id = ?', [req.params.id]);
    if (rows.length === 0) {
      return res.status(404).json({ error: 'User tidak ditemukan.' });
    }
    return res.json({ data: rows[0] });
  } catch (err) {
    console.error('Get user detail error:', err);
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

/**
 * POST /api/users
 * Create new user.
 */
router.post('/', authenticate, authorize('admin'), async (req, res) => {
  try {
    const { name, email, role, password } = req.body;
    if (!name || !email || !role || !password) {
      return res.status(400).json({ error: 'Data tidak lengkap.' });
    }

    const hashedPassword = await bcrypt.hash(password, 10);
    const [result] = await pool.execute(
      'INSERT INTO users (name, email, role, password, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())',
      [name, email, role, hashedPassword]
    );

    return res.status(201).json({ message: 'User berhasil dibuat.', id: result.insertId });
  } catch (err) {
    console.error('Create user error:', err);
    if (err.code === 'ER_DUP_ENTRY') {
      return res.status(409).json({ error: 'Email sudah digunakan.' });
    }
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

/**
 * PUT /api/users/:id
 * Update user.
 */
router.put('/:id', authenticate, authorize('admin'), async (req, res) => {
  try {
    const { name, email, role, password } = req.body;
    if (!name || !email || !role) {
      return res.status(400).json({ error: 'Data tidak lengkap.' });
    }

    let sql = 'UPDATE users SET name = ?, email = ?, role = ?, updated_at = NOW()';
    const params = [name, email, role];

    if (password) {
      const hashedPassword = await bcrypt.hash(password, 10);
      sql += ', password = ?';
      params.push(hashedPassword);
    }

    sql += ' WHERE id = ?';
    params.push(req.params.id);

    const [result] = await pool.execute(sql, params);
    if (result.affectedRows === 0) {
      return res.status(404).json({ error: 'User tidak ditemukan.' });
    }

    return res.json({ message: 'User berhasil diperbarui.' });
  } catch (err) {
    console.error('Update user error:', err);
    if (err.code === 'ER_DUP_ENTRY') {
      return res.status(409).json({ error: 'Email sudah digunakan.' });
    }
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

/**
 * DELETE /api/users/:id
 * Delete user.
 */
router.delete('/:id', authenticate, authorize('admin'), async (req, res) => {
  try {
    const [result] = await pool.execute('DELETE FROM users WHERE id = ?', [req.params.id]);
    if (result.affectedRows === 0) {
      return res.status(404).json({ error: 'User tidak ditemukan.' });
    }
    return res.json({ message: 'User berhasil dihapus.' });
  } catch (err) {
    console.error('Delete user error:', err);
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

module.exports = router;
