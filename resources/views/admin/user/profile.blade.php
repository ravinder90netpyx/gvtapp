@extends('admin.layouts.master')
@section('title')
   My Profile
@endsection
@section('content')
 <div class="container-fluid">      
      <div class="row">
        <div class="col-xl-12 order-xl-2">
          <div class="card card-profile">
            <img src="{{asset('images/profile_cover.png')}}" alt="Image placeholder" class="card-img-top">
            <div class="row justify-content-center">
              <div class="col-lg-3 order-lg-2">
                <div class="card-profile-image">
                  <a href="#">
                    @php 
                      $pic = $model->profile_picture ?? 'user.jpg'
                    @endphp
                    <img src="{{asset('images/'.$pic)}}" class="rounded-circle">
                  </a>
                </div>
              </div>
            </div>
            <div class="card-header text-center border-0 pt-8 pt-md-4 pb-0 pb-md-4">
              <div class="d-flex justify-content-between">
                <a href="{{route('supanel.profile-edit')}}" class="btn btn-sm btn-info  mr-4 ">Update Profile</a>
                <a href="{{route('supanel.changepassword')}}" class="btn btn-sm btn-default float-right">Change Password</a>
              </div>
            </div>
            <div class="card-body pt-5">
              <div class="table-responsive detailpage pt-2">
                 <table class="table text-left">
                   <tr>
                      <th>First Name</th>
                      <td> {{ $model->first_name }}</td>
                   </tr>      
                   <tr>
                      <th>Last Name</th>
                      <td> {{ $model->last_name }}</td>
                   </tr> 
                   <tr>
                      <th>Email</th>
                      <td>{{ $model->email }}</td>
                   </tr>              
                 </table>
               </div>
            </div>
          </div>          
        </div>
      </div>
  </div>
@endsection