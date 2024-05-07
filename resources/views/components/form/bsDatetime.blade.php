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
        {!! Form::input('search', $name, $value ?? '', array_merge(['id'=>$id, 'class' => 'form-control'.( !empty($errors->has($error_name)) ? ' is-invalid' : '' )], $field_attributes)); !!}
        <div class="invalid-feedback"> @if($errors->has($error_name)) {{ $errors->first($error_name) }} @endif </div>
    {!! !$vertical ? '</div>' : '' !!}
</div>
<script type="text/javascript">
$(function(){
    $("#{{ $id }}").datetimepicker(
        @php
        $pass_args = array(
            'format' => 'YYYY-MM-DD HH:mm:ss',
            'icons' => array(
                "time" => "fa fa-clock",
                "date" => "fa fa-calendar-day",
                "up" => "fa fa-chevron-up",
                "down" => "fa fa-chevron-down",
                "previous" => 'fa fa-chevron-left',
                "next" =>'fa fa-chevron-right',
                "today" =>'fa fa-screenshot',
                "clear" => 'fa fa-trash',
                "close" => 'fa fa-remove'
            )
        );
        echo json_encode($pass_args);
        @endphp
    );
});
</script>