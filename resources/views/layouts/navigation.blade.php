<nav x-data="{ open: false }" class="navbar text-white bg-gradient-to-r from-[#0D2660] via-[#102E66] to-[#0A1E46] shadow">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-10 w-auto" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('nav.dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('jobs.index')" :active="request()->routeIs('jobs.*')">
                        {{ __('nav.jobs') }}
                    </x-nav-link>
                    @if(auth()->check() && (auth()->user()->role ?? null)==='admin')
                        <x-nav-link :href="route('admin.settings.index')" :active="request()->routeIs('admin.settings.*')">
                            القوائم المنسدلة
                        </x-nav-link>
                        <x-nav-link :href="route('admin.districts.index')" :active="request()->routeIs('admin.districts.*')">
                            إدارة المناطق
                        </x-nav-link>
                    @endif
                    @php($unread = auth()->check() ? auth()->user()->unreadNotifications()->count() : 0)
                    <x-dropdown align="right" width="80">
                        <x-slot name="trigger">
                            <button class="btn btn-ghost btn-circle text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-gray-600 dark:text-gray-300">
                                    <path d="M12 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 006 14h12a1 1 0 00.707-1.707L18 11.586V8a6 6 0 00-6-6z"/>
                                    <path d="M9 18a3 3 0 006 0H9z"/>
                                </svg>
                                @if($unread>0)
                                    <span class="absolute -top-1 -right-1 text-[10px] px-1.5 py-0.5 rounded-full bg-red-600 text-white">{{ $unread }}</span>
                                @endif
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <div class="px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200">{{ __('nav.notifications') }}</div>
                            <div class="max-h-80 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700">
                                @php($items = auth()->check() ? auth()->user()->notifications()->latest()->take(10)->get() : collect())
                                @forelse($items as $n)
                                    <div class="px-4 py-2">
                                        <div class="font-medium text-gray-800 dark:text-gray-100">{{ data_get($n->data,'title') }}</div>
                                        <div class="text-xs text-gray-600 dark:text-gray-300">{{ data_get($n->data,'message') }}</div>
                                        <div class="mt-1 flex items-center justify-between text-[11px] text-gray-400">
                                            <span>{{ $n->created_at->diffForHumans() }}</span>
                                            @if(is_null($n->read_at))
                                            <form method="POST" action="{{ route('notifications.read',$n->id) }}">
                                                @csrf
                                                <button class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ __('notifications.mark_read') }}</button>
                                            </form>
                                            @else
                                                <span>{{ __('notifications.read') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="px-4 py-2 text-sm text-gray-500">{{ __('notifications.none') }}</div>
                                @endforelse
                            </div>
                            <div class="px-4 py-2 flex items-center justify-between">
                                <a class="text-indigo-600 dark:text-indigo-400 text-sm hover:underline" href="{{ route('notifications.index') }}">{{ __('notifications.view_all') }}</a>
                                <form method="POST" action="{{ route('notifications.read_all') }}">
                                    @csrf
                                    <button class="text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">{{ __('notifications.mark_all_read') }}</button>
                                </form>
                            </div>
                        </x-slot>
                    </x-dropdown>
                <!-- Locale Switcher -->
                <div class="hidden sm:flex items-center gap-2 ms-6">
                    <a href="{{ route('locale.switch','en') }}" class="text-sm {{ app()->getLocale()==='en'?'font-bold':'' }}">EN</a>
                    <span class="text-gray-400">|</span>
                    <a href="{{ route('locale.switch','ar') }}" class="text-sm {{ app()->getLocale()==='ar'?'font-bold':'' }}">AR</a>
                    <span class="text-gray-400">|</span>
                    <a href="{{ route('locale.switch','ku') }}" class="text-sm {{ app()->getLocale()==='ku'?'font-bold':'' }}">KU</a>
                </div>
                <!-- Theme Switcher -->
                <div x-data="{ t: localStorage.getItem('theme') || 'brand', init(){ document.documentElement.setAttribute('data-theme', this.t); }, toggle(){ this.t = this.t==='brand' ? 'brand-dark' : 'brand'; localStorage.setItem('theme', this.t); document.documentElement.setAttribute('data-theme', this.t); } }" class="ms-4 hidden sm:flex items-center text-white">
                    <button @click="toggle()" class="btn btn-ghost btn-circle text-white" aria-label="Toggle theme">
                        <svg x-show="t==='brand'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M21.64 13A9 9 0 1111 2.36 7 7 0 0021.64 13z"/></svg>
                        <svg x-show="t==='brand-dark'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M6.76 4.84l-1.8-1.79L3.17 4.84l1.79 1.79 1.8-1.79zM1 13h3v-2H1v2zm10 9h2v-3h-2v3zM4.84 19.16l1.8-1.79-1.8-1.8-1.79 1.8 1.79 1.79zM20 13h3v-2h-3v2zm-8-9h2V1h-2v3zm7.24 1.84l1.79-1.79-1.79-1.8-1.8 1.8 1.8 1.79zM12 6a6 6 0 100 12 6 6 0 000-12z"/></svg>
                    </button>
                </div>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name ?? __('nav.profile') }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('nav.profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('nav.logout') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('nav.dashboard') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('nav.profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('nav.logout') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
