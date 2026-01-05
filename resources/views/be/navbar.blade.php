<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" navbar-scroll="true">
      <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm">
              <a class="opacity-5 text-dark" href="javascript:;">Pages</a>
            </li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">
              {{ $breadcrumb ?? 'Dashboard' }}
            </li>
          </ol>
          <h6 class="font-weight-bolder mb-0">
            {{ $title ?? 'Dashboard' }}
          </h6>
        </nav>

        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
          <div class="ms-md-auto pe-md-3 d-flex align-items-center">
            <form method="GET" action="{{ route('traffic.index') }}" class="w-100">
              <input type="hidden" name="per_page" value="{{ request('per_page', $perPage ?? 5) }}">

              @if(request('open_filter'))
                <input type="hidden" name="open_filter" value="1">
              @endif

              <div class="input-group">
                <span class="input-group-text text-body">
                  <i class="fas fa-search"></i>
                </span>
                <input type="text"
                      name="search"
                      value="{{ request('search') }}"
                      class="form-control"
                      placeholder="Search IP / Hostname / Protocol...">
              </div>
            </form>

          </div>
          <ul class="navbar-nav justify-content-end">
            {{-- USER INFO & LOGOUT --}}
            <li class="nav-item dropdown pe-2 d-flex align-items-center">
              <a href="javascript:;" class="nav-link text-body font-weight-bold px-0" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-user me-sm-1"></i>
                {{-- Menampilkan Nama User yang sedang Login --}}
                <span class="d-sm-inline d-none">{{ Auth::user()->name }}</span>
              </a>
              <ul class="dropdown-menu dropdown-menu-end px-2 py-3 me-sm-n4" aria-labelledby="dropdownUser">
                <li class="mb-2">
                  <div class="dropdown-item border-radius-md">
                    <div class="d-flex py-1">
                      <div class="d-flex flex-column justify-content-center">
                        <h6 class="text-sm font-weight-normal mb-1">
                          <span class="font-weight-bold">Logged in as:</span>
                        </h6>
                        {{-- Menampilkan Email User --}}
                        <p class="text-xs text-secondary mb-0">
                          <i class="fa fa-envelope me-1"></i>
                          {{ Auth::user()->email }}
                        </p>
                      </div>
                    </div>
                  </div>
                </li>
                <hr class="horizontal dark mt-0">
                <li>
                  <a class="dropdown-item border-radius-md text-danger" href="javascript:;" 
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <div class="d-flex py-1">
                      <div class="my-auto">
                        <i class="fa fa-sign-out me-3 text-danger"></i>
                      </div>
                      <div class="d-flex flex-column justify-content-center">
                        <h6 class="text-sm font-weight-bold mb-1">Logout</h6>
                      </div>
                    </div>
                  </a>
                  {{-- Hidden Form untuk Logout (Keamanan Laravel) --}}
                  <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                  </form>
                </li>
              </ul>
            </li>

            {{-- RESPONSIVE TOGGLER (Jangan diubah) --}}
            <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
              <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
                <div class="sidenav-toggler-inner">
                  <i class="sidenav-toggler-line"></i>
                  <i class="sidenav-toggler-line"></i>
                  <i class="sidenav-toggler-line"></i>
                </div>
              </a>
            </li>

            {{-- SETTINGS ICON --}}
            <li class="nav-item px-3 d-flex align-items-center">
              <a href="javascript:;" class="nav-link text-body p-0">
                <i class="fa fa-cog fixed-plugin-button-nav cursor-pointer"></i>
              </a>
            </li>

            {{-- NOTIFICATION DROPDOWN (Lu bisa biarin isinya kalau mau ada notif dummy) --}}
            <li class="nav-item dropdown pe-2 d-flex align-items-center">
              <a href="javascript:;" class="nav-link text-body p-0" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-bell cursor-pointer"></i>
              </a>
              <ul class="dropdown-menu dropdown-menu-end px-2 py-3 me-sm-n4" aria-labelledby="dropdownMenuButton">
                <li class="mb-2">
                  <a class="dropdown-item border-radius-md" href="javascript:;">
                    <div class="d-flex py-1">
                      <div class="my-auto">
                        <img src="{{asset('be/img/team-2.jpg')}}" class="avatar avatar-sm me-3 ">
                      </div>
                      <div class="d-flex flex-column justify-content-center">
                        <h6 class="text-sm font-weight-normal mb-1">
                          <span class="font-weight-bold">System Online</span>
                        </h6>
                        <p class="text-xs text-secondary mb-0">
                          Connected to Postgres Server
                        </p>
                      </div>
                    </div>
                  </a>
                </li>
              </ul>
            </li>
          </ul>
        </div>
      </div>
    </nav>