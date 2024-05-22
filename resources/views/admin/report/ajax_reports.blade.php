<table class="table table-striped table-hover table-bordered table-sm mb-2 main-listing-table">
    <thead>
        <tr>
            <th>{{__('#')}}</th>
            <th style="width:120px">{{ __('Name') }}</th>
            <th style="width:120px">{{ __('Mobile Number') }}</th>
            @foreach($month_arr as $mn)
                <th>{{ $mn }}</th>
            @endforeach
           
            
        </tr>
    </thead>
     @php 
                

                    

                    @endphp

    @if($members)
        <tbody>
            @foreach($members as $key => $item)
                @php 
                $row_id = $key;
                
                $entries=\App\Models\Journal_Entry::where('member_id',$key)->get();
                $paid = [];
                foreach($entries as $ent){
                    array_push($paid,$ent['charge']);
                }

                @endphp
                <tr>
                    <td>
                        <div class="custom-checkbox custom-control">
                            <input class="custom-control-input data-checkboxes" type="checkbox" name="row_check[]" id="row_check_{{ $row_id }}" value="{{ $row_id }}">
                            <label class="custom-control-label" for="row_check_{{ $row_id }}"></label>
                        </div>                  
                    </td> 
                    
                    @php 
                    
                   
                    @endphp

                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['mobile_number'] }}</td>
                        @php 
                        $match = "";
                        $report=\App\Models\Report::where('member_id',$item['id'])->get();
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
                        @php } @endphp
                </tr>
            @endforeach
        </tbody>
    @endif
</table>