require('dotenv').config({ path: __dirname + '/../.env' });

const express = require('express');
const cors = require('cors');
const pool = require('./config/database');

// Import routes
const authRoutes = require('./routes/auth');
const inventoryRoutes = require('./routes/inventory');
const approvalRoutes = require('./routes/approval');
const qrRoutes = require('./routes/qr');
const bhpRoutes = require('./routes/bhp');
const roomRoutes = require('./routes/rooms');
const conditionRoutes = require('./routes/condition');
const userRoutes = require('./routes/users');
const poRoutes = require('./routes/po');
const statsRoutes = require('./routes/stats');
const { authenticate } = require('./middleware/auth');

const app = express();
const PORT = process.env.API_PORT || 3001;

// Middleware
app.use(cors({
  origin: process.env.CORS_ORIGIN || 'http://localhost:8000',
  credentials: true,
}));
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true }));

// Request logging
app.use((req, res, next) => {
  const start = Date.now();
  res.on('finish', () => {
    const duration = Date.now() - start;
    console.log(`[${new Date().toISOString()}] ${req.method} ${req.originalUrl} ${res.statusCode} ${duration}ms`);
  });
  next();
});

// Health check
app.get('/api/health', (req, res) => {
  res.json({ status: 'ok', timestamp: new Date().toISOString(), service: 'Lab Management API' });
});

// Routes
app.use('/api/auth', authRoutes);
app.use('/api/inventory', inventoryRoutes);
app.use('/api/approval', approvalRoutes);
app.use('/api/qr', qrRoutes);
app.use('/api/bhp', bhpRoutes);
app.use('/api/rooms', roomRoutes);
app.use('/api/condition', conditionRoutes);
app.use('/api/users', userRoutes);
app.use('/api/po', poRoutes);
app.use('/api/stats', statsRoutes);

// QR scan is public (no auth required for lookup)
app.post('/api/qr/scan-public', async (req, res) => {
  const { qr_code } = req.body;
  if (!qr_code) return res.status(400).json({ error: 'QR code wajib diisi.' });

  try {
    const [qrLogs] = await pool.execute(
      "SELECT inventory_item_id FROM qr_logs WHERE qr_code = ? AND action = 'generate' LIMIT 1",
      [qr_code]
    );

    if (qrLogs.length === 0) {
      return res.status(404).json({ error: 'QR Code tidak dikenali.' });
    }

    const [rows] = await pool.execute(`
      SELECT i.*, r.name AS room_name, r.code AS room_code, r.location AS room_location
      FROM inventory_items i
      LEFT JOIN rooms r ON i.room_id = r.id
      WHERE i.id = ?
    `, [qrLogs[0].inventory_item_id]);

    if (rows.length === 0) return res.status(404).json({ error: 'Item tidak ditemukan.' });

    // Log scan
    await pool.execute(`
      INSERT INTO qr_logs (inventory_item_id, qr_code, action, ip_address, user_agent, created_at, updated_at)
      VALUES (?, ?, 'scan', ?, ?, NOW(), NOW())
    `, [qrLogs[0].inventory_item_id, qr_code, req.ip, req.headers['user-agent'] || null]);

    return res.json({ message: 'Barang ditemukan.', data: rows[0] });
  } catch (err) {
    console.error('Public QR scan error:', err);
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

// 404 handler
app.use((req, res) => {
  res.status(404).json({ error: 'Route tidak ditemukan.' });
});

// Error handler
app.use((err, req, res, next) => {
  console.error('Unhandled error:', err);
  res.status(500).json({ error: 'Internal server error.' });
});

// Start server
async function start() {
  try {
    const connection = await pool.getConnection();
    console.log('✅ Database connected successfully');
    connection.release();
  } catch (err) {
    console.error('❌ Database connection failed:', err.message);
    console.log('⚠️  Server will start anyway — DB may become available later.');
  }

  app.listen(PORT, () => {
    console.log(`🚀 Lab Management API running on http://127.0.0.1:${PORT}`);
    console.log(`📋 API Health: http://127.0.0.1:${PORT}/api/health`);
  });
}

start();
