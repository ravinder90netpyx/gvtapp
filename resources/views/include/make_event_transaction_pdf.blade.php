<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Event Transaction</title>

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
    <div class="entry-section" style="text-align: center; padding:10px;">
        <h2 class="text-center">{{ $organization_name }}</h2>
        <p class="text-center">{!! $address !!}</p>
            <p>Event Transaction Details</p>
            <p style="text-align: left;">Here is the details of Event Transaction for event - {{ $event_name }}.</p>
        <div class="table-responsive" style="text-align:center;">
            <table border="1" cellpadding="2" cellspacing="0" width="100%">
                <tbody>
                    <tr>
                        <td style="width:50%">Reciept No.</td>
                        <td style="width:50%">{{ $reciept_number }}</td>
                    </tr>
                    <tr>
                        @php 
                        $payment = explode(' ', $payment_date);
                        @endphp
                        <td>Payment Date</td>
                        <td>{{ $payment[0] }}</td>
                    </tr>
                    <tr>
                        <td>Unit Number</td>
                        <td>{{ $unit_number }}</td>
                    </tr>
                    <tr>
                        <td>Name</td>
                        <td>{{ $name }}</td>
                    </tr>
                    <tr>
                        <td>Mobile Number</td>
                        <td>{{ $number }}</td>
                    </tr>
                    @foreach($relation_pdf as $rp)
                    <tr>
                        <td>{{ $rp['name'] }}</td>
                        <td>&#8377;{{ $rp['money'] }} ( {{ $rp['number'] }} Person )</td>
                    </tr>
                    @endforeach
                    <tr>
                        <td><b>Total</b></td>
                        <td><b>&#8377;{{ $total }}</b></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p style="">!! Thank You for your Payment !!</p>
        <hr>
        <p style="font-size: 15px;"><i>{!! $line1 !!}</i></p>
        <hr>
        
    </div>
 
    {{-- <div class="row">
        <div class="col">
            <div class="margin-top">
                <p> Dear {{ $name }},</p>
                <p>Your entry has been successfully done. Here are some details :</p>
                <table class="mb-0 table table-bordered">
                    <tbody>
                        <tr>
                            <td scope="row" class="text-left" style="width: 150px;"> Name </td>
                            <td>{{ $name }}</td>
                        </tr>

                        <tr>
                            <td scope="row" class="text-left" style="width: 150px;"> Contact Number </td>
                            <td>{{ $mobile_number }}</td>
                        </tr>

                        <tr>
                            <td scope="row" class="text-left" style="width: 150px;"> Charge </td>
                            <td>&#8377;{{ $charge }}</td>
                        </tr>

                        <tr>
                            <td scope="row" class="text-left" style="width: 150px;">From Date</td>
                            <td>{{ $from_month }}</td>
                        </tr>

                        <tr>
                            <td scope="row" class="text-left" style="width: 150px;">To Date</td>
                            <td>{{ $to_month }}</td>
                        </tr>

                        <tr>
                            <td scope="row" class="text-left" style="width: 150px;">Mode of Transaction</td>
                            <td>{{ $mode }}</td>
                        </tr>

                        <tr>
                            <td scope="row" class="text-left" style="width: 150px;">Serial Number</td>
                            <td>{{ $series }}</td>
                        </tr>

                        <tr>
                            <td scope="row" class="text-left" style="width: 150px;">Date & Time</td>
                            <td>{{ $date }}</td>
                        </tr>

                        <tr>
                            <td scope="row" class="text-left" style="width: 150px;">Financial Year</td>
                            <td>{{ $year }}</td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>
    </div> --}}
</body>
</html>