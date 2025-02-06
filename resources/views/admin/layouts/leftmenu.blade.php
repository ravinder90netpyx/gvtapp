@php
$dropbox_token = config('filesystems.disks.dropbox.token');
$auth_user = Auth::user();
$roles = $auth_user->roles()->pluck('id')->toArray();
@endphp
<div class="scrollbar-inner">
  <!-- Brand -->
  <div class="sidenav-header  d-flex  align-items-center">
    <a class="navbar-brand" href="#{{-- {{route($folder['route_folder_name'].'.dashboard')}} --}}">
      <h1>{{ $folder['module_name'] }}</h1>
    </a>
    <div class=" ml-auto ">
      <!-- Sidenav toggler -->
      {{-- <div class="sidenav-toggler d-none d-xl-block" data-action="sidenav-pin" data-target="#sidenav-main">
        <div class="sidenav-toggler-inner" id="div-sidenav">
          <i class="sidenav-toggler-line"></i>
          <i class="sidenav-toggler-line"></i>
          <i class="sidenav-toggler-line"></i>
        </div>
      </div> --}}
    </div>
  </div>
  <div class="navbar-inner">
    <!-- Collapse -->
    <div class="collapse navbar-collapse" id="sidenav-collapse-main">
      <!-- Nav items -->
      <ul class="navbar-nav">    
        <li class="nav-item">
          <a class="nav-link{{ Str::contains(url()->current(), 'dashboard') ? ' active' : '' }}" href="{{route($folder['route_folder_name'].'.dashboard')}}">
            <i class="ni ni-shop"></i>
            <span class="nav-link-text">Dashboard</span>
          </a>
        </li>

        {{--
        @canany([ 'user_roles.manage' ])
        <li class="nav-item">
          <a {!! Str::contains(url()->current(), array('sample')) ? 'class="nav-link active" aria-expanded="true"' : 'class="nav-link collapsed" aria-expanded="false"' !!} href="#navbar-dashboards" aria-controls="navbar-dashboards" data-toggle="collapse" role="button">
            <i class="ni ni-shop"></i>
            <span class="nav-link-text">Level 1</span>
          </a>

          <div class="collapse{{ Str::contains(url()->current(), array('sample')) ? ' show' : '' }}" id="navbar-dashboards" style="">
            <ul class="nav nav-sm flex-column">
              @can('user_roles.manage')
              <li class="nav-item">
                <a class="nav-link{{ Str::contains(url()->current(), 'sample') ? ' active' : '' }}" href="{{route($folder['route_folder_name'].'.sample.index')}}">
                  <span class="sidenav-mini-icon"> 1 </span>
                  <span class="sidenav-normal"> Level 2 - Link 1 </span>
                </a>
              </li>
              @endcan

              @canany([ 'user_roles.manage' ])
              <li class="nav-item">
                <a {!! Str::contains(url()->current(), array('sample')) ? 'class="nav-link active" aria-expanded="true"' : 'class="nav-link collapsed" aria-expanded="false"' !!} href="#navbar-multilevel" data-toggle="collapse" role="button" aria-controls="navbar-multilevel">
                  <span class="sidenav-mini-icon"> 2 </span>
                  <span class="sidenav-normal"> Level 2 - Link 2 </span>
                </a>
                <div class="collapse{{ Str::contains(url()->current(), array('sample')) ? ' show' : '' }}" id="navbar-multilevel" style="">
                  <ul class="nav nav-sm flex-column">
                    @can('user_roles.manage')
                    <li class="nav-item">
                      <a class="nav-link{{ Str::contains(url()->current(), 'sample') ? ' active' : '' }}" href="{{route($folder['route_folder_name'].'.sample.index')}}">Level 3 - Link 1</a>
                    </li>
                    @endcan

                    @can('user_roles.manage')
                    <li class="nav-item">
                      <a class="nav-link{{ Str::contains(url()->current(), 'sample') ? ' active' : '' }}" href="{{route($folder['route_folder_name'].'.sample.index')}}">Level 3 - Link 2</a>
                    </li>
                    @endcan
                  </ul>
                </div>
              </li>
              @endcanany
            </ul>
          </div>
        </li>
        @endcanany
        --}}

        {{--
        @can('user_roles.manage')
        <li class="nav-item">
          <a class="nav-link{{ Str::contains(url()->current(), 'sample') ? ' active' : '' }}" href="{{route($folder['route_folder_name'].'.sample.index')}}">
            <i class="ni ni-tv-2"></i>
            <span class="nav-link-text">Sample Page</span>
          </a>
        </li>
        @endcan
        --}}

        @canany([ 'user_roles.manage', 'users.manage', 'organization.manage', 'organization.manage', 'templates.manage' ])
        <li class="nav-item">
          <a {!! Str::contains(url()->current(), array('user_roles', 'users', 'organization', 'organization_configs', 'templates')) ? 'class="nav-link active" aria-expanded="true"' : 'class="nav-link collapsed" aria-expanded="false"' !!} href="#user-management" data-toggle="collapse" role="button" aria-controls="user-management">
            <i class="ni ni-single-02"></i>
            <span class="nav-link-text">Users Management</span>
          </a>

          <div class="collapse{{ Str::contains(url()->current(), array('user_roles', 'users', 'organization', 'organization_configs', 'templates')) ? ' show' : '' }}" id="user-management" style="">
            <ul class="nav nav-sm flex-column">
              @can('organization.manage')

              <li class="nav-item">
                <a href="{{route($folder['route_folder_name'].'.organization.index')}}" class="nav-link{{ Str::contains(url()->current(), array('organization')) ? ' active' : '' }}">
                  <span class="sidenav-mini-icon"> O </span>
                  <span class="sidenav-normal"> Organization </span>
                </a>
              </li>
              @endcan

              @if(!in_array(1, $roles))
              @can('general_settings.manage_organization_config')
              <li class="nav-item">
                <a href="{{route($folder['route_folder_name'].'.organization_configs.index')}}" class="nav-link{{ Str::contains(url()->current(), array('organization_configs')) ? ' active' : '' }}">
                  <span class="sidenav-mini-icon"> WC </span>
                  <span class="sidenav-normal"> Whatsapp Configs </span>
                </a>
              </li>
              @endcan
              
              @can('templates.manage')
              <li class="nav-item">
                <a href="{{route($folder['route_folder_name'].'.templates.index')}}" class="nav-link{{ Str::contains(url()->current(), array('templates')) ? ' active' : '' }}">
                  <span class="sidenav-mini-icon"> T </span>
                  <span class="sidenav-normal"> Templates </span>
                </a>
              </li>
              @endcan
              @endif

              @can('user_roles.manage')
              <li class="nav-item">
                <a href="{{route($folder['route_folder_name'].'.user_roles.index')}}" class="nav-link{{ Str::contains(url()->current(), array('user_roles')) ? ' active' : '' }}">
                  <span class="sidenav-mini-icon"> R </span>
                  <span class="sidenav-normal"> User Roles </span>
                </a>
              </li>
              @endcan

              @can('users.manage')
              <li class="nav-item">
                <a href="{{route($folder['route_folder_name'].'.users.index')}}" class="nav-link{{ Str::contains(url()->current(), array('users')) ? ' active' : '' }}">
                  <span class="sidenav-mini-icon"> U </span>
                  <span class="sidenav-normal"> Users </span>
                </a>
              </li>
              @endcan
            </ul>
          </div>
        </li>
        @endcanany

        @canany([ 'charges.manage', 'members.manage', 'series.manage', 'charge_type.manage', 'group.manage', 'expense_type.manage' ])
          <li class="nav-item">
            <a {!! Str::contains(url()->current(), array('charges', 'members', 'series','chargetype', 'group', 'expense_type')) ? 'class="nav-link active" aria-expanded="true"' : 'class="nav-link collapsed" aria-expanded="false"' !!} href="#leftMenu2" data-toggle="collapse" role="button" aria-controls="leftMenu2">
              <i class="ni ni-single-02"></i>
              <span class="nav-link-text"> Masters </span>
            </a>

            <div class="collapse{{ Str::contains(url()->current(), array('charges', 'members', 'series', 'chargetype', 'group', 'expense_type')) ? ' show' : '' }}" id="leftMenu2" style="">
              <ul class="nav nav-sm flex-column">

                @can('series.manage')
                <li class="nav-item">
                  <a href="{{route($folder['route_folder_name'].'.series.index')}}" class="nav-link{{ Str::contains(url()->current(), array('series')) ? ' active' : '' }}">
                    <span class="sidenav-mini-icon"> RS </span>
                    <span class="sidenav-normal"> Manage Reciept Series </span>
                  </a>
                </li>
                @endcan

                @can('charges.manage')
                <li class="nav-item">
                  <a href="{{route($folder['route_folder_name'].'.charges.index')}}" class="nav-link{{ Str::contains(url()->current(), array('charges')) ? ' active' : '' }}">
                    <span class="sidenav-mini-icon"> C </span>
                    <span class="sidenav-normal"> Manage Charges </span>
                  </a>
                </li>
                @endcan

                @can('charge_type.manage')
                <li class="nav-item">
                  <a href="{{route($folder['route_folder_name'].'.chargetype.index')}}" class="nav-link{{ Str::contains(url()->current(), array('chargetype')) ? ' active' : '' }}">
                    <span class="sidenav-mini-icon"> CT </span>
                    <span class="sidenav-normal"> Manage Charge Type </span>
                  </a>
                </li>
                @endcan

                @can('members.manage')
                <li class="nav-item">
                  <a href="{{route($folder['route_folder_name'].'.members.index')}}" class="nav-link{{ Str::contains(url()->current(), array('members')) ? ' active' : '' }}">
                    <span class="sidenav-mini-icon"> M </span>
                    <span class="sidenav-normal"> Manage Members </span>
                  </a>
                </li>
                @endcan

                @can('group.manage')
                <li class="nav-item">
                  <a href="{{route($folder['route_folder_name'].'.group.index')}}" class="nav-link{{ Str::contains(url()->current(), array('group')) ? ' active' : '' }}">
                    <span class="sidenav-mini-icon"> MG </span>
                    <span class="sidenav-normal"> Manage Members Group </span>
                  </a>
                </li>
                @endcan

                @can('expense_type.manage')
                <li class="nav-item">
                  <a href="{{ route($folder['route_folder_name'].'.expense_type.index') }}" class="nav-link{{ Str::contains(url()->current(),array('expense_type')) ? ' active':'' }}">
                    <span class="sidenav-mini-icon"> ET </span>
                    <span class="sidenav-normal"> Manage Expense Type </span>
                  </a>
                </li>
                @endcan

              </ul>
            </div>
          </li>
        @endcanany

        @canany(['journal_entry.manage', 'expense.manage'])
          <li class="nav-item">
            <a {!! Str::contains(url()->current(), array('journal_entry', 'expenses')) ? 'class="nav-link active" aria-expanded="true"' : 'class="nav-link collapsed" aria-expanded="false"' !!} href="#leftMenu3" data-toggle="collapse" role="button" aria-controls="leftMenu3">
              <i class="fas fa-coins"></i>
              <span class="nav-link-text">Transactions</span>
            </a>
            <div class="collapse{{Str::contains(url()->current(), array('journal_entry', 'expenses')) ? ' show':''}}" id="leftMenu3" style="">
              <ul class="nav nav-sm flex-column">

                @can('journal_entry.manage')
                <li class="nav-item">
                  <a href="{{route($folder['route_folder_name'].'.journal_entry.index')}}" class="nav-link{{ Str::contains(url()->current(), array('journal_entry')) ? ' active' : '' }}">
                    <span class="sidenav-mini-icon"> JT </span>
                    <span class="sidenav-normal"> Manage Journal Txn </span>
                  </a>
                </li>
                @endcan

                @can('expense.manage')
                <li class="nav-item">
                  <a href="{{ route($folder['route_folder_name'].'.expenses.index') }}" class="nav-link{{ Str::contains(url()->current(),array('expenses')) ?' active':'' }}">
                    <span class="sidenav-mini-icon"> E </span>
                    <span class="sidenav-normal"> Manage Expenses </span>
                  </a>
                </li>
                @endcan
              </ul>
            </div>
          </li>
        @endcanany

        @canany(['journal_entry.report'])
        <li class="nav-item">
          <a {!! Str::contains(url()->current(),array('report', 'pending_report', 'personal_report', 'transaction_report', 'fine_report', 'expense_report')) ? 'class="nav-link active" aria-expanded="true"': 'class="nav-link collapsed" aria-expended="false"' !!} href="#leftMenu4" data-toggle="collapse" role="button" aria-controls="leftMenu4">
            <i class="fas fa-book"></i>
            <span class="nav-link-text">Report</span>
          </a>
          <div class="collapse{{ Str::contains(url()->current(), array('report', 'pending_report', 'personal_report', 'transaction_report', 'fine_report', 'expense_report')) ? ' show':'' }}" id="leftMenu4" style="">
            <ul class="nav nav-sm flex-column">
              @can('journal_entry.report')
              <li class="nav-item">
                <a href="{{route($folder['route_folder_name'].'.report.index')}}" class="nav-link{{ Str::contains(url()->current(), array('report')) ? ' active' : '' }}">
                  <span class="sidenav-mini-icon"> MWT </span>
                  <span class="sidenav-normal"> Month-Wise Txn Report </span>
                </a>
              </li>
              @endcan

              @can('journal_entry.report')
              <li class="nav-item">
                <a href="{{route($folder['route_folder_name'].'.pending_report')}}" class="nav-link{{ Str::contains(url()->current(), array('pending_report')) ? ' active' : '' }}">
                  <span class="sidenav-mini-icon"> CT </span>
                  <span class="sidenav-normal"> Consolidated Txn Report </span>
                </a>
              </li>
              @endcan

              @can('journal_entry.report')
              <li class="nav-item">
                <a href="{{route($folder['route_folder_name'].'.personal_report')}}" class="nav-link{{ Str::contains(url()->current(), array('personal_report')) ? ' active' : '' }}">
                  <span class="sidenav-mini-icon"> DW </span>
                  <span class="sidenav-normal"> Date-Wise Txn Report </span>
                </a>
              </li>
              @endcan

              @can('journal_entry.report')
              <li class="nav-item">
                <a href="{{route($folder['route_folder_name'].'.transaction_report')}}" class="nav-link{{ Str::contains(url()->current(), array('transaction_report')) ? ' active' : '' }}">
                  <span class="sidenav-mini-icon"> BT </span>
                  <span class="sidenav-normal"> Bank Transaction Report </span>
                </a>
              </li>
              @endcan

              @can('journal_entry.report')
              <li class="nav-item">
                <a href="{{route($folder['route_folder_name'].'.fine_report')}}" class="nav-link{{ Str::contains(url()->current(), array('fine_report')) ? ' active' : '' }}">
                  <span class="sidenav-mini-icon"> FC </span>
                  <span class="sidenav-normal"> Fine Collection Report </span>
                </a>
              </li>
              @endcan
              
              @can('journal_entry.report')
              <li class="nav-item">
                <a href="{{route($folder['route_folder_name'].'.expense_report')}}" class="nav-link{{ Str::contains(url()->current(), array('expense_report')) ? ' active' : '' }}">
                  <span class="sidenav-mini-icon"> ER </span>
                  <span class="sidenav-normal"> Expense Report </span>
                </a>
              </li>
              @endcan
            </ul>
          </div>
        </li>
        @endcanany
      </ul>               
    </div>
  </div>
</div>
