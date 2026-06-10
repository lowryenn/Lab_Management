<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        $response = $this->apiCall('GET', '/api/rooms');
        $roomsData = $response->json()['data'] ?? [];
        $rooms = $this->hydrateCollection(Room::class, $roomsData);

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
            'code' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:0',
        ]);

        $response = $this->apiCall('POST', '/api/rooms', $request->only('name', 'code', 'location', 'capacity'));

        if ($response->failed()) {
            return back()->withErrors(['code' => $response->json()['error'] ?? 'Gagal menambahkan ruangan.'])->withInput();
        }

        return redirect()->route('rooms.index')->with('success', 'Ruangan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $response = $this->apiCall('GET', "/api/rooms/{$id}");
        if ($response->failed()) {
            abort(404);
        }
        $roomData = $response->json()['room'] ?? $response->json()['data'];
        $room = $this->hydrateModel(Room::class, $roomData);

        return view('admin.rooms.edit', compact('room'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:0',
        ]);

        $response = $this->apiCall('PUT', "/api/rooms/{$id}", $request->only('name', 'code', 'location', 'capacity'));

        if ($response->failed()) {
            return back()->withErrors(['code' => $response->json()['error'] ?? 'Gagal memperbarui ruangan.'])->withInput();
        }

        return redirect()->route('rooms.index')->with('success', 'Data ruangan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $response = $this->apiCall('DELETE', "/api/rooms/{$id}");
        if ($response->failed()) {
            return redirect()->route('rooms.index')->with('error', $response->json()['error'] ?? 'Gagal menghapus ruangan.');
        }

        return redirect()->route('rooms.index')->with('success', 'Ruangan berhasil dihapus.');
    }
}
