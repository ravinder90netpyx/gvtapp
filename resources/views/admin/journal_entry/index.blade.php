@php
$auth_user = \Illuminate\Support\Facades\Auth::user();
$roles = $auth_user->roles()->pluck('id')->toArray();
@endphp
@extends($folder['folder_name'].'.layouts.master')

@section('title') {{ __('admin.text_html_title', ['module_name'=>$folder['module_name'], 'title'=>$title_shown]) }} @endsection

@section('bodyAttr')
@endsection

@section('css')
<!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-ui@1.13.3/themes/base/core.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-ui@1.13.3/themes/base/base.css"> -->
<style type="text/css">
    table td.link-icons a{
        display: inline-block;
        padding: 0 1px;
    }
</style>
@endsection

@section('scripts')
<!-- <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/jquery-ui@1.10.5/keycode.js"></script> -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/bootstrap-autocomplete@2.3.7/dist/latest/bootstrap-autocomplete.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript">

function form_submit(send , id = null){
    var series_id= $('#series_id').val();
    var entry_year= $('#entry_year').val();
    var entry_date= $('#entry_date').val();
    var organization_id = $('#organization_id').val();
    // var back_date= $('#back_date').val();
    // var member_mob= $('#member_mob').val();
    var member_id= $('#member_mob').val();
    var paid = $('#charge').val();
    var from_month = $('#from_month').val();
    var to_month = $('#to_month').val();
    var payment_mode = $('#payment_mode').val();
    var remarks = $('#remarks').val();
    var custom_month = $('#custom_month').val();
    var charge_type_id = $('#charge_type_id').val();
    var reciept_date = $('#reciept_date').val();

    // form_data = {
    //     'series_id' : series_id,
    //     'entry_date' : entry_date,
    //     'entry_year' : entry_year,
    //     'back_date' : back_date,
    //     'member_mob' : member_mob,
    //     'member_id' : member_id
    // };
    if(id === undefined || id === null || id === false || id === 0 || id === '') {
        method = "POST";
        action = "{{ $action }}";
    } else {
        method = "PUT";
        action="{{ $act }}/"+id;
    }
    $.ajax({
        url: action,
        method: method,
        data: {'_token': '{!! csrf_token() !!}', 'series_id' : series_id, 'entry_date' : entry_date, 'entry_year' : entry_year, 'member_id' : member_id, 'organization_id' : organization_id, 'paid_money' : paid, 'from_month' : from_month, 'to_month': to_month, 'payment_mode':payment_mode, 'send' : send, 'remarks' : remarks, 'custom_month':custom_month, 'charge_type_id': charge_type_id, 'reciept_date':reciept_date},
        success: function(response){
            $('#cred_modal').modal('hide');
            location.reload();
        },
        error: function(error) {
            var msg = JSON.parse(error.responseText);
            var err = {};
            err = msg.errors;
            form = document.getElementById('form_id');
            form.classList.remove('was-validated');
            $('#form_id input[type=text], input[type=search], input[type=hidden], input[type=number], input[type=date], input[type=radio], select').addClass('is-valid');
            $('#form_id input[type=text], input[type=search], input[type=hidden], input[type=number], input[type=date], input[type=radio], select').removeClass('is-invalid');

            for (var key in err){
                if(key != "member_id"){
                    $('#'+key).addClass('is-invalid');
                    $('#'+key).closest('div').find('.invalid-feedback').text(err[key]);
                    $('#'+key).removeClass('is-valid');
                } else {
                    $('#member_mob').removeClass('is-valid');
                    $('#member_mob').addClass('is-invalid');
                    $('#member_mob').closest('div').find('.invalid-feedback').text("Choose a valid Member");
                }
            }
            console.error('Error fetching folder content:', error);
        }
    });
}

function ajax_edit(id){
    $.ajax({
        url: '{{ $act }}/'+id+'/edit',
        method: "GET",
        data: {'_token': '{!! csrf_token() !!}', 'id' : id},
        success: function(response){
            $('#changable_div').html(response.html);
            $('#exampleModalLabel').text(response.title_shown);
            // console.log(action, method);
            $('#next_number').closest('.form-group-multi-input').find('label').append(' (S. NO.- '+response.series_title+')');
            $('#series_id').prop('disabled',true);
            $('#organization_id').prop('disabled',true);
            $('#entry_year').prop('disabled',true);
            autocomplete_trigger(response.org_id);
            datetimepicker_month('from_month');
            datetimepicker_month('to_month');
            custom_datetimepicker_month('custom_month');
            reciept_datetimepicker();
        },
        error: function(error) {
            console.error('Error fetching folder content:', error);
        }
    });
}

function ajax_show(id){
    $.ajax({
        url: '{{ $act }}/'+id,
        method: "GET",
        data: {'_token': '{!! csrf_token() !!}', 'id' : id},
        success: function(response){
            $('#changable_div').html(response.html);
            $('#exampleModalLabel').text(response.title_shown);
            // console.log(action, method);
            $('#next_number').closest('.form-group-multi-input').find('label').append(' (S. NO.- '+response.series_title+')');
            $('#series_id').prop('disabled',true);
            $('#member_mob').prop('disabled',true);
        },
        error: function(error) {
            console.error('Error fetching folder content:', error);
        }
    });
}

function series_select(org_id){
    $.ajax({
        url: '{{ route("supanel.journal_entry.series_select") }}',
        method: "POST",
        data: {'_token': '{!! csrf_token() !!}', 'org_id' : org_id},
        success: function(response){
            $('#series_div_id').html(response.html);
            $('#series_id').prop('disabled',false);
        },
        error: function(error) {
            console.error('Error fetching folder content:', error);
        }
    });
}

function series_data(ser_id){
    $.ajax({
        url: '{{ route("supanel.journal_entry.series_data") }}',
        method: "POST",
        data: {'_token': '{!! csrf_token() !!}', 'ser_id' : ser_id},
        success: function(response){
            $('#next_number').val(response.next_num);
            $('#next_number').closest('.form-group-multi-input').find('label').append(' (S. NO.- '+response.serial_no+')');
        },
        error: function(error) {
            console.error('Error fetching folder content:', error);
        }
    });
}

function datetimepicker_month(id){
    date = new Date('2020-01-01');
    $('#'+id).datepicker({
        format: "yyyy-mm",
        startView: 1,
        minViewMode: 1,
        startDate : date,
        container: '#cred_modal',
        autoclose: true
    });
}
function custom_datetimepicker_month(id){
    date = new Date('2020-01-01');
    $('#'+id).datepicker({
        format: "yyyy-mm",
        startView: 1,
        minViewMode: 1,
        multidate : true,
        startDate : date,
        container: '#cred_modal',

        autoclose: true
    });
}

function autocomplete_trigger(org_id){
    if ($('#member_mob').hasClass("select2-hidden-accessible")) {
       $('#member_mob').select2('destroy');
    }
    $("#member_mob").select2({
        dropdownParent: $('#changable_div'),
        minimumInputLength : '1',
        ajax: {
            url : "{{ route('supanel.journal_entry.ajax_member') }}?org_id="+org_id,
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

function download_file(id){
    if(confirm("Are you sure to re-generate PDF?")){
        window.open("/supanel/journal_entry/"+id+"/make?redirect=1");
    } else{
        window.open("/supanel/journal_entry/"+id+"/show");
    }
}

function regenerate_file(id){
    document.location.href = "/supanel/journal_entry/"+id+"/make?redirect_index=1";
}

function send_pdf(id){
    if(confirm("Do you really want to send the receipt? Please confirm")){
        document.location.href = "/supanel/journal_entry/"+id+"/send";
    }
}

function reciept_datetimepicker(){
    $('#reciept_date').datetimepicker({
        useCurrent : false,
        showClose : true,
        format : "YYYY-MM-DD",
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
}

$(function(){
    id = null;

    $("#add_but").on('click',function(e){
        e.preventDefault();
        id = null;

        form = document.getElementById('form_id');
        $('#member_mob').val('');

        form.classList.remove('was-validated');
        $('#changable_div input[type=text], input[type=search], input[type=hidden], input[type=number], input[type=date], input[type=radio], select').val('');
        $('#changable_div input[type=text], input[type=search], input[type=hidden], input[type=number], input[type=date], input[type=radio], select').prop('disabled', false);
        $('#next_number').prop('disabled',true);
        // $('.charge').hide();
        @if($mode =='show' || in_array(1,$roles))
        $('#series_id').prop('disabled', true);
        $('#member_mob').prop('disabled', true);
        @endif
        @if(!in_array(1, $roles))
            autocomplete_trigger('{{ $auth_user->organization_id}}');
            series_select('{{ $auth_user->organization_id}}');
        @endif
        $('#charge').prop('disabled', true);
        @php
            $form_data=[];
            $now=Carbon\Carbon::now();
            $arr=array_reverse($financial_years);
            $val_year = $arr[0];
        @endphp
        $('#entry_date').val("{{ $now }}");
        $('#entry_year').val("{{ $val_year }}");
        $('#cred_modal').modal('show');
        $('#cred_modal').modal({ backdrop:false });
        // $('#form_id').trigger('reset');
        // $('#member_mob').val('');
        $('#form_btn_submit').show();
        $('#exampleModalLabel').text("{{ $title_shown }}");
    });

    datetimepicker_month('from_month');
    datetimepicker_month('to_month');
    custom_datetimepicker_month('custom_month');
    reciept_datetimepicker();

    
    $(document).on("click","#custom_toggle",function() {
        is_check = $(this).prop('checked');
        $('#from_month').val('');
        $('#to_month').val('');
        $('#custom_month').val('');
        if(is_check){
            $('.from_month').hide();
            $('#from_month').prop('required',false);
            $('.to_month').hide();
            $('#to_month').prop('required', false);
            $('.custom_month').show();
            $('#custom_month').prop('required',true);
        } else{
            $('.from_month').show();
            $('#from_month').prop('required',true);
            $('.to_month').show();
            $('#to_month').prop('required',true);
            $('.custom_month').hide();
            $('#custom_month').prop('required',false);
        }
    });

    $('#from_month').on('change',function(){
        date = new Date($(this).val());
        $('#to_month').datepicker('setStartDate' , date);
    });

    $('#to_month').on('change',function(){
        date = new Date($(this).val());
        $('#from_month').datepicker('setEndDate', date);
    });

    // $('#cred_modal').on('hide.bs.modal', function (e) {
    //     document.getElementById('form_id').reset();
    // });

    $('.edit_but').on('click', function(e){
        e.preventDefault();
        id = $(this).data('id');
        $('#cred_modal').modal('show');
        $('#cred_modal').modal({ backdrop:false });
        ajax_edit(id);
    });

    $('.show_but').on('click', function(e){
        e.preventDefault();
        id = $(this).data('id');
        $('#cred_modal').modal('show');
        $('#cred_modal').modal({ backdrop:false });
        ajax_show(id);

        // $('#changable_div input[type=text], input[type=search], input[type=hidden], input[type=number], input[type=date], input[type=radio], select').prop('disabled', true);
        $('#form_btn_submit').hide();
        $('#form_btn_save_submit').hide();
    });

    $(document).on('change', '#organization_id', function(){
        val = $(this).val();
        $('#series_id').val('');
        $('#member_mob').val('');
        $('#next_number').val('');
        $('#charge').val('');
        $('.charge').hide();
        if(val !== undefined && val !== null && val !== 0 && val !== ''){
            $('#series_id').prop('disabled', false);
            $('#member_mob').prop('disabled', false);
            autocomplete_trigger(val);
            series_select(val);
        } else{
            $('#series_id').val('');
            $('#member_mob').val('');
            $('#next_number').val('');
            $('#next_number').closest('.form-group-multi-input').find('label').html('Series<span class="req"></span>');
            $('#series_id').prop('disabled', true);
            $('#member_mob').prop('disabled', true);
            if ($('#member_mob').hasClass("select2-hidden-accessible")) {
                $('#member_mob').select2('destroy');
            }
        }
    });

    $(document).on('click', '#back_date', function(){
        var val=$(this).prop('checked');
        if(val){
            $('#entry_date').datetimepicker({
                useCurrent : true,
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
        } else{
            $('#entry_date').datetimepicker('destroy');
        }
    });

    // $(document).on('click input', '#member_mob', function(){
    //     $('#member_id').val('');
    //     // this.setCustomValidity('Choose a valid member');
    //     org_id = $('#organization_id').val();
    //     console.log(org_id);
    //     // autocomplete_trigger
    //     // $(this).autoComplete({
    //     //     resolverSettings : {
    //     //         url : "{{ route('supanel.journal_entry.ajax_member') }}?org_id="+org_id,
    //     //     },
    //     //     minLength : 1,
    //     //     // appendTo : ".modal-body",
    //     //     select:function ( el, item ) {
    //     //         $('#member_id').val(item);
    //     //         this.setCustomValidity('');
    //     //     }
    //     // });
        
    // });

    $(document).on('change','#member_mob', function(){
        $('#charge').val('');
        check = $('#unpaid').prop('checked');
        
        if(this.validationMessage === ''){
            $('.charge').show();
        } else{
            $('.charge').hide();
        }
    });

    $(document).on('change', '#series_id', function(){
        $('#next_number').val('');
        $('#next_number').closest('.form-group-multi-input').find('label').html('Series<span class="req"></span>');
        val = $(this).val();
        if(val !== undefined && val !== null && val !== 0 && val !== ''){
            series_data(val);
        }
    });

    $(document).on('click', '#unpaid', function(){
        var val = $(this).prop('checked');
        if(val){
            $('#charge').prop('disabled', false);
        } else{
            $('#charge').prop('disabled', true);
            $('#charge').val('');
        }
    });

    $('#entry_year').on('change',function(){
        var val = $(this).val();
        var arr= val.split('-');
        var yr = arr[0];
        var time= yr+"-04-01 00:00:00";
        $('#entry_date').val(time);
    });

    $('#entry_date').on('change', function(){
        val= $(this).val();
        var date = new Date(val);
        check = !isNaN(date.getTime()) && val !== '';
        if(check){
            
        } else{
            val = $('#entry_year').val();
            if(val !='') {
                var arr= val.split('-');
                var time = arr[0]+"-04-01 00:00:00";
            } else{
                @php $now=Carbon\Carbon::now(); @endphp
                var time = "{!! $now->toDateTimeString() !!}";
            }
            $(this).val(time);
        }
    });

    $('#form_btn_submit').on('click', function(e){
        var form_je = document.getElementById('form_id');
        e.preventDefault();
        var send =0;
        if(form_je.checkValidity()=== true){
            form_submit(send,id);
        } else{
            form_je.classList.add('was-validated');
        }
    });

    $('#form_btn_save_submit').on('click', function(e){
        if(confirm("Do you really want to send the receipt? Please confirm")){
            var form_je = document.getElementById('form_id');
            e.preventDefault();
            var send = 1;
            // form_je.reportValidity();
            if(form_je.checkValidity()=== true){
                form_submit(send,id);
            } else{
                form_je.classList.add('was-validated');
            }
        } else{
            e.preventDefault();
        }
    });
});
</script>
@endsection

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <h1>{{ $title_shown }}</h1>
        </div>
    </div>
</div>

<form action="{{ route($module['main_route'].'.bulk') }}" method="POST"> 
    <div class="card card-listing">
        <div class="card-header">   
            <div class="form-inline form-list-actions">
                <div class="row"> 
                    @canany([ $module['permission_group'].'.status', $module['permission_group'].'.delete' ])
                    <div class="col-auto mt-1 mb-1">
                        <div class="form-group">
                            <div class="input-group input-group-sm">
                                <label for="combined_action" class="combined_action_label mt-1 mr-3 d-none d-sm-block">{!! __('admin.text_action') !!}</label>
                                <div class="input-group-prepend">
                                    <div class="input-group-text p-0 pl-2">
                                        <div class="custom-checkbox custom-control">
                                            <input type="checkbox" class="custom-control-input select-all" id="select_all_check" aria-label="Checkbox for selecting all checkboxes">
                                            <label class="custom-control-label" for="select_all_check"></label>
                                        </div>
                                    </div>
                                </div>

                                <select name="combined_action" id="combined_action" class="custom-select">
                                    <option value="">{{ __('admin.text_select') }}</option>
                                    @can($module['permission_group'].'.status')
                                    <option value="activate">{{ __('admin.text_activate') }}</option>
                                    <option value="deactivate">{{ __('admin.text_deactivate') }}</option>
                                    @endcan

                                    @can($module['permission_group'].'.delete')
                                    <option value="delete">{{ __('admin.text_delete') }}</option>
                                    @endcan
                                </select>

                                <span class="input-group-append">
                                    @csrf
                                    @method('HEAD')
                                    <input type="submit" class="btn btn-success" value="{{ __('admin.text_go') }}" name="btn_apply">
                                </span>
                            </div>
                        </div>
                    </div>
                    @endcanany

                    @can($module['permission_group'].'.add')
                    <div class="col-auto mt-1 mb-1">
                        <div class="form-group">
                            <a class="btn btn-primary btn-add btn-sm" id="add_but" rel="tab" href="{{ route($module['main_route'].'.create') }}" title="{{ __('admin.text_add') }}">{{ __('admin.text_add') }}</a>
                        </div>
                    </div>
                    @endcan

                    @include('include.search', [ 'query'=>( $query ?? '' ) ])
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
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered table-sm mb-2 main-listing-table">
                    <thead>
                        <tr>
                            <th style="width:40px">{{ __('admin.text_idcheck') }}</th>
                            <th style="width:180px">{{ __('admin.text_actions') }}</th>
                            <th>{{ __('Member Name') }}</th>
                            <th>{{ __('Unit Number') }}</th>
                            <th>{{ __('Serial Number') }}</th>
                            <th>{{ __('Payment Date') }}</th>
                            <th style="width:150px">{{ __('admin.text_date_created') }}</th>
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

                                    <td class="link-icons">
                                        @can($module['permission_group'].'.status')
                                            @if($item[$model->getStatusColumn()]=='1')
                                            <a href="{{ route($module['main_route'].'.action', ['mode'=>'deactivate', 'id'=>$row_id]) }}" title="{{ __('admin.text_deactivate') }}">
                                                <i class="{{ config('custom.icons.active') }}"></i>
                                            </a>
                                            @elseif($item[$model->getStatusColumn()]=='0')
                                            <a href="{{ route($module['main_route'].'.action', ['mode'=>'activate', 'id'=>$row_id]) }}" title="{{ __('admin.text_activate') }}">
                                                <i class="{{ config('custom.icons.inactive') }}"></i>
                                            </a>
                                            @endif
                                        @endcan

                                        <a href="{{ route($module['main_route'].'.show', $row_id) }}" data-id="{{ $row_id }}" class="show_but" title="{{ __('admin.text_show') }}" rel="tab">
                                            <i class="{{ config('custom.icons.info') }}"></i>
                                        </a>

                                        <a href='' onclick="download_file({{ $row_id }})" title="Download Reciept" rel="tab">
                                            <i class="fa fa-lg fa-download"></i>
                                        </a>

                                        <a href='' onclick="regenerate_file({{ $row_id }})" title="Regenerate File" rel="tab">
                                            <i class="fa fa-lg fa-redo"></i>
                                        </a>

                                        @php
                                            $org_id = $item['organization_id'];
                                            $wht_model = \App\Models\Templates::where([['name','=','reciept'],['organization_id','=',$org_id]])->count();
                                        @endphp

                                        @if($wht_model>0)
                                        <a href='' onclick="send_pdf({{ $row_id }})" title="Send Reciept on Whatsapp" rel="tab">
                                            <i class="fas fa-lg fa-external-link-alt"></i>
                                        </a>
                                        @endif

                                        @can($module['permission_group'].'.edit')
                                        <a href="{{ route($module['main_route'].'.edit', $row_id) }}" data-id="{{ $row_id }}" class="edit_but" title="{{ __('admin.text_edit') }}" rel="tab">
                                            <i class="text-primary fa-lg fas fa-edit"></i>
                                        </a>
                                        @endcan

                                        @can($module['permission_group'].'.delete')
                                            <a href="{{ route($module['main_route'].'.action', ['mode'=>'delete', 'id'=>$row_id]) }}" onclick="return confirm('Are you sure to delete?');" title="{{ __('admin.text_delete') }}">
                                                <i class="text-danger fas fa-lg fa-trash-alt"></i>
                                            </a>
                                        @endcan
                                    </td>
                                    @php $series= \App\Models\Series::find($item['series_id']);
                                        $member = \App\Models\Members::find($item['member_id']);
                                    @endphp
                                    <td>{{ $member->name }}</td>
                                    <td>{{ $member->unit_number }}</td>
                                    <td>{{ $item['series_number'] }}</td>
                                    <td>{{ $item['entry_date'] }}</td>
                                    <td>{{ $row_time }}</td>
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
<div class="modal fade" id="cred_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" data-focus="false" role="dialog">
    <div class="modal-dialog modal-lg">
        {!! Form::open(['id'=>'form_id', 'method'=>$method, 'class'=>'needs-validation', 'novalidate']) !!}
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{ $title_showns }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="overflow:hidden;">
                    <div class="row" id="changable_div">
                        @include($module['main_view'].'.form_include',['bsmodal'=>true, 'module'=>$module, 'form_data'=>null])
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Back</button>
                    <button type="submit" id="form_btn_submit" value="submit" class="btn btn-primary">Save</button>
                    <button type="submit" id="form_btn_save_submit" value = "save_submit" class="btn btn-primary">Save & Send</button>
                </div>
            </div>
        {!! Form::close() !!}
    </div>
</div>
@endsection