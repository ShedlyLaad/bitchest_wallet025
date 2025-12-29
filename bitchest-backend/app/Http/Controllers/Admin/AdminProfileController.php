<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminProfileController extends Controller
{
    public function update(Request $request)
    {
        $admin = auth()->user();

        $request->validate([
            'name'     => 'sometimes|string',
            'email'    => 'sometimes|email|unique:users,email,' . $admin->id,
            'password' => 'nullable|confirmed|min:6'
        ]);

        if ($request->password) {
            $admin->password = Hash::make($request->password);
        }

        $admin->update($request->only('name', 'email'));

        return response()->json(['message' => 'Profil mis à jour', 'admin' => $admin]);
    }
}
