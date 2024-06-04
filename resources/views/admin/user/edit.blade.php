@extends('admin.layouts.master')

@section('title')
   Update Profile
@endsection

@section('content')
 <div class="container-fluid">
      <!-- Table -->
      <div class="row">
        <div class="col">
          <div class="card shadow">
            <div class="card-header bg-light  text-right">
                  <i class="ni ni-ruler-pencil"></i>
                  <span>Update Profile</span>
            </div>
            <div class="m-5">
              <form action="{{ route('supanel.profile') }}" method="POST" enctype="multipart/form-data">
                  @csrf
                    <div class="form-row">
                        <div class="form-group col-md-6">
                         <label for="first_name">First Name</label>
                          <input name="first_name"
                             class="form-control @error('first_name') is-invalid @enderror" type="text" value="{{ $model->first_name }}">
                         @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group col-md-6">
                         <label for="last_name">Last Name</label>
                          <input name="last_name"
                             class="form-control @error('last_name') is-invalid @enderror" type="text" value="{{ $model->last_name }}">
                         @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group col-md-6">
                         <label for="email">Email</label>
                          <input name="email"
                             class="form-control @error('email') is-invalid @enderror" type="email" value="{{ $model->email }}">
                         @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                          @enderror
                        </div>
                         <div class="form-group col-md-6">
                        <div class="">
                          <label for="profile_picture">Profile Picture</label>
                          <input id="inputError" type="file" class="form-control-file @error('profile_picture') is-invalid @enderror" name="profile_picture">
                        </div>
                            @error('profile_picture')
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
