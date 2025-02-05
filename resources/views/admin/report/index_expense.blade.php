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
<link href="https://cdn.jsdelivr.net/npm/virtual-select-plugin@1.0.44/dist/virtual-select.min.css" rel="stylesheet">
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
<script src="https://cdn.jsdelivr.net/npm/virtual-select-plugin@1.0.44/dist/virtual-select.min.js"></script>
<script type="text/javascript">

function datetimepicker_month(id){
    $('#'+id).datepicker({
        format: "yyyy-mm",
        startView: 1,
        numberOfMonths: 1,
        minViewMode: 1,
        startDate: '2021-01',
    });
}

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
    var date = new Date(to_date);
    $('#to_date').datepicker('setEndDate' , date);
    $('#to_date').datepicker('setStartDate' , from_date);

    // datetimepicker_month2('to_date',from_date,to_date);

}); 

function download_csv_pdf(){
    abc = $("#repo_id").DataTable({
        'dom': '<"top"lfB>rt<"bottom"ip><"clear">',
        'bPaginate': true,
        'paging' : true,
        // 'scrollY' : 'auto',
        'lengthMenu': [1, 5, 15, 30, 50, 100],
        // 'processing' : true,
        // 'iDisplayLength' : 10,
        'pageLength': 15,
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
    // abc.columns.adjust();
}

$(function(){
    download_csv_pdf();
    fromid = 'from_date';
    toid = 'to_date';
    datetimepicker_month(fromid);
    datetimepicker_month(toid);

    /** Order By Date Start **/
    $('#cf-form').submit(function(e) {
        e.preventDefault();
        var formData = {
            from_date: $("#from_date").val(),
            to_date: $("#to_date").val(),
            report_type: 'expense',
        };

        // Send an AJAX request
        $.ajax({
            type: 'POST',
            url: '{!! route($module['main_route'].'.ajax_data') !!}',
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
                // console.error(error);
                console.error(xhr.responseText);
            }
        });
    });
    /** Order By Date End **/


    $('#from_date').show();
    $('#to_date').show();

});
</script>
@endsection
<!-- <style type="text/css">
    #member_id+.select2-container{
        width: 300px !important;
    }
     .select2-search--inline {
    display: block!important;
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
                    <div class="col-auto mt-1 mb-1">
                        <div class="form-group">
                            <div class="input-group input-group-sm">
                                <label for="member_id[]" class="combined_action_label mt-1 mr-3 d-none d-sm-block">{!! __('From') !!}</label>
                                <input class="form-control" id="from_date" name="from_date" type="text" value="">
                            </div>
                        </div>
                    </div>

                    <div class="col-auto mt-1 mb-1">
                        <div class="form-group">
                            <div class="input-group input-group-sm">
                                <label for="member_id[]" class="combined_action_label mt-1 mr-3 d-none d-sm-block">{!! __('To') !!}</label>
                                <input class="form-control" id="to_date" name="to_date" type="text" value="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-auto mt-1 mb-1 my-2">
                <div class="form-group">
                    <button id="submit_button" type="submit" class="btn btn-primary btn-add btn-sm" rel="tab" href="">{{ __('Submit') }}</button>
                </div>
            </div>
        </div>

        
        <div class="card-body">
            <div class="row" style="display: block;">
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
                            <th>{{ __('User') }}</th>
                            <th>{{ __('admin.text_name') }}</th>
                            <th>{{ __('Remarks') }}</th>
                            <th>{{__('Date') }}</th>
                            <th>{{__('Image') }}</th>
                        </tr>
                    </thead>
                    @if($data->count())
                        <tbody>
                            @foreach($data as $item)
                            @php 
                            $user = \App\Models\User::where([['status','>','0'], ['id','=',$item['user_id']]])->first();
                            $image = !empty($item['image'])? 'View Image':'';
                            @endphp
                               
                            <tr>
                                <td>{{ $user->first_name.' '.$user->last_name}}</td>
                                <td>{{ $item['name'] }}</td>
                                <td>{{ $item['remarks'] }}</td>
                                <td>{!! $item['date'] !!}</td>                            
                                <td>@if(!empty($item['image'])) <a href="{!! asset('upload/expense/' . $item['image']) !!}" target="_blank"> View Image</a> @else No Image Found @endif</td>
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
                            <div class="col">{{-- {!! $data->appends(compact('perpage', 'query'))->links() !!} --}}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection