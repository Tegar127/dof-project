<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">User Management</h2>
            <p class="text-slate-500 text-sm mt-1">Manage system access and roles.</p>
        </div>
        <button
            @click="showUserModal = true; editingUser = null; userForm = {}"
            class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-500/30 flex items-center gap-2 text-sm font-semibold"
        >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New User
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-50/50 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <th class="px-6 py-4">User Info</th>
                    <th class="px-6 py-4">Role</th>
                    <th class="px-6 py-4">Position</th>
                    <th class="px-6 py-4">Group</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                <template x-for="user in users" :key="user.id">
                    <tr class="hover:bg-slate-50/80 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-white flex items-center justify-center font-bold shadow-md shadow-indigo-500/20" x-text="user.name.charAt(0)"></div>
                                <div>
                                    <div class="font-semibold text-slate-900" x-text="user.name"></div>
                                    <div class="text-sm text-slate-500" x-text="user.email"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span :class="getRoleBadge(user.role)" class="px-3 py-1 text-xs font-bold rounded-full uppercase tracking-wide">
                                <span x-text="user.role"></span>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span x-show="user.position" class="text-xs font-semibold uppercase tracking-wider text-slate-600 bg-slate-100 px-2 py-1 rounded" x-text="user.position"></span>
                            <span x-show="!user.position" class="text-xs text-slate-400">-</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2" x-show="user.group_name">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span class="text-sm font-medium text-slate-700" x-text="user.group_name"></span>
                            </div>
                            <span x-show="!user.group_name" class="text-sm text-slate-400 italic">No Group</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button
                                @click="deleteUser(user.id)"
                                class="text-slate-400 hover:text-red-600 transition-colors p-2 hover:bg-red-50 rounded-lg"
                                title="Delete User"
                            >
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>
