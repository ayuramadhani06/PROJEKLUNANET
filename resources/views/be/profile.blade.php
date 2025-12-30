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
                <li class="nav-item" role="presentation">
                    <button class="nav-link active"
                            id="profile-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#profile"
                            type="button"
                            role="tab">
                        Profile Information
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link"
                            id="security-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#security"
                            type="button"
                            role="tab">
                        Privacy & Security
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content mt-4">

                {{-- TAB INFORMASI PROFIL --}}
                <div class="tab-pane fade show active" id="profile" role="tabpanel">
                    <form>
                        <div class="row">

                            {{-- FOTO --}}
                            <div class="col-md-4 text-center">
                                <img src="{{ asset('be/img/default-avatar.png') }}"
                                     class="img-fluid rounded mb-3"
                                     style="max-width:200px">

                                <button type="button" class="btn btn-info btn-sm w-100">
                                    Upload Foto 
                                </button>

                                <small class="text-muted d-block mt-2">
                                    Max 2MB · JPG, PNG, WEBP
                                </small>
                            </div>

                            {{-- DATA --}}
                            <div class="col-md-8">
                                <div class="table-responsive">
                                    <table class="table table-borderless align-middle">
                                        <tbody>

                                        <tr>
                                            <th width="30%">Name</th>
                                            <td>
                                                <input type="text" class="form-control"
                                                       placeholder="Enter your name">
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>Email</th>
                                            <td>
                                                <input type="email" class="form-control"
                                                       placeholder="example@gmail.com">
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="text-end mt-3">
                                    <button type="button" class="btn btn-info">
                                        Save Changes
                                    </button>
                                </div>

                            </div>
                        </div>
                    </form>
                </div>

                {{-- TAB PRIVASI & KEAMANAN --}}
                <div class="tab-pane fade" id="security" role="tabpanel">
                    <form>
                        <div class="table-responsive">
                            <table class="table table-borderless align-middle">
                                <tbody>

                                <tr>
                                    <th width="30%">Old Password</th>
                                    <td>
                                        <div class="position-relative">
                                            <input type="password" class="form-control" id="oldPassword"
                                                placeholder="Enter your old password">
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <th>New Password</th>
                                    <td>
                                        <div class="position-relative">
                                            <input type="password" class="form-control" id="newPassword"
                                                placeholder="Enter your new password">
                                        </div>
                                    </td>
                                </tr>

                                
                                </tbody>
                            </table>
                        </div>

                        <div class="text-end mt-3">
                            <button type="button" class="btn btn-info">
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

</div>
@endsection

