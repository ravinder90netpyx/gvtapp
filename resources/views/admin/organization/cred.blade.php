@extends($folder['folder_name'].'.layouts.master')

@section('title') {{ __('admin.text_html_title', ['module_name'=>$folder['module_name'], 'title'=>$title_shown]) }} @endsection

@section('bodyAttr')
@endsection

@section('css')
@endsection

@section('scripts')
@endsection

@section('content')
<div class="app-page-title row">
    <div class="col page-title-wrapper">
        <div class="page-title-heading">
            <h1>{{ $title_shown }}</h1>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="main-card mb-3 card">
            <div class="card-body">
                {!! Form::open(['url'=>$action, 'method'=>$method, 'class'=>'needs-validation', 'novalidate']) !!}
                    @php $current_field = 'name'; @endphp
                    {!! Form::bsText($current_field, __('admin.text_name'), $form_data->$current_field ?? '', ['required']); !!}

                    @php $current_field = 'gst_number'; @endphp
                    {!! Form::bsText($current_field, __('GSTIN Number'), $form_data->$current_field ?? '', ['required', 'placeholder'=>__('admin.text_unique_msg')]); !!}

                    @php $current_field = 'description'; @endphp
                    {!! Form::bsTextArea($current_field, __('Description'), $form_data->$current_field ?? '', ['required']); !!}

                    @php 
                    $current_field = 'users_allowed';
                    $min_users = $form_data->$current_field ?? 1;
                    @endphp
                    {!! Form::bsInput('number', $current_field, __('Number of Licenses'), $form_data->$current_field ?? '1', ['required', 'step'=>'1', 'min'=>$min_users, 'max'=>'65535']); !!}

                    <button type="submit" class="mt-1 btn btn-primary">{!! __('admin.text_button_submit') !!}</button>
                    <a href="{{ route($module['main_route'].'.index') }}" class="mt-1 btn btn-danger">{!! __('admin.text_button_back') !!}</a>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection