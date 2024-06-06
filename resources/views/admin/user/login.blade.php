<!--
=========================================================
* Product Page: https://arunendra.com
* Coded by Arunendra Pratap rai
=========================================================
-->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>
    {{ urldecode(config('app.name')) }} | Log In
  </title>
<!-- Favicon -->
<link href="{{ asset('dashboard/img/brand/favicon.png') }}" rel="icon" type="image/png">
<!-- Fonts -->
<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
<!-- Icons -->
<link href="{{ asset('dashboard/css/nucleo.css') }}" rel="stylesheet" />
<!-- CSS Files -->
<link href="{{ asset('dashboard/css/fortawesome/css/all.min.css') }}" rel="stylesheet" />
<link href="{{ asset('dashboard/css/argon.min.css') }}" rel="stylesheet" />
<link href="{{asset('dashboard/css/toastr.min.css')}}" rel="stylesheet">
<style>
.alert{z-index:99;top:60px;right:18px;min-width:30%;position:fixed;animation:slide .5s forwards}@keyframes slide{100%{top:30px}}@media screen and (max-width:668px){.alert{left:10px;right:10px}}.error{width:100%;color:red;margin-top:5px}
</style>
</head>
<body class="login-gradient">
  <div class="main-content">
    <!-- Navbar -->
   {{-- <nav id="navbar-main" class="navbar navbar-horizontal navbar-transparent navbar-main navbar-expand-lg navbar-light">
      <div class="container px-4">
        <a class="navbar-brand" href="/">
         <h1 class="text-white">{{ config('app.name') }}</h1>
        </a>       
      </div>
    </nav>--}}
    <!-- Header -->
    <div class="header py-7 py-lg-5">
      <div class="container">
        <div class="header-body text-center mb-7">
          <div class="row justify-content-center">
            <div class="col-lg-5 col-md-6">
              <h1>{{ urldecode(config('app.name')) }}</h1>
            </div>
          </div>
        </div>
      </div>
      {{--<div class="separator separator-bottom separator-skew zindex-100">
        <svg x="0" y="0" viewBox="0 0 2560 100" preserveAspectRatio="none" version="1.1" xmlns="http://www.w3.org/2000/svg">
          <polygon class="fill-default" points="2560 0 2560 100 0 100"></polygon>
        </svg>
      </div>--}}
    </div>
    <!-- Page content -->
    <div class="container mt--8 pb-5">
      <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
          <div class="card bg-secondary shadow border-0">          
            <div class="card-body px-lg-5 py-lg-5">
              <div class="text-center text-muted mb-4">
                <small>Sign in with credentials</small>
              </div>
               <form method="POST" action="{{route('supanel.loginpost')}}">
                @csrf         
                <div class="form-group mb-3">
                  <div class="input-group input-group-alternative">
                    <div class="input-group-prepend">
                      <span class="input-group-text"><i class="ni ni-email-83"></i></span>
                    </div>
                    <input class="form-control @error('email') is-invalid @enderror" placeholder="Email" type="email" name="email">
                     @error('email')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
                <div class="form-group">
                  <div class="input-group input-group-alternative">
                    <div class="input-group-prepend">
                      <span class="input-group-text"><i class="ni ni-lock-circle-open"></i></span>
                    </div>
                    <input class="form-control @error('password') is-invalid @enderror" placeholder="Password" type="password" name="password">
                    @error('password')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
                <div class="custom-control custom-control-alternative custom-checkbox">
                  <input class="custom-control-input" id=" customCheckLogin" type="checkbox">
                  <label class="custom-control-label" for=" customCheckLogin">
                    <span class="text-muted">Remember me</span>
                  </label>
                </div>
                <div class="text-center">
                  <button type="submit" class="btn btn-primary my-4">Sign in</button>
                </div>
                {{--<div class="row mt-3">
                  <div class="col-12">
                    <a href="#" class="text-light"><small>Forgot password?</small></a>
                  </div>         
                </div>--}}
              </form>
            </div>
          </div>
          
        </div>
      </div>
    </div>
    <footer class="pt-2 pb-3">
      <div class="container">
        <div class="row align-items-center justify-content-xl-between">
          <div class="col-12">
            <div class="copyright text-center text-muted">
              ©{{ now()->year }} <a href="javascript:void(0);" class="font-weight-bold ml-1" target="_self">{{ urldecode(config('app.name')) }}</a>
            </div>
          </div>       
        </div>
      </div>
    </footer>
  </div>
  @include('admin.layouts.flash-message')  
  <!--   Core   -->  
<script src="{{ asset('dashboard/js/jquery.min.js') }}"></script>
<script src="{{ asset('dashboard/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('dashboard/js/js.cookie.js') }}"></script>  
<script src="{{ asset('dashboard/js/jquery.scrollbar.min.js') }}"></script>  
<script src="{{ asset('dashboard/js/jquery-scrollLock.min.js') }}"></script>  
<script src="{{ asset('dashboard/js/argon-modified.min.js') }}"></script>  
<script src="{{ asset('dashboard/js/toastr.min.js') }}"></script>  
<script>
//close the alert after 3 seconds.
$(document).ready(function(){
  setTimeout(function() {
        $(".alert").alert('close');
    }, 3000);
});
</script>
</body>
</html>