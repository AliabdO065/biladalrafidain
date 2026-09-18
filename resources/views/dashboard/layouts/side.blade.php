<aside class="app-aside app-aside-expand-md app-aside-light">
    <div class="aside-content">
        <header class="aside-header d-block d-md-none">
            <button class="btn-account" type="button" data-toggle="collapse" data-target="#dropdown-aside">
                <span class="account-icon"><span class="oi oi-caret-bottom"></span></span>
                <span class="account-summary"><span class="account-name">{{ Auth::user()->name }}</span></span>
            </button>
            <div id="dropdown-aside" class="dropdown-aside collapse">
                <div class="pb-3">
                    <a class="dropdown-item" href="{{ route('logout') }}">
                        <span class="dropdown-icon"><i class="oi oi-account-logout"></i></span> {{ __('Logout') }}
                    </a>
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

                    <li class="menu-item @if(request()->routeIs('dashboard.landing.languages*')) has-active @endif"><a href="{{ route('dashboard.landing.languages') }}" class="menu-link"><span class="menu-icon oi oi-globe"></span><span class="menu-text">{{ __('Languages') }}</span></a></li>
                    <li class="menu-item @if(request()->routeIs('dashboard.landing.settings*')) has-active @endif"><a href="{{ route('dashboard.landing.settings') }}" class="menu-link"><span class="menu-icon oi oi-cog"></span><span class="menu-text">{{ __('Settings') }}</span></a></li>
                    {{-- <li class="menu-item @if(request()->routeIs('dashboard.landing.about*')) has-active @endif"><a href="{{ route('dashboard.landing.about') }}" class="menu-link"><span class="menu-icon oi oi-person"></span><span class="menu-text">{{ __('About Us') }}</span></a></li> --}}
                    {{-- <li class="menu-item @if(request()->routeIs('dashboard.landing.stats*')) has-active @endif"><a href="{{ route('dashboard.landing.stats') }}" class="menu-link"><span class="menu-icon oi oi-graph"></span><span class="menu-text">{{ __('Trust Stats') }}</span></a></li> --}}
                    {{-- <li class="menu-item @if(request()->routeIs('dashboard.landing.services*')) has-active @endif"><a href="{{ route('dashboard.landing.services') }}" class="menu-link"><span class="menu-icon oi oi-layers"></span><span class="menu-text">{{ __('Services') }}</span></a></li> --}}
                    {{-- <li class="menu-item @if(request()->routeIs('dashboard.landing.steps*')) has-active @endif"><a href="{{ route('dashboard.landing.steps') }}" class="menu-link"><span class="menu-icon oi oi-list-rich"></span><span class="menu-text">{{ __('3-Step Process') }}</span></a></li> --}}
                    {{-- <li class="menu-item @if(request()->routeIs('dashboard.landing.comparisons*')) has-active @endif"><a href="{{ route('dashboard.landing.comparisons') }}" class="menu-link"><span class="menu-icon oi oi-grid-two-up"></span><span class="menu-text">{{ __('Comparison Table') }}</span></a></li> --}}
                    {{-- <li class="menu-item @if(request()->routeIs('dashboard.landing.reviews*')) has-active @endif"><a href="{{ route('dashboard.landing.reviews') }}" class="menu-link"><span class="menu-icon oi oi-star"></span><span class="menu-text">{{ __('Reviews') }}</span></a></li> --}}
                    {{-- <li class="menu-item @if(request()->routeIs('dashboard.landing.faqs*')) has-active @endif"><a href="{{ route('dashboard.landing.faqs') }}" class="menu-link"><span class="menu-icon oi oi-question-mark"></span><span class="menu-text">{{ __('FAQ') }}</span></a></li> --}}
                    {{-- <li class="menu-item @if(request()->routeIs('dashboard.landing.leads*')) has-active @endif"><a href="{{ route('dashboard.landing.leads') }}" class="menu-link"><span class="menu-icon oi oi-phone"></span><span class="menu-text">{{ __('Callback Leads') }}</span></a></li> --}}
                    {{-- <li class="menu-item @if(request()->routeIs('dashboard.landing.shipments*')) has-active @endif"><a href="{{ route('dashboard.landing.shipments') }}" class="menu-link"><span class="menu-icon oi oi-box"></span><span class="menu-text">{{ __('Shipments') }}</span></a></li> --}}
                    <li class="menu-item @if(request()->routeIs('dashboard.landing.policies*')) has-active @endif"><a href="{{ route('dashboard.landing.policies') }}" class="menu-link"><span class="menu-icon oi oi-document"></span><span class="menu-text">{{ __('Policies') }}</span></a></li>

                    <hr style="border:0; height:4px; background:#333; margin:15px 0; border-radius:2px;">

                    <li class="menu-item has-child">
                        <a href="#" class="menu-link">
                            <span class="menu-icon oi oi-image"></span>
                            <span class="menu-text">{{ __('Stock Photos') }}</span>
                        </a>
                        <ul class="menu">
                            <li class="menu-item"><a href="https://unsplash.com/s/photos/electrician" class="menu-link"
                                    target="_blank">Unsplash Elektriker</a></li>
                            <li class="menu-item"><a href="https://www.pexels.com/search/electrician/" class="menu-link"
                                    target="_blank">Pexels Elektriker</a></li>
                            <li class="menu-item"><a href="https://pixabay.com/images/search/electrician/"
                                    class="menu-link" target="_blank">Pixabay Elektriker</a></li>
                            <li class="menu-item"><a href="https://www.freepik.com/free-photos-vectors/electrician"
                                    class="menu-link" target="_blank">Freepik Elektriker</a></li>
                            <li class="menu-item"><a href="https://stock.adobe.com/search?k=electrician" class="menu-link"
                                    target="_blank">Adobe Stock Elektriker</a></li>
                        </ul>
                    </li>

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
