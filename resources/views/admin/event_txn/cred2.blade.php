@php 
$auth_user = Illuminate\Support\Facades\Auth::user();
$roles = $auth_user->roles()->pluck('id')->toArray();;
Form::macro('variantData', function($dataArray, $event_id=null)
{
    $dataArray['attendes_id'] = $dataArray['attendes_id'] ?? 0 ;
    $rowIndex= $dataArray['id'] ?? 0;
    $select = "<div class='col-sm'><select class='custom-select attendes_id' data-toggle='select' required name='attendes[".$rowIndex."][attendes_id]'><option value=''> --Select Attendes-- </option>";
    $event = \App\Models\Event::find($event_id);
    foreach(json_decode($event->collection_slab) as $rd){
      $attendes = \App\Models\Attendes::find($rd->attendes_id);
      $select .="<option value='".$rd->attendes_id."' ".(($rd->attendes_id == $dataArray['attendes_id'])?'Selected' : '')." >".$attendes->name."</option>";
    }
    $select.="</select></div>";
    $dataArray['name'] = $dataArray['name'] ?? '';
    $dataArray['amount'] = $dataArray['amount'] ?? 0;
    $dataArray['number'] = $dataArray['number'] ?? 0;
    return "<div class='row py-1 attendes[$rowIndex] attend_row'>"
    .Form::hidden('attendes['.$rowIndex.'][id]', $dataArray['id'] , ['class' => 'form-control']).
    $select."<div class='col-sm'>".Form::input('number', 'attendes['.$rowIndex.'][amount]', $dataArray['amount'], ['class'=>'form-control amount', 'placeholder'=>'Amount', 'required', 'readonly'],[])."</div>
    <div class='col-sm'>".Form::input('number','attendes['.$rowIndex.'][number]', $dataArray['number'] , ['class' => 'form-control number', 'placeholder'=>'Number of Attendes', 'required'])."</div>
    <div class='col-sm-auto pt-2'><i class='text-danger fa-lg fas fa-trash-alt del_row'></i></div>
    </div>";
});
$multi_data='';
if($mode == 'edit'){
  if(isset($dataArray) && is_array($dataArray) && count($dataArray) > 0){
    foreach($dataArray as $data){
      $multi_data = $multi_data.Form::variantData($data, $form_data->event_id);
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
<script>



function add_attendes_row(id,select) {
  var newRow = '<div class="row py-1 attendes['+ id +'] attend_row">' +
  '<input type="hidden"  name="attendes[' + id + '][id]" class="form-control">' +
  select+
  '<div class="col-sm">' +
  '<input type="number" placeholder="Amount" name="attendes[' + id + '][amount]" value="0"  class="form-control amount" readonly>' +
  '</div>' +
  '<div class="col-sm">' +
  '<input type="number" placeholder="No of Attendes" name="attendes[' + id + '][number]" value="0"  class="form-control number" min="0">' +
  '</div>' +
  '<div class="col-sm-auto pt-2">' +
  '<i class="text-danger fa-lg fas fa-trash-alt del_row"></i>' +
  '</div>' +
  '</div>';
  $('.temp1').append(newRow);
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

function get_total_amount(){
  var total_amount=0;
  $('.attend_row').each(function(){
    var amount = parseInt($(this).find('.amount').val())||0;
    var number = parseInt($(this).find('.number').val())||0;
    total_amount += parseInt(amount)*parseInt(number);
  });
  return total_amount;
}

function get_attendes(no, event_id){
  $.ajax({
    url: '{{ route("supanel.event_transaction.get_attendes") }}',
    method: "POST",
    data: {'_token': '{!! csrf_token() !!}', 'id' : event_id, 'no': no},
    success: function(response){
      // response.forEach(atten=>{
      //   add_attendes_row(atten.id, atten.name, atten.amount);
      // });
      add_attendes_row(no,response);
      $('[name="attendes['+no+'][attendes_id]"]').select2({
        placeholder: '{!! __('admin.text_select') !!}',
        allowClear: true
      });
    },
    error: function(error) {
      console.error('Error fetching folder content:', error);
    }
  });
}

function series_select(org_id){
  $.ajax({
    url: '{{ route("supanel.common.series_select") }}',
    method: "POST",
    data: {'_token': '{!! csrf_token() !!}', 'org_id' : org_id, 'type':'event'},
    success: function(response){
      $('#series_div_id').html(response.html);
      $('#series_id').prop('disabled',false);
    },
    error: function(error) {
      console.error('Error fetching folder content:', error);
    }
  });
}

function autocomplete_trigger(org_id){
  if ($('#member_id').hasClass("select2-hidden-accessible")) {
    $('#member_id').select2('destroy');
  }
  $("#member_id").select2({
    minimumInputLength : '1',
    ajax: {
      url : "{{ route('supanel.common.ajax_with_non_member') }}?org_id="+org_id,
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
  var rowCount =0;
  datetimepicker_date('payment_date');
  datetimepicker_date('reciept_date');
  @if(!in_array(1,$roles))
		$('#member_id').prop('disabled', false);
		$('#series_id').prop('disabled', false);
		autocomplete_trigger({{ $auth_user->organization_id }});
    series_select({{ $auth_user->organization_id }});
	@endif
  $('#event_id').on('change', function(){
    $('.temp1').html('');
    var val = $(this).val();
    if(val !=''){
      get_attendes(rowCount,val);
      rowCount++;
      $('.attend').show();
    } else{
      $('.attend').hide();
    }
  });

  $(document).on('change', '#organization_id', function(){
    val = $(this).val();
    $('#series_id').val('');
    $('#member_id').val('');
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
      $('#series_id').prop('disabled', true);
      $('#member_id').prop('disabled', true);
      if ($('#member_id').hasClass("select2-hidden-accessible")) {
        $('#member_id').select2('destroy');
      }
    }
  });

  $('#member_id').on('change',function(){
    val = $(this).val();
    if(val=='N/A'){
      $('.name').show();
      $('.name').find('label').append('<span class="req"></span>');
      $('#name').prop('required',true);
    } else{
      $('.name').hide();
      $('.name').find('label').html('Name');
      $('#name').prop('required',false);
    }
  });

  $('#series_id').on('change', function(){
    var val = $(this).val();
    if(val !== undefined && val !== null && val !== 0 && val !== ''){
      series_data(val);
    }
  });

  $(document).on('change','.attendes_id',function(){
    var amount = $(this).find('option:selected').data('amount');
    $(this).closest('.row').find('.amount').val(amount);
  });

  $('.new_row').on('click', function() {
    var val=$('#event_id').val();
    var max_len = $('.attendes_id').first().find('option').length;
    var len = $('.attend_row').length;
    // alert(max_len);
    if(len<max_len){
      get_attendes(rowCount, val);
      rowCount++;
    } else{
      @if($mode!='edit')
      alert('There are only '+len+' attendes in this event');
      @endif
    }
    if(len >= max_len-1){
      $(this).hide();
    }
    // add_row(rowCount);
  });

  // Bind the click event to delete a row
  $('.temp1').on('click', '.del_row', function() {
    if ($('.temp1 .row').length > 1) {
      $(this).closest('.row').remove();
      $('.new_row').show();
    }
  });

  $(document).on('input', '.number', function(){
    var total_amount= get_total_amount();
    console.log(total_amount);
    $('.attendes_footer').html('Total Amount :'+total_amount)
  });
  $('.number').trigger('input');

  $('#same_date').on('click',function(){
    var val=$(this).prop('checked');
    val_date = $('#payment_date').val();
    if(val && val_date!=''){
      $('#reciept_date').val(val_date);
    }
  });

  @if($mode=='edit')
    $('#series_id').closest('.form-group').find('label').append(" (S. NO.- {{ $form_data->series_number }})");
    $('.attend').show();
    $('#member_id').trigger('change');
    var max_len = $('.attendes_id').first().find('option').length;
    var len = $('.attend_row').length;
    if(len >= max_len){
      $('.new_row').hide();
    }
  @endif
});
</script>
@endsection

@section('content')
{!! Form::open(['url'=>$action, 'method'=>$method, 'class'=>'needs-validation', 'novalidate']) !!}
  <div class="row">
    <div class="offset-xl-2 col-xl-8 offset-lg-1 col-lg-10 col-md-12">
      <div class="main-card mb-3 card">
        <div class="card-header bg-primary d-flex justify-content-between align-items-center">
          <div>{{ $title_shown }}</div>
          <div>
            <button type="submit" class="mt-1 btn btn-dark btn-sm">{!! __('admin.text_button_submit') !!}</button>
            <a href="{{ route($module['main_route'].'.index') }}" class="mt-1 btn btn-danger btn-sm">{!! __('admin.text_button_back') !!}</a>
          </div>
        </div>

        <div class="card-body"> 
          <div class="row">
            @if(in_array(1,$roles) && $mode !='edit')
            <div class="col-md-6">
              @php 
              $current_field ='organization_id';
              $row_data =[];
              $data_select = \App\Models\Organization::where([['status','>','0'],['delstatus','<','1']])->get();
              foreach($data_select as $ds) $row_data[$ds->id] = $ds->name;
              @endphp
              {!! Form::bsSelect($current_field, __('Organization'), $row_data, $form_data->$current_field ?? '', ['data-toggle'=> 'select','required'], ['vertical'=>true]); !!}
            </div>
            @endif

            <div class="col-md-6">
              @php $current_field='event_id';
              $row_data = [];
              $disable = ($mode=='edit')?'disabled':'required';
              $now = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
              $org_id = $auth_user->organization_id;
              if(!empty($org_id)){
                  $data_select = \App\Models\Event::where([['status','>','0'], ['delstatus','<','1'], ['organization_id', '=', $org_id],['date','>',$now]])->get();
              } else{
                  $data_select = \App\Models\Event::where([['status','>','0'], ['delstatus','<','1'], ['date','>',$now]])->get();
              }
              foreach($data_select as $ds) $row_data[$ds->id] = $ds->name;
              @endphp
              {!! Form::bsSelect($current_field, __('Event'), $row_data, $form_data->$current_field ?? '', ['data-toggle'=> 'select',$disable], ['vertical'=>true]); !!}
            </div>

            <div class="col-md-6">
              @php $current_field = 'series_id';
              $row_data =[];
              $org_id = $auth_user->organization_id;
              if(!empty($org_id)){
                  $data_select = \App\Models\Series::where([['status','>','0'], ['delstatus','<','1'], ['organization_id', '=', $org_id],['type','=','event']])->get();
              } else{
                  $data_select = \App\Models\Series::where([['status','>','0'], ['delstatus','<','1'], ['type','=','event']])->get();
              }
              foreach($data_select as $ds) $row_data[$ds->id] = $ds->name;
              @endphp
              {!! Form::bsSelect($current_field, __('Series'), $row_data, $form_data->$current_field ?? '', ['data-toggle'=> 'select',$disable], ['vertical'=>true]); !!}
            </div>

            <div class="col-md-6">
              @php $current_field = 'member_id';
              $row_data=[];
              $org_id = $auth_user->organization_id;
              if(!empty($org_id)){
                  $data_select = \App\Models\Members::where([['status','>','0'], ['delstatus','<','1'], ['organization_id', '=', $org_id]])->get();
              } else{
                  $data_select = \App\Models\Members::where([['status','>','0'], ['delstatus','<','1']])->get();
              }
              foreach($data_select as $ds) $row_data[$ds->id] = $ds->name;
              @endphp
              <label for="series_id" class="form-control-label">Member<span class="req"></span></label>
              <select class="custom-select" data-toggle="select" required="" id="member_id" name="member_id">
                <option value=''> -- Select Option -- </option>
                <option value="N/A" @if($mode=='edit' && empty($form_data->$current_field)) selected @endif> Non-Member </option>
                @foreach($row_data as $ind=>$rd)
                <option value="{{ $ind }}" @if($mode=='edit' && $form_data->$current_field == $ind) selected @endif>{{ $rd }}</option>
                @endforeach
              </select>
              {{-- {!! Form::bsSelect($current_field, __('Member'), $row_data, $form_data->$current_field ?? '', ['data-toggle'=> 'select','required'], ['vertical'=>true]); !!} --}}
            </div>
                        
            <div class="col-md-6">
              @php $current_field = 'payment_date';
              $val= !empty($form_data->$current_field) ? date('Y-m-d', strtotime($form_data->$current_field)) : '';
              @endphp
              {!! Form::bsInput('text', $current_field, __('Payment Date'), $val ?? '', [ 'required', 'autocomplete'=>'off' ],  ['vertical'=>true]); !!}
            </div>

            <div class="col-md-6">
              @php
              $current_field = 'reciept_date';
              $val= !empty($form_data->$current_field) ? date('Y-m-d', strtotime($form_data->$current_field)) : '';
              $add_perm = ['vertical'=>true];
              $apm = ['addon_check'=>'same_date', 'addon_check_title' => 'Same as Payment Date'];
              $add_perm = array_merge($add_perm, $apm);
              @endphp
              {!! Form::bsInput('text', $current_field, __('Reciept Date'), $val ?? '', ['required', 'autocomplete'=>'off'], $add_perm); !!}
            </div>

            <div class="col-md-6 name" style="display:none;">
              @php $current_field='name'; @endphp
              {!! Form::bsInput('text', $current_field, __('Name'), $form_data->$current_field ?? '', ['autocomplete'=>'off' ],  ['vertical'=>true]); !!}
            </div>

            <div class="col-md-6">
              @php $current_field='mobile_number'; @endphp
              {!! Form::bsInput('text', $current_field, __('Mobile Number'), $form_data->$current_field ?? '', ['autocomplete'=>'off' ],  ['vertical'=>true]); !!}
            </div>

            <div class="col-md-6">
              @php $current_field = 'mode';
              $row_data=['cash'=>'Cash', 'online'=>'Online', 'personal_online'=>'Personal Online'];
              @endphp
              {!! Form::bsSelect($current_field, __('Payment Mode'), $row_data, $form_data->$current_field ?? '', ['data-toggle'=> 'select','required'], ['vertical'=>true]); !!}
            </div>

            <div class="col-md-6">
              @php $current_field = 'remark'; @endphp
              {!! Form::bsTextArea($current_field, __('Remark'), $form_data->$current_field ?? '', [], ['vertical'=>true]); !!}
            </div>

            <div class="card col-md-12 py-2 my-2 attend" style="display:none;">
              <div class="card-header" style=" color: white; background-color: #f5365c;"> Choose Attendes </div>
              <div class="card-body" style="background-color: #fafafa;">                           
                <div class="temp1">
                  {!! $multi_data !!}
                </div> 
              </div>
              <div class="card-footer" style="background-color: #d0cfcf;"><div class="row"><div class="col-md-6 attendes_footer"></div>
                <div class="col-md-6" style="text-align: end;"><i class='fa-lg fas fa-plus-circle new_row' title ="Add row" style="padding: 5px; background-color: black; color: white; cursor: pointer;" aria-hidden='true'></i>
                </div></div>
              </div>
            </div>
          </div>  
        </div>
      </div>
    </div>
  </div>
{!! Form::close() !!}
@endsection