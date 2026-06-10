<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\BhpItem;
use App\Models\InventoryConditionLog;
use App\Models\InventoryItem;
use App\Models\BhpTransaction;
use App\Models\MaintenanceLog;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StaffLabController extends Controller
{
    /**
     * Dashboard - Ringkasan
     */
    public function dashboard()
    {
        $totalAssets = InventoryItem::active()->count();
        $bhpLow = BhpItem::whereColumn('stock', '<=', 'min_stock')->count();
        $maintenanceDue = MaintenanceLog::whereDate('maintenance_date', '>=', now()->startOfWeek())
            ->whereDate('maintenance_date', '<=', now()->endOfWeek())
            ->count();

        $bhpItems = BhpItem::with('room')->get();
        $inventoryItems = InventoryItem::with('room')->active()->get();
        $rooms = Room::all();

        // Recent BHP transactions
        $recentTransactions = BhpTransaction::with(['bhpItem', 'user'])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        // Recent condition logs
        $conditionLogs = InventoryConditionLog::with(['inventoryItem', 'user'])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

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

        $batchId = 'BULK-' . time() . '-' . Str::random(6);
        $errors = [];

        foreach ($request->items as $index => $entry) {
            $bhp = BhpItem::find($entry['bhp_item_id']);
            if (!$bhp) {
                $errors[] = "Item #{$index}: BHP tidak ditemukan.";
                continue;
            }
            if ($bhp->stock < $entry['quantity']) {
                $errors[] = "Item #{$index}: Stok \"{$bhp->name}\" tidak cukup (sisa: {$bhp->stock} {$bhp->unit}).";
                continue;
            }

            $stockBefore = $bhp->stock;
            $bhp->decrement('stock', $entry['quantity']);

            BhpTransaction::create([
                'bhp_item_id' => $bhp->id,
                'type' => 'out',
                'quantity' => $entry['quantity'],
                'stock_before' => $stockBefore,
                'stock_after' => $bhp->stock,
                'description' => $entry['description'] ?? 'Pemakaian bulk',
                'batch_id' => $batchId,
                'user_id' => auth()->id(),
            ]);
        }

        if (!empty($errors)) {
            return back()->withErrors($errors)->withInput();
        }

        AuditLog::record(new BhpTransaction(), 'bulk_usage', [], [
            'batch_id' => $batchId,
            'count' => count($request->items),
        ]);

        return redirect()->route('staff_lab.dashboard', ['tab' => 'bulk_bhp'])
            ->with('success', count($request->items) . ' item BHP berhasil dicatat pemakaiannya (Batch: ' . $batchId . ').');
    }

    /**
     * Update BHP stock.
     */
    public function updateBhpStock(Request $request, BhpItem $bhpItem)
    {
        $request->validate([
            'stock' => 'required|integer|min:0',
        ]);

        $oldStock = $bhpItem->stock;
        $newStock = $request->stock;
        $type = $newStock >= $oldStock ? 'in' : 'out';
        $qty = abs($newStock - $oldStock);

        $bhpItem->update(['stock' => $newStock]);

        if ($qty > 0) {
            BhpTransaction::create([
                'bhp_item_id' => $bhpItem->id,
                'type' => $type,
                'quantity' => $qty,
                'stock_before' => $oldStock,
                'stock_after' => $newStock,
                'description' => 'Update manual stok',
                'user_id' => auth()->id(),
            ]);
        }

        return redirect()->route('staff_lab.dashboard', ['tab' => 'kelola_stok'])
            ->with('success', 'Stok ' . $bhpItem->name . ' diperbarui: ' . $oldStock . ' → ' . $newStock . ' ' . $bhpItem->unit . '.');
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

        $bhp = BhpItem::create([
            'name' => $request->name,
            'stock' => $request->stock,
            'unit' => $request->unit,
            'min_stock' => $request->min_stock ?? 0,
            'room_id' => $request->room_id,
        ]);

        if ($request->stock > 0) {
            BhpTransaction::create([
                'bhp_item_id' => $bhp->id,
                'type' => 'in',
                'quantity' => $request->stock,
                'stock_before' => 0,
                'stock_after' => $request->stock,
                'description' => 'Stok awal',
                'user_id' => auth()->id(),
            ]);
        }

        AuditLog::record($bhp, 'created', [], $bhp->toArray());

        return redirect()->route('staff_lab.dashboard', ['tab' => 'kelola_stok'])
            ->with('success', 'BHP "' . $request->name . '" berhasil ditambahkan.');
    }

    /**
     * Update inventory condition (Staff Lab).
     * If rusak_berat: old ID kept as history, new replacement item created.
     */
    public function updateCondition(Request $request, InventoryItem $item)
    {
        $request->validate([
            'condition' => 'required|in:baik,kurang_baik,rusak_ringan,rusak_berat',
            'description' => 'nullable|string|max:500',
        ]);

        $oldCondition = $item->condition;

        // Log condition change
        InventoryConditionLog::create([
            'inventory_item_id' => $item->id,
            'condition_before' => $oldCondition,
            'condition_after' => $request->condition,
            'description' => $request->description,
            'user_id' => auth()->id(),
        ]);

        $item->update(['condition' => $request->condition]);

        $message = 'Kondisi "' . $item->name . '" diperbarui: ' . $oldCondition . ' → ' . $request->condition . '.';

        // If rusak_berat: create replacement
        if ($request->condition === 'rusak_berat') {
            $item->update(['status' => 'inactive']);

            $newItem = InventoryItem::create([
                'label_code' => $item->label_code ? $item->label_code . '-R' : null,
                'name' => $item->name,
                'category' => $item->category ?? 'inventaris',
                'condition' => 'baik',
                'room_id' => $item->room_id,
                'item_type_id' => $item->item_type_id,
                'price' => $item->price,
                'status' => 'active',
                'approval_status' => 'pending',
                'replaced_from' => $item->id,
                'is_labeled' => false,
            ]);

            $item->update(['replaced_by' => $newItem->id]);

            $message = 'Barang ditandai rusak berat. ID lama (#' . $item->id . ') disimpan sebagai histori. Barang pengganti baru (#' . $newItem->id . ') dibuat.';

            AuditLog::record($item, 'condition_rusak_berat', ['condition' => $oldCondition], [
                'condition' => 'rusak_berat',
                'replacement_id' => $newItem->id,
            ]);
        }

        return redirect()->route('staff_lab.dashboard', ['tab' => 'kondisi_barang'])
            ->with('success', $message);
    }

    /**
     * Store maintenance log.
     */
    public function storeMaintenanceLog(Request $request)
    {
        $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'maintenance_date' => 'required|date',
            'condition_after' => 'required|in:baik,kurang_baik,rusak_ringan,rusak_berat',
            'description' => 'nullable|string',
            'uses_bhp' => 'nullable|boolean',
            'bhp_item_id' => 'nullable|required_if:uses_bhp,1|exists:bhp_items,id',
            'bhp_qty_used' => 'nullable|required_if:uses_bhp,1|integer|min:1',
        ]);

        if ($request->uses_bhp && $request->bhp_item_id) {
            $bhp = BhpItem::findOrFail($request->bhp_item_id);
            if ($bhp->stock < $request->bhp_qty_used) {
                return back()->withErrors(['bhp_qty_used' => 'Stok BHP tidak mencukupi. Sisa: ' . $bhp->stock . ' ' . $bhp->unit])
                    ->withInput();
            }

            $stockBefore = $bhp->stock;
            $bhp->decrement('stock', $request->bhp_qty_used);

            BhpTransaction::create([
                'bhp_item_id' => $bhp->id,
                'type' => 'out',
                'quantity' => $request->bhp_qty_used,
                'stock_before' => $stockBefore,
                'stock_after' => $bhp->stock,
                'description' => 'Maintenance: ' . ($request->description ?? 'Pemeliharaan'),
                'user_id' => auth()->id(),
            ]);
        }

        MaintenanceLog::create([
            'inventory_item_id' => $request->inventory_item_id,
            'maintenance_date' => $request->maintenance_date,
            'condition_after' => $request->condition_after,
            'description' => $request->description,
            'bhp_item_id' => $request->uses_bhp ? $request->bhp_item_id : null,
            'bhp_qty_used' => $request->uses_bhp ? $request->bhp_qty_used : 0,
            'user_id' => auth()->id(),
        ]);

        $inventoryItem = InventoryItem::findOrFail($request->inventory_item_id);
        $inventoryItem->update(['condition' => $request->condition_after]);

        return redirect()->route('staff_lab.dashboard', ['tab' => 'log_maintenance'])
            ->with('success', 'Log pemeliharaan untuk "' . $inventoryItem->name . '" berhasil disimpan.');
    }
}
