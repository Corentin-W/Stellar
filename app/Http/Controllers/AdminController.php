<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function panel()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::whereNotNull('email_verified_at')->count(),
            'admin_users' => User::where('admin', 1)->count(),
            'today_logins' => User::whereDate('last_login_at', today())->count(),
        ];

        $users = User::orderBy('created_at', 'desc')->paginate(20);

        return view('admin.panel', compact('stats', 'users'));
    }

    public function loginAsUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Vous ne pouvez pas vous connecter en tant que vous-même');
        }

        // Sauvegarder l'ID de l'admin original dans la session
        session(['original_admin_id' => auth()->id()]);

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', "Connecté en tant que {$user->name}");
    }

    public function toggleAdmin(Request $request, User $user)
    {
        $request->validate([
            'admin' => 'required|boolean'
        ]);

        $user->update([
            'admin' => $request->admin
        ]);

        return response()->json([
            'success' => true,
            'message' => $request->admin ? 'Utilisateur promu admin' : 'Droits admin retirés'
        ]);
    }
}
