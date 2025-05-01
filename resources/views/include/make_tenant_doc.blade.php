<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Verification Documents</title>

    <!-- <link href="/dashboard/css/bootstrap.min.css" rel="stylesheet" /> -->
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous"> -->
    <style>
        * {
  box-sizing: border-box;
}
.doc-detail {
    width: auto;
    max-width: 100%;
    margin: 0 auto;
    padding: 10px;
    font-size:14px;
    font-family: sans-serif;
}
.doc-detail h4 {
    font-size: 20px;
    text-align: center;
}
.doc-info1 {
    margin-bottom:10px;
}
.doc-detail h5 {
    font-size: 16px;
    text-align: left;
    border-top: 1px solid #ddd;
    padding: 15px 0 0;
    margin: 0 0 12px;
}
.img-info {
    float: right;
    width: 200px;
}
.img-info img {
    width: 200px; height: auto;
    display: block;
}
.doc-detail ul {
    display: inline-block;
    width: calc(100% - 200px);
    vertical-align: top;
    padding: 0;
    list-style: none;
}
.doc-detail ul li {
    display: block;
    padding: 5px 0;
}
.doc-detail li label {
    display: inline-block;
    width: 130px;
}
.doc-info1:after {
    display: table;
    clear: both;
    content: '';
}
.table-responsive {
    overflow-x: auto;
    margin: 15px 0 0;
}
.tables{
    border-collapse: collapse;
    width: 75%;
    /* margin: 20px auto; */
}
.tables td{
      border: 1px solid #ccc;
      padding: 12px 16px;
    }
.tables td:first-child {
      font-weight: bold;
      width: 30%;
      background-color: #f9f9f9;
    }

.tables td:last-child {
      width: 70%;
    }

table {
    border-collapse: collapse;
    display: table;
    width: 100%;
    margin-bottom: 1rem;
    color: #212529;
    vertical-align: middle;
    border: 1px solid #dee2e6;
    text-align: left;
    background: #f9f9f9;
}
table th, table td {
    border: 1px solid #ddd;
    padding: 12px;
    font-size: 14px;
    text-align: left;
}
.doc-detail .doc-info3 li label {
    width: 200px;
}
.agree-txt {
    background: #ddf4dd;
    padding: 10px;
    font-size: 14px;
}
.profile-image {
      width: 150px;
      height: auto;
      border-radius: 4px;
      border: 1px solid #ccc;
    }
    </style>
</head>
<body> 

    <div class="doc-detail">
        <h4>Tenant Registration Information</h4>
        <h4>{{$org_name ?? 'Golf View Tower'}}, GH-9, Sector-91, Mohali, SAS Nagar</h4>
        <h5> Personal Information </h5>
        @foreach($profile_data as $pd)
        <div class="doc-info1">
            {{-- <div class="img-info">
                <img src="{{ public_path('upload/tenant/' . $pd['photo']) }}">
                <!-- <img src="#"> -->
            </div> --}}
            <table class ="">
                <tr>
                    <td><span>Name</span></td>
                    <td><span>{{ $pd['name'] }}</span></td>
                    <td rowspan="5" class="image-cell">
                        <img src="{{ public_path('upload/tenant/' . $pd['photo']) }}" class="profile-image">
                    </td>
                </tr>
                <tr>
                    <td><span>Age</span></td>
                    <td><span>{{ $pd['age'] }}</span></td>
                </tr>
                <tr>
                    <td><span>Gender</span></td>
                    <td>{{ $pd['gender'] }}</span></td>
                </tr>
                <tr>
                    <td>Permanent Address</td>
                    <td>{{ $pd['address'] }}</td>
                </tr>
                <tr>
                    <td>Police Verification</td>
                    <td>{{ $pd['police_verification'] }}</td>
                </tr>
            </table>
        
        {{-- <div class="img-info">
                <img src="{{ public_path('upload/tenant/' . $pd['photo']) }}">
                <!-- <img src="#"> -->
            </div>
            <ul class="info-list">
                <li>
                    <label>Name : </label>
                    <span>{{ $pd['name'] }}</span>
                </li>
                <li>
                    <label>Age : </label>
                    <span>{{ $pd['age'] }}</span>
                </li>
                <li>
                    <label>Gender : </label>
                    <span>{{ $pd['gender'] }}</span>
                </li>

                <li>
                    <label>Address : </label>
                    <span>{{ $pd['address'] }}</span>
                </li>
            </ul> --}}
        </div>
        @endforeach
        @if(!empty($family_data))
        <div class="doc-info2">
            <h5>Family Member Details</h5>
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
            <h5> Tenancy Details </h5>
            <table class="tables">
                <tr>
                    <td>Unit Number</td>
                    <td>{{ $unit_number ?? 0 }}</td>
                </tr>

                <tr>
                    <td>Owner Name</td>
                    <td>{{ $owner_name ?? 'acx' }}</td>
                </tr>

                <tr>
                    <td>Start Date</td>
                    <td>{{ $start_date ?? '00-00-0000' }}</td>
                </tr>

                <tr>
                    <td>Rent Agreement Copy</td>
                    <td>{{ $rent_agreement ?? 'Pending' }}</td>
                </tr>

                <tr>
                    <td>Undertaking Copy</td>
                    <td>{{ $undertaking ?? 'Pending' }}</td>
                </tr>

                <tr>
                    <td>Acceptance Copy</td>
                    <td>{{ $acceptance ?? 'Pending' }}</td>
                </tr>
            </table>
            {{-- <ul>
                <li>
                    <label>Unit Number : </label>
                    <span>{{ $unit_number ?? '0' }}</span>
                </li>

                <li>
                    <label>Owner Name : </label>
                    <span>{{ $owner_name ?? 'abc' }}</span>
                </li>

                <li>
                    <label>Start Date : </label>
                    <span>{{ $start_date ?? '00-00-0000' }}</span>
                </li>

                <li>
                    <label>Rent Agreement : </label>
                    <span>{{ $rent_agreement ?? 'OK' }}</span>
                </li>
                <li>
                    <label>Police Verification Copy : </label>
                    <span>{{ $police_verification ?? 'OK' }}</span>
                </li>
                <li>
                    <label>Undertaking Copy : </label>
                    <span>{{ $undertaking ?? 'Ok' }}</span>
                </li>
                <li>
                    <label>Acceptance Copy : </label>
                    <span>{{ $acceptance ?? 'Ok' }}</span>
                </li>
            </ul> --}}
        </div>
        <div class="doc-info4">
            <div class="agree-txt">
                <p><span>NOTE : </span>This information is based on the data submitted by the respective Tenant/Tenants, hence all the information visible here is true as per submission, kindly verify.</p> 
            </div>
        </div>
    </div>
</body>
</html>