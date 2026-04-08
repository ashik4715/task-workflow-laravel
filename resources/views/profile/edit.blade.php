@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if(session('success'))
            <div
                class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded dark:bg-green-900 dark:border-green-700 dark:text-green-200">
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div
                class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded dark:bg-red-900 dark:border-red-700 dark:text-red-200">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Profile</h1>
                <p class="text-gray-500 dark:text-gray-400">Update your personal information and password</p>
            </div>

            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')
                <div class="p-6 space-y-6">
                    <div>
                        <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Personal Information</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Full
                                    Name</label>
                                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label for="email"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email
                                    Address</label>
                                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Change Password</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Leave blank if you don't want to change
                            your password.</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="current_password"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Current
                                    Password</label>
                                <input type="password" id="current_password" name="current_password"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="password"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">New
                                        Password</label>
                                    <input type="password" id="password" name="password"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label for="password_confirmation"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confirm New
                                        Password</label>
                                    <input type="password" id="password_confirmation" name="password_confirmation"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700 flex justify-between items-center">
                    <a href="{{ route('dashboard') }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-600">
                        Cancel
                    </a>
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection