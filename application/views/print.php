<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Print Out Booking <?php echo $booking; ?></php></title>

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
            border-bottom: 4px solid #D0D0D0;
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
        page[size="A4"] {
            background: white;
            width: 21cm;
            height: 29.7cm;
            display: block;
            margin: 0 auto;
            margin-bottom: 0.5cm;
            box-shadow: 0 0 0.5cm rgba(0,0,0,0.5);
        }
        @media print {
            body, page[size="A4"] {
                margin: 0;
                box-shadow: 0;
            }
        }
    </style>
</head>
<body>

<page size="A4">
<div id="container">
    <center>
    <h1>PRINT OUT TIKET PEMESANAN KENDARAAN</h1>
    </center>
    <h4 align="right">PT Dharmawan Merdeka<br>Jln. Mawar no 23, Kecamatan Delod Peken<br>Kabupaten Tabanan, Bali 82113</h4>

    <div id="body">
    <b><h2>Data Reservasi</h2></b>
        <code>
            Kode Booking : <?php echo $booking; ?><br>
            Pemesan : <?php echo $name; ?><br>
            HP : <?php echo $phone; ?><br>
            Tujuan : <?php echo $destination; ?><br>
            Lokasi Jemput : <?php echo $pick_up_location; ?><br>
            Dari tanggal : <?php echo date("d M Y",strtotime($start)); ?><br>
            Sampai tanggal : <?php echo date("d M Y",strtotime($end)); ?><br>
            Catatan : <?php echo $notes; ?>
        </code>
    <b><h2>Data Kendaraan</h2></b>
        <code>
            Tipe : <?php echo $type; ?><br>
            No Polisi : <?php echo $number; ?>
        </code>
    <b><h2>Data Biaya</h2></b>
        <code>
            Biaya per Hari : Rp <?php echo $price; ?>,-<br>
            <?php
                $datetime1 = date_create($start);
                $datetime2 = date_create($end);

                $interval = date_diff($datetime1, $datetime2);

                $duration = $interval->format("%a")+1;
            ?>
            Total Hari : <?php echo $duration; ?> Hari<br>
            Total Biaya : Rp <?php echo $price*$duration; ?>,-
        </code>
    </div>
    <p class="footer">
        Tabanan, <?php echo date("d M Y",strtotime($created)); ?>
        <br>
        <br>
        (Dharmawan)
    </p>
</div>
</page>

</body>
</html>