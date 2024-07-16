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

    <div class="entry-section" style="text-align: center; padding:10px;">
        <h2 class="text-center">{{ $org_name }}</h2>
            <p>Payment Receipt</p>
            <p style="text-align: left;">Thank You for Paying Monthly Maintenance Charges. Here is the payment receipt.</p>
        <div class="table-responsive" style="text-align:center;">
            <table border="1" cellpadding="2" cellspacing="0" width="100%">
                <tbody>
                    <tr>
                        <td style="width:50%">Reciept No.</td>
                        <td style="width:50%">{{ $series }}</td>
                    </tr>
                    <tr>
                        @php
                            $rec_date_arr = explode(' ', $reciept_date);
                            $pay_date_arr = explode(' ', $date);
                        @endphp
                        <td>Receipt Date</td>
                        <td>{{ $rec_date_arr[0] }}</td>
                    </tr>
                    <tr>
                        <td>Payment Date</td>
                        <td>{{ $pay_date_arr[0] }}</td>
                    </tr>
                    <tr>
                        <td>Customer Name</td>
                        <td>{{ $name }}</td>
                    </tr>
                    <tr>
                        <td>Customer Number</td>
                        <td>{{ $mobile_number }}</td>
                    </tr>
                    <tr>
                        <td>Payment Mode</td>
                        <td>{{ $mode }}</td>
                    </tr>
                    <tr>
                        <td>Paid Amount</td>
                        <td>&#8377;{{ $charge }}</td>
                    </tr>
                    @if(!empty($from_month))
                    <tr>@php
                        $single = 0;
                        if($from_month == $to_month) $single =1;
                        @endphp
                        <td>Month(s) Cleared</td>
                        <td>{{ date("M Y", strtotime($from_month)) }} @if($single == 0) - {{ date("M Y", strtotime($to_month)) }} @endif</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <p style="text-align: left;">{!! $note !!}</p>
        <hr>
        <p style="font-size: 15px;"><i>{!! $line1 !!}</i></p>
        <hr>
        <p style="text-align: left;">{!! $address !!}</p>
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