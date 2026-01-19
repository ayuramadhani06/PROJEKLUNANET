@extends('be.master')

@php
  $title = 'Profile Settings';
  $breadcrumb = 'Profile';
@endphp

@section('content')
<div class="container-fluid py-4">
    
    <div class="card">
        <div class="card-header pb-0">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">
                        Profile Information
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                        Privacy & Security
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content mt-4">

                {{-- TAB INFORMASI PROFIL --}}
                <div class="tab-pane fade show active" id="profile" role="tabpanel">
                    <form id="formUpdateProfile" action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        <div class="row justify-content-center">
                            <div class="col-md-10">
                                <div class="table-responsive">
                                    <table class="table table-borderless align-middle">
                                        <tbody>
                                            <tr>
                                                <th width="30%">Name</th>
                                                <td><input type="text" name="name" class="form-control" value="{{ Auth::user()->name }}" required></td>
                                            </tr>
                                            <tr>
                                                <th>Email</th>
                                                <td><input type="email" name="email" class="form-control" value="{{ Auth::user()->email }}" required></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-end mt-3">
                                    <button type="button" onclick="confirmSubmit('formUpdateProfile')" class="btn btn-info" style="background: linear-gradient(135deg, #8b0000 0%, #4a0e4e 100%);">Save Changes</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- TAB PRIVASI & KEAMANAN --}}
                <div class="tab-pane fade" id="security" role="tabpanel">
                    <form id="formUpdatePassword" action="{{ route('profile.password') }}" method="POST">
                        @csrf
                        <div class="row justify-content-center">
                            <div class="col-md-10">
                                <div class="table-responsive">
                                    <table class="table table-borderless align-middle">
                                        <tbody>
                                            <tr>
                                                <th width="30%">Old Password</th>
                                                <td><input type="password" name="old_password" class="form-control" required></td>
                                            </tr>
                                            <tr>
                                                <th>New Password</th>
                                                <td><input type="password" name="password" class="form-control" required></td>
                                            </tr>
                                            <tr>
                                                <th>Confirm New Password</th>
                                                <td><input type="password" name="password_confirmation" class="form-control" required></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-end mt-3">
                                    <button type="button" onclick="confirmSubmit('formUpdatePassword')" class="btn btn-info" style="background: linear-gradient(135deg, #8b0000 0%, #4a0e4e 100%);">Update Password</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPT SWEETALERT2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // 1. Alert Konfirmasi "Are you sure?"
    function confirmSubmit(formId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to save the changes?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#8b0000', // Warna Info
            cancelButtonColor: '#8392ab', // Warna Danger
            confirmButtonText: 'Yes, Save it!',
            cancelButtonText: 'No, Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(formId).submit();
            }
        })
    }

    // 2. Alert Berhasil (Jika ada session success)
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: "{{ session('success') }}",
            timer: 2000,
            showConfirmButton: false
        });
    @endif

    // 3. Alert Gagal (Jika ada error validasi)
    @if($errors->any())
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: "{{ $errors->first() }}",
        });
    @endif
</script>
@endsection