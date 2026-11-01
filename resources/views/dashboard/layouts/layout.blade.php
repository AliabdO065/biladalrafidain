<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="ltr">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"><!-- End Required meta tags -->
    <!-- Begin SEO tag -->
    <title>{{ __('Dashboard') }} — {{ __('Biladalrafidain') }}</title>
    <meta name="robots" content="noindex, nofollow">
    <link href="{{ asset('dashboard/assets/vendor/bootstrap5/bootstrap.min.css') }}" rel="stylesheet">
    <!-- FAVICONS -->
    <link rel="apple-touch-icon" sizes="144x144" href="{{asset('dashboard/assets/apple-touch-icon.png')}}">
    <link rel="shortcut icon" href="{{asset('dashboard/assets/favicon.ico')}}">
    <meta name="theme-color" content="#3063A0"><!-- End FAVICONS -->
    <!-- GOOGLE FONT -->
    <link href="https://fonts.googleapis.com/css?family=Fira+Sans:400,500,600" rel="stylesheet"><!-- End GOOGLE FONT -->
    <!-- BEGIN PLUGINS STYLES -->
    <link rel="stylesheet" href="{{asset('dashboard/assets/vendor/open-iconic/font/css/open-iconic-bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('dashboard/assets/vendor/@fortawesome/fontawesome-free/css/all.min.css')}}">
    <!-- END PLUGINS STYLES -->
    <!-- BEGIN THEME STYLES -->
    <link rel="stylesheet" href="{{asset('dashboard/assets/stylesheets/theme.min.css')}}" data-skin="default">
    <link rel="stylesheet" href="{{asset('dashboard/assets/stylesheets/theme-dark.min.css')}}" data-skin="dark">
    <link rel="stylesheet" href="{{ asset('dashboard/assets/stylesheets/custom.css') }}?v={{ filemtime(public_path('dashboard/assets/stylesheets/custom.css')) }}">

    <script>
      var skin = 'default', isCompact = false;
      try {
        var storedSkin = localStorage.getItem('skin');
        skin = storedSkin === 'dark' ? 'dark' : 'default';
        isCompact = localStorage.getItem('hasCompactMenu') === 'true';
      } catch (e) {}
      var disabledSkinStylesheet = document.querySelector('link[data-skin]:not([data-skin="' + skin + '"])');
      // Disable unused skin immediately
      disabledSkinStylesheet.setAttribute('rel', '');
      disabledSkinStylesheet.setAttribute('disabled', true);
      // add flag class to html immediately
      if (isCompact == true) document.querySelector('html').classList.add('preparing-compact-menu');
    </script><!-- END THEME STYLES -->
    @stack('styles')
    <link rel="stylesheet" href="{{ asset('dashboard/assets/stylesheets/operations.css') }}?v={{ filemtime(public_path('dashboard/assets/stylesheets/operations.css')) }}">
    <link rel="stylesheet" href="{{ asset('dashboard/assets/stylesheets/tables.css') }}?v={{ filemtime(public_path('dashboard/assets/stylesheets/tables.css')) }}">
    <link rel="stylesheet" href="{{ asset('dashboard/assets/stylesheets/dark.css') }}?v={{ filemtime(public_path('dashboard/assets/stylesheets/dark.css')) }}">
    <link rel="stylesheet" href="{{ asset('dashboard/assets/stylesheets/workspace.css') }}?v={{ filemtime(public_path('dashboard/assets/stylesheets/workspace.css')) }}">
  </head>
  <body data-busy-label="{{ __('flow.busy') }}">
    <!-- .app -->
    <div class="app">
      <!--[if lt IE 10]>
      <div class="page-message" role="alert">You are using an <strong>outdated</strong> browser. Please <a class="alert-link" href="http://browsehappy.com/">upgrade your browser</a> to improve your experience and security.</div>
      <![endif]-->
      <!-- .app-header -->
      <header class="app-header app-header-dark">
        <!-- .top-bar -->
        <div class="top-bar">
          <!-- .top-bar-brand -->
          <div class="top-bar-brand">
            <!-- toggle aside menu -->
            <button class="hamburger hamburger-squeeze mr-2" type="button" data-toggle="aside-menu" aria-label="toggle aside menu"><span class="hamburger-box"><span class="hamburger-inner"></span></span></button> <!-- /toggle aside menu -->
            <a href="{{route('dashboard.index')}}" style="font-size: 24px; font-weight: bold; color: #ffffff; text-decoration: none;">
                {{ __('Biladalrafidain') }}
            </a>
          </div><!-- /.top-bar-brand -->
          <!-- .top-bar-list -->
          <div class="top-bar-list">
            <!-- .top-bar-item -->
            <div class="top-bar-item px-2 d-md-none d-lg-none d-xl-none">
              <!-- toggle menu -->
              <button class="hamburger hamburger-squeeze" type="button" data-toggle="aside" aria-label="toggle menu"><span class="hamburger-box"><span class="hamburger-inner"></span></span></button> <!-- /toggle menu -->
            </div><!-- /.top-bar-item -->
            <!-- .top-bar-item -->
            <div class="top-bar-item top-bar-item-full">
            </div><!-- /.top-bar-item -->
            <!-- .top-bar-item -->
            <div class="top-bar-item top-bar-item-right px-0 d-none d-sm-flex">
              <!-- .nav -->
              <ul class="header-nav nav">
                <!-- .nav-item -->
                <!-- .nav-item -->
                <a href="{{route('dashboard.landing.leads')}}" class="nav-item">
                  <a class="nav-link" href="{{route('dashboard.landing.leads')}}">
                    <span class="oi oi-envelope-open"></span></a> <!-- .dropdown-menu -->
                </a>

                <!-- .nav-item -->
                <li class="nav-item dropdown header-nav-dropdown">
                  <a class="nav-link" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="oi oi-globe"></span></a> <!-- .dropdown-menu -->
                  <div class="dropdown-menu dropdown-menu-rich dropdown-menu-right">
                    <div class="dropdown-arrow"></div>
                    <div class="dropdown-sheets">
                      @foreach(['en' => 'English', 'ar' => 'العربية'] as $code => $label)
                        <a class="dropdown-item @if(app()->getLocale() === $code) active @endif"
                           href="{{ route('dashboard.setLocale', $code) }}">
                          {{ $label }}
                        </a>
                      @endforeach
                    </div>
                  </div>
                </li><!-- /.nav-item -->

                <!-- .nav-item -->
                <li class="nav-item dropdown header-nav-dropdown">
                  <a class="nav-link" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="oi oi-grid-three-up"></span></a> <!-- .dropdown-menu -->
                  <div class="dropdown-menu dropdown-menu-rich dropdown-menu-right">
                    <div class="dropdown-arrow"></div><!-- .dropdown-sheets -->
                    <div class="dropdown-sheets">
                      <!-- .dropdown-sheet-item -->



                      <div class="dropdown-sheet-item">
  <a href="{{route('dashboard.trips.index')}}" class="tile-wrapper">
    <span class="tile tile-lg bg-indigo"><i class="oi oi-layers"></i></span>
    <span class="tile-peek">{{ __('Trips') }}</span>
  </a>
</div>

{{-- Deferred tile. --}}

<div class="dropdown-sheet-item">
  <a href="{{route('dashboard.landing.leads')}}" class="tile-wrapper">
    <span class="tile tile-lg bg-yellow"><i class="oi oi-phone"></i></span>
    <span class="tile-peek">{{ __('Callback Leads') }}</span>
  </a>
</div>



                      <!-- .dropdown-sheet-item -->
                    </div><!-- .dropdown-sheets -->
                  </div><!-- .dropdown-menu -->
                </li><!-- /.nav-item -->
              </ul><!-- /.nav -->
              <!-- .btn-account -->
              <div class="dropdown d-none d-md-flex">
                <button class="btn-account" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="user-avatar user-avatar-md"></span> <span class="account-summary pr-lg-4 d-none d-lg-block"><span class="account-name">{{Auth::user()->name}} </span></span></button> <!-- .dropdown-menu -->
                <div class="dropdown-menu">
                  <div class="dropdown-arrow d-lg-none" x-arrow=""></div>
                  <div class="dropdown-arrow ml-3 d-none d-lg-block"></div>
                  <h6 class="dropdown-header d-none d-md-block d-lg-none">{{Auth::user()->name}} </h6>
                  <form method="POST" action="{{ route('logout') }}" class="m-0">@csrf<button type="submit" class="dropdown-item"><span class="dropdown-icon oi oi-account-logout"></span> {{ __('Logout') }}</button></form>
                </div><!-- /.dropdown-menu -->
              </div><!-- /.btn-account -->
            </div><!-- /.top-bar-item -->
          </div><!-- /.top-bar-list -->
        </div><!-- /.top-bar -->
      </header>
      
      
      
      <!-- /.app-header -->

@include('dashboard.layouts.side')
      

      <!-- .app-main -->
      <main class="app-main">
        @include('dashboard.partials.workspace')
        <!-- .wrapper -->
        @yield('content')


      </main>
      
      <!-- /.app-main -->
    </div><!-- /.app -->
    <!-- BEGIN BASE JS -->
    <script src="{{asset('dashboard/assets/vendor/jquery/jquery.min.js')}}"></script>
    <script src="{{asset('dashboard/assets/vendor/popper.js/umd/popper.min.js')}}"></script>
    <script src="{{asset('dashboard/assets/vendor/bootstrap/js/bootstrap.min.js')}}"></script> <!-- END BASE JS -->
    <!-- BEGIN PLUGINS JS -->
    <script src="{{asset('dashboard/assets/vendor/pace-progress/pace.min.js')}}"></script>
    <script src="{{asset('dashboard/assets/vendor/stacked-menu/js/stacked-menu.min.js')}}"></script>
    <script src="{{asset('dashboard/assets/vendor/perfect-scrollbar/perfect-scrollbar.min.js')}}"></script>
    <!-- END PLUGINS JS -->
    <!-- BEGIN THEME JS -->
    <script src="{{asset('dashboard/assets/javascript/theme.min.js')}}"></script> <!-- END THEME JS -->
    <!-- BEGIN PAGE LEVEL JS -->
    <!-- END PAGE LEVEL JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('dashboard/assets/vendor/bootstrap5/bootstrap.bundle.min.js') }}"></script>
  
<script>
function initDeleteConfirm() {
document.querySelectorAll('.delete-confirm').forEach(button => {
    button.addEventListener('click', function (e) {
        e.preventDefault();

        const confirmation = typeof Swal !== 'undefined' ? Swal.fire({
            title: @js(__('Are you sure?')),
            text: @js(__('This action cannot be undone!')),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff0000',
            cancelButtonColor: '#6c757d',
            confirmButtonText: @js(__('Yes, delete it!')),
            cancelButtonText: @js(__('Cancel'))
        }) : Promise.resolve({isConfirmed: window.confirm(@js(__('Are you sure?')))});
        confirmation.then((result) => {
            if (result.isConfirmed) {
                fetch(this.getAttribute('href'), {method:'DELETE', headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'}})
                    .then(response => {
                        if (!response.ok) throw new Error('Delete failed');
                        location.reload();
                    })
                    .catch(() => {
                        if (typeof Swal !== 'undefined') Swal.fire(@js(__('Error!')), @js(__('Something went wrong.')), 'error');
                        else window.alert(@js(__('Something went wrong.')));
                    });
            }
        });
    });
});


    
}

// call it once on page load
document.addEventListener('DOMContentLoaded', initDeleteConfirm);
document.querySelectorAll('form[data-once]').forEach(form => {
    form.addEventListener('submit', event => {
        if (form.dataset.busy === 'true') { event.preventDefault(); return; }
        form.dataset.busy = 'true';
        form.setAttribute('aria-busy', 'true');
        const clicked = event.submitter;
        if (clicked && clicked.name) {
            const value = document.createElement('input');
            value.type = 'hidden'; value.name = clicked.name; value.value = clicked.value;
            value.dataset.busySubmitValue = 'true'; form.append(value);
        }
        form.querySelectorAll('button[type="submit"], button:not([type])').forEach(button => {
            if (!button.disabled) { button.dataset.busyDisabled = 'true'; button.disabled = true; }
        });
        if (clicked) {
            const indicator = document.createElement('span');
            indicator.className = 'flow-spinner'; indicator.setAttribute('aria-hidden', 'true'); clicked.prepend(indicator);
        }
    });
});
window.addEventListener('pageshow', () => {
    document.querySelectorAll('form[data-busy="true"]').forEach(form => {
        delete form.dataset.busy; form.removeAttribute('aria-busy');
        form.querySelectorAll('[data-busy-disabled]').forEach(button => {button.disabled = false; delete button.dataset.busyDisabled;});
        form.querySelectorAll('.flow-spinner,[data-busy-submit-value]').forEach(element => element.remove());
    });
});
</script>

    @stack('scripts')
  </body>
</html>
