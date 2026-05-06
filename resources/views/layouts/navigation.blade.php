<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="flex items-center shrink-0">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block w-auto text-gray-800 fill-current h-9" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden sm:flex sm:items-center sm:ml-6 sm:space-x-8">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    @can('admin')
                    <x-nav-link :href="route('companies.index')" :active="request()->routeIs('companies.*')">
                        {{ __('Empresas') }}
                    </x-nav-link>
                    <x-nav-link :href="route('customers.index')" :active="request()->routeIs('customers.*')">
                        {{ __('Clientes') }}
                    </x-nav-link>
                    <x-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')">
                        {{ __('Productos') }}
                    </x-nav-link>
                    <x-nav-link :href="route('series.index')" :active="request()->routeIs('series.*')">
                        {{ __('Series') }}
                    </x-nav-link>
                    <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                        {{ __('Usuarios') }}
                    </x-nav-link>
                    @endcan
                    <!-- El botón de padrón SUNAT se movió a la página de Empresas -->

                    <!-- Generar Comprobante with Dropdown -->
                    <div class="relative" id="comprobante-menu">
                        <button type="button" onclick="toggleDropdown()" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none">
                            {{ __('Generar Comprobante') }}
                            <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <div id="comprobante-dropdown" class="absolute left-0 z-50 hidden w-48 py-1 mt-2 bg-white rounded-md shadow-lg">
                            <a href="{{ route('invoices.index', ['type' => '01']) }}" onclick="closeDropdown()" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                {{ __('Facturas') }}
                            </a>
                            <a href="{{ route('invoices.index', ['type' => '03']) }}" onclick="closeDropdown()" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                {{ __('Boletas') }}
                            </a>
                            <a href="{{ route('invoices.index', ['type' => '07']) }}" onclick="closeDropdown()" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                {{ __('Notas de Crédito') }}
                            </a>
                            <a href="{{ route('invoices.index', ['type' => 'NV']) }}" onclick="closeDropdown()" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                {{ __('Notas de Venta') }}
                            </a>
                            <div class="border-t border-gray-100"></div>
                            <a href="{{ route('invoices.create') }}" onclick="closeDropdown()" class="block px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                                + {{ __('Nuevo Comprobante') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Dropdown -->
            @auth
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out bg-white border border-transparent rounded-md hover:text-gray-700 focus:outline-none">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ml-1">
                                <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}" class="mt-2">
                            @csrf
                            <button type="submit" class="w-full px-3 py-2 text-sm text-left text-gray-700 hover:bg-gray-100">
                                {{ __('Log Out') }}
                            </button>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
            @endauth

            <!-- Hamburger -->
            <div class="flex items-center -mr-2 sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 text-gray-400 transition duration-150 ease-in-out rounded-md hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500">
                    <svg class="w-6 h-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
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
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            @can('admin')
            <x-responsive-nav-link :href="route('companies.index')" :active="request()->routeIs('companies.*')">
                {{ __('Empresas') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('customers.index')" :active="request()->routeIs('customers.*')">
                {{ __('Clientes') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')">
                {{ __('Productos') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('series.index')" :active="request()->routeIs('series.*')">
                {{ __('Series') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                {{ __('Usuarios') }}
            </x-responsive-nav-link>
            @endcan
        </div>

        <!-- Responsive Settings Options -->
        @auth
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="text-base font-medium text-gray-800">{{ Auth::user()->name }}</div>
                <div class="text-sm font-medium text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
        @endauth
    </div>
</nav>

<script>
function toggleDropdown() {
    var dropdown = document.getElementById('comprobante-dropdown');
    dropdown.classList.toggle('hidden');
}

function closeDropdown() {
    document.getElementById('comprobante-dropdown').classList.add('hidden');
}

document.addEventListener('click', function(event) {
    var menu = document.getElementById('comprobante-menu');
    var dropdown = document.getElementById('comprobante-dropdown');
    if (menu && !menu.contains(event.target)) {
        dropdown.classList.add('hidden');
    }
});
</script>
