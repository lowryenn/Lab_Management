<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\BhpItem;
use App\Models\InventoryItem;
use App\Models\Room;
use Illuminate\Http\Request;

class KepalaLabController extends Controller
{
    /**
     * Dashboard — Inventory access with edit (if not locked).
     */
    public function dashboard()
    {
        // Inventory items (separated: Inventaris & BHP)
        $inventarisItems = InventoryItem::with(['room', 'approver'])
            ->inventaris()
            ->active()
            ->orderByDesc('created_at')
            ->get();

        $bhpCategoryItems = InventoryItem::with(['room', 'approver'])
            ->bhpCategory()
            ->active()
            ->orderByDesc('created_at')
            ->get();

        // Room-based inventory counts (auto-counted via COUNT)
        $rooms = Room::withCount([
            'inventoryItems as total_items' => function ($q) {
                $q->where('status', 'active');
            },
            'inventoryItems as items_baik' => function ($q) {
                $q->where('status', 'active')->where('condition', 'baik');
            },
            'inventoryItems as items_rusak' => function ($q) {
                $q->where('status', 'active')->where('condition', '!=', 'baik');
            },
        ])->get();

        // Stats
        $totalInventory = InventoryItem::active()->count();
        $totalAssetValue = InventoryItem::active()->sum('price');
        $needsRepair = InventoryItem::active()->where('condition', '!=', 'baik')->count();
        $pendingApproval = InventoryItem::where('approval_status', 'pending')->count();
        $lockedCount = InventoryItem::where('approval_status', 'approved')->count();

        return view('dashboard.kepala_lab', compact(
            'inventarisItems', 'bhpCategoryItems', 'rooms',
            'totalInventory', 'totalAssetValue', 'needsRepair',
            'pendingApproval', 'lockedCount'
        ));
    }

    /**
     * Update inventory item (only if NOT locked/approved).
     */
    public function updateItem(Request $request, InventoryItem $item)
    {
        // Check lock status
        if ($item->isLocked()) {
            return redirect()->route('kepala_lab.dashboard', ['tab' => 'inventaris'])
                ->with('error', 'Data "' . $item->name . '" sudah disetujui Kaprodi dan tidak bisa diubah (LOCKED).');
        }

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

        $oldValues = $item->toArray();

        $item->update($request->only([
            'name', 'label_code', 'category', 'description', 'brand',
            'condition', 'room_id', 'price', 'purchase_date',
        ]));

        AuditLog::record($item, 'updated', $oldValues, $item->fresh()->toArray());

        return redirect()->route('kepala_lab.dashboard', ['tab' => $request->category === 'bhp' ? 'bhp' : 'inventaris'])
            ->with('success', 'Data "' . $item->name . '" berhasil diperbarui.');
    }

    /**
     * Add new inventory item.
     */
    public function storeItem(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'label_code' => 'nullable|string|max:255|unique:inventory_items,label_code',
            'category' => 'required|in:inventaris,bhp',
            'description' => 'nullable|string|max:500',
            'brand' => 'nullable|string|max:255',
            'room_id' => 'nullable|exists:rooms,id',
            'price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
        ]);

        $item = InventoryItem::create([
            'name' => $request->name,
            'label_code' => $request->label_code,
            'category' => $request->category,
            'description' => $request->description,
            'brand' => $request->brand,
            'condition' => 'baik',
            'room_id' => $request->room_id,
            'price' => $request->price ?? 0,
            'purchase_date' => $request->purchase_date,
            'status' => 'active',
            'approval_status' => 'pending',
            'is_labeled' => (bool) $request->label_code,
        ]);

        AuditLog::record($item, 'created', [], $item->toArray());

        return redirect()->route('kepala_lab.dashboard', ['tab' => $request->category === 'bhp' ? 'bhp' : 'inventaris'])
            ->with('success', 'Item "' . $item->name . '" berhasil ditambahkan dan menunggu review Kaprodi.');
    }
}
