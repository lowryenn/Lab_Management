const express = require('express');
const pool = require('../config/database');
const { authenticate, authorize } = require('../middleware/auth');

const router = express.Router();

/**
 * GET /api/po
 * List uncompleted purchase orders.
 */
router.get('/', authenticate, authorize('staff_admin', 'admin'), async (req, res) => {
  try {
    const [pos] = await pool.execute(`
      SELECT po.*,
             i.name AS item_name, i.label_code AS item_label_code, i.room_id AS item_room_id,
             r.name AS room_name, r.code AS room_code,
             u.name AS creator_name
      FROM purchase_orders po
      LEFT JOIN inventory_items i ON po.inventory_item_id = i.id
      LEFT JOIN rooms r ON i.room_id = r.id
      LEFT JOIN users u ON po.created_by = u.id
      WHERE po.status != 'completed'
      ORDER BY po.created_at DESC
    `);

    for (let po of pos) {
      const [receipts] = await pool.execute(
        `SELECT gr.*, u.name AS receiver_name 
         FROM goods_receipts gr 
         LEFT JOIN users u ON gr.received_by = u.id 
         WHERE gr.purchase_order_id = ? 
         ORDER BY gr.created_at DESC`,
        [po.id]
      );
      po.inventory_item = {
        id: po.inventory_item_id,
        name: po.item_name,
        label_code: po.item_label_code,
        room_id: po.item_room_id,
        room: po.item_room_id ? { id: po.item_room_id, name: po.room_name, code: po.room_code } : null
      };
      po.goods_receipts = receipts;
    }

    return res.json({ data: pos });
  } catch (err) {
    console.error('Get POs error:', err);
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

/**
 * POST /api/po/:itemId
 * Create Purchase Order for an approved inventory item.
 */
router.post('/:itemId', authenticate, authorize('staff_admin', 'admin'), async (req, res) => {
  try {
    const itemId = req.params.itemId;

    // Check item lock status
    const [items] = await pool.execute('SELECT * FROM inventory_items WHERE id = ?', [itemId]);
    if (items.length === 0) {
      return res.status(404).json({ error: 'Item tidak ditemukan.' });
    }

    const item = items[0];
    if (item.approval_status !== 'approved') {
      return res.status(403).json({ error: 'Item belum disetujui Kaprodi.' });
    }

    // Check if PO already exists
    const [existingPos] = await pool.execute('SELECT * FROM purchase_orders WHERE inventory_item_id = ?', [itemId]);
    if (existingPos.length > 0) {
      return res.status(400).json({ error: 'PO sudah dibuat untuk item ini.' });
    }

    // Generate PO number: PO-YEAR-XXX
    const year = new Date().getFullYear();
    const [lastPo] = await pool.execute(
      'SELECT po_number FROM purchase_orders WHERE YEAR(created_at) = ? ORDER BY id DESC LIMIT 1',
      [year]
    );

    let nextNumber = 1;
    if (lastPo.length > 0) {
      const lastPoNum = lastPo[0].po_number;
      const parts = lastPoNum.split('-');
      const lastNum = parseInt(parts[parts.length - 1]);
      if (!isNaN(lastNum)) {
        nextNumber = lastNum + 1;
      }
    }
    const poNumber = `PO-${year}-${String(nextNumber).padStart(3, '0')}`;

    const [result] = await pool.execute(
      `INSERT INTO purchase_orders 
        (po_number, inventory_item_id, status, total_ordered, total_received, created_by, created_at, updated_at) 
       VALUES (?, ?, 'ordered', 1, 0, ?, NOW(), NOW())`,
      [poNumber, itemId, req.user.id]
    );

    return res.status(201).json({
      message: 'Purchase Order berhasil dibuat.',
      id: result.insertId,
      po_number: poNumber,
    });
  } catch (err) {
    console.error('Create PO error:', err);
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

/**
 * POST /api/po/goods-receipt/:poId
 * Record a Goods Receipt for a PO.
 */
router.post('/goods-receipt/:poId', authenticate, authorize('staff_admin', 'admin'), async (req, res) => {
  try {
    const poId = req.params.poId;
    const { qty_received, received_date } = req.body;

    if (!qty_received || !received_date) {
      return res.status(400).json({ error: 'Jumlah diterima dan tanggal wajib diisi.' });
    }

    // Get PO
    const [pos] = await pool.execute('SELECT * FROM purchase_orders WHERE id = ?', [poId]);
    if (pos.length === 0) {
      return res.status(404).json({ error: 'Purchase Order tidak ditemukan.' });
    }

    const po = pos[0];
    const remaining = po.total_ordered - po.total_received;
    if (qty_received > remaining) {
      return res.status(400).json({ error: `Jumlah diterima melebihi sisa PO (${remaining} unit).` });
    }

    // Insert Goods Receipt
    await pool.execute(
      `INSERT INTO goods_receipts (purchase_order_id, qty_received, received_date, received_by, created_at, updated_at) 
       VALUES (?, ?, ?, ?, NOW(), NOW())`,
      [poId, qty_received, received_date, req.user.id]
    );

    // Update PO stats
    const newTotalReceived = po.total_received + parseInt(qty_received);
    const newStatus = newTotalReceived >= po.total_ordered ? 'completed' : 'partial';

    await pool.execute(
      'UPDATE purchase_orders SET total_received = ?, status = ?, updated_at = NOW() WHERE id = ?',
      [newTotalReceived, newStatus, poId]
    );

    return res.json({
      message: `${qty_received} unit berhasil dicatat diterima.`,
      status: newStatus,
    });
  } catch (err) {
    console.error('Record goods receipt error:', err);
    return res.status(500).json({ error: 'Internal server error.' });
  }
});

module.exports = router;
