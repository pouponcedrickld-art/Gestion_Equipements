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
        $query = Notification::where('user_id', $user->id)->orderBy('created_at', 'desc');

        if ($request->filled('lu')) {
            $query->where('lu', $request->boolean('lu'));
        }

        return response()->json($query->paginate(20));
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
        Notification::where('user_id', $user->id)->where('lu', false)->update(['lu' => true]);
        return response()->json(['message' => 'Toutes les notifications marquées comme lues']);
    }

    public function destroy(Notification $notification)
    {
        $notification->delete();
        return response()->json(['message' => 'Notification supprimée avec succès']);
    }
}
