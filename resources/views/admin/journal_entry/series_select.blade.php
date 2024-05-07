@php 
$val = $form_data->series_id ?? '';
$val2 =  $form_data->series_next_number ?? '';
$data_multi = [
	['type'=>'select', 'name'=>'series_id', 'value'=>$val, 'data'=>$row_data, 'field_attributes'=>['id'=>'series_id', 'required', 'disabled', 'title'=>'Series', 'placeholder'=> 'Select Series']],
    ['type'=>'number', 'name'=>'next_number', 'value'=>$val2, 'field_attributes'=>['id'=>'next_number', 'disabled', 'title'=>'Series Number']]
];
@endphp
{!! Form::bsMultiInput($data_multi, __('Series<span class="req"></span>'), ['vertical'=>true]); !!}
{{--{!! Form::bsSelect($current_field, __('Series'), $row_data, $form_data->$current_field ?? '', ['required'], ['vertical'=>true]); !!}--}}