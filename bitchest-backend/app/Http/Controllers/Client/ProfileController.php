<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\UpdateProfileRequest;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();

        $user->fill([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'name' => trim($request->first_name . ' ' . $request->last_name),
            'phone' => $request->phone,
        ]);

        $user->save();

        return response()->json([
            'message' => 'Profil mis à jour',
            'user' => $user->fresh()
        ]);
    }
}

