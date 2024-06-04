@php
$authentic_user = auth()->user();
$authentic_username = ucwords($authentic_user->first_name.' '.$authentic_user->last_name);
@endphp
<!--
=========================================================
*By Arunendra Pratap Rai
-->
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>@yield('title')</title>
<META NAME="robots" CONTENT="noindex,nofollow">
<!-- Favicon -->
<link href="{{ asset('dashboard/img/brand/favicon.png') }}" rel="icon" type="image/png">
<!-- Fonts -->
<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
<!-- Icons -->
<link href="{{ asset('dashboard/css/bootstrap.min.css') }}" rel="stylesheet" />
<link href="{{ asset('dashboard/css/nucleo.css') }}" rel="stylesheet" />
<link href="{{ asset('dashboard/css/fortawesome/css/all.min.css') }}" rel="stylesheet" />
<link href="{{ asset('dashboard/css/argon.min.css') }}" rel="stylesheet" />

<!-- CSS Files -->
<link href="{{asset('dashboard/css/toastr.min.css')}}" rel="stylesheet">
<link href="{{asset('dashboard/css/select2.min.css')}}" rel="stylesheet">
<link href="{{ asset('dashboard/css/custom.css') }}" rel="stylesheet">
<link href="{{ asset('dashboard/js/DataTables/datatables.min.css') }}" rel="stylesheet">
@yield('css')

<script src="{{ asset('dashboard/js/jquery.min.js') }}"></script>
<script src="{{ asset('dashboard/js/moment.min.js') }}"></script>
<script src="{{ asset('dashboard/js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('dashboard/js/bootstrap-datetimepicker.js') }}"></script>
<script src="{{ asset('dashboard/js/DataTables/datatables.min.js') }}"></script>

<link href="{{ asset('dashboard/js/bootstrap-treeview/bootstrap-treeview.min.css') }}" rel="stylesheet">
<script src="{{ asset('dashboard/js/bootstrap-treeview/bootstrap-treeview.min.js') }}"></script>
<script src="{{ asset('dashboard/js/Chart.min.js') }}"></script>

<style>
.alert{z-index:99;top:60px;right:18px;min-width:30%;position:fixed;animation:slide .5s forwards}@keyframes slide{100%{top:30px}}@media screen and (max-width:668px){.alert{left:10px;right:10px}}.error{width:100%;color:red;margin-top:5px}
</style>
</head>
<body @yield('bodyAttr')>

<form id="logout-form" action="{{ route($folder['route_folder_name'].'.logout') }}" method="POST" style="display: none;">
    {{ csrf_field() }}
</form>
<!-- Sidenav -->
<nav class="sidenav navbar navbar-vertical  fixed-left  navbar-expand-xs navbar-light" id="sidenav-main">
  @include('admin.layouts.leftmenu')
</nav> 
<div class="main-content" id="panel"> 
  <nav class="navbar navbar-top navbar-expand navbar-light bg-secondary border-bottom">
    <div class="container-fluid">
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <h4>Welcome 
          @can('general_settings.manage_config')<a href="{{route($folder['route_folder_name'].'.config.index')}}" rel="tab" class="text-success font-weight-normal">{{ $authentic_username }}</a>@endcan
          @cannot('general_settings.manage_config')<span class="text-dark font-weight-normal">{{ $authentic_username }}</span>@endcannot
        </h4>
        <ul class="navbar-nav align-items-center ml-auto">
          <li class="nav-item dropdown">
            <a class="nav-link pr-0" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <div class="media align-items-center">
                <span class="avatar avatar-sm rounded-circle">
                  <img alt="Image placeholder" src="{{ asset('dashboard/img/user.png') }}" style="max-width: 20px;">
                </span>
                <div class="media-body ml-2 d-none d-lg-block">
                  <span class="mb-0 text-sm  font-weight-bold text-dark">{{ $authentic_username }}</span>
                </div>
              </div>
            </a>

            <div class="dropdown-menu  dropdown-menu-right ">
              <div class="dropdown-header noti-title">
              <h6 class="text-overflow m-0">Welcome</h6>
              </div>
              
              <a href="#!" class="dropdown-item">
                <i class="ni ni-single-02"></i>
                <span>My profile</span>
              </a>

              @can('general_settings.manage_config')
              <a href="{{route($folder['route_folder_name'].'.config.index')}}" class="dropdown-item">
                <i class="ni ni-settings-gear-65"></i>
                <span>Settings</span>
              </a>
              @endcan

              @if(in_array(auth()->user()->id, [1]))
                <a href="{{ asset('log-viewer') }}" class="dropdown-item" target="_blank">
                  <i class="fas fa-clipboard-list"></i>
                  <span>Log Viewer</span>
                </a>
              @endif

              <div class="dropdown-divider"></div>
              <a href="javascript:void(0);" class="dropdown-item" onclick="document.getElementById('logout-form').submit();">
                <i class="ni ni-button-power"></i>
                <span>Logout</span>
              </a>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container-fluid">
    <div class="header-body pt-3">    
        @include('admin.layouts.flash-message')  
        @yield('content')
    </div>
  </div>  

  <footer class="footer">
    <div class="row align-items-center justify-content-lg-between">
      <div class="col-lg-12">
        <div class="copyright text-center text-muted">
          © {{date('Y')}} {{ urldecode(config('app.name')) }}
        </div>
      </div>
    </div>
  </footer>

</div>
  <!--   Core   -->  
<script src="{{ asset('dashboard/js/popper.min.js') }}"></script>
<script src="{{ asset('dashboard/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('dashboard/js/js.cookie.js') }}"></script>  
<script src="{{ asset('dashboard/js/jquery.scrollbar.min.js') }}"></script>  
<script src="{{ asset('dashboard/js/jquery-scrollLock.min.js') }}"></script>    
<script src="{{ asset('dashboard/js/sweetalert.min.js') }}"></script>  
<script src="{{ asset('dashboard/js/toastr.min.js') }}"></script>
<script src="{{ asset('dashboard/js/select2.min.js') }}"></script>
<script src="{{ asset('dashboard/js/bootstrap-tagsinput.min.js') }}"></script>
<script src="{{ asset('dashboard/js/custom.js') }}"></script>
@yield('scripts')
<script>
function sticky_header(){
  scrollTop = $(document).scrollTop();

  var $cardlisting_offset = $(".card-listing .card-header").get(0).scrollHeight + 80;
  //console.log($cardlisting_offset);
  if(scrollTop>$cardlisting_offset){
    $(".card-listing .card-header").addClass('sticky_top');
  } else{
    $(".card-listing .card-header").removeClass('sticky_top');
  }
}

$(document).ready(function(){
  //$(".card-listing .card-header").clone().insertAfter('.card-listing .card-header').addClass('sticky-header-fixed');

  $('[data-toggle="select"]').select2({
    placeholder: '{!! __('admin.text_select') !!}',
    allowClear: true
  });

  $('[data-toggle="select-multiple"]').select2({
    allowClear: true,
    closeOnSelect: false
  });

  sticky_header();
  $(window).scroll(function(){
    sticky_header();
  });
});
</script>
<script src="{{ asset('dashboard/js/argon-modified.min.js') }}"></script>
</body>
</html>