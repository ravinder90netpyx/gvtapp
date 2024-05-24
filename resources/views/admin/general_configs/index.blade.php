@php
$conf_scheduler = $model->getGroup('scheduler');
$conf_config = $model->getGroup('config');
@endphp
@extends($folder['folder_name'].'.layouts.master')

@section('title') {{ __('admin.text_html_title', ['module_name'=>$folder['module_name'], 'title'=>$title_shown]) }} @endsection

@section('bodyAttr')
@endsection

@section('css')
@endsection

@section('scripts')
@endsection

@section('content')
@php

@endphp
{!! Form::open(['url'=>route($module['main_route'].'.indexpost'), 'method'=>'post', 'class'=>'needs-validation', 'novalidate']) !!}
    <div class="row">
        <div class="offset-xl-2 col-xl-8 offset-lg-1 col-lg-10 col-md-12">
            <div class="main-card mb-3 card">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                    <div>{{ $title_shown }}</div>
                    <div>
                        <button type="submit" class="mt-1 btn btn-dark btn-sm">{!! __('admin.text_button_submit') !!}</button>
                    </div>
                </div>

                <div class="card-body"> 
                   <div class="row">    
                        {{csrf_field()}}
                        @method("POST")

                        <div class="col-md-6">
                            @php $current_field = 'scheduler[source_number]'; @endphp
                            {!! Form::bsInput('number', $current_field, __('Job Frequency(In Minutes)'), $conf_scheduler['frequency_minute'] ?? '1', ['required', 'min'=>'1', 'max'=>'1000', 'step'=>'1'], ['vertical'=>true]); !!}
                        </div>

                        <div class="col-md-6">
                            @php $current_field = 'scheduler[jobs_per_schedule]'; @endphp
                            {!! Form::bsInput('number', $current_field, __('Jobs Per Schedule'), $conf_scheduler['jobs_per_schedule'] ?? '1', ['required', 'min'=>'1', 'max'=>'100000', 'step'=>'1'], ['vertical'=>true]); !!}
                        </div>

                        <div class="col-md-6">
                            @php $current_field = 'scheduler[distribution_percent]'; @endphp
                            {!! Form::bsInput('number', $current_field, __('Distribution Percent'), $conf_scheduler['distribution_percent'] ?? '10', ['required', 'min'=>'0', 'max'=>'100', 'step'=>'1'], ['vertical'=>true]); !!}
                        </div>

                        <div class="col-md-6">
                            @php $current_field = 'config[admin_mail]'; @endphp
                            {!! Form::bsText($current_field, __('Admin Email Address'), $conf_config['admin_mail'] ?? '', ['required', 'data-toggle'=>'tags'], ['vertical'=>true]); !!}
                        </div>
                    </div>  
                </div>

                <div class="card-footer bg-dark d-flex justify-content-end align-items-center">
                    <button type="submit" class="btn btn-primary btn-sm">{!! __('admin.text_button_submit') !!}</button>
                </div>
            </div>
        </div>
    </div>
{!! Form::close() !!}
@endsection