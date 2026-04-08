@extends('layouts.app')

@section('title', 'Users')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            {{ session('error') }}
        </div>
    @endif

    <h1 class="text-2xl font-bold text-gray-900 mb-6">User Management</h1>

    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-4 border-b border-gray-200">
            <form method="GET" action="{{ url('/users') }}" class="flex flex-wrap gap-4">
                <div>
                    <select name="role" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Roles</option>
                        <option value="USER" {{ request('role') == 'USER' ? 'selected' : '' }}>User</option>
                        <option value="ADMIN" {{ request('role') == 'ADMIN' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>
                <div>
                    <select name="is_active" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    Filter
                </button>
                <a href="{{ url('/users') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">
                    Clear
                </a>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="p-4">
            @if($users->isEmpty())
                <p class="text-gray-500 text-center py-8">No users found.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Name</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Email</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Role</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Status</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Created</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-3 px-4 text-sm text-gray-900">{{ $user->name }}</td>
                                    <td class="py-3 px-4 text-sm text-gray-600">{{ $user->email }}</td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 text-xs rounded-full {{ $user->role == 'ADMIN' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ $user->role }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 text-xs rounded-full {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-gray-500">{{ $user->created_at->format('M d, Y') }}</td>
                                    <td class="py-3 px-4">
                                        <div class="flex gap-2">
                                            <form method="POST" action="{{ url('/users/' . $user->id . '/status') }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="is_active" value="{{ $user->is_active ? '0' : '1' }}">
                                                <button type="submit" class="text-sm {{ $user->is_active ? 'text-red-600 hover:text-red-700' : 'text-green-600 hover:text-green-700' }}">
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
        
        <div class="p-4 border-t border-gray-200">
            {{ $users->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
