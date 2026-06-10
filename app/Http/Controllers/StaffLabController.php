<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\BhpItem;
use App\Models\InventoryConditionLog;
use App\Models\InventoryItem;
use App\Models\BhpTransaction;
use App\Models\MaintenanceLog;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StaffLabController extends Controller
{
    /**
     * Dashboard - Ringkasan
     */
    public function dashboard()
    {
        // 1. Fetch Stats
        $statsRes = $this->apiCall('GET', '/api/stats/staff_lab');
        $stats = $statsRes->json();
        $totalAssets = $stats['totalAssets'] ?? 0;
        $bhpLow = $stats['bhpLow'] ?? 0;
        $maintenanceDue = $stats['maintenanceDue'] ?? 0;

        // 2. Fetch BHP items
        $bhpRes = $this->apiCall('GET', '/api/bhp');
        $bhpItems = collect($bhpRes->json()['data'] ?? [])->map(function($data) {
            $item = $this->hydrateModel(BhpItem::class, $data);
            if (!empty($data['room_name'])) {
                $item->setRelation('room', $this->hydrateModel(Room::class, [
                    'id' => $data['room_id'],
                    'name' => $data['room_name'],
                    'code' => $data['room_code']
                ]));
            }
            return $item;
        });

        // 3. Fetch Inventory items
        $invRes = $this->apiCall('GET', '/api/inventory', ['status' => 'active', 'limit' => 1000]);
        $inventoryItems = collect($invRes->json()['data'] ?? [])->map(function($data) {
            $item = $this->hydrateModel(InventoryItem::class, $data);
            if (!empty($data['room_name'])) {
                $item->setRelation('room', $this->hydrateModel(Room::class, [
                    'id' => $data['room_id'],
                    'name' => $data['room_name'],
                    'code' => $data['room_code']
                ]));
            }
            return $item;
        });

        // 4. Fetch Rooms
        $roomsRes = $this->apiCall('GET', '/api/rooms');
        $rooms = collect($roomsRes->json()['data'] ?? [])->map(fn($data) => $this->hydrateModel(Room::class, $data));

        // 5. Fetch Recent BHP transactions
        $txRes = $this->apiCall('GET', '/api/bhp/transactions/recent');
        $recentTransactions = collect($txRes->json()['data'] ?? [])->map(function($data) {
            $tx = $this->hydrateModel(BhpTransaction::class, $data);
            $tx->setRelation('bhpItem', $this->hydrateModel(BhpItem::class, [
                'id' => $data['bhp_item_id'],
                'name' => $data['bhp_item_name'],
                'unit' => $data['bhp_item_unit']
            ]));
            $tx->setRelation('user', $this->hydrateModel(User::class, [
                'id' => $data['user_id'],
                'name' => $data['user_name']
            ]));
            return $tx;
        });

        // 6. Fetch Recent condition logs
        $logsRes = $this->apiCall('GET', '/api/condition/logs/recent');
        $conditionLogs = collect($logsRes->json()['data'] ?? [])->map(function($data) {
            $log = $this->hydrateModel(InventoryConditionLog::class, $data);
            $log->setRelation('inventoryItem', $this->hydrateModel(InventoryItem::class, [
                'id' => $data['inventory_item_id'],
                'name' => $data['item_name']
            ]));
            $log->setRelation('user', $this->hydrateModel(User::class, [
                'id' => $data['user_id'],
                'name' => $data['user_name']
            ]));
            return $log;
        });

        return view('dashboard.staff_lab', compact(
            'totalAssets', 'bhpLow', 'maintenanceDue',
            'bhpItems', 'inventoryItems', 'rooms',
            'recentTransactions', 'conditionLogs'
        ));
    }

    /**
     * Bulk BHP usage — multiple items at once.
     */
    public function bulkBhpUsage(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.bhp_item_id' => 'required|exists:bhp_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.description' => 'nullable|string|max:255',
        ]);

        $res = $this->apiCall('POST', '/api/bhp/bulk-usage', ['items' => $request->items]);

        if ($res->failed()) {
            return back()->withErrors([$res->json()['error'] ?? 'Gagal mencatat pemakaian BHP.'])->withInput();
        }

        return redirect()->route('staff_lab.dashboard', ['tab' => 'bulk_bhp'])
            ->with('success', $res->json()['message']);
    }

    /**
     * Update BHP stock.
     */
    public function updateBhpStock(Request $request, $id)
    {
        $request->validate([
            'stock' => 'required|integer|min:0',
        ]);

        // Fetch old stock to calculate diff
        $bhpRes = $this->apiCall('GET', "/api/bhp/{$id}");
        if ($bhpRes->failed()) {
            return back()->with('error', 'BHP tidak ditemukan.');
        }

        $bhp = $bhpRes->json()['data'];
        $oldStock = $bhp['stock'];
        $newStock = $request->stock;
        $qty = abs($newStock - $oldStock);

        if ($qty > 0) {
            $res = $this->apiCall('PATCH', "/api/bhp/{$id}/stock", [
                'type' => $newStock >= $oldStock ? 'in' : 'out',
                'quantity' => $qty,
                'description' => 'Update manual stok'
            ]);

            if ($res->failed()) {
                return back()->with('error', $res->json()['error'] ?? 'Gagal memperbarui stok.');
            }
        }

        return redirect()->route('staff_lab.dashboard', ['tab' => 'kelola_stok'])
            ->with('success', 'Stok berhasil diperbarui.');
    }

    /**
     * Store new BHP item.
     */
    public function storeBhp(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'stock' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'min_stock' => 'nullable|integer|min:0',
            'room_id' => 'nullable|exists:rooms,id',
        ]);

        $res = $this->apiCall('POST', '/api/bhp', $request->all());

        if ($res->failed()) {
            return back()->withErrors([$res->json()['error'] ?? 'Gagal menambahkan BHP.'])->withInput();
        }

        return redirect()->route('staff_lab.dashboard', ['tab' => 'kelola_stok'])
            ->with('success', 'BHP berhasil ditambahkan.');
    }

    /**
     * Update inventory condition (Staff Lab).
     * If rusak_berat: old ID kept as history, new replacement item created.
     */
    public function updateCondition(Request $request, $id)
    {
        $request->validate([
            'condition' => 'required|in:baik,kurang_baik,rusak_ringan,rusak_berat',
            'description' => 'nullable|string|max:500',
        ]);

        $res = $this->apiCall('POST', "/api/condition/{$id}/update", $request->only('condition', 'description'));

        if ($res->failed()) {
            return redirect()->route('staff_lab.dashboard', ['tab' => 'kondisi_barang'])
                ->with('error', $res->json()['error'] ?? 'Gagal memperbarui kondisi barang.');
        }

        return redirect()->route('staff_lab.dashboard', ['tab' => 'kondisi_barang'])
            ->with('success', $res->json()['message']);
    }

    /**
     * Store maintenance log.
     */
    public function storeMaintenanceLog(Request $request)
    {
        $request->validate([
            'inventory_item_id' => 'required',
            'maintenance_date' => 'required|date',
            'condition_after' => 'required|in:baik,kurang_baik,rusak_ringan,rusak_berat',
            'description' => 'nullable|string',
            'uses_bhp' => 'nullable|boolean',
            'bhp_item_id' => 'nullable|required_if:uses_bhp,1',
            'bhp_qty_used' => 'nullable|required_if:uses_bhp,1|integer|min:1',
        ]);

        $res = $this->apiCall('POST', '/api/condition/maintenance', $request->all());

        if ($res->failed()) {
            return back()->withErrors(['bhp_qty_used' => $res->json()['error'] ?? 'Gagal mencatat pemeliharaan.'])->withInput();
        }

        return redirect()->route('staff_lab.dashboard', ['tab' => 'kondisi_barang'])
            ->with('success', $res->json()['message']);
    }
}
