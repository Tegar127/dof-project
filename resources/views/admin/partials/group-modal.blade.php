<!-- Group Modal -->
<div x-show="showGroupModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="showGroupModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/50 transition-opacity" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="showGroupModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" @click.away="showGroupModal = false" class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="text-center mb-6">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 mb-4">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900">Create New Group</h3>
                    <p class="text-sm text-slate-500 mt-1">Add a new functional group to the system.</p>
                </div>
                
                <form @submit.prevent="saveGroup()" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Group Name</label>
                        <input type="text" x-model="groupForm.name" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all" placeholder="e.g., Marketing, HR, Finance">
                    </div>

                    <div class="pt-2">
                        <div class="flex items-start gap-3">
                             <div class="flex h-6 items-center">
                                <input type="checkbox" id="is_private" x-model="groupForm.is_private" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                             </div>
                             <div>
                                <label for="is_private" class="text-sm font-medium text-slate-900">Private Group</label>
                                <p class="text-xs text-slate-500">Only invited members can see this group.</p>
                             </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Invite Members (Optional)</label>
                        <div class="border border-slate-200 rounded-lg max-h-48 overflow-y-auto bg-slate-50 p-2 space-y-1 custom-scrollbar">
                             <template x-for="user in users" :key="user.id">
                                <label class="flex items-center gap-3 p-2 hover:bg-white rounded-lg cursor-pointer transition-colors group">
                                    <input type="checkbox" :value="user.id" x-model="groupForm.invited_users" class="rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 bg-white">
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-medium text-slate-800 truncate" x-text="user.name"></div>
                                        <div class="text-xs text-slate-500 truncate" x-text="user.email + (user.group_name ? ' • ' + user.group_name : '')"></div>
                                    </div>
                                </label>
                             </template>
                             <div x-show="users.length === 0" class="text-center py-4 text-xs text-slate-400">
                                No users available to invite.
                             </div>
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1.5 italic">Note: Selected users will be reassigned to this group.</p>
                    </div>

                    <div class="flex gap-3 pt-4 border-t border-slate-100">
                        <button type="button" @click="showGroupModal = false" class="flex-1 px-4 py-2.5 border border-slate-300 text-slate-700 font-medium rounded-xl hover:bg-slate-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="flex-1 px-4 py-2.5 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-500/30">
                            Save Group
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
