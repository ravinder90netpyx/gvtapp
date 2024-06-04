@php 
$vertical = empty($extra_attributes['vertical']) ? false : true;
$slug = !empty($extra_attributes['slug']) ? $extra_attributes['slug'] : false;
$slug_capitalize = !empty($extra_attributes['slug_capitalize']) ? true : false;
$disable_slug_script = !empty($extra_attributes['disable_slug_script']) ? true : false;

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

        {!! $slug ? '<div class="input-group">' : '' !!}
            {!! Form::text($name, $value ?? '', array_merge(['class' => 'form-control'.( !empty($errors->has($error_name)) ? ' is-invalid' : '' )], $field_attributes)); !!}

            @if($slug)
            <div class="input-group-append">
                <button class="btn btn-outline-primary" type="button" id="{{ $name }}_getslug">{{ $extra_attributes['slug_btntext'] ?? 'Auto Fill' }}</button>
            </div>
            @endif

            <div class="invalid-feedback"> @if($errors->has($error_name)) {{ $errors->first($error_name) }} @endif </div>
        {!! $slug ? '</div>' : '' !!}

    {!! !$vertical ? '</div>' : '' !!}
</div>
@if($slug && empty($disable_slug_script))
<script type="text/javascript">
$(function(){
    $("#{{ $name }}_getslug").click(function(){
        var str = $('#{{ $slug }}').val();
        str1 = str.replace(/\s+/g, '_').toLowerCase();  
        str2 = str1.replace(/[^a-zA-Z0-9-_]/g,'');
        @if($slug_capitalize) str2 = str2.toUpperCase(); @endif

        $(this).closest('.input-group').find('.form-control').val(str2);
    });
});
</script>
@endif