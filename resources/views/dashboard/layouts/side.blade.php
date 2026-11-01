<aside class="app-aside app-aside-expand-md app-aside-light">
    <div class="aside-content">
        <header class="aside-header d-block d-md-none">
            <button class="btn-account" type="button" data-toggle="collapse" data-target="#dropdown-aside">
                <span class="account-icon"><span class="oi oi-caret-bottom"></span></span>
                <span class="account-summary"><span class="account-name">{{ Auth::user()->name }}</span></span>
            </button>
            <div id="dropdown-aside" class="dropdown-aside collapse">
                <div class="pb-3">
                    <form method="POST" action="{{ route('logout') }}" class="m-0">@csrf<button type="submit" class="dropdown-item"><span class="dropdown-icon"><i class="oi oi-account-logout"></i></span> {{ __('Logout') }}</button></form>
                </div>
            </div>
        </header>

        <div class="aside-menu overflow-hidden">
            <nav id="stacked-menu" class="stacked-menu">
                <ul class="menu">
                    <li class="menu-item @if(request()->routeIs('dashboard.index')) has-active @endif">
                        <a href="{{ route('dashboard.index') }}" class="menu-link">
                            <span class="menu-icon oi oi-dashboard"></span>
                            <span class="menu-text">{{ __('Dashboard') }}</span>
                        </a>
                    </li>

                    <li class="menu-item @if(request()->routeIs('dashboard.help')) has-active @endif">
                        <a href="{{ route('dashboard.help') }}" class="menu-link"><span class="menu-icon oi oi-book"></span><span class="menu-text">{{ __('guide.title') }}</span></a>
                    </li>

                    <li class="menu-header sidebar-section-label"><span class="d-compact-menu-none">{{ __('Operations Management') }}</span></li>
                    <li class="menu-item @if(request()->routeIs('dashboard.trips.*')) has-active @endif"><a href="{{ route('dashboard.trips.index') }}" class="menu-link"><span class="menu-icon oi oi-map"></span><span class="menu-text">{{ __('Trips') }}</span></a></li>
                    <li class="menu-item @if(request()->routeIs('dashboard.landing.shipments*')) has-active @endif"><a href="{{ route('dashboard.landing.shipments') }}" class="menu-link"><span class="menu-icon oi oi-box"></span><span class="menu-text">{{ __('Shipments') }}</span></a></li>
                    <li class="menu-item @if(request()->routeIs('dashboard.accounts.*')) has-active @endif">{{-- Phase 3/4: <a href="{{ route('dashboard.accounts.index') }}" class="menu-link"><span class="menu-icon oi oi-spreadsheet"></span><span class="menu-text">{{ __('Merchant Accounts') }}</span></a> --}}</li>
                    <li class="menu-item @if(request()->routeIs('dashboard.landing.leads*')) has-active @endif"><a href="{{ route('dashboard.landing.leads') }}" class="menu-link"><span class="menu-icon oi oi-phone"></span><span class="menu-text">{{ __('Callback Leads') }}</span></a></li>

{{--  Phase 3 deferred.
                    <li class="menu-header sidebar-section-label"><span class="d-compact-menu-none">{{ __('trade.title') }}</span></li>
                    @foreach(['index'=>['imports','data-transfer-upload'],'contacts'=>['contacts','people'],'messages'=>['messages','chat'],'templates'=>['templates','document']] as $action=>$item)
                    <li class="menu-item @if(request()->routeIs('dashboard.trade.'.$action)) has-active @endif"> Phase 3/4: <a href="{{ route('dashboard.trade.'.$action) }}" class="menu-link"><span class="menu-icon oi oi-{{ $item[1] }}"></span><span class="menu-text">{{ __('trade.'.$item[0]) }}</span></a> </li>
                    @endforeach

                     --}}
                    <li class="menu-header sidebar-section-label"><span class="d-compact-menu-none">{{ __('Content Management') }}</span></li>
                    <li class="menu-item @if(request()->routeIs('dashboard.catalog.*')) has-active @endif">{{-- Phase 3/4: <a href="{{ route('dashboard.catalog.index') }}" class="menu-link"><span class="menu-icon oi oi-grid-three-up"></span><span class="menu-text">{{ __('catalog.admin_title') }}</span></a> --}}</li>
                    <li class="menu-item @if(request()->routeIs('dashboard.landing.languages*')) has-active @endif"><a href="{{ route('dashboard.landing.languages') }}" class="menu-link"><span class="menu-icon oi oi-globe"></span><span class="menu-text">{{ __('Languages') }}</span></a></li>
                    <li class="menu-item @if(request()->routeIs('dashboard.landing.settings*')) has-active @endif"><a href="{{ route('dashboard.landing.settings') }}" class="menu-link"><span class="menu-icon oi oi-cog"></span><span class="menu-text">{{ __('Settings') }}</span></a></li>
                    <li class="menu-item @if(request()->routeIs('dashboard.landing.policies*')) has-active @endif"><a href="{{ route('dashboard.landing.policies') }}" class="menu-link"><span class="menu-icon oi oi-document"></span><span class="menu-text">{{ __('Policies') }}</span></a></li>

                </ul>
            </nav>
        </div>

        <footer class="aside-footer border-top p-2">
            <button class="btn btn-light btn-block text-primary" data-toggle="skin">
                <span class="d-compact-menu-none">{{ __('Night mode') }}</span> <i class="oi oi-moon ml-1"></i>
            </button>
        </footer>
    </div>
</aside>
