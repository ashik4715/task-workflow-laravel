<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function index(int $taskId): JsonResponse
    {
        $task = Task::findOrFail($taskId);
        $user = Auth::user();
        
        if (!$user->isAdmin() && $task->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comments = TaskComment::with('user')
            ->where('task_id', $taskId)
            ->orderBy('created_at', 'asc')
            ->paginate(15);

        return response()->json($comments);
    }

    public function store(Request $request, int $taskId): JsonResponse
    {
        $task = Task::findOrFail($taskId);
        
        $user = Auth::user();
        if (!$user->isAdmin() && $task->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'comment' => 'required|string',
        ]);

        $comment = TaskComment::create([
            'task_id' => $taskId,
            'user_id' => Auth::id(),
            'comment' => $validated['comment'],
        ]);

        AuditLog::log(Task::class, $taskId, 'comment_added', null, ['comment' => $validated['comment']]);

        return response()->json([
            'message' => 'Comment added successfully',
            'comment' => $comment->load('user'),
        ], 201);
    }

    public function destroy(int $taskId, int $commentId): JsonResponse
    {
        $comment = TaskComment::where('task_id', $taskId)->findOrFail($commentId);
        
        $user = Auth::user();
        if (!$user->isAdmin() && $comment->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $oldValues = $comment->toArray();
        $comment->delete();
        
        AuditLog::log(Task::class, $taskId, 'comment_deleted', $oldValues, null);

        return response()->json([
            'message' => 'Comment deleted successfully',
        ]);
    }
}
