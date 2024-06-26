@extends('admin.layouts.master')
@section('title')
    {{urldecode(config('app.name'))}} | Dashboard
@endsection
@section('content')
      <!-- Table -->
      <div class="row">
        <div class="col-md-12">
         
         <h1 class="mb-4">Dashboard</h1>

          <div class="row">
<div class="col-xl-3 col-md-6">
<div class="card card-stats">

<div class="card-body">
<div class="row">
<div class="col">
<h5 class="card-title text-uppercase text-muted mb-0">Total traffic</h5>
<span class="h2 font-weight-bold mb-0">350,897</span>
</div>
<div class="col-auto">
<div class="icon icon-shape bg-gradient-red text-white rounded-circle shadow">
<i class="ni ni-active-40"></i>
</div>
</div>
</div>
<p class="mt-3 mb-0 text-sm">
<span class="text-success mr-2"><i class="fa fa-arrow-up"></i> 3.48%</span>
<span class="text-nowrap">Since last month</span>
</p>
</div>
</div>
</div>
<div class="col-xl-3 col-md-6">
<div class="card card-stats">

<div class="card-body">
<div class="row">
<div class="col">
<h5 class="card-title text-uppercase text-muted mb-0">New users</h5>
<span class="h2 font-weight-bold mb-0">2,356</span>
</div>
<div class="col-auto">
<div class="icon icon-shape bg-gradient-orange text-white rounded-circle shadow">
<i class="ni ni-chart-pie-35"></i>
</div>
</div>
</div>
<p class="mt-3 mb-0 text-sm">
<span class="text-success mr-2"><i class="fa fa-arrow-up"></i> 3.48%</span>
<span class="text-nowrap">Since last month</span>
</p>
</div>
</div>
</div>
<div class="col-xl-3 col-md-6">
<div class="card card-stats">

<div class="card-body">
<div class="row">
<div class="col">
<h5 class="card-title text-uppercase text-muted mb-0">Sales</h5>
<span class="h2 font-weight-bold mb-0">924</span>
</div>
<div class="col-auto">
<div class="icon icon-shape bg-gradient-green text-white rounded-circle shadow">
<i class="ni ni-money-coins"></i>
</div>
</div>
</div>
<p class="mt-3 mb-0 text-sm">
<span class="text-success mr-2"><i class="fa fa-arrow-up"></i> 3.48%</span>
<span class="text-nowrap">Since last month</span>
</p>
</div>
</div>
</div>
<div class="col-xl-3 col-md-6">
<div class="card card-stats">

<div class="card-body">
<div class="row">
<div class="col">
<h5 class="card-title text-uppercase text-muted mb-0">Performance</h5>
<span class="h2 font-weight-bold mb-0">49,65%</span>
</div>
<div class="col-auto">
<div class="icon icon-shape bg-gradient-info text-white rounded-circle shadow">
<i class="ni ni-chart-bar-32"></i>
</div>
</div>
</div>
<p class="mt-3 mb-0 text-sm">
<span class="text-success mr-2"><i class="fa fa-arrow-up"></i> 3.48%</span>
<span class="text-nowrap">Since last month</span>
</p>
</div>
</div>
</div>
</div>


{{-- here--}}



<div class="row">
<div class="col-xl-8">
<div class="card bg-default">
<div class="card-header bg-transparent">
<div class="row align-items-center">
<div class="col">
<h6 class="text-light text-uppercase ls-1 mb-1">Overview</h6>
<h5 class="h3 text-white mb-0">Sales value</h5>
</div>
<div class="col">
<ul class="nav nav-pills justify-content-end">
<li class="nav-item mr-2 mr-md-0" data-toggle="chart" data-target="#chart-sales-dark" data-update="{&quot;data&quot;:{&quot;datasets&quot;:[{&quot;data&quot;:[0, 20, 10, 30, 15, 40, 20, 60, 60]}]}}" data-prefix="$" data-suffix="k">
<a href="#" class="nav-link py-2 px-3 active" data-toggle="tab">
<span class="d-none d-md-block">Month</span>
<span class="d-md-none">M</span>
</a>
</li>
<li class="nav-item" data-toggle="chart" data-target="#chart-sales-dark" data-update="{&quot;data&quot;:{&quot;datasets&quot;:[{&quot;data&quot;:[0, 20, 5, 25, 10, 30, 15, 40, 40]}]}}" data-prefix="$" data-suffix="k">
<a href="#" class="nav-link py-2 px-3" data-toggle="tab">
<span class="d-none d-md-block">Week</span>
<span class="d-md-none">W</span>
</a>
</li>
</ul>
</div>
</div>
</div>
<div class="card-body">

<div class="chart"><div class="chartjs-size-monitor"><div class="chartjs-size-monitor-expand"><div class=""></div></div><div class="chartjs-size-monitor-shrink"><div class=""></div></div></div><div class="chartjs-size-monitor"><div class="chartjs-size-monitor-expand"><div class=""></div></div><div class="chartjs-size-monitor-shrink"><div class=""></div></div></div>

<canvas id="chart-sales-dark" class="chart-canvas chartjs-render-monitor" style="display: block; width: 632px; height: 350px;" width="632" height="350"></canvas>
</div>
</div>
</div>
</div>
<div class="col-xl-4">
<div class="card">
<div class="card-header bg-transparent">
<div class="row align-items-center">
<div class="col">
<h6 class="text-uppercase text-muted ls-1 mb-1">Performance</h6>
<h5 class="h3 mb-0">Total orders</h5>
</div>
</div>
</div>
<div class="card-body">

<div class="chart"><div class="chartjs-size-monitor"><div class="chartjs-size-monitor-expand"><div class=""></div></div><div class="chartjs-size-monitor-shrink"><div class=""></div></div></div>
<canvas id="chart-bars" class="chart-canvas chartjs-render-monitor" width="277" height="350" style="display: block; width: 277px; height: 350px;"></canvas>
</div>
</div>
</div>
</div>
</div>



{{--close--}}


        </div>
      </div>
@endsection