@php
    $auth_user = Auth::user();
    $roles = $auth_user->roles()->pluck('id')->toArray();
    $row_data=[];
    $organization_id = $auth_user->organization_id;
    if(empty($organization_id)){
        $data_select=\App\Models\Members::where([['delstatus','<','1'],['status','>','0']])->get();
    } else {
        $data_select=\App\Models\Members::where([['delstatus','<','1'],['status','>','0'], ['organization_id','=', $organization_id]])->get();
    }
@endphp
@extends($folder['folder_name'].'.layouts.master')

@section('title') {{ __('admin.text_html_title', ['module_name'=>$folder['module_name'], 'title'=>$title_shown]) }} @endsection

@section('bodyAttr')
@endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/virtual-select-plugin@1.0.44/dist/virtual-select.min.css
" rel="stylesheet">
<style>
    .submit_bttn {
    padding: 0 !important;
/*    margin: -30px 0 0 0px !important;*/
}
.submit_bttn button#submit_button {
    margin-top: -60px;
}
.from_to-label {
    margin: 6px 6px 0 0 !important;
    width: 18%;
}
.form-feilds {
    width: 100% !important;
}
.form-group.to-form-feild {
    margin-top: 12px !important;
}
</style>
@endsection

@section('scripts')

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/virtual-select-plugin@1.0.44/dist/virtual-select.min.js
"></script>
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

function download_csv_pdf(){
    abc = $("#repo_id").DataTable({
        // "dom": "Bt",
        // "dom":'Bfrtip',
        'dom': '<"top"lfB>rt<"bottom"ip><"clear">',
        'bPaginate': true,
        'paging' : true,
        // 'scrollY' : 'auto',
        'lengthMenu': [1, 5, 10, 30, 50, 100],
        // 'processing' : true,
        // 'iDisplayLength' : 10,
        'pageLength': 10,
        'language': {
            'paginate': {
                'first': '<< ',
                'last': ' >>',
                'next': ' >',
                'previous': '< '
            },
            'lengthMenu': 'Display _MENU_ records per page',
            'info': 'Showing page _PAGE_ of _PAGES_',
            'infoEmpty': 'No records available'
        },
        "buttons": [
            {
                extend: 'csv',
                text: "Export to CSV",
                filename:"Report"
            },
            {
                extend: 'pdf',
                text: 'Export to PDF',
                filename:"Pdf-Report"
                
            }
        ],
    });
}

$(function(){
    download_csv_pdf();
    fromid = 'from_date';
    toid = 'to_date';
    datetimepicker_month(fromid);
    datetimepicker_month(toid);
    
    var options = [
        @foreach($data_select as $ds)
            { label: "{{ $ds->name }}", value: "{{ $ds->id }}", alias: "{{ $ds->unit_number }}" },
        @endforeach
    ];
    VirtualSelect.init({ 
        ele: '#member_id',
        options: options,
        multiple: true,
        search: true,
        noOptionsText: "{{__('No results found')}}"
    });

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
            report_type: 'report_pending',
        };

        // Send an AJAX request
        $.ajax({
            type: 'POST',
            url: '{!! route($module['main_route'].'.report_by_date') !!}',
            data: {'_token': '{!! csrf_token() !!}',formData},
            dataType: 'json',
            success: function(response) {
                //console.log(response);
                
                $("#repo_id").DataTable().clear().destroy();
                $('#repo_id').html(response.html);
                download_csv_pdf();
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

    // $('#select_all_member').on('click', function(){
    //     val = $(this).prop('checked');
    //     if(val){
    //         $(this).closest('.input-group').find('select').select2('destroy').find('option').prop('selected', $(this).prop('checked')).end().select2();
    //         $('[data-toggle="select-multiple"]').select2({
    //             allowClear: true,
    //             closeOnSelect: false,
    //             templateSelection: function(selected, container) {
    //                 if (selected.id !== '') {
    //                     $(container).text(selected.text);
    //                 }
    //                 return container;
    //             }
    //         });
    //     } else{
    //         $(this).closest('.input-group').find('select').select2('destroy').find('option').prop('selected', $(this).prop('checked')).end().select2();
    //     }
    // });
});
</script>
@endsection
<!-- <style type="text/css">
    #member_id+.select2-container{
        width: 300px !important;
    }
</style> -->
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <h1>{{ $title_shown }}</h1>
        </div>
    </div>
</div>

<form id="cf-form" autocomplete="off"> 
    @csrf
    @method('HEAD')
    <div class="card card-listing">
        <div class="card-header">   
            <div class="form-inline form-list-actions">
                <div class="row"> 
                    
                    <div class="col-auto mt-1 mb-1" style="width: 450px;">
                        <div class="form-group">
                            <div class="input-group input-group-sm" style="width:100%">
                                <label for="member_id[]" class="combined_action_label mt-1 mr-3 d-none d-sm-block">{!! __('Member') !!}</label>
                                <!-- <div class="input-group-prepend">
                                    <div class="input-group-text p-0 pl-2">
                                        <div class="custom-checkbox custom-control">
                                            <input type="checkbox" class="custom-control-input" id="select_all_member" aria-label="Checkbox for selecting all checkboxes">
                                            <label class="custom-control-label" for="select_all_member"></label>
                                        </div>
                                    </div>
                                </div> -->
                                
                                <!-- <select name="member_id[]" id="member_id" multiple data-toggle ="select-multiple" class="custom-select">
                                    @foreach($data_select as $ds)
                                        <option value="{!! $ds->id !!}">{!! $ds->name !!}</option>
                                    @endforeach
                                </select> -->
                                <div class="" id="member_id" style="margin-left: 20px;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="from-to-inputs">
                        <div class="col-auto mt-1 mb-1" >
                            <div class="form-group">
                                <div class="input-group input-group-sm form-feilds">
                                    <label for="from_date" class="combined_action_label mt-1 mr-3 d-none d-sm-block from_to-label">{!! __('From') !!}</label>
                                    <input class="form-control" id="from_date" name="from_date" type="text" value="">
                                </div>
                            </div>
                        </div>

                        <div class="col-auto mt-1 mb-1">
                            <div class="form-group to-form-feild">
                                <div class="input-group input-group-sm form-feilds">
                                    <label for="to_date" class="combined_action_label mt-1 mr-3 d-none d-sm-block from_to-label">{!! __('To') !!}</label>
                                    <input class="form-control" id="to_date" name="to_date" type="text" value="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-auto mt-1 mb-1 my-2 submit_bttn">
                <div class="form-group">
                    <button id="submit_button" type="submit" class="btn btn-primary btn-add btn-sm" rel="tab" href="">{{ __('Submit') }}</button>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <div class="row">
                <div class="col-auto perpage-wrap">{{-- @include('include.perpage', ['perpage'=>$perpage, 'default_perpage'=>$module['default_perpage']]) --}}</div>
                <div class="col pagination-wrap">
                    <div class="float-right">
                        <div class="row">
                             <div class="col-auto mt-2"><span class="pagination-info">{{-- {{ __('admin.text_page_info', ['firstItem'=>$data->firstItem(), 'lastItem'=>$data->lastItem(), 'total'=>$data->total()]) }} --}}</span></div> 
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive" id="table-responsive2">
                {{-- @include($module['main_view'].'.ajax_reports',['bsmodal'=>true, 'module'=>$module]) --}}
                <table class="table table-striped table-hover table-bordered table-sm mb-2 main-listing-table" id= "repo_id">
                    <thead>
                        <tr>
                            <th>{{ __('admin.text_name') }}</th>
                            <th>{{ __('Unit Number') }}</th>
                            <th>{{__('Mobile Number') }}</th>
                            <th>{{ __('Total Amount') }}</th>
                            <th>{{ __('Amount Paid') }}</th>
                            <th>{{ __('Pending Amount') }}</th>
                        </tr>
                    </thead>

                    @if($data->count())
                    <tbody>
                        @foreach($data as $item)
                            @php 
                            $row_id = $item[$model->getKeyName()];
                            $dt_str = $carbon->createFromFormat('Y-m-d H:i:s', $item[$model::CREATED_AT]);
                            $row_time = $dt_str->format(config('custom.datetime_format'));
                            $journal_entry = \App\Models\Journal_Entry::where([['member_id', '=', $item['id']],['delstatus', '<', '1'], ['status','>','0']])->count();
                            @endphp
                            @if($journal_entry>0)
                            <tr>
                                <td>{{ $item['name'] }}</td>
                                <td>{{ $item['unit_number'] }}</td>
                                <td>{{ $item['mobile_number'] }}</td>
                                @php 
                                $match = '';
                                $total_charge = '';
                                $pending_money = ''; 
                                $report=\App\Models\Report::where('member_id',$item['id'])->whereIn('month',$month_arr)->get();
                                $charge=\App\Models\Charges::where('id',$item['charges_id'])->first()->rate;
                                ;
                                $mm = [];
                                $money = [];
                                foreach($report as $rp){
                                    $mm[] = $rp['month'];
                                    $money[] = $rp['money_paid'];
                                }

                                $total_money = array_sum($money);

                            
                                $total_charge = count($month_arr) * $charge;
                                $pending_money = $total_charge - $total_money;
                                @endphp
                                
                                <td>&#8377;{{ $total_charge }}</td>
                                <td>&#8377;{{ $total_money }}</td>
                                <td>&#8377;{{ $pending_money }}</td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                    @endif
                </table>
            </div>

            <div class="row">
                <div class="col pagination-wrap">
                    <div class="float-right">
                        <div class="row">
                            <div class="col">{{-- {!! $data->appends(compact('perpage', 'query'))->links() !!} --}}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection