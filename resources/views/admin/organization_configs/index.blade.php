@php
    // dd($model);
    $auth_user = Auth::user();  
    $roles = $auth_user->roles()->pluck('name','id')->toArray();
    $conf_whatsapp_settings = $model->getGroup('whatsapp_settings', $auth_user->organization_id);
    // $conf_config = $model->getGroup('config');
@endphp
@extends($folder['folder_name'].'.layouts.master')

@section('title') {{ __('admin.text_html_title', ['module_name'=>$folder['module_name'], 'title'=>$title_shown]) }} @endsection

@section('bodyAttr')
@endsection

@section('css')
<link rel="stylesheet" href="{!! asset('vendor/intl-tel-input/build/css/intlTelInput.css') !!}">
@endsection

@section('scripts')
<script src="{!! asset('vendor/intl-tel-input/build/js/intlTelInput.min.js') !!}"></script>


<script type="text/javascript">
    // $(function(){
    //     const abc=$('#whatsapp_settings\\[source_number\\]').intlTelInput({
    //         utilsScript: "{!! asset('vendor/intl-tel-input/lib/libphonenumber/build/utils.js') !!}",
    //         autoPlaceholder: true,
    //         nationalMode: false,
    //         onlyCountries: ['in'],
    //         allowDropdown: false
    //     });

    //     $('#whatsapp_settings\\[source_number\\]').on('input', function(){
    //         // abc=$(this).getValidationError();
    //         is_valid=$('#whatsapp_settings\\[source_number\\]').intlTelInput('isValidNumber');
    //         country_data=$('#whatsapp_settings\\[source_number\\]').intlTelInput('getSelectedCountryData');
    //         dial_code= country_data.dialCode;
    //         //console.log(is_valid);
    //         if(is_valid && dial_code==91){
    //             //$("#contact-form-home").addClass('was-validated');
    //             this.setCustomValidity('');
    //             $(this).removeClass('is-invalid');
    //             $(this).addClass('is-valid');
    //         } else{
    //             this.setCustomValidity('Phone is Invalid');
    //             $(this).addClass('is-invalid');
    //             $(this).removeClass('is-valid');
    //         }
    //         // alert(this.checkValidity());
    //     });

    //     $('.intl-tel-input').addClass('w-100');
    // });
</script>
@endsection

@section('content')
@php

@endphp
{!! Form::open(['url'=>route($module['main_route'].'.store'), 'method'=>'post', 'autocomplete'=>'off', 'class'=>'needs-validation', 'novalidate']) !!}
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
                            @php $current_field = 'whatsapp_settings[source_number]'; @endphp
                            {!! Form::bsInput('tel', $current_field, __('Source Number'), $conf_whatsapp_settings['source_number'] ?? '', ['required'], ['vertical'=>true]); !!}
                        </div>

                        {{--<div class="col-md-6">
                            @php $current_field = 'whatsapp_settings[template_id]'; @endphp
                            {!! Form::bsInput('text', $current_field, __('Template Id'), $conf_whatsapp_settings['template_id'] ?? '', ['required'], ['vertical'=>true]); !!}
                        </div> --}}

                        <div class="col-md-6">
                            @php $current_field = 'whatsapp_settings[api_key]'; @endphp
                            {!! Form::bsInput('password', $current_field, __('Api Key'), $conf_whatsapp_settings['api_key'] ?? '', ['required'], ['vertical'=>true]); !!}
                        </div>

                        <div class="col-md-6">
                            @php $current_field = 'whatsapp_settings[src_name]'; @endphp
                            {!! Form::bsInput('text', $current_field, __('API Source Name'), $conf_whatsapp_settings['src_name'] ?? '', ['required'], ['vertical'=>true]); !!}
                        </div>

                        <div class="col-md-6">
                            @php $current_field = 'whatsapp_settings[channel]'; @endphp
                            {!! Form::bsInput('text', $current_field, __('Channel'), $conf_whatsapp_settings['channel'] ?? '', ['required'], ['vertical'=>true]); !!}
                        </div>

                        <div class="col-md-6">
                            @php $current_field = 'whatsapp_settings[api_url]'; @endphp
                            {!! Form::bsInput('text', $current_field, __('API Url'), $conf_whatsapp_settings['api_url'] ?? '', ['required'], ['vertical'=>true]); !!}
                        </div>

                       {{-- <div class="col-md-6">
                            @php $current_field = 'config[admin_mail]'; @endphp
                            {!! Form::bsText($current_field, __('Admin Email Address'), $conf_config['admin_mail'] ?? '', ['required', 'data-toggle'=>'tags'], ['vertical'=>true]); !!}
                        </div> --}}
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