<table class="table table-striped table-hover table-bordered table-sm mb-2 main-listing-table">
    <thead>
        <tr>
            <th>{{ __('S.No') }}</th>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Unit Number') }}</th>
            <th>{{ __('Month') }}</th>
            <th>{{__('Mode') }}</th>
            <th>{{ __('Amount') }}</th>
            <th>{{ __('Advance') }}</th>
            <th>{{ __('Reciept Number') }}</th>
            <th>{{ __('Reciept Date') }}</th>
            <th>{{ __('Payment Date') }}</th>
        </tr>
    </thead>


    @if($members)
        @php
        $count =1;
        @endphp
        <tbody>
            @foreach($members as $key => $item)
                @foreach($month_arr as $mt)
                    @php
                    $row_id = $key;
                    
                    @endphp
                    <tr>
                        <td>{{ $count }}</td>
                        <td>{{ $item['name'] }}</td>
                        <td>{{ $item['unit_number'] }}</td>
                        <td>{{ $mt }}</td>

                        @php 
                        $count++;
                        $match = '';
                        $total_charge = '';
                        $pending_money = ''; 
                        $report=\App\Models\Report::where([['member_id','=',$item['id']],['month','=',$mt]])->first();
                        $reciept_date = $payment_date = $mode = $reciept_no = 'N/A';
                        $charge = 0;
                        $advance = 'N/A';
                        if(!empty($report)){
                            $journal_entry_id = $report->journal_entry_id;
                            $charge = $report->money_paid;
                            $jrl_model = \App\Models\Journal_Entry::find($journal_entry_id);
                            if(!empty($jrl_model)){
                                $mode = $jrl_model->payment_mode ?? 'N/A';
                                $payment_date = $jrl_model->entry_date ?? 'N/A';
                                $reciept_date = $jrl_model->reciept_date ?? '-';
                                $reciept_no = $jrl_model->series_number ?? '-';
                                $advance = ($jrl_model->charge != $charge) ? $jrl_model->charge : '-';
                                if(!empty($payment_date) && $payment_date != 'N/A'){
                                    $payment_arr = explode(' ', $payment_date);
                                    $payment_date = $payment_arr[0];
                                }
                                if(!empty($reciept_date) && $reciept_date != '-'){
                                    $reciept_date = explode(' ', $reciept_date);
                                    $reciept_date = $reciept_date[0];
                                }
                            }
                        }
                        @endphp

                        <td>{{ $mode }}</td>
                        <td>&#8377;{{ $charge }}</td>
                        @if($advance == 'N/A' || $advance == '-')
                        <td>{{ $advance }}</td>
                        @else
                        <td>&#8377;{{ $advance }}</td>
                        @endif
                        <td>{{ $reciept_no }}</td>
                        <td>{{ $payment_date }}</td>
                        <td>{{ $reciept_date }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    @endif
</table>