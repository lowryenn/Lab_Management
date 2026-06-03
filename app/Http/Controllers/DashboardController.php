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
        $totalUsers = User::count();
        $totalRooms = Room::count();
        $totalInventory = InventoryItem::count();

        return view('dashboard.admin', compact('totalUsers', 'totalRooms', 'totalInventory'));
    }
}
