<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\InventoryItem;
use App\Models\Room;
use App\Models\User;
use App\Models\InventoryConditionLog;
use App\Models\MaintenanceLog;
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
        $pendingRes = $this->apiCall('GET', '/api/approval/pending');
        $pendingItems = collect($pendingRes->json()['data'] ?? [])->map(function($data) {
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

        // Recently approved/rejected
        $historyRes = $this->apiCall('GET', '/api/approval/history');
        $reviewedItems = collect($historyRes->json()['data'] ?? [])->map(function($data) {
            $item = $this->hydrateModel(InventoryItem::class, $data);
            if (!empty($data['room_name'])) {
                $item->setRelation('room', $this->hydrateModel(Room::class, [
                    'id' => $data['room_id'],
                    'name' => $data['room_name']
                ]));
            }
            if (!empty($data['approved_by_name'])) {
                $item->setRelation('approver', $this->hydrateModel(User::class, [
                    'id' => $data['approved_by'],
                    'name' => $data['approved_by_name']
                ]));
            }
            return $item;
        });

        // Kepala Lab users list
        $usersRes = $this->apiCall('GET', '/api/users');
        $kepalaLabs = collect($usersRes->json()['data'] ?? [])
            ->filter(fn($u) => $u['role'] === 'kepala_lab')
            ->map(fn($data) => $this->hydrateModel(User::class, $data));

        // Stats
        $stats = $this->apiCall('GET', '/api/stats/kaprodi')->json();
        $pendingCount = $stats['pendingCount'] ?? 0;
        $approvedCount = $stats['approvedCount'] ?? 0;
        $rejectedCount = $stats['rejectedCount'] ?? 0;
        $totalAssetValue = $stats['totalAssetValue'] ?? 0;
        $roomCount = $stats['roomCount'] ?? 0;

        return view('dashboard.kaprodi', compact(
            'pendingItems', 'reviewedItems', 'kepalaLabs',
            'pendingCount', 'approvedCount', 'rejectedCount',
            'totalAssetValue', 'roomCount'
        ));
    }

    /**
     * Show detail page for a specific item (before approval).
     */
    public function showItemDetail($id)
    {
        $res = $this->apiCall('GET', "/api/inventory/{$id}");
        if ($res->failed()) {
            abort(404);
        }
        $body = $res->json();
        $data = $body['data'];
        $item = $this->hydrateModel(InventoryItem::class, $data);
        if (!empty($data['room_name'])) {
            $item->setRelation('room', $this->hydrateModel(Room::class, [
                'id' => $data['room_id'],
                'name' => $data['room_name'],
                'code' => $data['room_code']
            ]));
        }
        if (!empty($body['condition_logs'])) {
            $logs = collect($body['condition_logs'])->map(function($log) {
                $l = $this->hydrateModel(InventoryConditionLog::class, $log);
                if (!empty($log['user_name'])) {
                    $l->setRelation('user', $this->hydrateModel(User::class, ['id' => $log['user_id'], 'name' => $log['user_name']]));
                }
                return $l;
            });
            $item->setRelation('conditionLogs', $logs);
        }
        if (!empty($body['maintenance_logs'])) {
            $logs = collect($body['maintenance_logs'])->map(function($log) {
                $l = $this->hydrateModel(MaintenanceLog::class, $log);
                if (!empty($log['user_name'])) {
                    $l->setRelation('user', $this->hydrateModel(User::class, ['id' => $log['user_id'], 'name' => $log['user_name']]));
                }
                return $l;
            });
            $item->setRelation('maintenanceLogs', $logs);
        }

        return view('dashboard.kaprodi_detail', compact('item'));
    }

    /**
     * Approve an inventory item — LOCKS it permanently.
     */
    public function approveItem($id)
    {
        $response = $this->apiCall('POST', "/api/approval/{$id}/approve");
        if ($response->failed()) {
            return redirect()->route('kaprodi.dashboard', ['tab' => 'review'])
                ->with('error', $response->json()['error'] ?? 'Gagal menyetujui item.');
        }

        return redirect()->route('kaprodi.dashboard', ['tab' => 'review'])
            ->with('success', 'Item berhasil disetujui.');
    }

    /**
     * Reject an inventory item.
     */
    public function rejectItem(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $response = $this->apiCall('POST', "/api/approval/{$id}/reject", [
            'reason' => $request->rejection_reason
        ]);

        if ($response->failed()) {
            return redirect()->route('kaprodi.dashboard', ['tab' => 'review'])
                ->with('error', $response->json()['error'] ?? 'Gagal menolak item.');
        }

        return redirect()->route('kaprodi.dashboard', ['tab' => 'review'])
            ->with('success', 'Item ditolak.');
    }

    /**
     * Delete Kepala Lab user.
     */
    public function deleteKepalaLab($id)
    {
        $response = $this->apiCall('DELETE', "/api/approval/users/{$id}");
        if ($response->failed()) {
            return redirect()->route('kaprodi.dashboard', ['tab' => 'kepala_lab'])
                ->with('error', $response->json()['error'] ?? 'Gagal menghapus Kepala Lab.');
        }

        return redirect()->route('kaprodi.dashboard', ['tab' => 'kepala_lab'])
            ->with('success', 'Kepala Lab berhasil dihapus.');
    }
}
