<!DOCTYPE html>
<html>
<head>
    <title>Laporan Item</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        table, th, td { border: 1px solid black; }
        th, td { padding: 8px; text-align: left; }
    </style>
</head>
<body>
    <h2>Daftar Seluruh Item</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Item</th>
                <th>Harga</th>
                <th>Stok</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{ $item->product_id ?? '-' }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->base_price }}</td>
                <td>{{ $item->stock_unit }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>