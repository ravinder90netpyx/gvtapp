@php
    $current_url = Request::url();
    $grp = $group ?? '';
@endphp
@extends($folder['folder_name'].'.layouts.master')

@section('title') {{ __('admin.text_html_title', ['module_name'=>$folder['module_name'], 'title'=>$title_shown]) }} @endsection

@section('bodyAttr')
@endsection

@section('css')
@endsection

@section('scripts')
<script type="text/javascript">
    function send_reminder(id){
        if(confirm("Are you sure to send reminder to Whatsapp? Please Confirm")){
            document.location.href = "/supanel/members/"+id+"/reminder";
            // window.open("/supanel/members/"+id+"/reminder");
        }
    }
    $(function(){
        $('#grp_but').on('click',function(e){
            e.preventDefault();
            grp = $('#group').val();
            document.location.href = "{!! $current_url.'?group=' !!}"+grp;
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
                                    @can($module['permission_group'].'.reminder')
                                    <option value="reminder">{{ __('Send Reminder') }}</option>
                                    @endcan
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

                    <div class="col-auto mt-1 mb-1">
                        <div class="form-group">
                            <div class="input-group input-group-sm">
                                <label for="group" class=" mt-1 mr-3 d-none d-sm-block">{!! __('Group') !!}</label>

                                @php
                                    $group_mod = \App\Models\Group::where([['delstatus', '<', '1'], ['status','>','0']])->get();
                                @endphp
                                <select name="group" id="group" class="custom-select">
                                    <option value="">{{ __('admin.text_select') }}</option>
                                    @foreach($group_mod as $gr)
                                        <option value="{!! $gr->id !!}" @if($gr->id == $grp) selected @endif>{!! $gr->name !!}</option>
                                    @endforeach
                                </select>

                                <span class="input-group-append">
                                    <input type="submit" class="btn btn-success" value="{{ __('admin.text_go') }}" id ="grp_but" name="btn_apply">
                                </span>
                            </div>
                        </div>
                    </div>

                    @can($module['permission_group'].'.add')
                    <div class="col-auto mt-1 mb-1">
                        <div class="form-group">
                            <a class="btn btn-primary btn-add btn-sm" rel="tab" href="{{ route($module['main_route'].'.create') }}" title="{{ __('admin.text_add') }}">{{ __('admin.text_add') }}</a>
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
                            <th style="width:120px">{{ __('admin.text_actions') }}</th>
                            <th>{{ __('admin.text_name') }}</th>
                            <th>{{ __('Unit Number') }}</th>
                            <th>{{__('Mobile Number') }}</th>
                            <th>{{__('Group') }}</th>
                            <th>{{ __('Charge') }}</th>
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

                                    <td>
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

                                        @can($module['permission_group'].'.edit')
                                        <a href="{{ route($module['main_route'].'.edit', $row_id) }}" title="{{ __('admin.text_edit') }}" rel="tab">
                                            <i class="{{ config('custom.icons.edit') }}"></i>
                                        </a>
                                        @endcan

                                        @php
                                            $org_id = $item['organization_id'];
                                            $wht_model = \App\Models\Templates::where([['name','=','reminder'],['organization_id','=',$org_id]])->count();
                                            $now = \Carbon\Carbon::now();
                                            $curr_month = $now->format('Y-m');
                                            $je_model = \App\Models\Report::where([['member_id', '=',$row_id],['month','=',$curr_month],['status','>','0'],['delstatus','<', '1']])->orderBy('id','DESC')->first();
                                        @endphp

                                        @if($wht_model>0)
                                            <a @if(empty($je_model)) href='' @endif onclick="send_reminder({{ $row_id }})" title="Send Reminder on Whatsapp" rel="tab">
                                                <i class="fas fa-bell"></i>
                                            </a>
                                        @endif

                                        @can($module['permission_group'].'.delete')
                                            <a href="{{ route($module['main_route'].'.action', ['mode'=>'delete', 'id'=>$row_id]) }}" onclick="return confirm('Are you sure to delete?');" title="{{ __('admin.text_delete') }}">
                                                <i class="{{ config('custom.icons.delete') }}"></i>
                                            </a>
                                        @endcan
                                    </td>       

                                    @php $charge=\App\Models\Charges::where('id',$item['charges_id'])->first()->name;
                                    $grp ='';
                                    if(!empty($item['group_id'])){
                                        $grp = \App\Models\Group::where('id', $item['group_id'])->first()->name;
                                    }
                                    @endphp
                                    <td>{{ $item['name'] }}</td>
                                    <td>{{ $item['unit_number'] }}</td>
                                    <td>{{ $item['mobile_number'] }}</td>
                                    <td>{{ $grp }}</td>
                                    <td>{{ $charge }}</td>
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
@endsection