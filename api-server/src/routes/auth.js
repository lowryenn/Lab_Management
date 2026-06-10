const express = require('express');
const bcrypt = require('bcryptjs');
const pool = require('../config/database');
const { generateToken } = require('../middleware/auth');

const router = express.Router();

/**
 * POST /api/auth/login
 * Login and receive JWT token.
 */
router.post('/login', async (req, res) => {
  try {
    const { email, password } = req.body;
    if (!email || !password) {
      return res.status(400).json({ error: 'Email dan password wajib diisi.' });
    }

    const [rows] = await pool.execute('SELECT * FROM users WHERE email = ?', [email]);
    if (rows.length === 0) {
      return res.status(401).json({ error: 'Email atau password salah.' });
    }

    const user = rows[0];
    const valid = await bcrypt.compare(password, user.password);
    if (!valid) {
      return res.status(401).json({ error: 'Email atau password salah.' });
    }

    const token = generateToken(user);
    return res.json({
      message: 'Login berhasil.',
      token,
      user: { id: user.id, name: user.name, email: user.email, role: user.role },
    });
  } catch (err) {
    console.error('Login error:', err);
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

/**
 * GET /api/auth/me
 * Get current authenticated user info.
 */
router.get('/me', async (req, res) => {
  // This route requires the authenticate middleware to be applied upstream
  return res.json({ user: req.user });
});

module.exports = router;
