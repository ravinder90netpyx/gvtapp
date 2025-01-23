
    <thead>
        <tr>
            <th>{{ __('Name') }}</th>
            <th >{{ __('Unit Number') }}</th>
            <th>{{__('Amount') }}</th>
            <th>{{__('Date and Time') }}</th>
        </tr>
    </thead>

    @if($je_model)
        <tbody>
            @foreach($je_model as  $key => $item)
            @if($item['charge_type_id']!='8')
                <tr>
                    @php
                    $member = \App\Models\Members::where('id',$item['member_id'])->first();
                    @endphp

                    <td>{{ $member->name }}</td>
                    <td>{{ $member->unit_number }}</td>
                    <td>{{ $item['charge'] }}</td>
                    <td>{{ $item['entry_date'] }}</td>
                </tr>
            @endif
            @endforeach
        </tbody>
    @endif