<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
        <a href="{{ route('web.index') }}" class="brand-link">
            <img
                src="{{ asset('images/logo.png') }}"
                alt="Veron Indonesia"
                class="brand-image opacity-75 shadow"
            />

            <span class="brand-text fw-light">Veron Indonesia</span>
        </a>
    </div>

    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul
                class="nav sidebar-menu flex-column"
                data-lte-toggle="treeview"
                role="navigation"
                aria-label="Main navigation"
                data-accordion="false"
                id="navigation"
            >
                @foreach (config('combobox.menu') as $item)

                    @if(!isset($item['permission']) || in_array($item['permission'], session('permission_security', [])))

                        @if (!isset($item['children']))
                            <li class="nav-item">
                                <a
                                    href="{{ route($item['route']) }}"
                                    class="nav-link {{ request()->routeIs($item['route']) ? 'active' : '' }}"
                                >
                                    <i class="nav-icon bi {{ $item['icon'] }}"></i>
                                    <p>{{ $item['label'] }}</p>
                                </a>
                            </li>
                        @endif

                    @endif

                    @if (isset($item['children']))

                        @php
                            $isOpen = collect($item['children'])->contains(function ($child) {
                                return request()->routeIs($child['route']);
                            });
                        @endphp

                        <li class="nav-item {{ $isOpen ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ $isOpen ? 'active' : '' }}">
                                <i class="nav-icon bi {{ $item['icon'] }}"></i>

                                <p>
                                    {{ $item['label'] }}
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                </p>
                            </a>

                            @php
                                $children = collect($item['children'])->filter(function($child){
                                    if(!isset($child['permission'])){
                                        return true;
                                    }

                                    return in_array($child['permission'], session('permission_security', []));

                                });
                            @endphp

                            <ul class="nav nav-treeview">
                                @foreach ($children as $child)

                                    <li class="nav-item">
                                        <a
                                            href="{{ route($child['route']) }}"
                                            class="nav-link {{ request()->routeIs($child['route']) ? 'active' : '' }}"
                                        >
                                            <i class="nav-icon {{ request()->routeIs($child['route']) ? 'bi-circle-fill' : 'bi-circle' }}"></i>

                                            <p>{{ $child['label'] }}</p>
                                        </a>
                                    </li>

                                @endforeach
                            </ul>
                        </li>

                    @endif

                @endforeach
            </ul>
        </nav>
    </div>
</aside>
