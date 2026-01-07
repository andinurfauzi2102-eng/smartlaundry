<?php
session_start();
include 'koneksi.php';

// CEK LOGIN
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$user_role = $_SESSION['role'];
$username  = $_SESSION['username'];
$is_admin_or_kasir = ($user_role === 'admin' || $user_role === 'kasir');

// KONEKSI
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Koneksi gagal: " . $conn->connect_error);

// DATA PELANGGAN (ADMIN/KASIR)
$unique_customers_result = null;
if ($is_admin_or_kasir) {
    $unique_customers_result = $conn->query("
        SELECT nama_pelanggan, COUNT(order_id) total_orders
        FROM pesanan
        GROUP BY nama_pelanggan
        ORDER BY total_orders DESC
    ");
}

// SEMUA PESANAN
$all_orders_result = $conn->query("
    SELECT order_id, nama_pelanggan, tanggal_pesanan, jenis_layanan, berat_kg, total_harga, status_pesanan
    FROM pesanan
    ORDER BY order_id DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>📦 Status Pesanan - Smart Laundry</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>
body{margin:0;font-family:'Poppins',sans-serif;background:#f3f7fb}
.sidebar{width:230px;background:#3caea3;color:#fff;height:100vh;position:fixed;padding-top:30px}
.sidebar h2{margin-left:25px}
.sidebar a{display:block;padding:12px 25px;color:#fff;text-decoration:none}
.sidebar a.active,.sidebar a:hover{background:rgba(255,255,255,.2)}
.main-content{margin-left:240px;padding:30px}
.data-table-container{background:#fff;padding:20px;border-radius:8px;margin-bottom:25px}
table{width:100%;border-collapse:collapse}
th,td{padding:12px;border-bottom:1px solid #ddd}
th{background:#3caea3;color:#fff;text-align:left}
.status-Baru{color:blue;font-weight:700}
.status-Diproses{color:orange;font-weight:700}
.status-Dikirim{color:purple;font-weight:700}
.status-Selesai{color:green;font-weight:700}
</style>
</head>
<body>

<div class="sidebar">
    <h2>Smart Laundry</h2>
    <a href="<?= $is_admin_or_kasir ? 'dashboard_kasir.php' : 'dashboard_pelanggan.php' ?>">🏠 Beranda</a>
    <a href="status_pesanan.php" class="active">📦 Status Pesanan</a>
    <a href="logout.php">🚪 Logout</a>
</div>

<div class="main-content">

<?php if ($is_admin_or_kasir): ?>
<h2>👤 Pelanggan Aktif</h2>
<div class="data-table-container">
<table>
<tr><th>Nama</th><th>Total Pesanan</th></tr>
<?php while($c = $unique_customers_result->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($c['nama_pelanggan']) ?></td>
<td><?= $c['total_orders'] ?></td>
</tr>
<?php endwhile; ?>
</table>
</div>
<?php endif; ?>

<h2>📋 Daftar Pesanan</h2>
<div class="data-table-container">
<?php if ($all_orders_result->num_rows > 0): ?>
<table>
<tr>
<th>ID</th>
<th>Pelanggan</th>
<th>Layanan</th>
<th>Berat</th>
<th>Tanggal</th>
<th>Total</th>
<th>Status</th>
</tr>

<?php while($row = $all_orders_result->fetch_assoc()): ?>
<tr>
<td><?= $row['order_id'] ?></td>
<td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
<td><?= htmlspecialchars($row['jenis_layanan']) ?></td>
<td><?= $row['berat_kg'] ?> Kg</td>
<td><?= date('d M Y', strtotime($row['tanggal_pesanan'])) ?></td>
<td>Rp<?= number_format($row['total_harga'],0,',','.') ?></td>
<td class="status-<?= $row['status_pesanan'] ?>">
    <?= $row['status_pesanan'] ?>
</td>
</tr>
<?php endwhile; ?>

</table>
<?php else: ?>
<p>Belum ada pesanan.</p>
<?php endif; ?>
</div>

</div>

<?php
$conn->close();
?>
</body>
</html>
