@php 
$vertical = empty($extra_attributes['vertical']) ? false : true;
$id = trim(str_replace(array('[', ']'), '_', $name), '_');

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
        {!! Form::input('search', $name, $value ?? '', array_merge(['id'=>$id, 'readonly', 'class' => 'form-control'.( !empty($errors->has($error_name)) ? ' is-invalid' : '' )], $field_attributes)); !!}
        <div class="invalid-feedback"> @if($errors->has($error_name)) {{ $errors->first($error_name) }} @endif </div>
    {!! !$vertical ? '</div>' : '' !!}
</div>
<script type="text/javascript">
$(function(){
    $("#{{ $id }}").datepicker(
        @php
        $pass_args = array(
            'format' => 'yyyy-mm-dd',
            'autoclose' => true
        );
        echo json_encode($pass_args);
        @endphp
    );
});
</script>