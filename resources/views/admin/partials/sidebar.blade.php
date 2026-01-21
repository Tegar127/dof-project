<div class="w-64 bg-slate-900 min-h-screen text-white flex flex-col fixed left-0 top-0 bottom-0 z-20 transition-transform duration-300">
    <div class="p-6 border-b border-slate-800">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-indigo-500 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
            </div>
            <span class="font-bold text-lg tracking-wide">AdminPanel</span>
        </div>
    </div>

    <nav class="flex-1 p-4 space-y-1">
        <button 
            @click="activeTab = 'users'" 
            :class="activeTab === 'users' ? 'bg-indigo-600/10 text-indigo-400 border-r-2 border-indigo-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
            class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-r-lg transition-all"
        >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            User Management
        </button>

        <button 
            @click="activeTab = 'groups'"
            :class="activeTab === 'groups' ? 'bg-indigo-600/10 text-indigo-400 border-r-2 border-indigo-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
            class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-r-lg transition-all"
        >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            Groups Management
        </button>
    </nav>

    <div class="p-4 border-t border-slate-800">
        <button 
            @click="handleLogout()"
            class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-red-400 hover:bg-red-950/30 rounded-lg transition-colors"
        >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            Sign Out
        </button>
    </div>
</div>
