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
    const abc=$('#mobile_number').intlTelInput({
        utilsScript: "{!! asset('vendor/intl-tel-input/lib/libphonenumber/build/utils.js') !!}",
        autoPlaceholder: true,
        nationalMode: false,
        // onlyCountries: ['in','us', 'ca', 'au', 'nz'],
        onlyCountries: ['in'],
        // preferredCountries: ['in'],
        allowDropdown: false
    });

    $('#mobile_number').on('input', function(){
        // abc=$(this).getValidationError();

        is_valid=$('#mobile_number').intlTelInput('isValidNumber');
        country_data=$('#mobile_number').intlTelInput('getSelectedCountryData');
        dial_code= country_data.dialCode;
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
    });

    $('.intl-tel-input').addClass('w-100');

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
                    {!! Form::bsText($current_field, __('Name'), $form_data->$current_field ?? '', ['required']); !!}

                    @php $current_field = 'unit_number'; @endphp
                    {!! Form::bsText($current_field, __('Unit Number'), $form_data->$current_field ?? '', ['required']); !!}

                    @php $current_field = 'mobile_number'; @endphp
                    {!! Form::bsInput('tel',$current_field, __('Mobile Number'), $form_data->$current_field ?? '', ['required', 'autocomplete'=>'tel']); !!}
                    
                    @php 
                    $current_field = 'charges_id';
                    $row_data = [];
                    $data_select = \App\Models\Charges::where([['delstatus','<','1'],['status','>','0']])->get();
                    foreach($data_select as $ds) $row_data[$ds->id] = $ds->name;
                    @endphp
                    {!! Form::bsSelect($current_field, __('Charge'), $row_data, $form_data->$current_field ?? '', ['data-toggle'=> 'select', 'required']); !!}

                    @if(in_array(1, $roles) && $mode!='edit')
                        @php 
                        $current_field = 'organization_id';
                        $row_data=[];
                        $data_select=\App\Models\Organization::select('id','name')->get();
                        foreach($data_select as $ds) $row_data[$ds->id]=$ds->name;
                        @endphp
                        {!! Form::bsSelect($current_field, __('Organization'), $row_data, $form_data->$current_field ?? '', ['data-toggle'=> 'select', 'required']); !!}
                    @endif

                    <button type="submit" class="mt-1 btn btn-primary">{!! __('admin.text_button_submit') !!}</button>
                    <a href="{{ route($module['main_route'].'.index') }}" class="mt-1 btn btn-danger">{!! __('admin.text_button_back') !!}</a>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection