@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    <h1 class="text-2xl font-bold text-gray-900 mb-6">Audit Logs</h1>

    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-4 border-b border-gray-200">
            <form method="GET" action="{{ url('/audit-logs') }}" class="flex flex-wrap gap-4">
                <div>
                    <select name="entity_type" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Types</option>
                        <option value="App\Models\User" {{ request('entity_type') == 'App\Models\User' ? 'selected' : '' }}>User</option>
                        <option value="App\Models\Task" {{ request('entity_type') == 'App\Models\Task' ? 'selected' : '' }}>Task</option>
                    </select>
                </div>
                <div>
                    <select name="action" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Actions</option>
                        <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Created</option>
                        <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>Updated</option>
                        <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                        <option value="approved" {{ request('action') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('action') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="comment_added" {{ request('action') == 'comment_added' ? 'selected' : '' }}>Comment Added</option>
                    </select>
                </div>
                <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    Filter
                </button>
                <a href="{{ url('/audit-logs') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">
                    Clear
                </a>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="p-4">
            @if($logs->isEmpty())
                <p class="text-gray-500 text-center py-8">No audit logs found.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Time</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">User</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Action</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Entity</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-3 px-4 text-sm text-gray-500">{{ $log->created_at->format('M d, Y g:i A') }}</td>
                                    <td class="py-3 px-4 text-sm text-gray-900">{{ $log->user->name ?? 'System' }}</td>
                                    <td class="py-3 px-4">
                                        @switch($log->action)
                                            @case('created')
                                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Created</span>
                                                @break
                                            @case('updated')
                                                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Updated</span>
                                                @break
                                            @case('deleted')
                                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Deleted</span>
                                                @break
                                            @case('approved')
                                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Approved</span>
                                                @break
                                            @case('rejected')
                                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Rejected</span>
                                                @break
                                            @case('comment_added')
                                                <span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">Comment Added</span>
                                                @break
                                            @default
                                                <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">{{ $log->action }}</span>
                                        @endswitch
                                    </td>
                                    <td class="py-3 px-4 text-sm text-gray-600">
                                        {{ class_basename($log->entity_type) }} #{{ $log->entity_id }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-gray-500 max-w-xs truncate">
                                        @if($log->old_values)
                                            <span class="text-red-600">-{{ implode(', ', array_keys($log->old_values)) }}</span>
                                        @endif
                                        @if($log->new_values)
                                            <span class="text-green-600">+{{ implode(', ', array_keys($log->new_values)) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        
        <div class="p-4 border-t border-gray-200">
            {{ $logs->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
