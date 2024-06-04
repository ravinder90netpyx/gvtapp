@php 
$vertical = empty($extra_attributes['vertical']) ? false : true; 
$addon_check = !empty($extra_attributes['addon_check']) ? true : false;
$addon_check_checked = !empty($extra_attributes['addon_check_checked']) ? true : false;

if(substr($name, -2)=="[]"){
    $fieldname_extract = rtrim($name, '[]');
    $error_name = $fieldname_extract.'.*';
} else{
    $error_name = str_replace('[', '.', str_replace(']', '', $name));
}
@endphp
<div class="position-relative form-group{{ !$vertical ? ' row' : '' }}">
    {!! Form::rawLabel($name, $label_text.( in_array('required', $field_attributes) ? '<span class="req"></span>' : '' ), ['class' => 'form-control-label'.( !$vertical ? ' col-md-3 col-form-label' : '' )]); !!}
    {!! !$vertical ? '<div class="col-md-9">' : '' !!}
        {!! $addon_check ? '<div class="input-group">' : '' !!}
            {!! Form::input($type, $name, $value ?? '', array_merge(['class' => 'form-control'.( !empty($errors->has($error_name)) ? ' is-invalid' : '' )], $field_attributes)); !!}
            @if($addon_check)
                <div class="input-group-append">
                    <div class="input-group-text p-0 pl-3">
                        <div class="custom-checkbox custom-control">
                            {!! Form::hidden($extra_attributes['addon_check'], '0', ['id'=>'']); !!}
                            {!! Form::checkbox($extra_attributes['addon_check'], '1', $addon_check_checked, ['id'=>$extra_attributes['addon_check'], 'class'=>'custom-control-input']); !!}
                            <label class="custom-control-label" for="{{ $extra_attributes['addon_check'] }}" title="{{ $extra_attributes['addon_check_title'] ?? 'Required' }}"></label>
                        </div>
                    </div>
                </div>
            @endif
            <div class="invalid-feedback"> @if($errors->has($error_name)) {{ $errors->first($error_name) }} @endif </div>
        {!! $addon_check ? '</div>' : '' !!}
    {!! !$vertical ? '</div>' : '' !!}
</div>

{{-- {!! Form::bsInput('search', $current_field, __('Member'), $form_data->$current_field ?? '', ['required'], ['vertical'=>true ]); !!} --}}