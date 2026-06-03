<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\ProcurementDraft;
use App\Models\ProcurementDraftItem;
use App\Models\Room;
use Illuminate\Http\Request;

class KaprodiController extends Controller
{
    /**
     * Dashboard - Ringkasan
     */
    public function dashboard()
    {
        $totalAssetValue = InventoryItem::sum('price');
        $roomCount = Room::count();
        $pendingDrafts = ProcurementDraft::where('status', 'draft')
            ->whereHas('items', function ($q) {
                $q->where('review_status', 'pending');
            })
            ->count();

        $drafts = ProcurementDraft::with(['items.replacesInventory', 'room', 'user'])
            ->orderByDesc('created_at')
            ->get();

        return view('dashboard.kaprodi', compact(
            'totalAssetValue',
            'roomCount',
            'pendingDrafts',
            'drafts'
        ));
    }

    /**
     * Approve a draft item.
     */
    public function approveItem(ProcurementDraftItem $item)
    {
        if ($item->draft->status === 'locked') {
            abort(403, 'Draf sudah dikunci.');
        }

        $item->update(['review_status' => 'approved']);

        return redirect()->route('kaprodi.dashboard', ['tab' => 'review_draf'])
            ->with('success', 'Item "' . $item->name . '" berhasil disetujui.');
    }

    /**
     * Reject a draft item.
     */
    public function rejectItem(ProcurementDraftItem $item)
    {
        if ($item->draft->status === 'locked') {
            abort(403, 'Draf sudah dikunci.');
        }

        $item->update(['review_status' => 'rejected']);

        return redirect()->route('kaprodi.dashboard', ['tab' => 'review_draf'])
            ->with('success', 'Item "' . $item->name . '" ditolak.');
    }

    /**
     * Finalize (lock) a draft.
     */
    public function finalizeDraft(ProcurementDraft $draft)
    {
        if ($draft->status === 'locked') {
            return redirect()->route('kaprodi.dashboard', ['tab' => 'finalisasi'])
                ->with('error', 'Draf sudah dikunci sebelumnya.');
        }

        $draft->update(['status' => 'locked']);

        return redirect()->route('kaprodi.dashboard', ['tab' => 'finalisasi'])
            ->with('success', 'Draf ' . $draft->draft_number . ' berhasil difinalisasi dan dikunci. Item yang disetujui akan diteruskan ke Staf Administrasi.');
    }
}
