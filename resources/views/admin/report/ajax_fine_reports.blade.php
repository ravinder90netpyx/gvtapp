
    <thead>
        <tr>
            <th>{{ __('Name') }}</th>
            <th >{{ __('Unit Number') }}</th>
            <th>{{__('Amount')}}</th>
            <th>{{__('Date and Time')}}</th>
            {{-- @foreach($month_arr as $mn)
                <th>{{ date("Y M", strtotime($mn)) }}</th>
            @endforeach --}}
        </tr>
    </thead>

    @if($model_get)
        <tbody>
            @foreach($model_get as $key => $item)
                <tr>
                    @php
                    $member = \App\Models\Members::where('id',$item['member_id'])->first();
                    $fine = \App\Models\Entrywise_Fine::where([['status','>','0'],['delstatus','<','1'],['journal_entry_id','=',$item['id']]])->first()->fine_paid;
                    @endphp

                    <td>{{ $member->name }}</td>
                    <td>{{ $member->unit_number }}</td>
                    <td>{{ $fine }}</td>
                    <td>{{ $item['entry_date'] }}</td>
                </tr>
            @endforeach
        </tbody>
    @endif