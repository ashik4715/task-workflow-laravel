<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Forbidden. Admin access required.'], 403);
        }

        $logs = AuditLog::with('user')
            ->when($request->has('entity_type'), function ($query) use ($request) {
                $query->where('entity_type', $request->entity_type);
            })
            ->when($request->has('entity_id'), function ($query) use ($request) {
                $query->where('entity_id', $request->entity_id);
            })
            ->when($request->has('action'), function ($query) use ($request) {
                $query->where('action', $request->action);
            })
            ->when($request->has('user_id'), function ($query) use ($request) {
                $query->where('user_id', $request->user_id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($logs);
    }

    public function show(int $id): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Forbidden. Admin access required.'], 403);
        }

        $log = AuditLog::with('user')->findOrFail($id);
        return response()->json($log);
    }
}
