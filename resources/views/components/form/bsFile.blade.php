@php 
$vertical = empty($extra_attributes['vertical']) ? false : true; 

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
        <div class="custom-file">
            {!! Form::file($name, array_merge(['class' => 'custom-file-input'.( !empty($errors->has($error_name)) ? ' is-invalid' : '' ), 'lang'=>'en'], $field_attributes)); !!}
            <label class="custom-file-label" for="{{ $name }}"></label>
            <div class="invalid-feedback"> @if($errors->has($error_name)) {{ $errors->first($error_name) }} @endif </div>
        </div>
    {!! !$vertical ? '</div>' : '' !!}
</div>