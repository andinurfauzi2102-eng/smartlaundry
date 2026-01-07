<?php
session_start();
include 'koneksi.php';

// =======================
// 1. AUTENTIKASI
// =======================
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$role = $_SESSION['role']; // kasir / customer
$is_kasir = ($role === 'kasir');

// =======================
// 2. KONEKSI DATABASE
// =======================
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}

// =======================
// 3. UPDATE STATUS (KHUSUS KASIR)
// =======================
if ($is_kasir && isset($_POST['update_status'])) {
    $id_pembayaran_post = $_POST['id_pembayaran'];
    $status_bayar_baru = $_POST['status_bayar'];

    $stmt_update = $conn->prepare("UPDATE riwayat_transaksi SET status_bayar=? WHERE id_pembayaran=?");
    $stmt_update->bind_param("ss", $status_bayar_baru, $id_pembayaran_post);
    $stmt_update->execute();
    $stmt_update->close();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// =======================
// 4. AMBIL DATA TRANSAKSI
// =======================
$sql = "SELECT id_pembayaran, nama_pelanggan, tanggal_bayar, total_harga, jumlah_bayar, metode, status_bayar, status_pesanan 
        FROM riwayat_transaksi";

$params = [];
$types = '';

// Pelanggan hanya bisa lihat data mereka sendiri
if (!$is_kasir) {
    $sql .= " WHERE nama_pelanggan=?";
    $params[] = $username;
    $types .= 's';
}

$sql .= " ORDER BY tanggal_bayar DESC, id_pembayaran DESC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Error menyiapkan statement: " . $conn->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

// =======================
// 5. FUNGSI BANTUAN
// =======================
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function getStatusClass($status_text, $type='bayar') {
    $status_text = strtolower($status_text);
    if ($type === 'bayar') {
        return ($status_text === 'lunas') ? 'status-lunas' : 'status-belum-lunas';
    } else {
        return match($status_text) {
            'selesai' => 'status-selesai',
            'proses' => 'status-proses',
            default => 'status-menunggu'
        };
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Riwayat Transaksi - Smart Laundry</title>
<style>
body { font-family: Arial, sans-serif; padding: 20px; background:#f3f7fb; }
.container { max-width:1200px; margin:auto; background:white; padding:20px; border-radius:10px; }
h2 { color:#1f3936; border-bottom:2px solid #3caea3; padding-bottom:10px; margin-bottom:20px; }

table { width:100%; border-collapse: collapse; margin-top:20px; }
th, td { border:1px solid #ddd; padding:8px; text-align:left; }
th { background:#3caea3; color:white; text-transform: uppercase; }
tr:hover { background:#f1f1f1; }

.status-lunas { color: green; font-weight:bold; }
.status-belum-lunas { color: orange; font-weight:bold; }
.status-selesai { color: #17a2b8; font-weight:bold; }
.status-proses, .status-menunggu { color:#6c757d; font-weight:bold; }

select, button { padding:5px 10px; margin-top:5px; }
.btn-back { background:#6c757d; color:white; padding:8px 12px; text-decoration:none; border-radius:5px; display:inline-block; margin-bottom:10px; }
.btn-back:hover { background:#5a6268; }
</style>
</head>
<body>
<div class="container">

<a href="<?= $is_kasir ? 'dashboard_kasir.php' : 'dashboard_pelanggan.php'; ?>" class="btn-back">&larr; Kembali</a>

<h2>Riwayat Transaksi <?= $is_kasir ? 'Seluruh Pelanggan' : $username; ?></h2>

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
<?php if($is_kasir) echo "<th>Aksi</th>"; ?>
</tr>
</thead>
<tbody>
<?php if($result && $result->num_rows > 0): ?>
    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['id_pembayaran']); ?></td>
        <td><?= htmlspecialchars($row['nama_pelanggan']); ?></td>
        <td><?= htmlspecialchars($row['tanggal_bayar']); ?></td>
        <td><?= formatRupiah($row['total_harga']); ?></td>
        <td><?= formatRupiah($row['jumlah_bayar']); ?></td>
        <td><?= htmlspecialchars($row['metode']); ?></td>
        <td class="<?= getStatusClass($row['status_bayar'], 'bayar'); ?>"><?= htmlspecialchars($row['status_bayar']); ?></td>
        <td class="<?= getStatusClass($row['status_pesanan'], 'pesanan'); ?>"><?= htmlspecialchars($row['status_pesanan']); ?></td>
        <?php if($is_kasir): ?>
        <td>
            <form method="post">
                <input type="hidden" name="id_pembayaran" value="<?= htmlspecialchars($row['id_pembayaran']); ?>">
                <select name="status_bayar" required>
                    <option value="Belum Lunas" <?= ($row['status_bayar']=='Belum Lunas')?'selected':''; ?>>Belum Lunas</option>
                    <option value="Lunas" <?= ($row['status_bayar']=='Lunas')?'selected':''; ?>>Lunas</option>
                </select>
                <button type="submit" name="update_status">Update</button>
            </form>
        </td>
        <?php endif; ?>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
<tr><td colspan="<?= $is_kasir ? '9' : '8'; ?>" style="text-align:center; padding:20px;">Belum ada transaksi.</td></tr>
<?php endif; ?>
</tbody>
</table>

</div>
</body>
</html>
