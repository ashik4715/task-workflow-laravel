@extends('layouts.app')

@section('title', $user->name)

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-6 border-b border-gray-200">
            <h1 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h1>
            <p class="text-gray-500">{{ $user->email }}</p>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <h2 class="text-sm font-semibold text-gray-600 mb-1">Role</h2>
                    <span class="px-2 py-1 text-sm rounded-full {{ $user->role == 'ADMIN' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                        {{ $user->role }}
                    </span>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-600 mb-1">Status</h2>
                    <span class="px-2 py-1 text-sm rounded-full {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-600 mb-1">Created At</h2>
                    <p class="text-gray-700">{{ $user->created_at->format('M d, Y g:i A') }}</p>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-600 mb-1">Updated At</h2>
                    <p class="text-gray-700">{{ $user->updated_at->format('M d, Y g:i A') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="flex gap-4">
        <a href="{{ url('/users') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">
            Back to Users
        </a>
        <a href="{{ route('users.permissions', $user->id) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
            Manage Permissions
        </a>
    </div>
</div>
@endsection
