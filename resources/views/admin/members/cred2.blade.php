@php
$auth_user = Illuminate\Support\Facades\Auth::user();
$roles = $auth_user->roles()->pluck('id')->toArray();
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
$(function(){
    // const abc=$('#mobile_number').intlTelInput({
    //     utilsScript: "{!! asset('vendor/intl-tel-input/lib/libphonenumber/build/utils2.js') !!}",
    //     autoPlaceholder: true,
    //     nationalMode: false,
    //     // onlyCountries: ['in','us', 'ca', 'au', 'nz'],
    //     onlyCountries: ['in'],
    //     // preferredCountries: ['in'],
    //     allowDropdown: false
    // });
    const abc=$('#mobile_number').intlTelInput({
        utilsScript: "{!! asset('vendor/intl-tel-input/lib/libphonenumber/build/utils.js') !!}",
        autoPlaceholder: true,
        nationalMode: false,
        onlyCountries: ['in'],
        allowDropdown: false
    });

    const abcd=$('#alternate_number').intlTelInput({
        utilsScript: "{!! asset('vendor/intl-tel-input/lib/libphonenumber/build/utils.js') !!}",
        autoPlaceholder: true,
        nationalMode: false,
        onlyCountries: ['in'],
        allowDropdown: false
    });

    $('#mobile_number').on('input', function(){
        // abc=$(this).getValidationError();
        is_valid=$('#mobile_number').intlTelInput('isValidNumber');
        country_data=$('#mobile_number').intlTelInput('getSelectedCountryData');
        dial_code= country_data.dialCode;
        //console.log(is_valid);
        if(is_valid && dial_code==91){
            //$("#contact-form-home").addClass('was-validated');
            this.setCustomValidity('');
            $(this).removeClass('is-invalid');
            $(this).addClass('is-valid');
        } else{
            this.setCustomValidity('Phone is Invalid');
            $(this).addClass('is-invalid');
            $(this).removeClass('is-valid');
        }
        // alert(this.checkValidity());
    });

    $('#alternate_number').on('input', function(){
        // abc=$(this).getValidationError();
        is_valid=$('#alternate_number').intlTelInput('isValidNumber');
        country_data=$('#alternate_number').intlTelInput('getSelectedCountryData');
        dial_code= country_data.dialCode;
        //console.log(is_valid);
        if(is_valid && dial_code==91){
            //$("#contact-form-home").addClass('was-validated');
            this.setCustomValidity('');
            $(this).removeClass('is-invalid');
            $(this).addClass('is-valid');
        } else{
            this.setCustomValidity('Phone is Invalid');
            $(this).addClass('is-invalid');
            $(this).removeClass('is-valid');
        }
        // alert(this.checkValidity());
    });

    $("#cred_form").submit(function(e) {
        if(this.checkValidity()===false){
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
        this.classList.add('was-validated');
        return true;
    });

    $('.intl-tel-input').addClass('w-100');
});
</script>
@endsection

@section('content')
{!! Form::open(['url'=>$action, 'method'=>$method, 'id'=>'cred_form', 'class'=>'needs-validation', 'novalidate']) !!}
    <div class="row">
        <div class="offset-xl-2 col-xl-8 offset-lg-1 col-lg-10 col-md-12">
            <div class="main-card mb-3 card">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                    <div>{{ $title_shown }}</div>
                    <div>
                        <button type="submit" class="mt-1 btn btn-dark btn-sm">{!! __('admin.text_button_submit') !!}</button>
                        <a href="{{ route($module['main_route'].'.index') }}" class="mt-1 btn btn-danger btn-sm">{!! __('admin.text_button_back') !!}</a>
                    </div>
                </div>

                <div class="card-body"> 
                   <div class="row">    
                        
                        <div class="col-md-6">
                             @php $current_field = 'name'; @endphp
                            {!! Form::bsText($current_field, __('Name'), $form_data->$current_field ?? '', ['required', 'pattern' => '[a-zA-Z\s]+'], ['vertical'=>true ]); !!}
                        </div>

                        <div class="col-md-6">
                            @php $current_field = 'unit_number'; @endphp
                            {!! Form::bsInput('number', $current_field, __('Unit Number'), $form_data->$current_field ?? '', ['required', 'min'=>1, 'max'=>9999 ], ['vertical'=>true ]); !!}
                        </div>
                        
                        <div class="col-md-6">
                            @php $current_field = 'mobile_number'; @endphp
                            {!! Form::bsInput('tel',$current_field, __('Mobile Number'), $form_data->$current_field ?? '', ['required', 'autocomplete'=>'tel'], ['vertical'=>true ]); !!}
                        </div>

                        <div class="col-md-6">
                            @php 
                            $current_field = 'charges_id';
                            $row_data = [];
                            $data_select = \App\Models\Charges::where([['delstatus','<','1'],['status','>','0']])->get();
                            foreach($data_select as $ds) $row_data[$ds->id] = $ds->name;
                            @endphp
                            {!! Form::bsSelect($current_field, __('Charge'), $row_data, $form_data->$current_field ?? '', ['data-toggle'=> 'select', 'required'], ['vertical'=> true]); !!}
                        </div>

                        @if(in_array(1, $roles) && $mode!='edit')
                        <div class="col-md-6">
                            @php 
                            $current_field = 'organization_id';
                            $row_data=[];
                            $data_select=\App\Models\Organization::select('id','name')->get();
                            foreach($data_select as $ds) $row_data[$ds->id]=$ds->name;
                            @endphp
                            {!! Form::bsSelect($current_field, __('Organization'), $row_data, $form_data->$current_field ?? '', ['data-toggle'=> 'select', 'required'], ['vertical'=> true]); !!}
                        </div>
                        @endif

                        <div class="col-md-6">
                             @php $current_field = 'alternate_name_1'; @endphp
                            {!! Form::bsText($current_field, __('Alternate Name 1'), $form_data->$current_field ?? '', ['required'], ['vertical'=>true ]); !!}
                        </div>

                        <div class="col-md-6">
                             @php $current_field = 'alternate_name_2'; @endphp
                            {!! Form::bsText($current_field, __('Alternate Name 2'), $form_data->$current_field ?? '', [''], ['vertical'=>true ]); !!}
                        </div>

                        <div class="col-md-6">
                             @php $current_field = 'sublet_name'; @endphp
                            {!! Form::bsText($current_field, __('Sublet Name'), $form_data->$current_field ?? '', ['required', 'pattern' => '[a-zA-Z\s]+'], ['vertical'=>true ]); !!}
                        </div>

                        <div class="col-md-6">
                            @php $current_field = 'alternate_number'; @endphp
                            {!! Form::bsInput('tel',$current_field, __('Alternate Number'), $form_data->$current_field ?? '', ['required', 'autocomplete'=>'tel'], ['vertical'=>true ]); !!}
                        </div>

                    </div>  
                </div>
            </div>
        </div>
    </div>
{!! Form::close() !!}
@endsection