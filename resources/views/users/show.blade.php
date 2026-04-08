@extends('layouts.app')

@section('title', $user->name)

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @if(session('success'))
        <div class="mb-4 bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-200 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h1>
            <p class="text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <h2 class="text-sm font-semibold text-gray-600 dark:text-gray-300 mb-1">Role</h2>
                    <span class="px-2 py-1 text-sm rounded-full {{ $user->role == 'ADMIN' ? 'bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200' : 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200' }}">
                        {{ $user->role }}
                    </span>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-600 dark:text-gray-300 mb-1">Associated Role</h2>
                    @if($user->roleModel)
                        <span class="px-2 py-1 text-sm rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200">
                            {{ $user->roleModel->name }}
                        </span>
                    @else
                        <span class="text-sm text-gray-500 dark:text-gray-400">No role assigned</span>
                    @endif
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-600 dark:text-gray-300 mb-1">Status</h2>
                    <span class="px-2 py-1 text-sm rounded-full {{ $user->is_active ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' }}">
                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-600 dark:text-gray-300 mb-1">Created At</h2>
                    <p class="text-gray-700 dark:text-gray-300">{{ $user->created_at->format('M d, Y g:i A') }}</p>
                </div>
            </div>
        </div>
    </div>

    @can('user.edit')
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Edit User</h2>
        </div>
        
        <form method="POST" action="{{ url('/users/' . $user->id) }}" class="p-6">
            @csrf
            @method('PUT')
            
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                <input type="text" name="name" id="name" value="{{ $user->name }}" required
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                <input type="email" name="email" id="email" value="{{ $user->email }}" required
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div class="mb-4">
                <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                <select name="role" id="role" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="USER" {{ $user->role == 'USER' ? 'selected' : '' }}>User</option>
                    <option value="ADMIN" {{ $user->role == 'ADMIN' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>

            <div class="mb-4">
                <label for="role_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Associate Role</label>
                <select name="role_id" id="role_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Select Role --</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ $user->role_id == $role->id ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Select a role to grant permissions based on role configuration.
                </p>
            </div>
            
            <div class="flex gap-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    Update User
                </button>
                <a href="{{ url('/users') }}" class="bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-200 px-4 py-2 rounded-lg text-sm font-medium">
                    Back to Users
                </a>
            </div>
        </form>
    </div>
    @endcan
</div>
@endsection