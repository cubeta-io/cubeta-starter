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
                                    <h5 class="card-title text-center pb-0 fs-4">Login to Your Account</h5>
                                    <p class="text-center small">Enter your email & password to login</p>
                                </div>

                                <form class="row g-3 needs-validation" action="{{route('dashboard.login')}}"
                                      method="POST" novalidate>
                                    @csrf
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
                                        <button class="btn btn-primary w-100" type="submit">Login</button>
                                    </div>
                                    <div class="col-12">
                                        <p class="small mb-1">Don't have account? <a
                                                href="{{route('dashboard.register-page')}}">Create
                                                an account</a></p>
                                        <p class="small mb-1">Forgot Your Password ? <a
                                                href="{{route('dashboard.request-reset-password-code-page')}}">Reset
                                                Your Password</a>
                                        </p>
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
