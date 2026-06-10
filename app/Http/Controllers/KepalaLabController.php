<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\BhpItem;
use App\Models\InventoryItem;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;

class KepalaLabController extends Controller
{
    /**
     * Dashboard — Inventory access with edit (if not locked).
     */
    public function dashboard()
    {
        // 1. Fetch Inventaris items
        $invRes = $this->apiCall('GET', '/api/inventory', ['category' => 'inventaris', 'status' => 'active', 'limit' => 500]);
        $inventarisItems = collect($invRes->json()['data'] ?? [])->map(function($data) {
            $item = $this->hydrateModel(InventoryItem::class, $data);
            if (!empty($data['room_name'])) {
                $item->setRelation('room', $this->hydrateModel(Room::class, [
                    'id' => $data['room_id'],
                    'name' => $data['room_name'],
                    'code' => $data['room_code'],
                ]));
            }
            if (!empty($data['approved_by_name'])) {
                $item->setRelation('approver', $this->hydrateModel(User::class, [
                    'id' => $data['approved_by'],
                    'name' => $data['approved_by_name'],
                ]));
            }
            return $item;
        });

        // 2. Fetch BHP items
        $bhpRes = $this->apiCall('GET', '/api/inventory', ['category' => 'bhp', 'status' => 'active', 'limit' => 500]);
        $bhpCategoryItems = collect($bhpRes->json()['data'] ?? [])->map(function($data) {
            $item = $this->hydrateModel(InventoryItem::class, $data);
            if (!empty($data['room_name'])) {
                $item->setRelation('room', $this->hydrateModel(Room::class, [
                    'id' => $data['room_id'],
                    'name' => $data['room_name'],
                    'code' => $data['room_code'],
                ]));
            }
            if (!empty($data['approved_by_name'])) {
                $item->setRelation('approver', $this->hydrateModel(User::class, [
                    'id' => $data['approved_by'],
                    'name' => $data['approved_by_name'],
                ]));
            }
            return $item;
        });

        // 3. Fetch Rooms with breakdown
        $roomsRes = $this->apiCall('GET', '/api/rooms');
        $rooms = collect($roomsRes->json()['data'] ?? [])->map(function($data) {
            $room = $this->hydrateModel(Room::class, $data);
            // set attributes for view compatibility
            $room->total_items = $data['total_inventory'] ?? 0;
            $room->items_baik = $data['inventory_baik'] ?? 0;
            $room->items_rusak = $data['inventory_rusak'] ?? 0;
            
            if (!empty($data['item_breakdown'])) {
                $room->item_breakdown = collect($data['item_breakdown'])->map(fn($b) => (object)$b);
            } else {
                $room->item_breakdown = collect();
            }
            return $room;
        });

        // 4. Fetch Stats
        $stats = $this->apiCall('GET', '/api/stats/kepala_lab')->json();
        $totalInventory = $stats['totalInventory'] ?? 0;
        $totalAssetValue = $stats['totalAssetValue'] ?? 0;
        $needsRepair = $stats['needsRepair'] ?? 0;
        $pendingApproval = $stats['pendingApproval'] ?? 0;
        $lockedCount = $stats['lockedCount'] ?? 0;

        return view('dashboard.kepala_lab', compact(
            'inventarisItems', 'bhpCategoryItems', 'rooms',
            'totalInventory', 'totalAssetValue', 'needsRepair',
            'pendingApproval', 'lockedCount'
        ));
    }

    /**
     * Update inventory item (only if NOT locked/approved).
     */
    public function updateItem(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'label_code' => 'nullable|string|max:255',
            'category' => 'required|in:inventaris,bhp',
            'description' => 'nullable|string|max:500',
            'brand' => 'nullable|string|max:255',
            'condition' => 'required|in:baik,kurang_baik,rusak_ringan,rusak_berat',
            'room_id' => 'nullable|exists:rooms,id',
            'price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
        ]);

        $response = $this->apiCall('PUT', "/api/inventory/{$id}", $request->only([
            'name', 'label_code', 'category', 'description', 'brand',
            'condition', 'room_id', 'price', 'purchase_date',
        ]));

        if ($response->failed()) {
            return redirect()->route('kepala_lab.dashboard', ['tab' => $request->category === 'bhp' ? 'bhp' : 'inventaris'])
                ->with('error', $response->json()['error'] ?? 'Gagal memperbarui data.');
        }

        return redirect()->route('kepala_lab.dashboard', ['tab' => $request->category === 'bhp' ? 'bhp' : 'inventaris'])
            ->with('success', 'Data berhasil diperbarui.');
    }

    /**
     * Add new inventory item.
     */
    public function storeItem(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'label_code' => 'nullable|string|max:255',
            'category' => 'required|in:inventaris,bhp',
            'description' => 'nullable|string|max:500',
            'brand' => 'nullable|string|max:255',
            'room_id' => 'nullable|exists:rooms,id',
            'price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
        ]);

        $response = $this->apiCall('POST', '/api/inventory', $request->all());

        if ($response->failed()) {
            return back()->withErrors(['label_code' => $response->json()['error'] ?? 'Gagal menambahkan item.'])->withInput();
        }

        return redirect()->route('kepala_lab.dashboard', ['tab' => $request->category === 'bhp' ? 'bhp' : 'inventaris'])
            ->with('success', 'Item berhasil ditambahkan.');
    }
}
