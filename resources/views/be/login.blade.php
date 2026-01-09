<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>

  {{-- Fonts & Icons --}}
  <link href="{{ asset('be/css/nucleo-icons.css') }}" rel="stylesheet" />
  <link href="{{ asset('be/css/nucleo-svg.css') }}" rel="stylesheet" />

  {{-- Soft UI CSS --}}
  <link id="pagestyle"
        href="{{ asset('be/css/soft-ui-dashboard.css?v=1.0.3') }}"
        rel="stylesheet" />
</head>

<body>
<main class="main-content mt-0">
  <section>
    <div class="page-header min-vh-75">
      <div class="container">
        <div class="row">

          {{-- LOGIN CARD --}}
          <div class="col-xl-4 col-lg-5 col-md-6 d-flex flex-column mx-auto">
            <div class="card card-plain mt-8">

              <div class="card-header pb-0 text-left bg-transparent">
                <h3 class="font-weight-bolder text-info text-gradient">
                  Welcome
                </h3>
                <p class="mb-0">Sign in to continue</p>
              </div>

              <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger text-white text-sm py-2">
                        {{ $errors->first() }}
                    </div>
                @endif
                <form method="POST" action="{{ route('login.process') }}">
                  @csrf

                  <label>Email</label>
                  <div class="mb-3">
                    <input type="email" name="email" class="form-control" required>
                  </div>

                  <label>Password</label>
                  <div class="mb-3">
                    <input type="password" name="password" class="form-control" required>
                  </div>

                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="remember">
                    <label class="form-check-label">Remember me</label>
                  </div>

                  <div class="text-center">
                    <button type="submit" class="btn bg-gradient-info w-100 mt-4">
                      Sign in
                    </button>
                  </div>
                </form>
              </div>

            </div>
          </div>

          {{-- RIGHT IMAGE --}}
          <div class="col-md-6">
            <div class="oblique position-absolute top-0 h-100 d-md-block d-none me-n8">
              <div
                class="oblique-image bg-cover position-absolute fixed-top ms-auto h-100 z-index-0 ms-n6"
                style="background-image: url('{{ asset('be/img/curved-images/curved6.jpg') }}');">
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </section>
</main>

{{-- CORE JS --}}
<script src="{{ asset('be/js/core/popper.min.js') }}"></script>
<script src="{{ asset('be/js/core/bootstrap.min.js') }}"></script>
<script src="{{ asset('be/js/plugins/perfect-scrollbar.min.js') }}"></script>
<script src="{{ asset('be/js/plugins/smooth-scrollbar.min.js') }}"></script>
<script src="{{ asset('be/js/soft-ui-dashboard.min.js?v=1.0.3') }}"></script>

</body>
</html>
