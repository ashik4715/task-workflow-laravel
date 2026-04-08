<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $stats = [
            'total_tasks' => $user->isAdmin() ? Task::count() : Task::where('user_id', $user->id)->count(),
            'pending_tasks' => $user->isAdmin() ? Task::where('status', 'PENDING')->count() : Task::where('user_id', $user->id)->where('status', 'PENDING')->count(),
            'in_progress_tasks' => $user->isAdmin() ? Task::where('status', 'IN_PROGRESS')->count() : Task::where('user_id', $user->id)->where('status', 'IN_PROGRESS')->count(),
            'completed_tasks' => $user->isAdmin() ? Task::where('status', 'COMPLETED')->count() : Task::where('user_id', $user->id)->where('status', 'COMPLETED')->count(),
            'approved_tasks' => $user->isAdmin() ? Task::where('status', 'APPROVED')->count() : Task::where('user_id', $user->id)->where('status', 'APPROVED')->count(),
            'rejected_tasks' => $user->isAdmin() ? Task::where('status', 'REJECTED')->count() : Task::where('user_id', $user->id)->where('status', 'REJECTED')->count(),
        ];

        $recentTasks = $user->isAdmin()
            ? Task::with('user')->orderBy('updated_at', 'desc')->limit(5)->get()
            : Task::with('user')->where('user_id', $user->id)->orderBy('updated_at', 'desc')->limit(5)->get();

        if ($user->isAdmin()) {
            $recentLogs = AuditLog::with('user')->orderBy('created_at', 'desc')->limit(5)->get();
            return view('dashboard.admin', compact('stats', 'recentTasks', 'recentLogs'));
        }

        return view('dashboard.user', compact('stats', 'recentTasks'));
    }
}
