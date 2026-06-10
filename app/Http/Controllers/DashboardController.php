<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function admin()
    {
        $response = $this->apiCall('GET', '/api/stats/admin');
        $stats = $response->json();

        $totalUsers = $stats['totalUsers'] ?? 0;
        $totalRooms = $stats['totalRooms'] ?? 0;
        $totalInventory = $stats['totalInventory'] ?? 0;

        return view('dashboard.admin', compact('totalUsers', 'totalRooms', 'totalInventory'));
    }
}
