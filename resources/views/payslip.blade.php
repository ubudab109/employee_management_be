<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Payslip Voucher</title>
  <style type="text/css">
    table {
      border-collapse: collapse;
      border-spacing: 0;
    }

    th,
    td {
      border: 1px solid #222;
      padding: 3.5px 4px;
    }

    .top .table-top td {
      font-size: 12px;
      padding-bottom: 40px;
    }

    .top .table-center th {
      font-size: 14px;
    }

    .top .table-center td {
      font-size: 14px;
    }

    .table-above-bottom td {
      border: 1px dotted #222;
      font-size: 10px;
    }

    .table-above-bottom-right td {
      border: 1px solid #222;
      font-size: 14px;
      padding: 3.5px 4px;
    }
  </style>
</head>

<body>

  <div class="container">
    <h2 style="text-align:center;text-decoration:underline">Payslip</h2>
    <table class="table table-top1" border="0" style="width:100%;">
      <tr>
        <td style="text-align:left;border:none;font-weight:600">EMP CODE</td>
        <td style="text-align:left;border:none">: {{$item['nip']}}</td>
        <td style="text-align:left;border:none;font-weight:600">DEPARTEMENT</td>
        <td style="text-align:left;border:none">: {{$item['division_name']}}</td>
      </tr>
      <tr>
        <td style="text-align:left;border:none;font-weight:600">EMP NAME</td>
        <td style="text-align:left;border:none">: {{$item['firstname']}} {{$item['lastname']}}</td>
        <td style="text-align:left;border:none;font-weight:600">GENDER</td>
        <td style="text-align:left;border:none">: {{$item['gender']}}</td>
      </tr>
      <tr>
        <td style="text-align:left;border:none;font-weight:600">MODE OF PAYMENT</td>
        <td style="text-align:left;border:none">: BANK TRANSFER</td>
        <td style="text-align:left;border:none;font-weight:600">DATE</td>
        <td style="text-align:left;border:none">: {{$item['month']}} / {{$item['years']}}</td>
      </tr>
      <tr>
        <td style="text-align:left;border:none;font-weight:600">BANK</td>
        <td style="text-align:left;border:none">: {{$item['bank_name']}}</td>
        <td style="text-align:left;border:none;font-weight:600">PAYSLIP CODE</td>
        <td style="text-align:left;border:none">: {{$item['payslip_code']}}</td>
      </tr>
      <tr>
        <td style="text-align:left;border:none;font-weight:600">BANK A/c No</td>
        <td style="text-align:left;border:none">: {{$item['account_number']}}</td>
        <td style="text-align:left;border:none;font-weight:600">JOB POSITION</td>
        <td style="text-align:left;border:none">: {{$item['job_position']}}</td>
      </tr>
    </table>
    <table class="table table-top2" style="width:100%;margin-top:10px;">
      <tr>
        <th colspan="2" style="text-align:center;font-weight:600;padding:10px">EARNINGS</th>
        <th colspan="2" style="text-align:center;font-weight:600;padding:10px">DEDUCTIONS</th>
      </tr>
      <tr>
        {{-- LOOP INCOME --}}
        <td colspan="2">
          <table style="border:none;width:100%;">
            @foreach ($item['salary_income'] as $income)
              <tr>
                <td style="text-align:left;border:none;width:50%;">{{$income['salary_name']}}</td>
                <td style="text-align:right;border:none;width:30%;">{{rupiah($income['amount'])}}</td>
              </tr>
            @endforeach
          </table>
        </td>
        {{-- LOOP CUTS --}}
        <td colspan="2">
          <table style="border:none;width:100%;">
            @foreach ($item['salary_cuts'] as $cut)
              <tr>
                <td style="text-align:left;border:none;width:50%;">{{$cut['salary_name']}}</td>
                <td style="text-align:right;border:none;width:30%;">{{rupiah(-$cut['amount'])}}</td>
              </tr>
            @endforeach
          </table>
        </td>
      </tr>
      <tr>
        <td colspan="4" style="text-align:left;font-weight:600;padding-top:10px;padding-bottom:10px;border-bottom:none">
          GROSS AMOUNT : {{rupiah($item['gross_amount'])}}</td>
      </tr>
      <tr>
        <td colspan="4" style="text-align:left;font-weight:600;padding-top:10px;padding-bottom:10px;border-bottom:none">
          DEDUCTION AMOUNT : {{rupiah($item['deduction_amount'])}}</td>
      </tr>
      <tr>
        <td colspan="4" style="text-align:left;font-weight:600;padding-top:10px;padding-bottom:10px;border-bottom:none">
          NET AMOUNT : {{rupiah($item['nett_amount'])}}</td>
      </tr>
      <tr>
        <td colspan="4" style="text-align:left;padding-top:10px;padding-bottom:10px;">
          THIS IS A COMPUTER GENERATED PAYSLIP. IT DOES NOT REQUIRE SIGNATURE
          For any queries on salary, please email to issues@companymail.in
        </td>
      </tr>
    </table>
  </div>
</body>

</html>