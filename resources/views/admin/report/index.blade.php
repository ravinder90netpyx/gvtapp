@php
    $auth_user = Auth::user();
    $roles = $auth_user->roles()->pluck('id')->toArray();
@endphp
@extends($folder['folder_name'].'.layouts.master')

@section('title') {{ __('admin.text_html_title', ['module_name'=>$folder['module_name'], 'title'=>$title_shown]) }} @endsection

@section('bodyAttr')
@endsection

@section('css')
@endsection

@section('scripts')

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript">


/*function datetimepicker_month(id){
    $('#'+id).datetimepicker({
        showClear : true,
        viewMode : 'years',
        format : 'YYYY-MM',
        icons:{
            date : "fa fa-calendar-day",
            previous : 'fa fa-chevron-left',
            next :'fa fa-chevron-right',
            clear : 'fa fa-trash',
            close : 'fa fa-remove'
        }
    });
}*/

function datetimepicker_month(id){
    $('#'+id).datepicker({
        format: "yyyy-mm",
        startView: 1,
        numberOfMonths: 1,
        minViewMode: 1,
        startDate: '2021-01',
    });
}
// function datetimepicker_month2(id,from_date,to_date){
//     console.log("in func=> "+to_date)
//     console.log("from func=> "+from_date)
//     var value = $("#to_date").val();
//     if(value){
//         $('#to_date').datepicker('update', '');
//         $('#to_date').val('');
//     }

//     $('#'+id).datepicker({
//         format: "yyyy-mm",
//         startView: 1,
//         numberOfMonths: 1,
//         minViewMode: 1,
//         startDate: from_date,
//         endDate: to_date,
//         // container: '.modal-body',
//         autoclose: true,

//     });
// }


$("#from_date").change(function(){
    var from_date = $(this).val();
    var value = $("#to_date").val();

    var myDate = new Date(from_date);

    var month = myDate.getMonth();
    if(month > 0){
        var newdt =  myDate.setFullYear(myDate.getFullYear() + 1);
    } else{
        var newdt = myDate.setFullYear(myDate.getFullYear());
        month = 12;
    }
    if(month>9){
        var to_date = myDate.getFullYear()+'-'+month; 
    } else{
        var to_date = myDate.getFullYear()+'-0'+month;
    }
    // console.log(to_date);
    var date = new Date(to_date);
    $('#to_date').datepicker('setEndDate' , date);
    $('#to_date').datepicker('setStartDate' , from_date);

    // datetimepicker_month2('to_date',from_date,to_date);

}); 


$(function(){
    fromid = 'from_date';
    toid = 'to_date';
    datetimepicker_month(fromid);
    datetimepicker_month(toid);
    
    $('#member_id').on('change',function(){
        // console.log($(this).val().length);
        if($(this).val().length>9){
            alert('This many members can cause load on system');
        }
    });

    /** Order By Date Start **/
    $('#cf-form').submit(function(e) {
        e.preventDefault();
       var formData = {
            from_date: $("#from_date").val(),
            to_date: $("#to_date").val(),
            memberIds: $("#member_id").val(),
        };

        // Send an AJAX request
        $.ajax({
            type: 'POST',
            url: '{!! route($module['main_route'].'.report_by_date') !!}',
            data: {'_token': '{!! csrf_token() !!}',formData},
            dataType: 'json',
            success: function(response) {
                //console.log(response);
                $('#table-responsive2').html(response.html);
            },
            error: function(xhr, status, error) {
                // Handle errors if needed
                console.error(xhr.responseText);
            }
        });
    });
    /** Order By Date End **/


    $('#from_date').show();
    $('#to_date').show();

    $('#select_all_member').on('click', function(){
        val = $(this).prop('checked');
        if(val){
            $(this).closest('.input-group').find('select').select2('destroy').find('option').prop('selected', $(this).prop('checked')).end().select2();
            $('[data-toggle="select-multiple"]').select2({
                allowClear: true,
                closeOnSelect: false,
                templateSelection: function(selected, container) {
                    if (selected.id !== '') {
                        $(container).text(selected.text);
                    }
                    return container;
                }
            });
        } else{
            $(this).closest('.input-group').find('select').select2('destroy').find('option').prop('selected', $(this).prop('checked')).end().select2();
        }
    });
});
</script>
@endsection
<style type="text/css">
    #member_id+.select2-container{
        width: 300px !important;
    }
</style>
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <h1>{{ $title_shown }}</h1>
        </div>
    </div>
</div>

<form id="cf-form"> 
    @csrf
    @method('HEAD')
    <div class="card card-listing">
        <div class="card-header">   
            <div class="form-inline form-list-actions">
                <div class="row"> 
                    
                    <div class="col-auto mt-1 mb-1" style="width: 450px;">
                        <div class="form-group">
                            <div class="input-group input-group-sm">
                                <label for="member_id[]" class="combined_action_label mt-1 mr-3 d-none d-sm-block">{!! __('Member') !!}</label>
                                <div class="input-group-prepend">
                                    <div class="input-group-text p-0 pl-2">
                                        <div class="custom-checkbox custom-control">
                                            <input type="checkbox" class="custom-control-input" id="select_all_member" aria-label="Checkbox for selecting all checkboxes">
                                            <label class="custom-control-label" for="select_all_member"></label>
                                        </div>
                                    </div>
                                </div>
                                @php 
                                    $row_data=[];
                                    $organization_id = $auth_user->organization_id;
                                    if(empty($organization_id)){
                                        $data_select=\App\Models\Members::where([['delstatus','<','1'],['status','>','0']])->get();
                                    } else {
                                        $data_select=\App\Models\Members::where([['delstatus','<','1'],['status','>','0'], ['organization_id','=', $organization_id]])->get();
                                    }
                                @endphp
                                
                                <select name="member_id[]" id="member_id" multiple data-toggle ="select-multiple" class="custom-select">
                                    @foreach($data_select as $ds)
                                        <option value="{!! $ds->id !!}">{!! $ds->name !!}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-auto mt-1 mb-1" >
                        <div class="form-group">
                            <div class="input-group input-group-sm">
                                <label for="from_date" class="combined_action_label mt-1 mr-3 d-none d-sm-block">{!! __('From') !!}</label>
                                <input class="form-control" id="from_date" name="from_date" type="text" value="">
                            </div>
                        </div>
                    </div>

                    <div class="col-auto mt-1 mb-1">
                        <div class="form-group">
                            <div class="input-group input-group-sm">
                                <label for="to_date" class="combined_action_label mt-1 mr-3 d-none d-sm-block">{!! __('To') !!}</label>
                                <input class="form-control" id="to_date" name="to_date" type="text" value="">
                            </div>
                        </div>
                    </div>

                    <div class="col-auto mt-1 mb-1">
                        <div class="form-group">
                            <button id="submit_button" type="submit" class="btn btn-primary btn-add btn-sm" rel="tab" href="">{{ __('Submit') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <div class="row">
                <div class="col-auto perpage-wrap">@include('include.perpage', ['perpage'=>$perpage, 'default_perpage'=>$module['default_perpage']])</div>
                <div class="col pagination-wrap">
                    <div class="float-right">
                        <div class="row">
                            <div class="col-auto mt-2"><span class="pagination-info">{{ __('admin.text_page_info', ['firstItem'=>$data->firstItem(), 'lastItem'=>$data->lastItem(), 'total'=>$data->total()]) }}</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive" id="table-responsive2">
                {{-- @include($module['main_view'].'.ajax_reports',['bsmodal'=>true, 'module'=>$module]) --}}
                <table class="table table-striped table-hover table-bordered table-sm mb-2 main-listing-table">
                    <thead>
                        <tr>
                            <th style="width:40px">{{ __('admin.text_idcheck') }}</th>
                            <th>{{ __('admin.text_name') }}</th>
                            <th>{{__('Mobile Number') }}</th>
                            @foreach($month_arr as $mn)
                                <th>{{ $mn }}</th>
                            @endforeach
                        </tr>
                    </thead>

                    @if($data->count())
                        <tbody>
                            @foreach($data as $item)
                                @php 
                                $row_id = $item[$model->getKeyName()];
                                $dt_str = $carbon->createFromFormat('Y-m-d H:i:s', $item[$model::CREATED_AT]);
                                $row_time = $dt_str->format(config('custom.datetime_format'));
                                @endphp
                                <tr>
                                    <td>
                                        <div class="custom-checkbox custom-control">
                                            <input class="custom-control-input data-checkboxes" type="checkbox" name="row_check[]" id="row_check_{{ $row_id }}" value="{{ $row_id }}">
                                            <label class="custom-control-label" for="row_check_{{ $row_id }}"></label>
                                        </div>
                                    </td> 

                                    <td>{{ $item['name'] }}</td>
                                    <td>{{ $item['mobile_number'] }}</td>
                                    @php
                                    $report=\App\Models\Report::where('member_id',$item['id'])->get();
                                    $match = 'N/A';
                                    $mm = [];
                                    $money = []; 
                                    foreach($report as $rp){
                                        $mm[] = $rp['month'];
                                        $money[] = $rp['money_paid'];
                                    }

                                    foreach($month_arr as $k => $v) {
                                        foreach($report as $ke => $rpt){
                                            if(in_array($v,$mm)) {
                                                if($rpt['month'] == $v){
                                                    $match = $rpt['money_paid'];
                                                }
                                            }else {
                                                $match = "N/A";
                                            }
                                    }
                            @endphp
                                <td>{{ $match }}</td>
                        @php
                        $match = 'N/A'; } @endphp
                                    
                                </tr>
                            @endforeach
                        </tbody>
                    @endif
                </table>
            </div>

            <div class="row">
                <div class="col pagination-wrap">
                    <div class="float-right">
                        <div class="row">
                            <div class="col"> {!! $data->appends(compact('perpage', 'query'))->links() !!}</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</form>
@endsection