<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\GoodsReceipt;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\QrLog;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StaffAdminController extends Controller
{
    /**
     * Dashboard — QR System + Inventory management.
     */
    public function dashboard()
    {
        // Inventory items for QR generation
        $inventoryItems = InventoryItem::with(['room', 'itemType'])
            ->active()
            ->orderByDesc('created_at')
            ->get();

        // Items without QR code (and already approved)
        $noQrItems = $inventoryItems->whereNull('qr_internal')->where('approval_status', 'approved');

        // Items with QR code
        $qrItems = $inventoryItems->whereNotNull('qr_internal');

        // Recent QR scan logs
        $recentScans = QrLog::with(['inventoryItem', 'scanner'])
            ->where('action', 'scan')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        // Rooms for inventory placement
        $rooms = Room::all();

        // Items approved by Kaprodi that need PO (not yet labeled/received)
        $approvedItems = InventoryItem::with(['room', 'approver'])
            ->where('approval_status', 'approved')
            ->whereNull('qr_internal') // Assuming not yet labeled means needs PO
            ->doesntHave('purchaseOrder')
            ->get();

        $purchaseOrders = PurchaseOrder::with(['inventoryItem.room', 'goodsReceipts'])
            ->where('status', '!=', 'completed')
            ->orderByDesc('created_at')
            ->get();

        // Stats
        $totalItems = $inventoryItems->count();
        $itemsWithQr = $qrItems->count();
        $pendingQr = $noQrItems->count();

        return view('dashboard.staff_admin', compact(
            'inventoryItems', 'noQrItems', 'qrItems',
            'recentScans', 'rooms',
            'approvedItems', 'purchaseOrders',
            'totalItems', 'itemsWithQr', 'pendingQr'
        ));
    }

    /**
     * Generate QR Code for an inventory item.
     */
    public function generateQr(InventoryItem $item)
    {
        $qrString = 'LABINV-' . ($item->label_code ?? $item->id) . '-' . strtoupper(Str::random(8));

        $qrData = $this->generateQrSvg($qrString);

        $item->update([
            'qr_internal' => $qrData,
            'is_labeled' => true,
        ]);

        QrLog::create([
            'inventory_item_id' => $item->id,
            'qr_code' => $qrString,
            'action' => 'generate',
            'scanned_by' => auth()->id(),
            'ip_address' => request()->ip(),
        ]);

        AuditLog::record($item, 'qr_generated', [], ['qr_code' => $qrString]);

        return redirect()->route('staff_admin.dashboard', ['tab' => 'qr_generate'])
            ->with('success', 'QR Code untuk "' . $item->name . '" berhasil di-generate. Kode: ' . $qrString);
    }

    /**
     * Scan/lookup QR code — returns item detail.
     */
    public function scanQr(Request $request)
    {
        $request->validate(['qr_code' => 'required|string']);

        $qrLog = QrLog::where('qr_code', $request->qr_code)
            ->where('action', 'generate')
            ->first();

        if (!$qrLog) {
            return redirect()->route('staff_admin.dashboard', ['tab' => 'qr_scan'])
                ->with('error', 'QR Code tidak dikenali.');
        }

        $item = InventoryItem::with('room')->find($qrLog->inventory_item_id);
        if (!$item) {
            return redirect()->route('staff_admin.dashboard', ['tab' => 'qr_scan'])
                ->with('error', 'Item tidak ditemukan.');
        }

        QrLog::create([
            'inventory_item_id' => $item->id,
            'qr_code' => $request->qr_code,
            'action' => 'scan',
            'scanned_by' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('staff_admin.dashboard', ['tab' => 'qr_scan'])
            ->with('scan_result', $item);
    }

    /**
     * Register received goods into inventory directly.
     */
    public function registerInventory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'label_code' => 'required|string|max:255|unique:inventory_items,label_code',
            'room_id' => 'required|exists:rooms,id',
            'category' => 'required|in:inventaris,bhp',
            'price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'brand' => 'nullable|string|max:255',
            'photo' => 'nullable|image|max:5120',
        ]);

        $data = [
            'name' => $request->name,
            'label_code' => $request->label_code,
            'room_id' => $request->room_id,
            'category' => $request->category,
            'price' => $request->price ?? 0,
            'purchase_date' => $request->purchase_date,
            'brand' => $request->brand,
            'condition' => 'baik',
            'is_labeled' => true,
            'status' => 'active',
            'approval_status' => 'approved', // Admin directly registering makes it approved
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ];

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('inventory_photos', 'public');
        }

        $item = InventoryItem::create($data);
        AuditLog::record($item, 'created_by_admin', [], $data);

        return redirect()->route('staff_admin.dashboard', ['tab' => 'update_inventaris'])
            ->with('success', 'Inventaris "' . $request->name . '" berhasil didaftarkan.');
    }

    /**
     * Create Purchase Order from approved inventory request.
     */
    public function createPurchaseOrder(InventoryItem $item)
    {
        if ($item->approval_status !== 'approved') {
            abort(403, 'Item belum disetujui Kaprodi.');
        }

        if ($item->purchaseOrder) {
            return redirect()->route('staff_admin.dashboard', ['tab' => 'penerimaan_barang'])
                ->with('error', 'PO sudah dibuat untuk item ini.');
        }

        PurchaseOrder::create([
            'po_number' => PurchaseOrder::generatePoNumber(),
            'inventory_item_id' => $item->id,
            'status' => 'ordered',
            'total_ordered' => 1, // Inventory items are single units
            'total_received' => 0,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('staff_admin.dashboard', ['tab' => 'penerimaan_barang'])
            ->with('success', 'Purchase Order berhasil dibuat untuk "' . $item->name . '".');
    }

    /**
     * Record goods receipt.
     */
    public function recordGoodsReceipt(Request $request, PurchaseOrder $purchaseOrder)
    {
        $request->validate([
            'qty_received' => 'required|integer|min:1|max:' . $purchaseOrder->remaining,
            'received_date' => 'required|date',
        ]);

        GoodsReceipt::create([
            'purchase_order_id' => $purchaseOrder->id,
            'qty_received' => $request->qty_received,
            'received_date' => $request->received_date,
            'received_by' => auth()->id(),
        ]);

        $purchaseOrder->increment('total_received', $request->qty_received);

        if ($purchaseOrder->total_received >= $purchaseOrder->total_ordered) {
            $purchaseOrder->update(['status' => 'completed']);
        } else {
            $purchaseOrder->update(['status' => 'partial']);
        }

        return redirect()->route('staff_admin.dashboard', ['tab' => 'penerimaan_barang'])
            ->with('success', $request->qty_received . ' unit berhasil dicatat diterima.');
    }

    /**
     * Generate a simple QR code SVG (inline placeholder).
     */
    private function generateQrSvg(string $data): string
    {
        return 'data:qr;code=' . urlencode($data);
    }
}
