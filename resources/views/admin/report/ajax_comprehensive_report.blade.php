
    <thead>
        <tr>
            <th>{{ __('Name') }}</th>
            <th >{{ __('Unit Number') }}</th>
            <th >{{ __('SR/Reciept Number') }}</th>
            <th >{{ __('Type') }}</th>
            <th>{{__('Amount') }}</th>
            <th>{{__('Payment Date') }}</th>
        </tr>
    </thead>

    @if($data)
        <tbody>
        @foreach($data as  $key => $item)
            <tr>
                @php
                $member = \App\Models\Members::where('id',$item['member_id'])->first();
                $type = \App\Models\ChargeType::where([['id','=',$item['charge_type_id']]])->first();
                @endphp

                <td>{{ $member->name }}</td>
                <td>{{ $member->unit_number }}</td>
                <td>{{ $item['series_number'] }}</td>
                <td>{{ $type->name }}</td>
                <td>{{ $item['charge'] }}</td>
                <td>{{ $item['entry_date'] }}</td>
            </tr>
        @endforeach
        </tbody>
    @endif