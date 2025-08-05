@php 
$auth_user = Illuminate\Support\Facades\Auth::user();
$roles = $auth_user->roles()->pluck('id')->toArray();;
Form::macro('variantData', function($dataArray)
{
    $rowIndex = $dataArray['attendes_id'] ?? 0 ;
    $dataArray['id']= $dataArray['id'] ?? 0;
    $dataArray['name'] = $dataArray['name'] ?? '';
    $dataArray['amount'] = $dataArray['amount'] ?? 0;
    $dataArray['number'] = $dataArray['number'] ?? '';

    return "<div class='row py-1 attendes[$rowIndex] attend_row'>"
    .Form::hidden('attendes['.$rowIndex.'][id]', $dataArray['id'] , ['class' => 'form-control']).
    "<div class='col-sm'>".Form::text('attendes['.$rowIndex.'][name]', $dataArray['name'] , ['class' => 'form-control name', 'placeholder'=>'Name', 'readonly'])."</div>
    <div class='col-sm'>".Form::input('number', 'attendes['.$rowIndex.'][amount]', $dataArray['amount'], ['class'=>'form-control amount', 'placeholder'=>'Amount', 'required', 'readonly'],[])."</div>
    <div class='col-sm'>".Form::input('number','attendes['.$rowIndex.'][number]', $dataArray['number'] , ['class' => 'form-control number', 'placeholder'=>'Number of Attendes', 'required'])."</div>
    </div>";
});
$multi_data='';
if($mode == 'edit'){
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
<script>
function add_attendes_row(id,name,amount) {
  var newRow = '<div class="row py-1 attendes['+ id +'] attend_row">' +
  '<input type="hidden"  name="attendes[' + id + '][attendes_id]" class="form-control">' +
  '<div class="col-sm">' +
  '<input type="text" placeholder="Name" name="attendes[' + id + '][name]" value="'+name+'" readonly class="form-control name">' +
  '</div>' +
  '<div class="col-sm">' +
  '<input type="number" placeholder="Amount" name="attendes[' + id + '][amount]" value="'+amount+'" readonly class="form-control amount">' +
  '</div>' +
  '<div class="col-sm">' +
  '<input type="number" placeholder="No of Attendes" name="attendes[' + id + '][number]" value=""  class="form-control number" min="0">' +
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

function get_attendes(event_id){
  $.ajax({
    url: '{{ route("supanel.event_transaction.get_attendes") }}',
    method: "POST",
    data: {'_token': '{!! csrf_token() !!}', 'id' : event_id},
    success: function(response){
      response.forEach(atten=>{
        add_attendes_row(atten.id, atten.name, atten.amount);
      });
    },
    error: function(error) {
      console.error('Error fetching folder content:', error);
    }
  });
}

$(function(){
  datetimepicker_date('payment_date');
  $('#event_id').on('change', function(){
    $('.temp1').html('');
    var val = $(this).val();
    if(val !=''){
      get_attendes(val);
      $('.attend').show();
    } else{
      $('.attend').hide();
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
  @if($mode=='edit')
    $('.attend').show();
    $('#member_id').trigger('change');
  @endif

  $(document).on('input', '.number', function(){
    var total_amount= get_total_amount();
    console.log(total_amount);
    $('.attendes_footer').html('Total Amount :'+total_amount)
  });
  $('.number').trigger('input');
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
              $now = \Carbon\Carbon::now()->format('Y-m-d');
              $data_select = \App\Models\Event::where([['status','>','0'],['delstatus','<','1'],['date','>',$now]])->get();
              foreach($data_select as $ds) $row_data[$ds->id] = $ds->name;
              @endphp
              {!! Form::bsSelect($current_field, __('Event'), $row_data, $form_data->$current_field ?? '', ['data-toggle'=> 'select',$disable], ['vertical'=>true]); !!}
            </div>

            <div class="col-md-6">
              @php $current_field = 'series_id';
              $row_data=[];
              $disable = ($mode=='edit')?'disabled':'required';
              $data_select = \App\Models\Series::where([['status','>','0'],['delstatus','<','1'],['type','=','event']])->get();
              foreach($data_select as $ds) $row_data[$ds->id] = $ds->name;
              @endphp
              {!! Form::bsSelect($current_field, __('Series'), $row_data, $form_data->$current_field ?? '', ['data-toggle'=> 'select',$disable], ['vertical'=>true]); !!}
            </div>

            <div class="col-md-6">
              @php $current_field = 'member_id';
              $row_data=[];
              $data_select = \App\Models\Members::where([['status','>','0'],['delstatus','<','1']])->get();
              foreach($data_select as $ds) $row_data[$ds->id] = $ds->name;
              @endphp
              <label for="series_id" class="form-control-label">Member<span class="req"></span></label>
              <select class="custom-select" data-toggle="select" required="" id="member_id" name="member_id">
                <option value=''> -- Select Option -- </option>
                <option value="N/A" @if($mode=='edit' && empty($form_data->$current_field)) selected @endif> N/A </option>
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
              <div class="card-footer attendes_footer" style="color:black; background-color: #d0cfcf;"></div>
            </div>
          </div>  
        </div>
      </div>
    </div>
  </div>
{!! Form::close() !!}
@endsection