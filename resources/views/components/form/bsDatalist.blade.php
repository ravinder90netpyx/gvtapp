@php 
$vertical = empty($extra_attributes['vertical']) ? false : true;
$multiple = !empty($field_attributes['multiple']) ? true : false;

$field_id = rtrim($name, '[]');
if(substr($name, -2)=="[]"){
    $error_name = rtrim($name, '[]');
    #$error_name = $error_name.'.*';
} else{
    $error_name = str_replace('[', '.', str_replace(']', '', $name));
}

if($multiple){
    $data = array_filter($data);
}

$default_attr = ['id'=>$field_id, 'data-toggle'=>( $multiple ? 'select-multiple' : 'select' ), 'class' => 'custom-select '.( !empty($errors->has($error_name)) ? 'is-invalid ' : '' )];
@endphp
<div class="position-relative form-group{{ !$vertical ? ' row' : '' }}">
    {!! Form::rawLabel($field_id, $label_text.( in_array('required', $field_attributes) ? '<span class="req"></span>' : '' ), ['class' => 'form-control-label'.( !$vertical ? ' col-md-3 col-form-label' : '' )]); !!}
    {!! !$vertical ? '<div class="col-md-9">' : '' !!}
        {!! Form::select($name, $data, $value ?? '', array_merge($default_attr, $field_attributes)); !!}
        <div class="invalid-feedback"> @if($errors->has($error_name)) {{ $errors->first($error_name) }} @endif </div>
    {!! !$vertical ? '</div>' : '' !!}
</div>