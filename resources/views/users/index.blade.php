@extends('layouts.app')

@section('title', 'Users')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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

    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">User Management</h1>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <form method="GET" action="{{ url('/users') }}" class="flex flex-wrap gap-4">
                <div>
                    <select name="role" class="px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Roles</option>
                        <option value="USER" {{ request('role') == 'USER' ? 'selected' : '' }}>User</option>
                        <option value="ADMIN" {{ request('role') == 'ADMIN' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>
                <div>
                    <select name="is_active" class="px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    Filter
                </button>
                <a href="{{ url('/users') }}" class="bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-200 px-4 py-2 rounded-lg text-sm font-medium">
                    Clear
                </a>
            </form>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="p-4">
            @if($users->isEmpty())
                <p class="text-gray-500 dark:text-gray-400 text-center py-8">No users found.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600 dark:text-gray-300">Name</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600 dark:text-gray-300">Email</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600 dark:text-gray-300">Role</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600 dark:text-gray-300">Status</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600 dark:text-gray-300">Created</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600 dark:text-gray-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="py-3 px-4 text-sm text-gray-900 dark:text-white">{{ $user->name }}</td>
                                    <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-400">{{ $user->email }}</td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 text-xs rounded-full {{ $user->role == 'ADMIN' ? 'bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200' : 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200' }}">
                                            {{ $user->role }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 text-xs rounded-full {{ $user->is_active ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' }}">
                                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-gray-500 dark:text-gray-400">{{ $user->created_at->format('M d, Y') }}</td>
                                    <td class="py-3 px-4">
                                        <div class="flex gap-2">
                                            <a href="{{ route('users.permissions', $user->id) }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">
                                                Permissions
                                            </a>
                                            <form method="POST" action="{{ url('/users/' . $user->id . '/status') }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="is_active" value="{{ $user->is_active ? '0' : '1' }}">
                                                <button type="submit" class="text-sm {{ $user->is_active ? 'text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300' : 'text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300' }}">
                                                    {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        
        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
            {{ $users->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection