<table class="table table-striped table-hover table-bordered table-sm mb-2 main-listing-table">
    <thead>
        <tr>
            <th>{{__('#')}}</th>
            <th style="width:120px">{{ __('Name') }}</th>
            <th style="width:120px">{{ __('Mobile Number') }}</th>
            @foreach($month_arr as $mn)
                <th>{{ $mn }}</th>
            @endforeach
            <th style="width:120px">{{ __('Created At') }}</th>
            
        </tr>
    </thead>

    @if($new_arr)
        <tbody>
            @foreach($new_arr as $key => $item)
                @php 
                $row_id = $key;
                $member=\App\Models\Members::where('id',$key)->first();
                @endphp
                <tr>
                    <td>
                        <div class="custom-checkbox custom-control">
                            <input class="custom-control-input data-checkboxes" type="checkbox" name="row_check[]" id="row_check_{{ $row_id }}" value="{{ $row_id }}">
                            <label class="custom-control-label" for="row_check_{{ $row_id }}"></label>
                        </div>                  
                    </td> 
                    
                    @php 
                    $member_name=$member->name; 
                    $member_number=$member->mobile_number;
                    $charges_id =$member->charges_id; 
                    @endphp

                    <td>{{ $member_name }}</td>
                    <td>{{ $member_number }}</td>
                    
                    @php $charge=\App\Models\Charges::where('id',$charges_id)->first()->rate; @endphp

                    @php 
                    $format = "Y-m";
                    
                    
                    @endphp
                        @php 
                        $match = "";
                        $new_charge = "";
                        echo "<pre>";
                        
                       foreach($month_arr as $k => $v) {
                            if(in_array($v,$item)) {
                                if($v == $month_arr[$k]){
                                    $match = "charge";
                                }else {
                                    $match = "N/A";
                                }

                            }else {
                                $match = "NA";
                            }
                            @endphp
                                <td>{{ $match }}</td>
                        @php } @endphp
                    
                    <td>{{ $member_name }}</td>
                </tr>
            @endforeach
        </tbody>
    @endif
</table>