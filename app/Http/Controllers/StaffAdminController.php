<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\InventoryItem;
use App\Models\ProcurementDraftItem;
use App\Models\PurchaseOrder;
use App\Models\Room;
use Illuminate\Http\Request;

class StaffAdminController extends Controller
{
    /**
     * Dashboard - Ringkasan
     */
    public function dashboard()
    {
        $activeProcurements = PurchaseOrder::where('status', '!=', 'completed')->count();
        $pendingDocuments = ProcurementDraftItem::where('review_status', 'approved')
            ->doesntHave('purchaseOrder')
            ->count();

        $approvedItems = ProcurementDraftItem::with(['draft.room'])
            ->where('review_status', 'approved')
            ->whereHas('draft', function ($q) {
                $q->where('status', 'locked');
            })
            ->doesntHave('purchaseOrder')
            ->get();

        $purchaseOrders = PurchaseOrder::with(['draftItem.draft.room', 'goodsReceipts'])
            ->where('status', '!=', 'completed')
            ->orderByDesc('created_at')
            ->get();

        $unlabledItems = GoodsReceipt::with(['purchaseOrder.draftItem'])
            ->whereHas('purchaseOrder', function ($q) {
                $q->where('total_received', '>', 0);
            })
            ->orderByDesc('received_date')
            ->get();

        $rooms = Room::all();

        return view('dashboard.staff_admin', compact(
            'activeProcurements',
            'pendingDocuments',
            'approvedItems',
            'purchaseOrders',
            'unlabledItems',
            'rooms'
        ));
    }

    /**
     * Create Purchase Order from approved draft item.
     */
    public function createPurchaseOrder(ProcurementDraftItem $draftItem)
    {
        // Must be approved and from locked draft
        if ($draftItem->review_status !== 'approved' || $draftItem->draft->status !== 'locked') {
            abort(403, 'Item belum disetujui atau draf belum dikunci.');
        }

        // Don't create duplicate PO
        if ($draftItem->purchaseOrder) {
            return redirect()->route('staff_admin.dashboard', ['tab' => 'penerimaan_barang'])
                ->with('error', 'PO sudah dibuat untuk item ini.');
        }

        PurchaseOrder::create([
            'po_number' => PurchaseOrder::generatePoNumber(),
            'procurement_draft_item_id' => $draftItem->id,
            'status' => 'ordered',
            'total_ordered' => $draftItem->quantity,
            'total_received' => 0,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('staff_admin.dashboard', ['tab' => 'penerimaan_barang'])
            ->with('success', 'Purchase Order berhasil dibuat untuk "' . $draftItem->name . '".');
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

        // Update PO totals
        $purchaseOrder->increment('total_received', $request->qty_received);

        // Update PO status
        if ($purchaseOrder->total_received >= $purchaseOrder->total_ordered) {
            $purchaseOrder->update(['status' => 'completed']);
        } else {
            $purchaseOrder->update(['status' => 'partial']);
        }

        return redirect()->route('staff_admin.dashboard', ['tab' => 'penerimaan_barang'])
            ->with('success', $request->qty_received . ' unit berhasil dicatat diterima.');
    }

    /**
     * Register received goods into inventory.
     */
    public function registerInventory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'label_code' => 'required|string|max:255|unique:inventory_items,label_code',
            'room_id' => 'required|exists:rooms,id',
            'price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'photo' => 'nullable|image|max:5120',
        ]);

        $data = [
            'name' => $request->name,
            'label_code' => $request->label_code,
            'room_id' => $request->room_id,
            'price' => $request->price ?? 0,
            'purchase_date' => $request->purchase_date,
            'condition' => 'baik',
            'is_labeled' => true,
        ];

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('inventory_photos', 'public');
        }

        InventoryItem::create($data);

        return redirect()->route('staff_admin.dashboard', ['tab' => 'update_inventaris'])
            ->with('success', 'Data inventaris "' . $request->name . '" dengan label ' . $request->label_code . ' berhasil disimpan.');
    }
}
