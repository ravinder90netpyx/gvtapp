@php 
$vertical = empty($extra_attributes['vertical']) ? false : true;
$prepend_hidden = empty($extra_attributes['prepend_hidden']) ? false : true;

if(substr($name, -2)=="[]"){
    $fieldname_extract = rtrim($name, '[]');
    $error_name = $fieldname_extract.'.*';
} else{
    $error_name = str_replace('[', '.', str_replace(']', '', $name));
}
@endphp
<div class="position-relative form-group{{ !$vertical ? ' row' : '' }}">
    {!! Form::rawLabel($name, $label_text, ['class' => 'form-control-label'.( !$vertical ? ' col-md-3 col-form-label' : '' )]); !!}
    {!! !$vertical ? '<div class="col-md-9">' : '<div>' !!}
        @if($prepend_hidden) {!! Form::hidden($name, '0', ['id'=>'']); !!} @endif
        <label class="custom-toggle">
            {!! Form::checkbox($name, $value ?? '1', $checked); !!}
            <span class="custom-toggle-slider rounded-circle" title="{{ $label_text }}" data-label-off="No" data-label-on="Yes"></span>
            <div class="invalid-feedback"> @if($errors->has($error_name)) {{ $errors->first($error_name) }} @endif </div>
        </label>
        
    {!! !$vertical ? '</div>' : '</div>' !!}
</div>