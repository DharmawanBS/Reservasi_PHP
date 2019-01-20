<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Kuitansi Pembayaran</title>

    <style type="text/css">

        ::selection { background-color: #E13300; color: white; }
        ::-moz-selection { background-color: #E13300; color: white; }

        body {
            background-color: #fff;
            margin: 40px;
            font: 13px/20px normal Helvetica, Arial, sans-serif;
            color: #4F5155;
        }

        a {
            color: #003399;
            background-color: transparent;
            font-weight: normal;
        }

        h1 {
            color: #444;
            background-color: transparent;
            border-bottom: 1px solid #D0D0D0;
            font-size: 19px;
            font-weight: normal;
            margin: 0 0 0 0;
            padding: 14px 15px 10px 15px;
        }

        h2 {
            color: #444;
            background-color: transparent;
            font-size: 17px;
            font-weight: normal;
            margin: 0 0 0 0;
            padding: 14px 15px 10px 15px;
        }

        h4 {
            color: #444;
            background-color: transparent;
            font-size: 10px;
            font-weight: normal;
        }

        code {
            font-family: Consolas, Monaco, Courier New, Courier, monospace;
            font-size: 12px;
            background-color: #f9f9f9;
            border: 1px solid #D0D0D0;
            color: #002166;
            display: block;
            margin: 14px 0 14px 0;
            padding: 12px 10px 12px 10px;
        }

        #body {
            margin: 0 15px 0 15px;
        }

        p.footer {
            text-align: right;
            font-size: 15px;
            border-top: 1px solid #D0D0D0;
            line-height: 32px;
            padding: 0 10px 0 10px;
            margin: 20px 0 0 0;
        }

        #container {
            margin: 10px;
            border: 1px solid #D0D0D0;
            box-shadow: 0 0 8px #D0D0D0;
        }

        body {
            background: rgb(204,204,204);
        }
        page[size="A5"] {
            background: white;
            width: 21cm;
            height: 14.8cm;
            display: block;
            margin: 0 auto;
            margin-bottom: 0.5cm;
            box-shadow: 0 0 0.5cm rgba(0,0,0,0.5);
        }
        @media print {
            body, page[size="A5"] {
                margin: 0;
                box-shadow: 0;
            }
        }
    </style>
</head>
<body>

<?php
    for($i=0;$i<2;$i++) {
        $price = 0;
        $date = '________';
        $method = '________';
        $type = '________';
        if ($payment[$i] !== NULL) {
            $price = (is_null($payment[$i]->payment_price) ? 0 : '<b>'.$payment[$i]->payment_price).'</b>';
            $date = (is_null($payment[$i]->payment_date) ? '________' : $payment[$i]->payment_date);
            $method = (is_null($payment[$i]->payment_method) ? '________' : '<b>'.$payment[$i]->payment_method).'</b>';
            $type = (is_null($payment[$i]->payment_type) ? '________' : '<b>'.$payment[$i]->payment_type).'</b>';
        }

        echo '<page size="A5">
              <div id="container">
                <h2 align="right">KWITANSI<br>RECEIPT</h2>
                <h3 align="right">No: '.$code.'/'.($i === 0 ? 'I' : 'II').'/'.date("Y").'</h3>
                Sudah Terima Dari : Bpk/Ibu/Sdr <b>'.$client_name.'</b><br>
                Banyaknya Uang : _____________________________________________________________________________________
                &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp_____________________________________________________________________________________<br>
                Untuk Pembayaran : <b>Bus '.$vehicle_type.' dari tanggal '.date("d M Y",strtotime($start)).' sampai dengan '.date("d M Y",strtotime($end)).'</b><br>
                Membayar via : '.$method.' ('.$type.')<br>
                Pada tanggal : '.date("d M Y",strtotime($date)).'
                <code><h2>Rp '.$price.',-</h2></code>
                <p class="footer">
                    Jakarta, '.date("d M Y",strtotime($date)).'
                    <br>
                    <br>
                    <br>
                    _______________________
                </p>
              </div>
              </page>';
    }
?>

</body>
</html>