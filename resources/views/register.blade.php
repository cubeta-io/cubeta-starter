@include('includes.header')
<main>
    <div class="container">
        <section
            class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">
                        <div class="d-flex justify-content-center py-4">
                            <a href="#" class="logo d-flex align-items-center w-auto">
                                <span class="d-none d-lg-block">{{config('cubeta-starter.project_name')}}</span>
                            </a>
                        </div><!-- End Logo -->
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="pt-4 pb-2">
                                    <h5 class="card-title text-center pb-0 fs-4">Create An Account</h5>
                                </div>
                                <form class="row g-3 needs-validation" action="{{route('dashboard.register')}}" method="POST"
                                      novalidate>
                                    @csrf
                                    <div class="col-6">
                                        <label for="first_name" class="form-label">First Name</label>
                                        <div class="input-group has-validation">
                                            <input type="text" name="first_name" class="form-control"
                                                   id="first_name" required>
                                            <div class="invalid-feedback">Please enter your first name.</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label for="last_name" class="form-label">Last Name</label>
                                        <div class="input-group has-validation">
                                            <input type="text" name="last_name" class="form-control"
                                                   id="last_name" required>
                                            <div class="invalid-feedback">Please enter your last name.</div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label for="email" class="form-label">Email</label>
                                        <div class="input-group has-validation">
                                            <span class="input-group-text" id="inputGroupPrepend">@</span>
                                            <input type="text" name="email" class="form-control"
                                                   id="email" required>
                                            <div class="invalid-feedback">Please enter your email.</div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" name="password" class="form-control"
                                               id="password" required>
                                        <div class="invalid-feedback">Please enter your password!</div>
                                    </div>
                                    <div class="col-12">
                                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                                        <input type="password" name="password_confirmation"
                                               class="form-control"
                                               id="password_confirmation" required>
                                        <div class="invalid-feedback">Please confirm your password!</div>
                                    </div>
                                    @if($errors->any())
                                        <div class="card p-1 mt-2 mb-2">
                                            <div class="card-body">
                                                <ul>
                                                    @foreach($errors->all() as $error)
                                                        <li style="color: red">
                                                            {{ $error }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="col-12">
                                        <button class="btn btn-primary w-100" type="submit">Register</button>
                                    </div>
                                    <div class="col-12">
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>
@include('includes.footer')
