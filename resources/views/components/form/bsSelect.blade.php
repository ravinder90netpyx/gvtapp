@php 
$vertical = empty($extra_attributes['vertical']) ? false : true;

$remove_blank_field = !empty($extra_attributes['remove_blank_field']) ? true : false;

if(substr($name, -2)=="[]"){
    $fieldname_extract = rtrim($name, '[]');
    $error_name = $fieldname_extract.'.*';
} else{
    $error_name = str_replace('[', '.', str_replace(']', '', $name));
}

$default_attr = ['class' => 'custom-select'.( !empty($errors->has($error_name)) ? ' is-invalid' : '' )];
if(!$remove_blank_field) $default_attr['placeholder'] = __('admin.text_select');
@endphp
<div class="position-relative form-group{{ !$vertical ? ' row' : '' }}">
    {!! Form::rawLabel($name, $label_text.( in_array('required', $field_attributes) ? '<span class="req"></span>' : '' ), ['class' => 'form-control-label'.( !$vertical ? ' col-md-3 col-form-label' : '' )]); !!}
    {!! !$vertical ? '<div class="col-md-9">' : '' !!}
        {!! Form::select($name, $data, $value ?? '', array_merge($default_attr, $field_attributes)); !!}
        <div class="invalid-feedback"> @if($errors->has($error_name)) {{ $errors->first($error_name) }} @endif </div>
    {!! !$vertical ? '</div>' : '' !!}
</div>