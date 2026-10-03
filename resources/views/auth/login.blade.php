@extends(backendView('layouts.auth'))

@section('title', 'Signin')

@section('content')
    <div class="container-xxl">

        <div class="row g-0">
            <div class="col-lg-6 d-none d-lg-flex justify-content-center align-items-center rounded-lg auth-h100">
                <div style="max-width: 25rem;">
                    <div class="text-center mb-5">
                        <!-- <img src="{{asset('public/AKUNTHA LOGO.png')}}" style="height: 120px; width: 342px;"> -->
                        <img src="{{asset('Bugle_logo.png')}}" style="height: 120px; width: 342px;">
                    </div>

                    <!-- Image block -->
                    <div class="">
                        <img src="{{ asset('backend/dist/assets/images/image_bugle.png') }}" 
     alt="login-img"
     style="max-width: 1000px; width: 100%; height: auto;">
                    </div>
                </div>
            </div>

            <div class="col-lg-6 d-flex justify-content-center align-items-center border-0 rounded-lg auth-h100">



                <div class="w-100 p-3 p-md-5 card border-0 shadow-sm" style="max-width: 32rem;">
                    <!-- Form -->
                    <form class="row g-1 p-3 p-md-4" method="post" action="{{ route('login') }}">
                        @csrf
                        <div class="col-12 text-center mb-5">
                            <h2>Sign in</h2>
                            <!-- <span>Free access to our dashboard.</span> -->
                        </div>
                        @if (session('error'))
                            <div class="invalid-feedback">
                                {{ session('error') }}
                            </div>
                        @endif
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @elseif (session('error'))
                            <div class="alert alert-danger" role="alert">
                                {{ session('error') }}
                            </div>
                        @endif

                        <!-- <div class="col-12 text-center mb-4">
                            <a class="btn btn-lg btn-light btn-block" href="#">
                                <span class="d-flex justify-content-center align-items-center">
                                    <img class="avatar xs me-2" src="{!! backendAssets('dist/assets/images/google.svg') !!}" alt="Image Description">
                                    Sign in with Google
                                </span>
                            </a>
                            <span class="dividers text-muted mt-4">OR</span>
                        </div> -->
                        <div class="col-12">
                            <div class="mb-2">
                                <label class="form-label">Email addresss</label>
                                <input type="email"
                                    class="form-control form-control-lg @error('email') is-invalid @enderror"
                                    placeholder="name@example.com" name="email" value="{{ old('email') }}" required
                                    autocomplete="email" autofocus>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-2">
                                <div class="form-label">
                                    <span class="d-flex justify-content-between align-items-center">
                                        Password
                                    </span>
                                </div>

                                <div class="input-group">
                                    <input type="password" id="password"
                                        class="form-control form-control-lg @error('password') is-invalid @enderror"
                                        placeholder="***************" name="password" required
                                        autocomplete="current-password">

                                    <span class="input-group-text" onclick="togglePassword()" style="cursor:pointer;">
                                        <i id="toggleIcon" class="fa fa-eye"></i>
                                    </span>
                                </div>

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <!-- <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
                                <label class="form-check-label" for="flexCheckDefault">
                                    Remember me
                                </label>
                            </div>
                        </div> -->
                        <div class="col-12 text-center mt-4">
                            <button type="submit" class="btn btn-lg btn-block btn-light text-uppercase" atl="signin"><b>SIGN
                                    IN</b></button>
                        </div>

                        <div class="col-12 text-center mt-3">
                            <span>Don't have an account yet? <a href="{{url('signup')}}" class="text-secondary">Sign up
                                    here</a></span>
                        </div>
                        <div class="col-4 text-center mt-2"></div>
                        <div class="col-4 text-center mt-2">
                            <a class="text-secondary" href="{{url('password/reset')}}">Forgot Password?</a>
                        </div>


                    </form>
                    <!-- End Form -->

                </div>
            </div>
        </div> <!-- End Row -->

    </div>
@endsection
<script>
    function togglePassword() {
        const input = document.getElementById("password");
        const icon = document.getElementById("toggleIcon");

        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        } else {
            input.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        }
    }
</script>

@push('styles')
@endpush

@push('custom_styles')
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@if(session('inactive'))
<script>
    Swal.fire({
        icon: 'warning',
        title: 'Account Inactive',
        text: "{{ session('inactive') }}",
        confirmButtonText: 'OK'
    });
</script>
@endif

@endpush

@push('custom_scripts')
@endpush

@push('modals')
@endpush