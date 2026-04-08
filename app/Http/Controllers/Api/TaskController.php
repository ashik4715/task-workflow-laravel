<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $tasks = Task::with(['user', 'creator', 'updater'])
            ->when(!$user->isAdmin(), function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->when($request->has('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->has('search'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('title', 'like', '%' . $request->search . '%')
                      ->orWhere('description', 'like', '%' . $request->search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($tasks);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $task = Task::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => Task::STATUS_PENDING,
        ]);

        AuditLog::log(Task::class, $task->id, 'created', null, $task->toArray());

        return response()->json([
            'message' => 'Task created successfully',
            'task' => $task->load(['user', 'creator', 'updater']),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $task = Task::with(['user', 'creator', 'updater', 'comments.user'])->findOrFail($id);
        
        $user = Auth::user();
        if (!$user->isAdmin() && $task->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($task);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $task = Task::findOrFail($id);
        
        $user = Auth::user();
        if (!$user->isAdmin() && $task->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:PENDING,IN_PROGRESS,COMPLETED,APPROVED,REJECTED',
        ]);

        if (isset($validated['status']) && !$task->canTransitionTo($validated['status'])) {
            return response()->json([
                'message' => 'Invalid status transition',
                'current_status' => $task->status,
                'requested_status' => $validated['status'],
            ], 422);
        }

        $oldValues = $task->toArray();
        $task->update($validated);
        $newValues = $task->fresh()->toArray();

        AuditLog::log(Task::class, $task->id, 'updated', $oldValues, $newValues);

        return response()->json([
            'message' => 'Task updated successfully',
            'task' => $task->load(['user', 'creator', 'updater']),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $task = Task::findOrFail($id);
        
        $user = Auth::user();
        if (!$user->isAdmin() && $task->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $oldValues = $task->toArray();
        $task->delete();
        
        AuditLog::log(Task::class, $task->id, 'deleted', $oldValues, null);

        return response()->json([
            'message' => 'Task deleted successfully',
        ]);
    }

    public function approve(int $id): JsonResponse
    {
        $task = Task::findOrFail($id);
        
        if ($task->status !== Task::STATUS_COMPLETED) {
            return response()->json([
                'message' => 'Only completed tasks can be approved',
            ], 422);
        }

        $oldValues = $task->toArray();
        $task->update(['status' => Task::STATUS_APPROVED]);
        $newValues = $task->fresh()->toArray();

        AuditLog::log(Task::class, $task->id, 'approved', $oldValues, $newValues);

        return response()->json([
            'message' => 'Task approved successfully',
            'task' => $task->load(['user', 'creator', 'updater']),
        ]);
    }

    public function reject(int $id): JsonResponse
    {
        $task = Task::findOrFail($id);
        
        if ($task->status !== Task::STATUS_COMPLETED) {
            return response()->json([
                'message' => 'Only completed tasks can be rejected',
            ], 422);
        }

        $oldValues = $task->toArray();
        $task->update(['status' => Task::STATUS_REJECTED]);
        $newValues = $task->fresh()->toArray();

        AuditLog::log(Task::class, $task->id, 'rejected', $oldValues, $newValues);

        return response()->json([
            'message' => 'Task rejected successfully',
            'task' => $task->load(['user', 'creator', 'updater']),
        ]);
    }
}
