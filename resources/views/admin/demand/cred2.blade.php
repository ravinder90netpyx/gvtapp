@php
// dump($errors);
$auth_user = Illuminate\Support\Facades\Auth::user();
$roles = $auth_user->roles()->pluck('id')->toArray();
Form::macro('variantData', function($dataArray)
{
    $rowIndex = $dataArray['id'] ?? 0 ;
    $dataArray['demand_charge_id'] = $dataArray['id'] ?? '';
    $charge_type_id = $dataArray['charge_type_id'] = $dataArray['charge_type_id'] ?? '';
    $charge_type = !empty($dataArray['charge_type_id']) ? \App\Models\ChargeType::find($dataArray['charge_type_id'])->name :'';
    $dataArray['name'] = $dataArray['name'] ?? '';
    $dataArray['pending_amount'] = $dataArray['pending_amount'] ?? 0;
    $dataArray['paid_amount'] = $dataArray['paid_amount'] ?? 0;
    $dataArray['amount'] = ($dataArray['pending_amount']+$dataArray['paid_amount']) ?? 0;
    $dataArray['remark'] = $dataArray['remark'] ?? '';

    return "<div class='row py-1 charge_type[$charge_type_id]'>"
    .Form::hidden('charge_type['.$charge_type_id.'][demand_charge_id]', $dataArray['demand_charge_id'] , ['class' => 'form-control']).
    "<div class='col-sm'>".Form::text('charge_type['.$charge_type_id.'][name]', $charge_type , ['class' => 'form-control name', 'placeholder'=>'Name', 'disabled'])."</div>
    <div class='col-sm'>".Form::input('number', 'charge_type['.$charge_type_id.'][amount]', $dataArray['amount'], ['class'=>'form-control amount', 'placeholder'=>'Amount', 'required'],[])."</div>
    <div class='col-sm'>".Form::text('charge_type['.$charge_type_id.'][remark]', $dataArray['remark'] , ['class' => 'form-control remark', 'placeholder'=>'Remarks', 'required'])."</div>
    </div>";
});
// dump($dataArray);
$multi_data ='';
if($mode=='insert'){
    $multi_data=Form::variantData([], 0);
}
else{
    if(isset($dataArray) && is_array($dataArray) && count($dataArray) > 0){
        foreach($dataArray as $data){
            $multi_data = $multi_data.Form::variantData($data);
        }
    }
}
@endphp
@extends($folder['folder_name'].'.layouts.master')

@section('title') {{ __('admin.text_html_title', ['module_name'=>$folder['module_name'], 'title'=>$title_shown]) }} @endsection

@section('bodyAttr')
@endsection

@section('css')
@endsection

@section('scripts')
<script type="text/javascript">
function charge_type_select(sel_arr){
  $('.addon').show()
  sel_arr.forEach(({id,name})=>{
  	add_charge_type_row(id,name);
  });
}
function add_charge_type_row(id,name) {
  var newRow = '<div class="row py-1 charge_type['+ id +']">' +
    '<input type="hidden"  name="charge_type[' + id + '][demand_charge_id]" class="form-control">' +
    '<div class="col-sm">' +
    '<input type="text" placeholder="Name" name="charge_type[' + id + '][name]" value="'+name+'" disabled class="form-control name">' +
    '</div>' +
    '<div class="col-sm">'+
    '<input type="number" placeholder="Amount" name="charge_type['+ id +'][amount]" class="form-control amount">'+
    '</div>'+
    '<div class="col-sm">'+
    '<input type="text" placeholder="Remarks" name="charge_type['+ id +'][remark]" class="form-control remark">'+
    '</div>'+
    '</div>';
  $('.temp1').append(newRow);
}

function datetimepicker_month(id){
  date = new Date('2020-01-01');
  $('#'+id).datepicker({
    format: "yyyy-mm",
    startView: 1,
    minViewMode: 1,
    startDate : date,
    // container: '#cred_modal',
    autoclose: true
  });
}

function datetimepicker_date(id){
  date = new Date('2020-01-01');
  $('#'+id).datepicker({
    format: "yyyy-mm-dd",
    startView: 0,
    minViewMode: 0,
    startDate : date,
    // container: '#cred_modal',
    autoclose: true
  });
}

function autocomplete_trigger(org_id){
  if ($('#member_id').hasClass("select2-hidden-accessible")) {
    $('#member_id').select2('destroy');
  }
  $("#member_id").select2({
    minimumInputLength : '1',
    ajax: {
      url : "{{ route('supanel.common.ajax_member') }}?org_id="+org_id,
      // data:'json',
      processResults: function(data) {
  	    // Transforms the top-level key of the response object from 'items' to 'results'
    	  return {
      	  results: data
      	};
    	}
  	}
  });
}

function series_select(org_id){
  $.ajax({
    url: '{{ route("supanel.common.series_select") }}',
    method: "POST",
    data: {'_token': '{!! csrf_token() !!}', 'org_id' : org_id, 'type':'demand'},
    success: function(response){
      $('#series_div_id').html(response.html);
      $('#series_id').prop('disabled',false);
      $('#series_id').select2();
    },
    error: function(error) {
      console.error('Error fetching folder content:', error);
    }
  });
}

function series_data(ser_id){
  $.ajax({
    url: '{{ route("supanel.common.series_data") }}',
    method: "POST",
    data: {'_token': '{!! csrf_token() !!}', 'ser_id' : ser_id},
    success: function(response){
      $('#series_id').val(ser_id);
      $('#next_number').val(response.next_num);
      $('#series_id').closest('.form-group').find('label').append(' (S. NO.- '+response.serial_no+')');
    },
    error: function(error) {
      console.error('Error fetching folder content:', error);
    }
  });
}

$(function(){
	@if(!in_array(1,$roles))
		$('#member_id').prop('disabled', false);
		$('#series_id').prop('disabled', false);
		autocomplete_trigger({{ $auth_user->organization_id }});
    series_select({{ $auth_user->organization_id }});
	@endif
  $(document).on('change', '#organization_id', function(){
    val = $(this).val();
    $('#series_id').val('');
    $('#member_id').val('');
    $('#next_number').val('');
    $('#charge').val('');
    $('.charge').hide();
    if(val !== undefined && val !== null && val !== 0 && val !== ''){
      $('#series_id').prop('disabled', false);
      $('#member_id').prop('disabled', false);
      autocomplete_trigger(val);
      series_select(val);
    } else{
      $('#series_id').val('');
      $('#member_id').val('');
      $('#next_number').closest('.form-group').find('label').html('Series<span class="req"></span>');
      $('#series_id').prop('disabled', true);
      $('#member_id').prop('disabled', true);
      if ($('#member_id').hasClass("select2-hidden-accessible")) {
        $('#member_id').select2('destroy');
      }
    }
  });

  $(document).on('change', '#series_id', function(){
    $('#next_number').val('');
    $('#next_number').closest('.form-group-multi-input').find('label').html('Series<span class="req"></span>');
    val = $(this).val();
    if(val !== undefined && val !== null && val !== 0 && val !== ''){
      $('#series_id').closest('.form-group').find('label').html('Series<span class="req"></span>');
      series_data(val);
			$(this).closest('#series_div_id').find('.input-group').hide();
    }
  });

  $('select[name="charge_type_id[]"]').on('change', function(){
    var selectedOptions = $(this).find('option:selected').map(function() {
  	  return {
    	  id: $(this).val(),
      	name: $(this).text()
    	};
    }).get();
  	$('.temp1').html('');
    console.log(selectedOptions.length);
    if(selectedOptions.length!=0) charge_type_select(selectedOptions);
        
    // console.log(selectedOptions);
  });
  datetimepicker_date('due_date');
  @if($mode=='edit')
  	$('.addon').show();
    $('#series_id').closest('.form-group').find('label').append(" (S. NO.- {{ $form_data->series_number }})");
  @endif
    // $('#charge_type_id').trigger('change');
});
</script>
@endsection

@section('content')
{!! Form::open(['url'=>$action, 'method'=>$method, 'class'=>'needs-validation', 'novalidate']) !!}
  <div class="row">
    <div class="offset-xl-2 col-xl-8 offset-lg-1 col-lg-10 col-md-12">
      <div class="main-card mb-3 card">
        <div class="card-header bg-primary d-flex justify-content-between align-items-center">
          <div>{{ $title_shown }}@if($mode=='edit')( {{ $form_data->demand_reciept_number ?? '' }} )@endif</div>
        	<div>
        		<button type="submit" class="mt-1 btn btn-dark btn-sm">{!! __('admin.text_button_submit') !!}</button>
        		<a href="{{ route($module['main_route'].'.index') }}" class="mt-1 btn btn-danger btn-sm">{!! __('admin.text_button_back') !!}</a>
      		</div>
    		</div>

    		<div class="card-body"> 
      		<div class="row">
        		@if(in_array(1, $roles) && $mode!='edit')
        		<div class="col-md-6">
          		@php 
          		$current_field = 'organization_id';
          		$row_data=[];
          		$data_select=\App\Models\Organization::select('id','name')->get();
          		foreach($data_select as $ds) $row_data[$ds->id]=$ds->name;
          		@endphp
          		{!! Form::bsSelect($current_field, __('Organization'), $row_data, $form_data->$current_field ?? '', ['data-toggle'=> 'select', 'required'], ['vertical'=> true]); !!}
        		</div>
        		@endif
                       
        		@if($mode != 'edit')
        		<div class="col-md-6" id="series_div_id">
          		@php $current_field = 'series_id';
          		$row_data =[];
          		$org_id = $auth_user->organization_id;
          		if(!empty($org_id)){
            		$data_select = \App\Models\Series::where([['status','>','0'], ['delstatus','<','1'], ['organization_id', '=', $org_id],['type','=','demand']])->get();
          		} else{
            		$data_select = \App\Models\Series::where([['status','>','0'], ['delstatus','<','1'], ['type','=','demand']])->get();
          		}
              $row_data =[];
          		foreach($data_select as $ds) $row_data[$ds->id] = $ds->name;
          		@endphp
          		{!! Form::bsSelect($current_field, __('Series'), $row_data, $form_data->$current_field ?? '', ['data-toggle'=> 'select', 'disabled', 'required'], ['vertical'=> true]); !!}
        		</div>
        		@endif

        		<div class="col-md-6">
          		@php $current_field = 'member_id';
          		$row_data =[];
          		$org_id = $auth_user->organization_id;
          		if(!empty($org_id)){
            		$data_select = \App\Models\Members::where([['status','>','0'], ['delstatus','<','1'], ['organization_id', '=', $org_id]])->get();
          		} else{
            		$data_select = \App\Models\Members::where([['status','>','0'], ['delstatus','<','1']])->get();
          		}
          		foreach($data_select as $ds) $row_data[$ds->id] = $ds->name;
          		@endphp
          		{!! Form::bsSelect($current_field, __('Member'), $row_data, $form_data->$current_field ?? '', ['data-toggle'=> 'select', 'disabled', 'required'], ['vertical'=> true]); !!}
        		</div>

        		<div class="col-md-6">
          		@php $current_field = 'charge_type_id';
          		$row_data =[];
          		$org_id = $auth_user->organization_id;
          		$data_select = \App\Models\ChargeType::where([['status','>','0'],['delstatus','<','1']])->get	();
          		foreach($data_select as $ds) $row_data[$ds->id] = $ds->name;
          		@endphp
          		{!! Form::bsSelect('charge_type_id[]', __('Charge Type'), $row_data, explode(',',$form_data->$current_field ?? ''), ['data-toggle'=> 'select-multiple', 'required', 'multiple'], ['vertical'=> true, 'remove_blank_field'=>true]); !!}
        		</div>

        		<div class="col-md-6">
          		@php $current_field = 'due_date';
          		$val= !empty($form_data->$current_field) ? date('Y-m-d', strtotime($form_data->$current_field)) : '';
          		@endphp
          		{!! Form::bsInput('text', $current_field, __('Due Date'), $val ?? '', [ 'required', 'autocomplete'=>'off' ],  ['vertical'=>true]); !!}
        		</div>

        		<div class="col-md-6 content_hide">
          		@php $current_field = 'partial'; @endphp
          		{!! Form::bsToggle($current_field, 'Partial Demand', '1', ( $form_data->$current_field ?? false ), ['required'], ['vertical'=>true]); !!}
        		</div>

        		{{-- <div class="col-md-6">
          		@php $current_field = 'charge'; @endphp
          		{!! Form::bsInput('number', $current_field, __('Amount'), $form_data->$current_field ?? '',['required'], ['vertical'=>true]); !!}
        		</div> --}}

        		{{-- <div class="col-md-6">
          		@php $current_field = 'name'; @endphp
          		{!! Form::bsPassword($current_field, 'Password', $form_data->$current_field ?? '', ['required'], ['vertical'=>true, 'reveal'=>true]); !!}
        		</div>
                        
        		<div class="col-md-6">
          		@php 
          		$current_field = 'name';
          		$row_data = [];
          		$data_select = DB::table('roles')->get();
          		foreach($data_select as $ds) $row_data[$ds->id] = $ds->name;
          		@endphp
          		{!! Form::bsSelect($current_field, __('admin.text_name'), $row_data, $form_data->$current_field ?? '', ['required'], ['vertical'=>true]); !!}
        		</div> --}}

        		<div class="col-md-6">
          		@php $current_field = 'remark'; @endphp
          		{!! Form::bsTextArea($current_field, __('Remark'), $form_data->$current_field ?? '', ['required'], ['vertical'=>true]); !!}
        		</div>

        		<div class="addon col-md-12 py-2 my-2" style="display:none;">
          		<div class="card-header" style=" color: white; background-color: #f5365c;"> Charge Type </div>
            	<div class="card-body" style="background-color: #fafafa;">                           
              	<div class="temp1">
                	{!! $multi_data !!}
              	</div> 
            	</div>
          	</div>
        	</div>  
      	</div>
    	</div>
  	</div>
	</div>
{!! Form::close() !!}
@endsection