<?php
// Koneksi ke database
include 'koneksi.php';

// Ambil semua transaksi dari tabel pembayaran
$result = mysqli_query($conn, "SELECT * FROM pembayaran ORDER BY id_pembayaran DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Detail Transaksi - Smart Laundry</title>
    <!-- Redirect otomatis ke dashboard kasir setelah 5 detik -->
    <meta http-equiv="refresh" content="5;url=dashboard_kasir.php">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; margin: 20px; background: #f3f7fb; }
        h2 { color: #3caea3; }
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 4px 12px rgba(0,0,0,0.08); border-radius: 8px; overflow: hidden; }
        th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #f8f9fa; color: #555; font-weight: 600; }
        tr:last-child td { border-bottom: none; }
        .badge { padding: 4px 10px; border-radius: 12px; font-size: 0.85em; font-weight: 600; }
        .badge-lunas { background: #d4edda; color: #155724; }
        .badge-belum { background: #f8d7da; color: #721c24; }
        .badge-proses { background: #ffeeba; color: #856404; }
        .badge-selesai { background: #d4edda; color: #155724; }
        .notice { margin: 20px 0; font-size: 1em; color: #555; }
    </style>
</head>
<body>

<h2>Detail Transaksi</h2>
<p class="notice">Halaman ini akan kembali ke dashboard kasir dalam 5 detik...</p>

<table>
    <thead>
        <tr>
            <th>ID Pembayaran</th>
            <th>Pelanggan</th>
            <th>Tanggal Bayar</th>
            <th>Total Harga</th>
            <th>Jumlah Bayar</th>
            <th>Metode</th>
            <th>Status Bayar</th>
            <th>Status Pesanan</th>
            <th>Kode Bayar</th>
            <th>Rekening Tujuan</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id_pembayaran']); ?></td>
                    <td><?= htmlspecialchars($row['nama_pelanggan']); ?></td>
                    <td><?= htmlspecialchars($row['tanggal_bayar']); ?></td>
                    <td>Rp <?= number_format($row['total_harga'],0,',','.'); ?></td>
                    <td>Rp <?= number_format($row['jumlah_bayar'],0,',','.'); ?></td>
                    <td><?= htmlspecialchars($row['metode']); ?></td>
                    <td>
                        <?php 
                            $badgeBayarClass = ($row['status_bayar'] == 'Lunas') ? 'badge-lunas' : 'badge-belum';
                        ?>
                        <span class="badge <?= $badgeBayarClass ?>"><?= $row['status_bayar']; ?></span>
                    </td>
                    <td>
                        <?php 
                            $badgeStatusClass = ($row['status_pesanan'] == 'Selesai') ? 'badge-selesai' : 'badge-proses';
                        ?>
                        <span class="badge <?= $badgeStatusClass ?>"><?= $row['status_pesanan']; ?></span>
                    </td>
                    <td><?= htmlspecialchars($row['kode_bayar'] ?? '-'); ?></td>
                    <td><?= htmlspecialchars($row['rekening_tujuan'] ?? '-'); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="10" style="text-align:center; padding: 20px; color: #777;">Belum ada transaksi.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
