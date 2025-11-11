<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemesanan #{{ $booking->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1 {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
    <h1>Bukti Pemesanan Lapangan</h1>
    <table>
        <tr>
            <th>ID Pemesanan:</th>
            <td>{{ $booking->id }}</td>
        </tr>
        <tr>
            <th>Dipesan Tanggal:</th>
            <td>{{ \Carbon\Carbon::parse($booking->created_at)->format('d M Y h:i:s') }}</td>
        </tr>
        <tr>
            <th>Tanggal Pemesanan:</th>
            <td>{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}</td>
        </tr>
        <tr>
            <th>Jam:</th>
            <td>{{\Carbon\Carbon::parse($booking->start_time)->format('h:i')}} - {{\Carbon\Carbon::parse($booking->end_time)->format('h:i')}}</td>
        </tr>
        <tr>
            <th>Lapangan:</th>
            <td>{{ $booking->field->name }}</td>
        </tr>
        <tr>
            <th>Jenis Pemesanan:</th>
            <td>{{ ucfirst($booking->booking_type) }}</td>
        </tr>
        <tr>
            <th>Status Pembayaran:</th>
            <td>
                {{$booking->payment_status}}
            </td>
        </tr>
        <tr>
            <th>Total Harga:</th>
            <td>Rp {{ number_format($booking->amount_paid, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Jumlah Bayar:</th>
            <td>Rp {{ number_format($booking->dp_amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Sisa Bayar:</th>
            <td>Rp {{ number_format($booking->remaining_amount, 0, ',', '.') }}</td>
        </tr>
    </table>
</body>
</html>