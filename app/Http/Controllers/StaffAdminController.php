<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\GoodsReceipt;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\QrLog;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class StaffAdminController extends Controller
{
    /**
     * Dashboard - Scanner, Generate QR & Register Manual.
     */
    public function dashboard()
    {
        // 1. Fetch inventory items
        $invRes = $this->apiCall('GET', '/api/inventory', ['limit' => 1000]);
        $inventoryItems = collect($invRes->json()['data'] ?? [])->map(function($data) {
            $item = $this->hydrateModel(InventoryItem::class, $data);
            if (!empty($data['room_name'])) {
                $item->setRelation('room', $this->hydrateModel(Room::class, [
                    'id' => $data['room_id'],
                    'name' => $data['room_name'],
                    'code' => $data['room_code'],
                    'location' => $data['room_location']
                ]));
            }
            return $item;
        });

        $noQrItems = $inventoryItems->filter(fn($item) => empty($item->qr_internal));
        $qrItems = $inventoryItems->filter(fn($item) => !empty($item->qr_internal));

        // 2. Fetch recent scans
        $scansRes = $this->apiCall('GET', '/api/qr/scans');
        $recentScans = collect($scansRes->json()['data'] ?? [])->map(function($data) {
            $log = $this->hydrateModel(QrLog::class, $data);
            $log->setRelation('inventoryItem', $this->hydrateModel(InventoryItem::class, [
                'id' => $data['inventory_item_id'],
                'name' => $data['item_name'],
                'label_code' => $data['item_label_code'],
                'room_id' => $data['item_room_id']
            ]));
            $log->setRelation('scanner', $this->hydrateModel(User::class, [
                'id' => $data['scanned_by'],
                'name' => $data['scanned_by_name']
            ]));
            return $log;
        });

        // 3. Fetch rooms
        $roomsRes = $this->apiCall('GET', '/api/rooms');
        $rooms = collect($roomsRes->json()['data'] ?? [])->map(fn($data) => $this->hydrateModel(Room::class, $data));

        // 4. Fetch Purchase Orders
        $poRes = $this->apiCall('GET', '/api/po');
        $poData = $poRes->json()['data'] ?? [];
        $purchaseOrders = collect($poData)->map(function($data) {
            $po = $this->hydrateModel(PurchaseOrder::class, $data);
            if (!empty($data['inventory_item'])) {
                $itemData = $data['inventory_item'];
                $item = $this->hydrateModel(InventoryItem::class, $itemData);
                if (!empty($itemData['room'])) {
                    $item->setRelation('room', $this->hydrateModel(Room::class, $itemData['room']));
                }
                $po->setRelation('inventoryItem', $item);
            }
            if (!empty($data['goods_receipts'])) {
                $receipts = collect($data['goods_receipts'])->map(function($gr) {
                    $r = $this->hydrateModel(GoodsReceipt::class, $gr);
                    if (!empty($gr['receiver_name'])) {
                        $r->setRelation('receiver', $this->hydrateModel(User::class, ['id' => $gr['received_by'], 'name' => $gr['receiver_name']]));
                    }
                    return $r;
                });
                $po->setRelation('goodsReceipts', $receipts);
            }
            return $po;
        });

        $poItemIds = $purchaseOrders->pluck('inventory_item_id')->toArray();

        // Approved items that need PO
        $approvedItems = $inventoryItems->filter(function($item) use ($poItemIds) {
            return $item->approval_status === 'approved' && empty($item->qr_internal) && !in_array($item->id, $poItemIds);
        });

        // Filter Inventaris and BHP active collections for viewing
        $allInventaris = $inventoryItems->filter(fn($item) => $item->category === 'inventaris' && $item->status === 'active');
        $allBhp = $inventoryItems->filter(fn($item) => $item->category === 'bhp' && $item->status === 'active');

        // Stats
        $totalItems = $inventoryItems->count();
        $itemsWithQr = $qrItems->count();
        $pendingQr = $noQrItems->count();

        return view('dashboard.staff_admin', compact(
            'inventoryItems', 'noQrItems', 'qrItems',
            'recentScans', 'rooms',
            'approvedItems', 'purchaseOrders',
            'totalItems', 'itemsWithQr', 'pendingQr',
            'allInventaris', 'allBhp'
        ));
    }

    /**
     * Generate QR Code for an inventory item.
     */
    public function generateQr($id)
    {
        $res = $this->apiCall('POST', "/api/qr/generate/{$id}");
        if ($res->failed()) {
            return redirect()->route('staff_admin.dashboard', ['tab' => 'qr_generate'])
                ->with('error', $res->json()['error'] ?? 'Gagal generate QR.');
        }

        $body = $res->json();
        return redirect()->route('staff_admin.dashboard', ['tab' => 'qr_generate'])
            ->with('success', 'QR Code berhasil di-generate. Kode: ' . $body['qr_code']);
    }

    /**
     * Update Campus QR Code for an inventory item.
     */
    public function updateCampusQr(Request $request, $id)
    {
        $request->validate([
            'qr_kampus' => 'required|string|max:255',
        ]);

        $res = $this->apiCall('POST', "/api/qr/campus/{$id}", [
            'qr_kampus' => $request->qr_kampus,
        ]);

        if ($res->failed()) {
            return redirect()->route('staff_admin.dashboard', ['tab' => 'qr_generate'])
                ->with('error', $res->json()['error'] ?? 'Gagal menyimpan QR Kampus.');
        }

        return redirect()->route('staff_admin.dashboard', ['tab' => 'qr_generate'])
            ->with('success', 'QR Kampus berhasil disimpan.');
    }

    /**
     * Scan/lookup QR code — returns item detail.
     */
    public function scanQr(Request $request)
    {
        $request->validate(['qr_code' => 'required|string']);

        $res = $this->apiCall('POST', '/api/qr/scan', [
            'qr_code' => $request->qr_code,
        ]);

        if ($res->failed()) {
            return redirect()->route('staff_admin.dashboard', ['tab' => 'qr_scan'])
                ->with('error', $res->json()['error'] ?? 'QR Code tidak dikenali.');
        }

        $itemData = $res->json()['data'];
        $item = $this->hydrateModel(InventoryItem::class, $itemData);
        if (!empty($itemData['room_name'])) {
            $item->setRelation('room', $this->hydrateModel(Room::class, [
                'id' => $itemData['room_id'],
                'name' => $itemData['room_name'],
                'code' => $itemData['room_code'],
                'location' => $itemData['room_location']
            ]));
        }

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
            'label_code' => 'required|string|max:255',
            'room_id' => 'required|exists:rooms,id',
            'category' => 'required|in:inventaris,bhp',
            'price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'brand' => 'nullable|string|max:255',
            'photo' => 'nullable|image|max:5120',
            'qr_kampus' => 'nullable|string|max:255',
        ]);

        $data = $request->only('name', 'label_code', 'room_id', 'category', 'price', 'purchase_date', 'brand', 'qr_kampus');
        $data['is_labeled'] = true;
        $data['condition'] = 'baik';
        $data['status'] = 'active';
        $data['approval_status'] = 'approved';

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('inventory_photos', 'public');
        }

        $res = $this->apiCall('POST', '/api/inventory', $data);

        if ($res->failed()) {
            return back()->withErrors(['label_code' => $res->json()['error'] ?? 'Gagal mendaftarkan inventaris.'])->withInput();
        }

        return redirect()->route('staff_admin.dashboard', ['tab' => 'update_inventaris'])
            ->with('success', 'Inventaris "' . $request->name . '" berhasil didaftarkan.');
    }

    /**
     * Create Purchase Order from approved inventory request.
     */
    public function createPurchaseOrder($id)
    {
        $res = $this->apiCall('POST', "/api/po/{$id}");

        if ($res->failed()) {
            return redirect()->route('staff_admin.dashboard', ['tab' => 'penerimaan_barang'])
                ->with('error', $res->json()['error'] ?? 'Gagal membuat Purchase Order.');
        }

        return redirect()->route('staff_admin.dashboard', ['tab' => 'penerimaan_barang'])
            ->with('success', 'Purchase Order berhasil dibuat.');
    }

    /**
     * Record goods receipt.
     */
    public function recordGoodsReceipt(Request $request, $id)
    {
        $request->validate([
            'qty_received' => 'required|integer|min:1',
            'received_date' => 'required|date',
        ]);

        $res = $this->apiCall('POST', "/api/po/goods-receipt/{$id}", $request->only('qty_received', 'received_date'));

        if ($res->failed()) {
            return redirect()->route('staff_admin.dashboard', ['tab' => 'penerimaan_barang'])
                ->with('error', $res->json()['error'] ?? 'Gagal mencatat penerimaan barang.');
        }

        return redirect()->route('staff_admin.dashboard', ['tab' => 'penerimaan_barang'])
            ->with('success', $res->json()['message'] ?? 'Penerimaan barang berhasil dicatat.');
    }
}
