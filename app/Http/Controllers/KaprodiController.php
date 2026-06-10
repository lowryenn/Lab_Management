<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\InventoryItem;
use App\Models\Room;
use Illuminate\Http\Request;

class KaprodiController extends Controller
{
    /**
     * Dashboard - Review & Approval only.
     * Kaprodi focuses ONLY on reviewing and approving inventory data.
     */
    public function dashboard()
    {
        // Pending items awaiting review
        $pendingItems = InventoryItem::with(['room', 'itemType'])
            ->where('approval_status', 'pending')
            ->active()
            ->orderByDesc('created_at')
            ->get();

        // Recently approved/rejected
        $reviewedItems = InventoryItem::with(['room', 'approver'])
            ->whereIn('approval_status', ['approved', 'rejected'])
            ->orderByDesc('approved_at')
            ->limit(50)
            ->get();

        // Stats
        $pendingCount = $pendingItems->count();
        $approvedCount = InventoryItem::where('approval_status', 'approved')->count();
        $rejectedCount = InventoryItem::where('approval_status', 'rejected')->count();
        $totalAssetValue = InventoryItem::where('approval_status', 'approved')->sum('price');
        $roomCount = Room::count();

        return view('dashboard.kaprodi', compact(
            'pendingItems', 'reviewedItems',
            'pendingCount', 'approvedCount', 'rejectedCount',
            'totalAssetValue', 'roomCount'
        ));
    }

    /**
     * Show detail page for a specific item (before approval).
     */
    public function showItemDetail(InventoryItem $item)
    {
        $item->load(['room', 'itemType', 'conditionLogs.user', 'maintenanceLogs.user', 'approver']);

        return view('dashboard.kaprodi_detail', compact('item'));
    }

    /**
     * Approve an inventory item — LOCKS it permanently.
     */
    public function approveItem(InventoryItem $item)
    {
        if ($item->approval_status === 'approved') {
            return redirect()->route('kaprodi.dashboard', ['tab' => 'review'])
                ->with('error', 'Item sudah disetujui sebelumnya.');
        }

        $oldStatus = $item->approval_status;

        $item->update([
            'approval_status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        AuditLog::record($item, 'approved', [
            'approval_status' => $oldStatus,
        ], [
            'approval_status' => 'approved',
            'approved_by' => auth()->id(),
        ]);

        return redirect()->route('kaprodi.dashboard', ['tab' => 'review'])
            ->with('success', 'Item "' . $item->name . '" berhasil disetujui dan dikunci (LOCKED). Data tidak bisa diubah lagi.');
    }

    /**
     * Reject an inventory item.
     */
    public function rejectItem(Request $request, InventoryItem $item)
    {
        if ($item->approval_status === 'approved') {
            return redirect()->route('kaprodi.dashboard', ['tab' => 'review'])
                ->with('error', 'Item sudah disetujui dan tidak bisa ditolak.');
        }

        $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $item->update([
            'approval_status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        AuditLog::record($item, 'rejected', [], [
            'approval_status' => 'rejected',
            'reason' => $request->rejection_reason,
        ]);

        return redirect()->route('kaprodi.dashboard', ['tab' => 'review'])
            ->with('success', 'Item "' . $item->name . '" ditolak.');
    }
}
