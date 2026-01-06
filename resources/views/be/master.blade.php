<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('be/img/apple-icon.png') }}">
  <link rel="icon" type="image/png" href="{{ asset('be/img/favicon.png') }}">

  <title>@yield('title', 'Monitoring')</title>

  {{-- Fonts & Icons --}}
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
  <link href="{{ asset('be/css/nucleo-icons.css') }}" rel="stylesheet" />
  <link href="{{ asset('be/css/nucleo-svg.css') }}" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>

  {{-- CSS --}}
  <link id="pagestyle" href="{{ asset('be/css/soft-ui-dashboard.css?v=1.0.3') }}" rel="stylesheet" />
</head>

<body class="g-sidenav-show bg-gray-100">

  {{-- SIDEBAR --}}
  @include('be.sidebar')

  <main class="main-content position-relative max-height-vh-100 h-100 mt-1 border-radius-lg">

    {{-- NAVBAR --}}
    @include('be.navbar')

    {{-- CONTENT --}}
    <div class="container-fluid py-4">
      @yield('content')
    </div>

    {{-- FOOTER --}}
    @include('be.footer')

  </main>

  {{-- JS CORE --}}
  <script src="{{ asset('be/js/core/popper.min.js') }}"></script>
  <script src="{{ asset('be/js/core/bootstrap.min.js') }}"></script>
  <script src="{{ asset('be/js/plugins/perfect-scrollbar.min.js') }}"></script>
  <script src="{{ asset('be/js/plugins/smooth-scrollbar.min.js') }}"></script>
  <script src="{{ asset('be/js/plugins/chartjs.min.js') }}"></script>
  {{-- script getLiveStats --}}
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  {{-- JS CORE --}}
  <script src="{{ asset('be/js/soft-ui-dashboard.min.js?v=1.0.3') }}"></script>

  {{-- PAGE SCRIPT --}}
  @yield('script')

</body>
</html>
