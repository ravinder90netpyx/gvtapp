@extends($folder['folder_name'].'.layouts.master')

@section('title') {{ __('admin.text_html_title', ['module_name'=>$folder['module_name'], 'title'=>$title_shown]) }} @endsection

@section('bodyAttr')
@endsection

@section('css')
@endsection

@section('scripts')
<script>
function send_pdf(id){
  if(confirm("Do you really want to send the Message? Please confirm")){
    // document.location.href = "/supanel/journal_entry/"+id+"/send";
    $.ajax({
      url: '{{ route("supanel.tenant.send_msg") }}',
      method: "POST",
      data: {'_token': '{!! csrf_token() !!}', 'id' : id},
      success: function(response){
        if(response != 'fail'){
	        $('.header-body').append('<div class="alert alert-success alert-block"><button type="button" class="close" data-dismiss="alert">×</button><strong>Message Sent Successfully</strong></div>');
        } else{
          $('.header-body').append('<div class="alert alert-info alert-block"><button type="button" class="close" data-dismiss="alert">×</button> <strong>Message can\'t be send</strong></div>');
        }
        setTimeout(function() {
          $(".alert").alert('close');
        }, 3000);
      },
      error: function(error) {
        console.error('Error fetching folder content:', error);
      }
    });
  }
}
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
                            <th>{{ 'Message Send' }}</th>
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

                                        {{-- <a href="{{ route($module['main_route'].'.show', $row_id) }}" title="{{ __('admin.text_show') }}" rel="tab">
                                            <i class="{{ config('custom.icons.info') }}"></i>
                                        </a> --}}
                                        @php
                                            $org_id = $item['organization_id'];
                                            $wht_model = \App\Models\Templates::where([['name','=','tenant_family'],['organization_id','=',$org_id]])->count();
                                        @endphp
                                        @if($wht_model>0)
                                        <a href='#' onclick="send_pdf({{ $row_id }})" title="Send Message on Whatsapp" rel="tab" class="px-1">
                                            <i class="fab fa-lg fa-whatsapp"></i>
                                        </a>
                                        @endif

                                        @can($module['permission_group'].'.edit')
                                        <a href="{{ route($module['main_route'].'.edit', $row_id) }}" title="{{ __('admin.text_edit') }}" rel="tab">
                                            <i class="{{ config('custom.icons.edit') }}"></i>
                                        </a>
                                        @endcan

                                        @can($module['permission_group'].'.delete')
                                            <a href="{{ route($module['main_route'].'.action', ['mode'=>'delete', 'id'=>$row_id]) }}" onclick="return confirm('Are you sure to delete?');" title="{{ __('admin.text_delete') }}">
                                                <i class="{{ config('custom.icons.delete') }}"></i>
                                            </a>
                                        @endcan
                                    </td>       

                                    <td>{{ $item['name'] }}</td>
                                    <td>{{ $item['counter'] }}</td>
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