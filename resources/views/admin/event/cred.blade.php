@php
$auth_user = \Illuminate\Support\Facades\Auth::user();
$roles = $auth_user->roles()->pluck('id')->toArray();
Form::macro('variantData1', function($dataArray,$count){
    $rowIndex =  $count;
    $dataArray['attendes_id'] = $dataArray['attendes_id'] ?? '';
    $dataArray['amount'] = $dataArray['amount'] ?? '';
    $data_select = \App\Models\Attendes::where([['status','>','0'],['delstatus','<','1']])->get();
    $row_data = [];
    foreach($data_select as $ds) $row_data[$ds->id] = $ds->name;
    return "<div class='row py-1 collection_slab[$rowIndex]'>
    <div class='col-sm'>".Form::select('collection_slab['.$rowIndex.'][attendes_id]', $row_data, $dataArray['attendes_id'],['class'=> 'custom-select attendes', 'placeholder'=>' --Select Attendes-- ', 'required'] )."</div>
    <div class='col-sm'>".Form::number('collection_slab['.$rowIndex.'][amount]', $dataArray['amount'] , ['class' => 'form-control label', 'placeholder' => "Amount"])."</div>
        <div class='col-sm-auto pt-2'>
            <i class='text-danger fa-lg fas fa-trash-alt del_row'></i>
        </div>
    </div>";
});
$multi_data='';
$count=0;
if($mode=='insert'){
    $multi_data=Form::variantData1([], 0);
}
else{
    if(isset($dataArray) && is_array($dataArray) && count($dataArray) > 0){
        foreach($dataArray as $data){
            $multi_data = $multi_data.Form::variantData1($data,$count);
            $count++;
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
function add_row(rowCount) {
    @php 
    $org_id = $auth_user->organization_id;
    if(empty($org_id)){
        $data_select = \App\Models\Attendes::where([['status','>','0'],['delstatus','<','1']])->get();
    } else{
        $data_select = \App\Models\Attendes::where([['status','>','0'],['delstatus','<','1'],['organization_id','=',$org_id]])->get();
    }
    @endphp
    var attemptOption = '<option value="" selected="selected"> --Select Attendes-- </option> @foreach($data_select as $ds) <option value="{{ $ds->id }}">{{ $ds->name }}</option> @endforeach';
    // rowCount ++;
    var newRow = '<div class="row py-1 collection_slab['+ rowCount +']">' +
        '<div class="col-sm"><select class="custom-select" required name="collection_slab['+ rowCount +'][attendes_id]">'+ attemptOption+ '</select></div>'+
        '<div class="col-sm">' +
        '<input type="number" name="collection_slab[' + rowCount + '][amount]" placeholder = "Amount" class="form-control label">' +
        '</div>' +
        '<div class="col-sm-auto pt-2">' +
        '<i class="text-danger fa-lg fas fa-trash-alt del_row"></i>' +
        '</div>' +
        '</div>';
    $('.temp1').append(newRow);
    // rowCount++; // Increment the row count for the next row
}

$(function(){
    var rowCount = $('.temp1 .row').length;
    $('.new_row').on('click', function() {
        add_row(rowCount);
        rowCount++;
    });

    // Bind the click event to delete a row
    $('.temp1').on('click', '.del_row', function() {
        if ($('.temp1 .row').length > 1) {
            $(this).closest('.row').remove();
        }
    });

    date = new Date('2020-01-01');
    $('#date').datetimepicker({
    useCurrent : false,
    showClose : true,
    format : "YYYY-MM-DD HH:mm:ss",
    icons:{
      time : "fa fa-clock",
      date : "fa fa-calendar-day",
      up : "fa fa-chevron-up",
      down : "fa fa-chevron-down",
      previous : 'fa fa-chevron-left',
      next :'fa fa-chevron-right',
      today :'fa fa-screenshot',
	    clear : 'fa fa-trash',
      close : 'fa fa-remove'
    }
  });
});
</script>
@endsection

@section('content')
<div class="app-page-title row">
    <div class="col page-title-wrapper">
        <div class="page-title-heading">
            <h1>{{ $title_shown }}</h1>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="main-card mb-3 card">
            <div class="card-body">
                {!! Form::open(['url'=>$action, 'method'=>$method, 'class'=>'needs-validation', 'novalidate']) !!}

                    @if(in_array(1,$roles) && $mode!='edit')
                    @php 
                    $current_field = 'organization_id';
                    $row_data=[];
                    $data_select=\App\Models\Organization::select('id','name')->get();
                    foreach($data_select as $ds) $row_data[$ds->id]=$ds->name;
                    @endphp
                    {!! Form::bsSelect($current_field, __('Organization'), $row_data, $form_data->$current_field ?? '', ['data-toggle'=> 'select', 'required'], []); !!}
                    @endif

                    @php $current_field = 'name'; @endphp
                    {!! Form::bsText($current_field, __('Name'), $form_data->$current_field ?? '', ['required']); !!}

                    @php $current_field = 'date'; @endphp
                    {!! Form::bsInput('text', $current_field, __('Event Date'), $form_data->$current_field ?? '', [ 'required', 'autocomplete'=>'off' ],  []); !!}

                    {{-- @if($mode != 'edit')
                    @php $current_field = 'series_id';
                    $row_data=[];
                    $data_select = \App\Models\Series::where([['status','>','0'],['delstatus','<','1'],['type','=','event']])->get();
                    foreach($data_select as $ds) $row_data[$ds->id] = $ds->name;
                    @endphp
                    {!! Form::bsSelect($current_field, __('Series'), $row_data, $form_data->$current_field ?? '', ['data-toggle'=> 'select', 'required'], []); !!}
                    @endif

                    @php $current_field = 'amount'; @endphp
                    {!! Form::bsInput('number', $current_field, __('Amount'), $form_data->$current_field ?? '',['required'], []); !!} --}}

                    <div class="card">
                        <div class="card-header" style=" color: white; background-color: #f5365c;"> Collection Slab </div>
                        <div class="card-body" style="background-color: #fafafa;">                           
                            <div class="temp1">
                                {!! $multi_data !!}
                            </div> 
                        </div>
                        <div class="card-footer" style="background-color: #d0cfcf;">
                           <div class="col-md-12" style="text-align: end;"><i class='fa-lg fas fa-plus-circle new_row' title ="Add row" style="padding: 5px; background-color: black; color: white; cursor: pointer;" aria-hidden='true'></i>
                           </div>
                        </div>
                    </div>

                    @php $current_field = 'description'; @endphp
                    {!! Form::bsTextArea($current_field, __('Remarks'), $form_data->$current_field ?? '', ['']); !!}

                    <button type="submit" class="mt-1 btn btn-primary">{!! __('admin.text_button_submit') !!}</button>
                    <a href="{{ route($module['main_route'].'.index') }}" class="mt-1 btn btn-danger">{!! __('admin.text_button_back') !!}</a>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection