<?php

namespace App\Http\Controllers\Direction;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Notification::query()
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        // Filtres
        if ($request->filled('lu')) {
            $query->where('lu', $request->boolean('lu'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        if ($request->filled('canal')) {
            $query->where('canal', $request->string('canal'));
        }

        // Recherche globale
        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('titre', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('titre')) {
            $titre = $request->string('titre');
            $query->where('titre', 'like', "%{$titre}%");
        }

        if ($request->filled('message')) {
            $message = $request->string('message');
            $query->where('message', 'like', "%{$message}%");
        }

        // Pagination (défaut 20)
        $perPage = (int) $request->integer('per_page', 20);
        $perPage = max(1, min($perPage, 50));

        return response()->json($query->paginate($perPage));
    }

    public function show(Notification $notification)
    {
        return response()->json($notification);
    }

    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->update(['lu' => true]);

        return response()->json(['message' => 'Notification marquée comme lue']);
    }

    public function markAllAsRead()
    {
        $user = Auth::user();
        Notification::where('user_id', $user->id)
            ->where('lu', false)
            ->update(['lu' => true]);

        return response()->json(['message' => 'Toutes les notifications marquées comme lues']);
    }

    public function destroy(Notification $notification)
    {
        $notification->delete();

        return response()->json(['message' => 'Notification supprimée avec succès']);
    }
}

