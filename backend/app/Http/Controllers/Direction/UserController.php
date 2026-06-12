<?php
namespace App\Http\Controllers\Direction;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $r)
    {
        $q = User::with(['agence', 'roles']);
        if (!$r->user()->hasRole('super_admin')) {
            $q->where('agence_id', $r->user()->agence_id);
        }
        return $q->paginate(20);
    }

    public function store(Request $r)
    {
        $r->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string|exists:roles,name',
            'agence_id' => 'required|exists:agences,id',
            'telephone' => 'nullable|string|max:30',
            'poste' => 'nullable|string|max:100',
            'actif' => 'sometimes|boolean',
            'agent_id' => 'nullable|exists:agents,id',
        ]);
        
        $u = User::create([
            'name' => $r->name,
            'email' => $r->email,
            'password' => Hash::make($r->password),
            'agence_id' => $r->agence_id,
            'telephone' => $r->telephone,
            'poste' => $r->poste,
            'actif' => $r->boolean('actif', true),
        ]);

        if ($r->agent_id) {
            $agent = Agent::find($r->agent_id);
            if ($agent) {
                $agent->user_id = $u->id;
                $agent->save();
            }
        }

        $u->assignRole($r->role);
        return $u->load(['agence', 'roles']);
    }

    public function show(User $user)
    {
        return $user->load(['agence', 'roles', 'permissions']);
    }

    public function update(Request $r, User $user)
    {
        $r->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'agence_id' => 'sometimes|exists:agences,id',
            'telephone' => 'nullable|string|max:30',
            'poste' => 'nullable|string|max:100',
            'actif' => 'boolean',
        ]);
        $data = $r->only(['name', 'email', 'agence_id', 'telephone', 'poste', 'actif']);
        if ($r->filled('password')) {
            $data['password'] = Hash::make($r->password);
        }
        $user->update($data);
        if ($r->filled('role')) {
            $user->syncRoles([$r->role]);
        }
        return $user->load(['agence', 'roles']);
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Impossible de supprimer votre propre compte'], 422);
        }
        $user->delete();
        return response()->noContent();
    }

    public function toggleActif(User $user)
    {
        $user->update(['actif' => !$user->actif]);
        return $user->load('agence');
    }
}
