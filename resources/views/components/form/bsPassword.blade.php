@php 
$vertical = empty($extra_attributes['vertical']) ? false : true; 
$reveal = !empty($extra_attributes['reveal']) ? true : false; 

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
        @if($reveal)
        <div class="input-group">
            <div class="input-group-prepend">
                <div class="input-group-text p-0 pl-2">
                    <div class="custom-checkbox custom-control">
                        <input type="checkbox" class="custom-control-input" id="{{ $name }}_checkpass">
                        <label class="custom-control-label" for="{{ $name }}_checkpass" title="Show Password"></label>
                    </div>
                </div>
            </div>
        @endif
            {!! Form::input('password', $name, $value ?? '', array_merge(['class' => 'form-control'.( !empty($errors->has($error_name)) ? ' is-invalid' : '' )], $field_attributes)); !!}
            <div class="invalid-feedback"> @if($errors->has($error_name)) {{ $errors->first($error_name) }} @endif </div>
        {!! $reveal ? '</div>' : '' !!}
        

    {!! !$vertical ? '</div>' : '' !!}
</div>
@if($reveal)
<script type="text/javascript">
$(function(){
    $("#{{ $name }}_checkpass").click(function(){
        var $checked = $(this).prop('checked');
        var $target_selector = $(this).closest(".input-group").find("#{{ $name }}");

        if($checked){
            $target_selector.attr('type', 'text');
        } else{
           $target_selector.attr('type', 'password'); 
        }
    });
});
</script>
@endif