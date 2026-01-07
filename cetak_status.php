<?php
session_start();
include 'koneksi.php';

$data = null;

/* ================== CEK STATUS PESANAN ================== */
if (isset($_POST['cek'])) {
    $order_id = $_POST['order_id'];

    $stmt = $conn->prepare("SELECT * FROM pesanan WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

/* ================== REDIRECT SESUAI LOGIN ================== */
$redirect_dashboard = 'login.php'; // default

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'kasir') {
        $redirect_dashboard = 'dashboard_kasir.php';
    } elseif ($_SESSION['role'] === 'customer' || $_SESSION['role'] === 'pelanggan') {
        $redirect_dashboard = 'dashboard_pelanggan.php';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>SmartLaundry | Cek Status Pesanan</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

<?php if ($data) { ?>
<!-- AUTO REDIRECT 5 DETIK -->
<meta http-equiv="refresh" content="5;url=<?= $redirect_dashboard; ?>">
<?php } ?>

<style>
body{
    margin:0;
    padding:0;
    font-family:'Poppins',sans-serif;
    background:linear-gradient(135deg,#3caea3,#1f3936);
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
}
.container{
    background:#fff;
    width:420px;
    padding:30px;
    border-radius:15px;
    box-shadow:0 10px 30px rgba(0,0,0,0.15);
}
.logo{
    text-align:center;
    font-size:28px;
    font-weight:700;
    color:#3caea3;
}
.subtitle{
    text-align:center;
    font-size:14px;
    color:#777;
    margin-bottom:25px;
}
form{
    display:flex;
    flex-direction:column;
    gap:12px;
}
input[type=number]{
    padding:12px;
    border-radius:8px;
    border:1px solid #ccc;
}
button{
    padding:12px;
    border:none;
    border-radius:8px;
    background:#3caea3;
    color:#fff;
    font-size:15px;
    font-weight:600;
    cursor:pointer;
}
button:hover{ background:#2b9b92; }
.result{
    margin-top:25px;
    padding:20px;
    border-radius:10px;
    background:#f3f7fb;
}
.status-selesai{
    color:#28a745;
    font-weight:700;
}
.status-proses{
    color:#f7a01d;
    font-weight:700;
}
.footer{
    margin-top:20px;
    text-align:center;
    font-size:12px;
    color:#aaa;
}
</style>
</head>

<body>

<div class="container">
    <div class="logo">🧺 SmartLaundry</div>
    <div class="subtitle">Cek Status Pesanan Laundry Anda</div>

    <form method="post">
        <input type="number" name="order_id" placeholder="Masukkan ID Pesanan" required>
        <button type="submit" name="cek">🔍 Cek Status</button>
    </form>

    <?php if ($data) { ?>
    <div class="result">
        <p><b>ID Pesanan:</b> <?= $data['order_id']; ?></p>
        <p><b>Nama Pelanggan:</b> <?= $data['nama_pelanggan']; ?></p>
        <p><b>Layanan:</b> <?= $data['jenis_layanan']; ?></p>
        <p><b>Status Pesanan:</b>
            <?php if ($data['status_pesanan'] === 'Selesai') { ?>
                <span class="status-selesai">SELESAI ✅</span>
            <?php } else { ?>
                <span class="status-proses"><?= $data['status_pesanan']; ?> ⏳</span>
            <?php } ?>
        </p>
        <p style="margin-top:15px;font-size:13px;color:#555;">
            Anda akan kembali ke dashboard dalam <b>5 detik</b>...
        </p>
    </div>
    <?php } ?>

    <div class="footer">
        © <?= date('Y'); ?> SmartLaundry
    </div>
</div>

</body>
</html>
