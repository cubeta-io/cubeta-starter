@extends('layout')
@section('content')
    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Profile</h1>
        </div><!-- End Page Title -->

        <section class="section profile">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-body pt-3">
                            <!-- Bordered Tabs -->
                            <ul class="nav nav-tabs nav-tabs-bordered">

                                <li class="nav-item">
                                    <button class="nav-link active" data-bs-toggle="tab"
                                            data-bs-target="#profile-overview">Overview
                                    </button>
                                </li>

                                <li class="nav-item">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-edit">Edit
                                        Profile
                                    </button>
                                </li>

                            </ul>
                            <div class="tab-content pt-2">

                                <div class="tab-pane fade show active profile-overview p-3" id="profile-overview">

                                    <h5 class="card-title">Profile Details</h5>

                                    <div class="row">
                                        <div class="col-lg-3 col-md-4 label ">Full Name</div>
                                        <div class="col-lg-9 col-md-8">{{$user->first_name}} {{$user->last_name}}</div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-3 col-md-4 label">Email</div>
                                        <div class="col-lg-9 col-md-8">{{$user->email}}</div>
                                    </div>

                                </div>

                                <div class="tab-pane fade profile-edit pt-3" id="profile-edit">

                                    <!-- Profile Edit Form -->
                                    <form action="{{route('web.protected.update-user-data')}}" method="POST">
                                        @method('PUT')
                                        @csrf
                                        <div class="row mb-3">
                                            <label for="first_name" class="col-md-4 col-lg-3 col-form-label">First
                                                Name</label>
                                            <div class="col-md-8 col-lg-9">
                                                <input name="first_name" type="text" class="form-control"
                                                       id="first_name"
                                                       value="{{old('first_name') ?? $user->first_name}}">
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label for="last_name" class="col-md-4 col-lg-3 col-form-label">Last
                                                Name</label>
                                            <div class="col-md-8 col-lg-9">
                                                <input name="last_name" type="text" class="form-control"
                                                       id="last_name"
                                                       value="{{old('last_name') ?? $user->last_name}}">
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label for="email" class="col-md-4 col-lg-3 col-form-label">Email</label>
                                            <div class="col-md-8 col-lg-9">
                                                <input name="email" type="text" class="form-control"
                                                       id="email"
                                                       value="{{old('email') ?? $user->email}}">
                                            </div>
                                        </div>

                                        <div class="text-center">
                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                        </div>
                                    </form><!-- End Profile Edit Form -->
                                </div>
                            </div><!-- End Bordered Tabs -->
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
