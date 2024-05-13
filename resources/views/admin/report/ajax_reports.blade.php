<table class="table table-striped table-hover table-bordered table-sm mb-2 main-listing-table">
    <thead>
        <tr>
            <th>{{__('#')}}</th>
            <th style="width:120px">{{ __('Name') }}</th>
            @foreach($month_arr as $mn)
                <th>{{ $mn }}</th>
            @endforeach
            <th style="width:120px">{{ __('Created At') }}</th>
            
        </tr>
    </thead>

    @if($form_data->count())
        <tbody>
            @foreach($form_data as $item)
                @php 
                $row_id = $item['id'];
                $dt_str = $carbon->createFromFormat('Y-m-d H:i:s', $item['created_at']);
                $row_time = $dt_str->format(config('custom.datetime_format'));
                @endphp
                <tr>
                    <td>
                        <div class="custom-checkbox custom-control">
                            <input class="custom-control-input data-checkboxes" type="checkbox" name="row_check[]" id="row_check_{{ $row_id }}" value="{{ $row_id }}">
                            <label class="custom-control-label" for="row_check_{{ $row_id }}"></label>
                        </div>                  
                    </td> 
                    <td>{{ $item['member_id'] }}</td>
                    @php 
                    $format = "Y-m";
                         $from = $item['from_month'];
                         $to = $item['to_month']; 
                         $month_arr1 = $helpers->get_financial_month_year($from,$to,$format);
                    @endphp
                        @php 
                        $match = "";
                       foreach($month_arr as $k => $v) {
                            if(in_array($v,$month_arr1)) {
                                    $match = "matched";
                            }else {
                                $match = "not";
                            }
                            @endphp
                                <td>{{ $match }}</td>
                        @php } @endphp
                    
                    <td>{{ $row_time }}</td>
                </tr>
            @endforeach
        </tbody>
    @endif
</table>