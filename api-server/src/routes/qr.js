const express = require('express');
const QRCode = require('qrcode');
const { v4: uuidv4 } = require('uuid');
const pool = require('../config/database');
const { authenticate, authorize } = require('../middleware/auth');

const router = express.Router();

/**
 * POST /api/qr/generate/:itemId
 * Generate QR Code for an inventory item.
 * Role: staff_admin
 */
router.post('/generate/:itemId', authenticate, authorize('staff_admin', 'admin'), async (req, res) => {
  try {
    const { itemId } = req.params;

    const [rows] = await pool.execute('SELECT * FROM inventory_items WHERE id = ?', [itemId]);
    if (rows.length === 0) {
      return res.status(404).json({ error: 'Item tidak ditemukan.' });
    }

    const item = rows[0];

    // Generate unique QR code string
    const qrString = `LABINV-${item.label_code || item.id}-${uuidv4().slice(0, 8).toUpperCase()}`;

    // Generate QR as base64 PNG
    const qrDataUrl = await QRCode.toDataURL(qrString, {
      width: 300,
      margin: 2,
      color: { dark: '#1e293b', light: '#ffffff' },
      errorCorrectionLevel: 'H',
    });

    // Save QR to database
    await pool.execute(
      'UPDATE inventory_items SET qr_internal = ?, is_labeled = 1, updated_at = NOW() WHERE id = ?',
      [qrDataUrl, itemId]
    );

    // Log QR generation
    await pool.execute(`
      INSERT INTO qr_logs (inventory_item_id, qr_code, action, scanned_by, ip_address, created_at, updated_at)
      VALUES (?, ?, 'generate', ?, ?, NOW(), NOW())
    `, [itemId, qrString, req.user.id, req.ip]);

    // Audit log
    await pool.execute(`
      INSERT INTO audit_logs (user_id, model_type, model_id, action, new_values, ip_address, created_at, updated_at)
      VALUES (?, 'App\\\\Models\\\\InventoryItem', ?, 'qr_generated', ?, ?, NOW(), NOW())
    `, [req.user.id, itemId, JSON.stringify({ qr_code: qrString }), req.ip]);

    return res.json({
      message: 'QR Code berhasil di-generate.',
      qr_code: qrString,
      qr_image: qrDataUrl,
      item: {
        id: item.id,
        name: item.name,
        label_code: item.label_code,
      },
    });
  } catch (err) {
    console.error('QR generate error:', err);
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

/**
 * POST /api/qr/scan
 * Scan/lookup QR code — returns item detail.
 * Public or authenticated.
 */
router.post('/scan', async (req, res) => {
  try {
    const { qr_code } = req.body;
    if (!qr_code) {
      return res.status(400).json({ error: 'QR code wajib diisi.' });
    }

    // Search in qr_logs for the QR code string
    const [qrLogs] = await pool.execute(
      "SELECT inventory_item_id FROM qr_logs WHERE qr_code = ? AND action = 'generate' LIMIT 1",
      [qr_code]
    );

    let itemId = null;

    if (qrLogs.length > 0) {
      itemId = qrLogs[0].inventory_item_id;
    } else {
      // Try matching by label_code prefix in qr_code
      const codePart = qr_code.replace('LABINV-', '').split('-')[0];
      if (codePart) {
        const [items] = await pool.execute(
          'SELECT id FROM inventory_items WHERE label_code = ? OR id = ? LIMIT 1',
          [codePart, isNaN(codePart) ? 0 : codePart]
        );
        if (items.length > 0) itemId = items[0].id;
      }
    }

    if (!itemId) {
      return res.status(404).json({ error: 'QR Code tidak dikenali. Barang tidak ditemukan.' });
    }

    // Get full item detail
    const [rows] = await pool.execute(`
      SELECT i.*, r.name AS room_name, r.code AS room_code, r.location AS room_location
      FROM inventory_items i
      LEFT JOIN rooms r ON i.room_id = r.id
      WHERE i.id = ?
    `, [itemId]);

    if (rows.length === 0) {
      return res.status(404).json({ error: 'Item tidak ditemukan.' });
    }

    // Log scan event
    const scannedBy = req.user ? req.user.id : null;
    await pool.execute(`
      INSERT INTO qr_logs (inventory_item_id, qr_code, action, scanned_by, ip_address, user_agent, created_at, updated_at)
      VALUES (?, ?, 'scan', ?, ?, ?, NOW(), NOW())
    `, [itemId, qr_code, scannedBy, req.ip, req.headers['user-agent'] || null]);

    return res.json({
      message: 'Barang ditemukan.',
      data: rows[0],
    });
  } catch (err) {
    console.error('QR scan error:', err);
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

/**
 * POST /api/qr/campus/:itemId
 * Upload campus QR code for an item.
 * Role: staff_admin
 */
router.post('/campus/:itemId', authenticate, authorize('staff_admin', 'admin'), async (req, res) => {
  try {
    const { qr_kampus } = req.body;
    if (!qr_kampus) {
      return res.status(400).json({ error: 'Data QR kampus wajib diisi.' });
    }

    const [rows] = await pool.execute('SELECT id FROM inventory_items WHERE id = ?', [req.params.itemId]);
    if (rows.length === 0) {
      return res.status(404).json({ error: 'Item tidak ditemukan.' });
    }

    await pool.execute(
      'UPDATE inventory_items SET qr_kampus = ?, updated_at = NOW() WHERE id = ?',
      [qr_kampus, req.params.itemId]
    );

    return res.json({ message: 'QR kampus berhasil disimpan.' });
  } catch (err) {
    console.error('Campus QR error:', err);
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

/**
 * GET /api/qr/logs/:itemId
 * Get scan history for an item.
 */
router.get('/logs/:itemId', authenticate, async (req, res) => {
  try {
    const [rows] = await pool.execute(`
      SELECT ql.*, u.name AS scanned_by_name
      FROM qr_logs ql
      LEFT JOIN users u ON ql.scanned_by = u.id
      WHERE ql.inventory_item_id = ?
      ORDER BY ql.created_at DESC
      LIMIT 50
    `, [req.params.itemId]);

    return res.json({ data: rows });
  } catch (err) {
    console.error('QR logs error:', err);
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

module.exports = router;
