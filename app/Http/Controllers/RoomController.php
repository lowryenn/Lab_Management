<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::all();
        return view('admin.rooms.index', compact('rooms'));
    }

    public function create()
    {
        return view('admin.rooms.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:rooms',
            'location' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:0',
        ]);

        Room::create($request->all());

        return redirect()->route('rooms.index')->with('success', 'Ruangan berhasil ditambahkan.');
    }

    public function edit(Room $room)
    {
        return view('admin.rooms.edit', compact('room'));
    }

    public function update(Request $request, Room $room)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:rooms,code,'.$room->id,
            'location' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:0',
        ]);

        $room->update($request->all());

        return redirect()->route('rooms.index')->with('success', 'Data ruangan berhasil diperbarui.');
    }

    public function destroy(Room $room)
    {
        $room->delete();
        return redirect()->route('rooms.index')->with('success', 'Ruangan berhasil dihapus.');
    }
}
