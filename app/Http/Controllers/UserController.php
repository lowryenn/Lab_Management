<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $response = $this->apiCall('GET', '/api/users');
        $usersData = $response->json()['data'] ?? [];
        $users = $this->hydrateCollection(User::class, $usersData);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'role' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $response = $this->apiCall('POST', '/api/users', $request->only('name', 'email', 'role', 'password'));

        if ($response->failed()) {
            return back()->withErrors(['email' => $response->json()['error'] ?? 'Gagal menambahkan pengguna.'])->withInput();
        }

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $response = $this->apiCall('GET', "/api/users/{$id}");
        if ($response->failed()) {
            abort(404);
        }
        $user = $this->hydrateModel(User::class, $response->json()['data']);

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'role' => 'required|string',
        ]);

        $data = $request->only('name', 'email', 'role');
        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $response = $this->apiCall('PUT', "/api/users/{$id}", $data);

        if ($response->failed()) {
            return back()->withErrors(['email' => $response->json()['error'] ?? 'Gagal memperbarui pengguna.'])->withInput();
        }

        return redirect()->route('users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $response = $this->apiCall('DELETE', "/api/users/{$id}");
        if ($response->failed()) {
            return redirect()->route('users.index')->with('error', $response->json()['error'] ?? 'Gagal menghapus pengguna.');
        }

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
