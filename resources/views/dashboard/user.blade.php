@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    <h1 class="text-2xl font-bold text-gray-900 mb-6">Dashboard</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-gray-500">
            <p class="text-sm text-gray-500">Total Tasks</p>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['total_tasks'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
            <p class="text-sm text-gray-500">Pending</p>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['pending_tasks'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
            <p class="text-sm text-gray-500">In Progress</p>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['in_progress_tasks'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-orange-500">
            <p class="text-sm text-gray-500">Completed</p>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['completed_tasks'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
            <p class="text-sm text-gray-500">Approved</p>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['approved_tasks'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
            <p class="text-sm text-gray-500">Rejected</p>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['rejected_tasks'] }}</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-900">Recent Tasks</h2>
            <a href="{{ url('/tasks/create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                Create Task
            </a>
        </div>
        <div class="p-4">
            @if($recentTasks->isEmpty())
                <p class="text-gray-500 text-center py-4">No tasks yet. Create your first task!</p>
            @else
                <div class="space-y-3">
                    @foreach($recentTasks as $task)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100">
                            <div class="flex-1">
                                <a href="{{ url('/tasks/' . $task->id) }}" class="font-medium text-gray-900 hover:text-blue-600">
                                    {{ $task->title }}
                                </a>
                                <p class="text-sm text-gray-500">{{ $task->user->name }}</p>
                            </div>
                            <div class="ml-4">
                                @switch($task->status)
                                    @case('PENDING')
                                        <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                        @break
                                    @case('IN_PROGRESS')
                                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">In Progress</span>
                                        @break
                                    @case('COMPLETED')
                                        <span class="px-2 py-1 text-xs rounded-full bg-orange-100 text-orange-800">Completed</span>
                                        @break
                                    @case('APPROVED')
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Approved</span>
                                        @break
                                    @case('REJECTED')
                                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Rejected</span>
                                        @break
                                @endswitch
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="p-4 border-t border-gray-200">
            <a href="{{ url('/tasks') }}" class="text-blue-600 hover:text-blue-700 font-medium">View all tasks →</a>
        </div>
    </div>
</div>
@endsection
