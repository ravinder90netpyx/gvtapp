<table class="table table-striped table-hover table-bordered table-sm mb-2 main-listing-table">
    <thead>
        <tr>
            
            <th style="width:120px">{{ __('Name') }}</th>
            <th style="width:120px">{{ __('Mobile Number') }}</th>
            <th style="width:120px">{{ __('Total Amount') }}</th>
            <th style="width:120px">{{ __('Total Paid') }}</th>
            <th style="width:120px">{{ __('Pending Amount') }}</th>

        </tr>
    </thead>


    @if($members)
        <tbody>
            @foreach($members as $key => $item)
                @php 
                $row_id = $key;
               
                @endphp
                <tr>
                    <td>{{ $item['name'] }}</td>
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
            @endforeach
        </tbody>
    @endif
</table>