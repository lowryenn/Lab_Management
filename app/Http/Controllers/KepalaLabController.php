<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\ProcurementDraft;
use App\Models\ProcurementDraftItem;
use App\Models\Room;
use Illuminate\Http\Request;

class KepalaLabController extends Controller
{
    /**
     * Dashboard - Ringkasan
     */
    public function dashboard()
    {
        $pendingApprovals = ProcurementDraftItem::where('review_status', 'pending')
            ->whereHas('draft', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->count();

        $totalAssetValue = InventoryItem::sum('price');
        $needsRepair = InventoryItem::where('condition', '!=', 'baik')->count();
        $drafts = ProcurementDraft::with(['items', 'room'])
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->get();
        $rooms = Room::all();
        $inventoryItems = InventoryItem::where('condition', '!=', 'baik')->get();

        return view('dashboard.kepala_lab', compact(
            'pendingApprovals',
            'totalAssetValue',
            'needsRepair',
            'drafts',
            'rooms',
            'inventoryItems'
        ));
    }

    /**
     * Store a new procurement draft.
     */
    public function storeDraft(Request $request)
    {
        $request->validate([
            'room_id' => 'nullable|exists:rooms,id',
        ]);

        $draft = ProcurementDraft::create([
            'draft_number' => ProcurementDraft::generateDraftNumber(),
            'room_id' => $request->room_id,
            'user_id' => auth()->id(),
            'status' => 'draft',
        ]);

        return redirect()->route('kepala_lab.dashboard', ['tab' => 'riwayat_draf'])
            ->with('success', 'Draf pengadaan ' . $draft->draft_number . ' berhasil dibuat.');
    }

    /**
     * Add item to a draft.
     */
    public function addDraftItem(Request $request, ProcurementDraft $draft)
    {
        // Only allow adding to own drafts that are still in draft status
        if ($draft->user_id !== auth()->id() || $draft->status !== 'draft') {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'link' => 'nullable|url|max:500',
            'replaces_inventory_id' => 'nullable|exists:inventory_items,id',
        ]);

        ProcurementDraftItem::create([
            'procurement_draft_id' => $draft->id,
            'name' => $request->name,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'link' => $request->link,
            'replaces_inventory_id' => $request->replaces_inventory_id,
        ]);

        return redirect()->route('kepala_lab.dashboard', ['tab' => 'riwayat_draf'])
            ->with('success', 'Item "' . $request->name . '" berhasil ditambahkan ke draf ' . $draft->draft_number . '.');
    }

    /**
     * Delete a draft item.
     */
    public function deleteDraftItem(ProcurementDraftItem $item)
    {
        $draft = $item->draft;
        if ($draft->user_id !== auth()->id() || $draft->status !== 'draft') {
            abort(403);
        }

        $item->delete();

        return redirect()->route('kepala_lab.dashboard', ['tab' => 'riwayat_draf'])
            ->with('success', 'Item berhasil dihapus dari draf.');
    }

    /**
     * Delete an entire draft.
     */
    public function deleteDraft(ProcurementDraft $draft)
    {
        if ($draft->user_id !== auth()->id() || $draft->status !== 'draft') {
            abort(403);
        }

        $draftNumber = $draft->draft_number;
        $draft->delete();

        return redirect()->route('kepala_lab.dashboard', ['tab' => 'riwayat_draf'])
            ->with('success', 'Draf ' . $draftNumber . ' berhasil dihapus.');
    }
}
