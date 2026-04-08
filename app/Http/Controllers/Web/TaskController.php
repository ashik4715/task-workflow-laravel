<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $tasks = Task::with('user')
            ->when(!$user->isAdmin(), function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('title', 'like', '%' . $request->search . '%')
                      ->orWhere('description', 'like', '%' . $request->search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('tasks.index', compact('tasks'));
    }

    public function create()
    {
        return view('tasks.create');
    }

    public function store(Request $request)
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

        return redirect()->route('tasks.show', $task)->with('success', 'Task created successfully');
    }

    public function show(Task $task)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $task->user_id !== $user->id) {
            abort(403);
        }

        $task->load(['user', 'comments.user']);
        return view('tasks.show', compact('task'));
    }

    public function update(Request $request, Task $task)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $task->user_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:PENDING,IN_PROGRESS,COMPLETED,APPROVED,REJECTED',
        ]);

        if (isset($validated['status']) && !$task->canTransitionTo($validated['status'])) {
            return back()->with('error', 'Invalid status transition from ' . $task->status . ' to ' . $validated['status']);
        }

        $oldValues = $task->toArray();
        $task->update($validated);
        $newValues = $task->fresh()->toArray();

        AuditLog::log(Task::class, $task->id, 'updated', $oldValues, $newValues);

        return redirect()->route('tasks.show', $task)->with('success', 'Task updated successfully');
    }

    public function destroy(Task $task)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $task->user_id !== $user->id) {
            abort(403);
        }

        $oldValues = $task->toArray();
        $task->delete();
        
        AuditLog::log(Task::class, $task->id, 'deleted', $oldValues, null);

        return redirect('/tasks')->with('success', 'Task deleted successfully');
    }

    public function approve(Task $task)
    {
        $user = Auth::user();
        if (!$user->isAdmin()) {
            abort(403);
        }

        if ($task->status !== Task::STATUS_COMPLETED) {
            return back()->with('error', 'Only completed tasks can be approved');
        }

        $oldValues = $task->toArray();
        $task->update(['status' => Task::STATUS_APPROVED]);
        $newValues = $task->fresh()->toArray();

        AuditLog::log(Task::class, $task->id, 'approved', $oldValues, $newValues);

        return back()->with('success', 'Task approved successfully');
    }

    public function reject(Task $task)
    {
        $user = Auth::user();
        if (!$user->isAdmin()) {
            abort(403);
        }

        if ($task->status !== Task::STATUS_COMPLETED) {
            return back()->with('error', 'Only completed tasks can be rejected');
        }

        $oldValues = $task->toArray();
        $task->update(['status' => Task::STATUS_REJECTED]);
        $newValues = $task->fresh()->toArray();

        AuditLog::log(Task::class, $task->id, 'rejected', $oldValues, $newValues);

        return back()->with('success', 'Task rejected successfully');
    }

    public function storeComment(Request $request, Task $task)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $task->user_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'comment' => 'required|string',
        ]);

        $comment = TaskComment::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'comment' => $validated['comment'],
        ]);

        AuditLog::log(Task::class, $task->id, 'comment_added', null, ['comment' => $validated['comment']]);

        return back()->with('success', 'Comment added successfully');
    }
}
