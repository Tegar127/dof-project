@extends('layouts.app')

@section('title', 'Admin Panel - DOF')

@section('content')
<div class="min-h-screen bg-slate-50 font-sans" x-data="adminApp()" x-init="init()">
    <!-- Header -->
    <div class="bg-gradient-to-r from-slate-900 to-slate-800 text-white">
        <div class="max-w-7xl mx-auto px-6 py-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Admin Panel</h1>
                    <p class="text-slate-300">Manage users, groups, and system settings</p>
                </div>
                <button
                    @click="handleLogout()"
                    class="bg-white/10 hover:bg-white/20 backdrop-blur-md px-4 py-2.5 rounded-xl text-sm font-medium transition-all border border-white/10 flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-6 py-8">
        <!-- Tabs -->
        <div class="mb-8 border-b border-slate-200">
            <nav class="flex gap-8">
                <button
                    @click="activeTab = 'users'"
                    :class="activeTab === 'users' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
                    class="py-4 px-1 border-b-2 font-medium text-sm transition-colors"
                >
                    Users Management
                </button>
                <button
                    @click="activeTab = 'groups'"
                    :class="activeTab === 'groups' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
                    class="py-4 px-1 border-b-2 font-medium text-sm transition-colors"
                >
                    Groups Management
                </button>
            </nav>
        </div>

        <!-- Users Tab -->
        <div x-show="activeTab === 'users'" x-transition>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-6 border-b border-slate-200 flex justify-between items-center">
                    <h2 class="text-xl font-bold text-slate-900">Users</h2>
                    <button
                        @click="showUserModal = true; editingUser = null; userForm = {}"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add User
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Group</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <template x-for="user in users" :key="user.id">
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-semibold" x-text="user.name.charAt(0)"></div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-slate-900" x-text="user.name"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500" x-text="user.email"></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="getRoleBadge(user.role)" class="px-2 py-1 text-xs font-semibold rounded-full" x-text="user.role"></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500" x-text="user.group_name || '-'"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button
                                            @click="deleteUser(user.id)"
                                            class="text-red-600 hover:text-red-900 ml-4"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Groups Tab -->
        <div x-show="activeTab === 'groups'" x-transition>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-6 border-b border-slate-200 flex justify-between items-center">
                    <h2 class="text-xl font-bold text-slate-900">Groups</h2>
                    <button
                        @click="showGroupModal = true"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Group
                    </button>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <template x-for="group in groups" :key="group">
                            <div class="p-4 border border-slate-200 rounded-lg hover:border-indigo-300 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                        </div>
                                        <span class="font-medium text-slate-900" x-text="group"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Modal -->
    <div x-show="showUserModal" x-transition class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
        <div @click.away="showUserModal = false" class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
            <h3 class="text-xl font-bold mb-6 text-slate-800">Add New User</h3>
            
            <form @submit.prevent="saveUser()" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Name</label>
                    <input type="text" x-model="userForm.name" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Email</label>
                    <input type="email" x-model="userForm.email" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Password</label>
                    <input type="password" x-model="userForm.password" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Role</label>
                    <select x-model="userForm.role" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select Role</option>
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                        <option value="reviewer">Reviewer</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Group</label>
                    <select x-model="userForm.group_name" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">No Group</option>
                        <template x-for="group in groups" :key="group">
                            <option :value="group" x-text="group"></option>
                        </template>
                    </select>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" @click="showUserModal = false" class="flex-1 px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        Save User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Group Modal -->
    <div x-show="showGroupModal" x-transition class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
        <div @click.away="showGroupModal = false" class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
            <h3 class="text-xl font-bold mb-6 text-slate-800">Add New Group</h3>
            
            <form @submit.prevent="saveGroup()" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Group Name</label>
                    <input type="text" x-model="groupForm.name" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g., Marketing">
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" @click="showGroupModal = false" class="flex-1 px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        Save Group
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection
