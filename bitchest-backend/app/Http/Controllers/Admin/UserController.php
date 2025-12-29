<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Services\UserService;
use App\Models\User;
use App\Models\Portfolio;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmailMailable;

class UserController extends Controller
{
    public function store(StoreUserRequest $request, UserService $userService)
    {
        $data = $userService->createClient($request->name, $request->email);

        return response()->json([
            'user' => $data['user'],
            'temporary_password' => $data['password']
        ], 201);
    }

    public function index()
    {
        return response()->json(\App\Models\User::where('role','client')->get());
    }

    public function show($id)
    {
        return response()->json(\App\Models\User::findOrFail($id));
    }

    public function destroy($id)
    {
        $u = \App\Models\User::findOrFail($id);
        $u->delete();
        return response()->json(['message' => 'Deleted']);
    }
    public function approve($id)
    {
        $user = User::findOrFail($id);
    
        if ($user->isActive()) {
            return response()->json(['message' => 'User already active'], 400);
        }
    
        $user->update([
            'status' => User::STATUS_ACTIVE,
            'must_change_password' => false,
            'euro_balance' => 500.00,
        ]);
    
      
        Mail::to($user->email)->send(new VerifyEmailMailable($user));
    
        return response()->json([
            'message' => 'User approved and account activated',
            'user' => $user
        ]);
    }

    public function block($id)
    {
        $user = User::findOrFail($id);

        if ($user->isAdmin()) {
            return response()->json(['message' => 'Cannot block an admin'], 400);
        }

        $user->update([
            'status' => User::STATUS_BLOCKED,
            'must_change_password' => false,
        ]);

        return response()->json([
            'message' => 'User blocked',
            'user' => $user,
        ]);
    }
    

}
