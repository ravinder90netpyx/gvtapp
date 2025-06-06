@extends($folder['folder_name'].'.layouts.master')

@section('title') {{ __('admin.text_html_title', ['module_name'=>$folder['module_name'], 'title'=>$title_shown]) }} @endsection

@section('bodyAttr')
@endsection

@section('css')
@endsection

@section('scripts')
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
                            <a class="btn btn-primary btn-add btn-sm" rel="tab" href="#" title="{{ __('admin.text_add') }}">{{ __('admin.text_add') }}</a>
                        </div>
                    </div>
                    @endcan

                    @include('include.search', [ 'query'=>( $query ?? '' ) ])
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <div class="row">
                @php
                $data_arr = [
                    'query='.$query ?? ''
                ];
                @endphp
                <div class="col-auto perpage-wrap">@include('include.perpage', ['perpage'=>$perpage, 'default_perpage'=>$module['default_perpage'],'data_arr' => $data_arr])</div>
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
                            <th>{{ __('Alias Name') }}</th>
                            <th>{{ __('Type') }}</th>
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
                                        <a class="edit_charge_type" title="{{ __('admin.text_edit') }}" attr_id="{{$item['id']}}" rel="tab">
                                            <i class="{{ config('custom.icons.edit') }}"></i>
                                        </a>
                                        @endcan

                                        @can($module['permission_group'].'.delete')
                                            <a href="{{ route($module['main_route'].'.action', ['mode'=>'delete', 'id'=>$row_id]) }}" onclick="return confirm('Are you sure to delete?');" title="{{ __('admin.text_delete') }}">
                                                <i class="{{ config('custom.icons.delete') }}"></i>
                                            </a>
                                        @endcan
                                    </td>       

                                    <td class="name-{{$item['id']}}">{{ $item['name'] }}</td>
                                    <td class="name-{{$item['id']}}-alias">{{ $item['alias_name'] }}</td>
                                    <td class="name-{{$item['id']}}-type">{{ $item['type'] }}</td>
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
<div class="modal fade" id="charge-type-modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" data-focus="false" role="dialog">
    <div class="modal-dialog modal-lg">
        {!! Form::open(['id'=>'form_id','url'=>$action, 'method'=>'post', 'class'=>'needs-validation form_charge_type', 'novalidate']) !!}
        @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{ $title_shown }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="overflow:hidden;">
                    <div class="row" id="changable_div">
                        <div class="col-md-12"  >
                             {!! Form::bsInput('text', 'name', __('Name'), $form_data->name ?? '', [ 'required', 'autocomplete'=>'off','class' => 'form-control charge_type_name' ],  ['vertical'=>true]); !!}
                        </div>

                        <div class="col-md-12">
                            {!! Form::bsInput('text', 'alias_name', __('Alias Name'), $form_data->alias_name ?? '', [ 'required', 'autocomplete'=>'off','class' => 'form-control alias_name' ],  ['vertical'=>true]); !!}
                        </div>

                        <div class="col-md-12">
                            @php 
                            $current_field = 'type';
                            $row_data = ['fine'=>'Fine', 'maintenance'=>'Maintenance', 'others'=>'Other Charge'];
                            
                            @endphp
                            {!! Form::bsSelect($current_field, __('Type'), $row_data, $form_data->$current_field ?? '', ['required', 'class'=>'form-control type'], ['vertical'=>true]); !!}
                        </div>

                    </div>
                </div>
                <input name="_method" type="hidden" class="form-method" value="Post">
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Back</button>
                    <button type="submit" id="form_btn_submit" value="submit" class="btn btn-primary">Save</button>
                    
    </div>
     {!! Form::close() !!}
</div>

<script>
    $(document).on('click', '.btn-add', function(){
        $('#charge-type-modal').modal('show');
        $(".form_charge_type")[0].reset()
        $('.form-method').val('POST')
    })
    $(document).on('click', '.edit_charge_type', function(){
        $(".form_charge_type")[0].reset()
         $('.form-method').val('PUT')
        var attr_id=$(this).attr('attr_id');
        if(attr_id){
            var name=$('.name-'+attr_id).text();
            var alias=$('.name-'+attr_id+'-alias').text();
            var type= $('.name-'+attr_id+'-type').text();
  
            $('.charge_type_name').val(name);
            $('.alias_name').val(alias);
            $('.type').val(type);
        }
        
        
        var form_action='/chargetype/'+attr_id;
       
        var base_url="<?php echo URL::to('/supanel'); ?>";
        $('.form_charge_type').prop('action', base_url+form_action);
        $('#charge-type-modal').modal('show');
        
    })
    </script>
@endsection