<?php

namespace App\Http\Controllers;

use App\Models\BhpItem;
use App\Models\InventoryItem;
use App\Models\MaintenanceLog;
use App\Models\Room;
use Illuminate\Http\Request;

class StaffLabController extends Controller
{
    /**
     * Dashboard - Ringkasan
     */
    public function dashboard()
    {
        $totalAssets = InventoryItem::count();
        $bhpLow = BhpItem::whereColumn('stock', '<=', 'min_stock')->count();
        $maintenanceDue = MaintenanceLog::whereDate('maintenance_date', '>=', now()->startOfWeek())
            ->whereDate('maintenance_date', '<=', now()->endOfWeek())
            ->count();
        $bhpItems = BhpItem::with('room')->get();
        $inventoryItems = InventoryItem::with('room')->get();

        return view('dashboard.staff_lab', compact(
            'totalAssets',
            'bhpLow',
            'maintenanceDue',
            'bhpItems',
            'inventoryItems'
        ));
    }

    /**
     * Update BHP stock.
     */
    public function updateBhpStock(Request $request, BhpItem $bhpItem)
    {
        $request->validate([
            'stock' => 'required|integer|min:0',
        ]);

        $bhpItem->update(['stock' => $request->stock]);

        return redirect()->route('staff_lab.dashboard', ['tab' => 'kelola_stok'])
            ->with('success', 'Stok ' . $bhpItem->name . ' berhasil diperbarui menjadi ' . $request->stock . ' ' . $bhpItem->unit . '.');
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

        BhpItem::create([
            'name' => $request->name,
            'stock' => $request->stock,
            'unit' => $request->unit,
            'min_stock' => $request->min_stock ?? 0,
            'room_id' => $request->room_id,
        ]);

        return redirect()->route('staff_lab.dashboard', ['tab' => 'kelola_stok'])
            ->with('success', 'BHP "' . $request->name . '" berhasil ditambahkan.');
    }

    /**
     * Store maintenance log.
     */
    public function storeMaintenanceLog(Request $request)
    {
        $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'maintenance_date' => 'required|date',
            'condition_after' => 'required|in:baik,kurang_baik,rusak_berat',
            'description' => 'nullable|string',
            'uses_bhp' => 'nullable|boolean',
            'bhp_item_id' => 'nullable|required_if:uses_bhp,1|exists:bhp_items,id',
            'bhp_qty_used' => 'nullable|required_if:uses_bhp,1|integer|min:1',
        ]);

        // If using BHP, check and deduct stock
        if ($request->uses_bhp && $request->bhp_item_id) {
            $bhp = BhpItem::findOrFail($request->bhp_item_id);
            if ($bhp->stock < $request->bhp_qty_used) {
                return back()->withErrors(['bhp_qty_used' => 'Stok BHP tidak mencukupi. Sisa: ' . $bhp->stock . ' ' . $bhp->unit])
                    ->withInput();
            }
            $bhp->decrement('stock', $request->bhp_qty_used);
        }

        // Create maintenance log
        MaintenanceLog::create([
            'inventory_item_id' => $request->inventory_item_id,
            'maintenance_date' => $request->maintenance_date,
            'condition_after' => $request->condition_after,
            'description' => $request->description,
            'bhp_item_id' => $request->uses_bhp ? $request->bhp_item_id : null,
            'bhp_qty_used' => $request->uses_bhp ? $request->bhp_qty_used : 0,
            'user_id' => auth()->id(),
        ]);

        // Update inventory item condition
        $inventoryItem = InventoryItem::findOrFail($request->inventory_item_id);
        $inventoryItem->update(['condition' => $request->condition_after]);

        return redirect()->route('staff_lab.dashboard', ['tab' => 'log_maintenance'])
            ->with('success', 'Log pemeliharaan untuk "' . $inventoryItem->name . '" berhasil disimpan.');
    }
}
