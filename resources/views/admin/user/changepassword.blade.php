@extends('admin.layouts.master')

@section('title')
   Update Profile
@endsection

@section('content')
 <div class="container-fluid">
      <!-- Table -->
      <div class="row">
        <div class="col-md-8 offset-md-2 mt-5">
          <div class="card shadow">
            <div class="card-header bg-light">
                  <i class="ni ni-lock-circle-open"></i>
                  <span>Update Profile</span>
            </div>
            <div class="m-5">
              <form action="{{ route('supanel.changepassword') }}" method="POST">
                  @csrf
                      <div class="form-row">
                        <div class="form-group col-md-12">
                           <label for="current_password">Current Password</label>
                             <input type="password" class="form-control @error('current_password') is-invalid @enderror" name="current_password" autocomplete="current-password">
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group col-md-12">
                           <label for="password">New Password</label>
                               <input id="new_password" type="password" class="form-control @error('new_password') is-invalid @enderror" name="new_password" autocomplete="current-password">
                             @error('new_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group col-md-12">
                           <label for="password">Confirm Password</label>
                              <input id="new_confirm_password" type="password" class="form-control @error('new_confirm_password') is-invalid @enderror" name="new_confirm_password" autocomplete="current-password">
                             @error('new_confirm_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="button-bottom ">
                        <a href="{{route('supanel.profile')}}" class="btn btn-default btn-lg">Cancel</a>
                        <button type="submit" class="btn btn-primary btn-lg">Submit</button>
                    </div>
                </form>
            </div>
          </div>
        </div>
      </div>
  </div>
@endsection
