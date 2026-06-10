const jwt = require('jsonwebtoken');
require('dotenv').config({ path: __dirname + '/../../.env' });

const JWT_SECRET = process.env.JWT_SECRET || 'lab-management-secret-key-change-me-in-production';

/**
 * Authenticate JWT token from Authorization header.
 */
function authenticate(req, res, next) {
  const authHeader = req.headers.authorization;
  if (!authHeader || !authHeader.startsWith('Bearer ')) {
    return res.status(401).json({ error: 'Token tidak ditemukan. Silakan login.' });
  }

  const token = authHeader.split(' ')[1];
  try {
    const decoded = jwt.verify(token, JWT_SECRET);
    req.user = decoded;
    next();
  } catch (err) {
    return res.status(401).json({ error: 'Token tidak valid atau sudah expired.' });
  }
}

/**
 * RBAC middleware — restrict access by role.
 * @param  {...string} roles  Allowed roles
 */
function authorize(...roles) {
  return (req, res, next) => {
    if (!req.user) {
      return res.status(401).json({ error: 'Belum terautentikasi.' });
    }
    if (!roles.includes(req.user.role)) {
      return res.status(403).json({ error: 'Akses ditolak. Role Anda tidak memiliki izin.' });
    }
    next();
  };
}

/**
 * Generate a JWT for a user.
 */
function generateToken(user) {
  return jwt.sign(
    { id: user.id, email: user.email, role: user.role, name: user.name },
    JWT_SECRET,
    { expiresIn: process.env.JWT_EXPIRES_IN || '24h' }
  );
}

module.exports = { authenticate, authorize, generateToken, JWT_SECRET };
