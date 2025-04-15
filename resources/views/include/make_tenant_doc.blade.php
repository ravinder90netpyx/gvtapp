<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Entry</title>

    <!-- <link href="/dashboard/css/bootstrap.min.css" rel="stylesheet" /> -->
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous"> -->
    <style>
        h2 {
            text-align: center;
        }
        .entry-section table td{
            height:35px;
        }
    </style>
</head>
<body>

    <div class="doc-detail">
    <h2>{{$org_name ?? 'Golf View Tower'}}</h2>
    <h3> Verification Document </h3>
    @foreach($profile_data as $pd)
    <div class="doc-info1">
        <div class="img-info">
            {{-- <img src="/upload/tenant/' . $pd['photo']"> --}}
            <img src="#">
        </div>
        <ul class="info-list">
            <li>
                <label>Name</label>
                <span>{{ $pd['name'] }}</span>
            </li>
            <li>
                <label>Age</label>
                <span>{{ $pd['age'] }}</span>
            </li>
            <li>
                <label>Gender</label>
                <span>{{ $pd['gender'] }}</span>
            </li>

            <li>
                <label>Address</label>
                <span>{{ $pd['address'] }}</span>
            </li>
        </ul>
    </div>
    @endforeach
    @if(!empty($family_data))
    <div class="doc-info2">
        <h3>Family Member Details</h3>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Age</th>
                        <th>Gender</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($family_data as $fam)
                    <tr>
                        <td>{{ $fam['name'] }}</td>
                        <td>{{ $fam['age'] }}</td>
                        <td>{{ $fam['gender'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
    <div class="doc-info3">
        <ul>
            <li>
                <label>Unit Number</label>
                <span>{{ $unit_number }}</span>
            </li>

            <li>
                <label>Owner Name</label>
                <span>{{ $owner_name }}</span>
            </li>

            <li>
                <label>Start Date</label>
                <span>{{ $start_date }}</span>
            </li>

            <li>
                <label>Rent Agreement</label>
                <span>{{ $rent_agreement }}</span>
            </li>
            <li>
                <label>Police Verification Copy</label>
                <span>{{ $police_verification }}</span>
            </li>
            <li>
                <label>Undertaking Copy</label>
                <span>{{ $undertaking }}</span>
            </li>
            <li>
                <label>Acceptance Copy</label>
                <span>{{ $acceptance }}</span>
            </li>
        </ul>
    </div>
    <div class="doc-info4">
        <div class="agree-txt">
            I hereby accept that all the above document are correct.
        </div>
    </div>
</div>
</body>
</html>