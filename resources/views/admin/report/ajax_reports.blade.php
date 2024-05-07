<table class="table table-striped table-hover table-bordered table-sm mb-2 main-listing-table">
    <thead>
        <tr>
            <th>{{__('Name')}}</th>
            <th style="width:120px">{{ __('Mobile_Number') }}</th>
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

                    {{-- <td>
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

                        <a href="{{ route($module['main_route'].'.show', $row_id) }}" title="{{ __('admin.text_show') }}" rel="tab">
                            <i class="{{ config('custom.icons.info') }}"></i>
                        </a>

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
                    </td> --}}

                    <td>{{ $item['name'] }}</td>
                    <td>{{ $row_time }}</td>
                </tr>
            @endforeach
        </tbody>
    @endif
</table>