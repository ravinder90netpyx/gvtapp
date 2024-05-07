@php 
$vertical = empty($extra_attributes['vertical']) ? false : true;
$input_group_sm = !empty($extra_attributes['input_group_sm']) ? true : false;
$remove_blank_field = !empty($extra_attributes['remove_blank_field']) ? true : false;

$addon_check = !empty($extra_attributes['addon_check']) ? true : false;
$addon_check_checked = !empty($extra_attributes['addon_check_checked']) ? true : false;

$addon_button = !empty($extra_attributes['addon_button_text']) ? true : false;
if($addon_button && empty($extra_attributes['addon_button_attributes'])){
    $extra_attributes['addon_button_attributes'] = [];
}
@endphp
<div class="position-relative form-group-multi-input form-group{{ !$vertical ? ' row' : '' }}">
    {!! Form::rawLabel($multi_attributes[0]['name'], $label_text, ['class' => 'form-control-label'.( !$vertical ? ' col-md-3 col-form-label' : '' )]); !!}
    {!! !$vertical ? '<div class="col-md-6">' : '' !!}
        <div class="input-group @if($input_group_sm) input-group-sm @endif">
@if($addon_check)
    <div class="input-group-prepend">
        <div class="input-group-text p-0 pl-3">
            <div class="custom-checkbox custom-control">
                {!! Form::hidden($extra_attributes['addon_check'], '0', ['id'=>'']); !!}
                {!! Form::checkbox($extra_attributes['addon_check'], '1', $addon_check_checked, ['id'=>$extra_attributes['addon_check'], 'class'=>'custom-control-input']); !!}
                <label class="custom-control-label" for="{{ $extra_attributes['addon_check'] }}" title="{{ $extra_attributes['addon_check_title'] ?? 'Required' }}"></label>
            </div>
        </div>
    </div>
@endif
@foreach($multi_attributes as $arr)
@php
extract($arr);
if(substr($name, -2)=="[]"){
    $fieldname_extract = rtrim($name, '[]');
    $error_name = $fieldname_extract.'.*';
} else{
    $error_name = str_replace('[', '.', str_replace(']', '', $name));
}
@endphp
    @if($type=="textarea")
        {!! Form::textarea($name, $value ?? '', array_merge(['class' => 'form-control'.( !empty($errors->has($error_name)) ? ' is-invalid' : '' ), 'cols'=>'100', 'rows'=>'4'], $field_attributes)); !!}
    @elseif($type=="select")
        @php
        $additional_data = $extra_attributes['additional_data'] ?? [];
        $default_attr = ['class' => 'custom-select'.( !empty($errors->has($error_name)) ? ' is-invalid' : '' )];
        if(!$remove_blank_field) $default_attr['placeholder'] = __('admin.text_select');
        @endphp
        {!! Form::select($name, $data, $value ?? '', array_merge($default_attr, $field_attributes), $additional_data); !!}
    @else
        {!! Form::input($type, $name, $value ?? '', array_merge(['class' => 'form-control'.( !empty($errors->has($error_name)) ? ' is-invalid' : '' )], $field_attributes)); !!}
    @endif       
@endforeach
@if($addon_button)
    <div class="input-group-append">
        {!! Form::button( ($extra_attributes['addon_button_text'] ?? 'GET'), array_merge(['class'=>'btn btn-dark'], $extra_attributes['addon_button_attributes']) ); !!}
    </div>
@endif
            <div class="invalid-feedback"> @if($errors->has($error_name)) {{ $errors->first($error_name) }} @endif </div>
       </div>
    {!! !$vertical ? '</div>' : '' !!}
</div>

