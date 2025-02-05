
    <thead>
        <tr>
            <th>{{ __('User') }}</th>
            <th>{{ __('Name') }}</th>
            <th >{{ __('Remarks') }}</th>
            <th>{{__('Date') }}</th>
            <th>{{__('Image') }}</th>
        </tr>
    </thead>

    @if($model_get)
        <tbody>
            @foreach($model_get as  $key => $item)
                <tr>
                    @php
                    $user = \App\Models\User::where([['status','>','0'],['id','=',$item['user_id']]])->first();
                    @endphp

                    <td>{{ $user->first_name.' '.$user->last_name }}</td>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['remarks'] }}</td>
                    <td>{{ $item['date'] }}</td>
                    <td>@if(!empty($item['image'])) <a href="{{ asset('upload/expense/'.$item['image']) }}" target="_blank">View Image</a> @else No Image Found @endif</td>
                </tr>
            @endforeach
        </tbody>
    @endif