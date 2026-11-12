@extends($folder['folder_name'].'.layouts.master')

@section('title') {{ __('admin.text_html_title', ['module_name'=>$folder['module_name'], 'title'=>$title_shown]) }} @endsection

@section('bodyAttr')
@endsection

@section('css')
@endsection

@section('scripts')
<script>
function datetimepicker_date(id){
    date = new Date('2020-01-01');
    $('#'+id).datepicker({
        format: "yyyy-mm-dd",
        startView: 0,
        minViewMode: 0,
        startDate : date,
        // container: '#cred_modal',
        autoclose: true
    });
}
$(function(){
    datetimepicker_date('start_date');
    $('#same_date').on('click', function(){
        val = $(this).prop('checked');
        if(val){
            let today = new Date().toISOString().split('T')[0];
            $('#start_date').val(today);
        } else{
            $('#start_date').val('')
        }
    });
});
</script>
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

                    @php $current_field = 'address'; @endphp
                    {!! Form::bsTextArea($current_field, __('Address'), $form_data->$current_field ?? '', ['required']); !!}

                    @php
                    $current_field = 'start_date';
                    $val= !empty($form_data->$current_field) ? date('Y-m-d', strtotime($form_data->$current_field)) : '';
                    $add_perm = [];
                    $apm = ['addon_check'=>'same_date', 'addon_check_title' => 'Same as Todays Date'];
                    $add_perm = array_merge($add_perm, $apm);
                    @endphp
                    {!! Form::bsInput('text', $current_field, __('Start Date'), $val ?? '', ['required', 'autocomplete'=>'off'], $add_perm); !!}

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