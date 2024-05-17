<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Entry</title>
    <style>
        h2 {
            text-align: center;
        }
    </style>
</head>
<body>
    <h2 class="text-center">Journal Entry</h2>
 
    <div class="row">
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
    </div>
    <br>
    <br>
    <div class="footer margin-top">
        <div>Thank you</div>
        <div>Journal Entry Team</div>
    </div>
</body>
</html>