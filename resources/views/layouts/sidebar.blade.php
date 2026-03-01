{{-- Sidebar Navigation --}}
<aside class="sidebar bg-slate-800 text-gray-300 flex flex-col overflow-y-auto"
       :class="{ 'open': sidebarOpen }">

    {{-- Logo / Brand --}}
    <div class="px-6 py-5 border-b border-slate-700">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-indigo-500 flex items-center justify-center">
                <i class="fas fa-wallet text-white text-sm"></i>
            </div>
            <span class="text-lg font-bold text-white tracking-tight">SpendWise</span>
        </a>
    </div>

    {{-- Navigation Groups --}}
    <nav class="flex-1 px-3 py-4 space-y-6">

        {{-- Main --}}
        <div>
            <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Main</p>
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition
                      {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-700 text-gray-300' }}">
                <i class="fas fa-th-large w-5 text-center"></i>
                <span>Dashboard</span>
            </a>
        </div>

        {{-- Finance --}}
        <div>
            <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Finance</p>
            <div class="space-y-1">
                <a href="{{ route('accounts.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition
                          {{ request()->routeIs('accounts.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-700 text-gray-300' }}">
                    <i class="fas fa-university w-5 text-center"></i>
                    <span>Accounts</span>
                </a>
                <a href="{{ route('incomes.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition
                          {{ request()->routeIs('incomes.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-700 text-gray-300' }}">
                    <i class="fas fa-arrow-down w-5 text-center text-green-400"></i>
                    <span>Income</span>
                </a>
                <a href="{{ route('expenses.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition
                          {{ request()->routeIs('expenses.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-700 text-gray-300' }}">
                    <i class="fas fa-arrow-up w-5 text-center text-red-400"></i>
                    <span>Expenses</span>
                </a>
                <a href="{{ route('transfers.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition
                          {{ request()->routeIs('transfers.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-700 text-gray-300' }}">
                    <i class="fas fa-exchange-alt w-5 text-center text-blue-400"></i>
                    <span>Transfers</span>
                </a>
                <a href="{{ route('budgets.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition
                          {{ request()->routeIs('budgets.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-700 text-gray-300' }}">
                    <i class="fas fa-piggy-bank w-5 text-center text-amber-400"></i>
                    <span>Budgets</span>
                </a>
                <a href="{{ route('recurring-expenses.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition
                          {{ request()->routeIs('recurring-expenses.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-700 text-gray-300' }}">
                    <i class="fas fa-redo-alt w-5 text-center text-purple-400"></i>
                    <span>Recurring</span>
                </a>
        </div>

        {{-- Settings --}}
        <div>
            <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Settings</p>
            <div class="space-y-1">
                <a href="{{ route('categories.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition
                          {{ request()->routeIs('categories.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-700 text-gray-300' }}">
                    <i class="fas fa-tags w-5 text-center"></i>
                    <span>Categories</span>
                </a>
                <a href="{{ route('activity-logs.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition
                          {{ request()->routeIs('activity-logs.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-700 text-gray-300' }}">
                    <i class="fas fa-history w-5 text-center"></i>
                    <span>Activity Log</span>
                </a>
            </div>
        </div>

        {{-- Admin (visible only for admin users) --}}
        @if(Auth::user()->isAdmin())
        <div>
            <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Admin</p>
            <div class="space-y-1">
                <a href="{{ route('admin.users.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition
                          {{ request()->routeIs('admin.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-700 text-gray-300' }}">
                    <i class="fas fa-users-cog w-5 text-center"></i>
                    <span>Users</span>
                </a>
            </div>
        </div>
        @endif
    </nav>

    {{-- User info at bottom --}}
    <div class="px-4 py-4 border-t border-slate-700">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-indigo-500 flex items-center justify-center text-white text-sm font-bold">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <div class="truncate">
                <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-slate-400 truncate">{{ Auth::user()->email }}</p>
            </div>
        </div>
    </div>
</aside>
