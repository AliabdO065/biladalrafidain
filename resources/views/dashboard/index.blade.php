@extends('dashboard.layouts.layout')

@section('content')
<div class="wrapper">
  <!-- .page -->
  <div class="page">
    <!-- .page-inner -->
    <div class="page-inner">
      <!-- .page-title-bar -->
      <header class="page-title-bar">
        <div class="d-flex flex-column flex-md-row">
          <p class="lead">
            <span class="font-weight-bold">{{ __('Hi, :name', ['name' => Auth::user()->name]) }}</span>
            <span class="d-block text-muted">
              {{ __("Here's what's up with your business today!") }} <br>
              {{ __("Let's catch up on what's happening with your business!") }}
            </span>
          </p>
          <div class="ml-auto"></div>
        </div>
        <style>
          .bord {
            border: 2px skyblue solid;
          } 
        </style>
      </header>
      <!-- /.page-title-bar -->

      <!-- .page-section -->
      <div class="page-section">
        <!-- .section-block -->
        <div class="section-block">
          <!-- metric row -->
          <div class="metric-row">
            <div class="col-lg-9 mt-5">
              <div class="metric-row metric-flush">
                <div class="col ml-6 bord">
                  <!-- .metric -->
                  <a href="{{route('dashboard.landing.services')}}" class="metric metric-bordered align-items-center">
                    <h2 class="metric-label"> {{ __('Services') }} </h2>
                    <p class="metric-value h3">
                      <sub><i class="oi oi-layers"></i></sub> <span class="value">{{$servicesCount}} </span>
                    </p>
                  </a> <!-- /.metric -->
                </div><!-- /metric column -->

                <div class="col ml-6 bord">
                  <!-- .metric -->
                  <a href="{{route('dashboard.landing.reviews')}}" class="metric metric-bordered align-items-center">
                    <h2 class="metric-label"> {{ __('Reviews') }} </h2>
                    <p class="metric-value h3">
                      <sub><i class="oi oi-star"></i></sub> <span class="value">{{$reviewsCount}} </span>
                    </p>
                  </a> <!-- /.metric -->
                </div><!-- /metric column -->
              </div>
            </div><!-- metric column -->

            <div class="col-lg-9">
              <div class="metric-row metric-flush">
                <div class="col ml-6 bord">
                  <!-- .metric -->
                  <a href="{{route('dashboard.landing.faqs')}}" class="metric metric-bordered align-items-center">
                    <h2 class="metric-label"> {{ __('FAQ') }} </h2>
                    <p class="metric-value h3">
                      <sub><i class="oi oi-document"></i></sub> <span class="value">{{$faqsCount}} </span>
                    </p>
                  </a> <!-- /.metric -->
                </div><!-- /metric column -->

                <div class="col ml-6 bord">
                  <!-- .metric -->
                  <a href="{{route('dashboard.landing.leads')}}" class="metric metric-bordered align-items-center">
                    <h2 class="metric-label"> {{ __('Callback Leads') }} </h2>
                    <p class="metric-value h3">
                      <sub><i class="oi oi-phone"></i></sub> <span class="value">{{$leadsCount}} </span>
                    </p>
                  </a> <!-- /.metric -->
                </div><!-- /metric column -->
              </div>
            </div><!-- metric column -->
          </div><!-- /metric row -->
        </div><!-- /.section-block -->

        <!-- grid row -->
        <div class="row">
          <!-- grid column -->
        </div><!-- /grid row -->
      </div><!-- /.page-section -->
    </div><!-- /.page-inner -->
  </div><!-- /.page -->
</div>
@endsection
