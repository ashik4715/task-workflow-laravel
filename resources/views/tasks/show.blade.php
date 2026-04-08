@extends('layouts.app')

@section('title', $task->title)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @if(session('success'))
        <div class="mb-4 bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-200 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-200 px-4 py-3 rounded">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $task->title }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Created by {{ $task->user->name }} on {{ $task->created_at->format('M d, Y') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    @switch($task->status)
                        @case('PENDING')
                            <span class="px-3 py-1 text-sm rounded-full bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 font-medium">Pending</span>
                            @break
                        @case('IN_PROGRESS')
                            <span class="px-3 py-1 text-sm rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 font-medium">In Progress</span>
                            @break
                        @case('COMPLETED')
                            <span class="px-3 py-1 text-sm rounded-full bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 font-medium">Completed</span>
                            @break
                        @case('APPROVED')
                            <span class="px-3 py-1 text-sm rounded-full bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 font-medium">Approved</span>
                            @break
                        @case('REJECTED')
                            <span class="px-3 py-1 text-sm rounded-full bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 font-medium">Rejected</span>
                            @break
                    @endswitch
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <div class="mb-6">
                <h2 class="text-sm font-semibold text-gray-600 dark:text-gray-300 mb-2">Description</h2>
                <p class="text-gray-700 dark:text-gray-300">{{ $task->description ?? 'No description provided.' }}</p>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <h2 class="text-sm font-semibold text-gray-600 dark:text-gray-300 mb-1">Created At</h2>
                    <p class="text-gray-700 dark:text-gray-300">{{ $task->created_at->format('M d, Y g:i A') }}</p>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-600 dark:text-gray-300 mb-1">Last Updated</h2>
                    <p class="text-gray-700 dark:text-gray-300">{{ $task->updated_at->format('M d, Y g:i A') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Update Task</h2>
            </div>
            
            @auth
                @if(Auth::user()->isAdmin())
            <form method="POST" action="{{ url('/tasks/' . $task->id) }}" class="p-6">
                @csrf
                @method('PUT')
                
                <div class="mb-4">
                    <label for="title" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Title</label>
                    <input type="text" name="title" id="title" required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        value="{{ $task->title }}">
                </div>
                
                <div class="mb-4">
                    <label for="description" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Description</label>
                    <textarea name="description" id="description" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ $task->description }}</textarea>
                </div>
                
                <div class="mb-6">
                    <label for="status" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Status</label>
                    <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="PENDING" {{ $task->status == 'PENDING' ? 'selected' : '' }}>Pending</option>
                        <option value="IN_PROGRESS" {{ $task->status == 'IN_PROGRESS' ? 'selected' : '' }}>In Progress</option>
                        <option value="COMPLETED" {{ $task->status == 'COMPLETED' ? 'selected' : '' }}>Completed</option>
                        @if(Auth::user()->isAdmin())
                            <option value="APPROVED" {{ $task->status == 'APPROVED' ? 'selected' : '' }}>Approved</option>
                            <option value="REJECTED" {{ $task->status == 'REJECTED' ? 'selected' : '' }}>Rejected</option>
                        @endif
                    </select>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Valid transitions: PENDING → IN_PROGRESS/COMPLETED, IN_PROGRESS → COMPLETED
                    </p>
                </div>
                
                <div class="flex gap-4">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        Update Task
                    </button>
                    @if($task->status == 'COMPLETED')
                        <form method="POST" action="{{ url('/tasks/' . $task->id . '/approve') }}" class="inline">
                            @csrf
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                                Approve
                            </button>
                        </form>
                        <form method="POST" action="{{ url('/tasks/' . $task->id . '/reject') }}" class="inline">
                            @csrf
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                                Reject
                            </button>
                        </form>
                    @endif
                </div>
            </form>
            
            <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                <form method="POST" action="{{ url('/tasks/' . $task->id) }}" onsubmit="return confirm('Are you sure you want to delete this task?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:text-red-700 text-sm font-medium">
                        Delete Task
                    </button>
                </form>
            </div>
                @else
            <div class="p-6">
                <p class="text-gray-600 dark:text-gray-400">Only administrators can update this task.</p>
            </div>
                @endif
            @endauth
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Comments</h2>
            </div>
            
            <div class="p-4 max-h-96 overflow-y-auto">
                @if($task->comments->isEmpty())
                    <p class="text-gray-500 dark:text-gray-400 text-sm text-center py-4">No comments yet.</p>
                @else
                    <div class="space-y-4">
                        @foreach($task->comments as $comment)
                            <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex justify-between items-start">
                                    <p class="font-medium text-sm text-gray-900 dark:text-white">{{ $comment->user->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $comment->created_at->diffForHumans() }}</p>
                                </div>
                                <p class="text-sm text-gray-700 dark:text-gray-300 mt-1">{{ $comment->comment }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            
            <form method="POST" action="{{ url('/tasks/' . $task->id . '/comments') }}" class="p-4 border-t border-gray-200 dark:border-gray-700">
                @csrf
                <div class="mb-2">
                    <textarea name="comment" placeholder="Add a comment..." rows="2" required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"></textarea>
                </div>
                <button type="submit" class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    Add Comment
                </button>
            </form>
        </div>
    </div>
</div>
@endsection