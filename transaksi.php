<?php
include 'koneksi.php';

/* ================= CEK ORDER ================= */
if(!isset($_GET['order_id'])){
    die("Order ID tidak ditemukan");
}
$order_id = $_GET['order_id'];

$q = mysqli_query($conn,"SELECT * FROM pesanan WHERE order_id='$order_id'");
$p = mysqli_fetch_assoc($q);

if(!$p){
    die("Pesanan tidak ditemukan");
}

/* ================= GENERATE ID ================= */
$id_pembayaran = "PAY-" . date("YmdHis");

/* ================= METODE ================= */
$metode = $_POST['metode'] ?? '';

/* ================= SIMPAN ================= */
if(isset($_POST['simpan'])){
    $tanggal = $_POST['tanggal_bayar'];
    $jumlah  = $_POST['jumlah_bayar'];

    $rekening = ($metode == 'Transfer') ? $_POST['rekening_tujuan'] : NULL;

    $status_bayar   = ($jumlah >= $p['total_harga']) ? 'Lunas' : 'Belum Lunas';
    $status_pesanan = ($status_bayar == 'Lunas') ? 'Selesai' : 'Proses';

    $sql = "INSERT INTO pembayaran
        (id_pembayaran, order_id, nama_pelanggan, tanggal_bayar, total_harga,
         jumlah_bayar, metode, rekening_tujuan, status_bayar, status_pesanan)
        VALUES
        ('$id_pembayaran','$order_id','".$p['nama_pelanggan']."',
         '$tanggal','".$p['total_harga']."','$jumlah',
         '$metode','$rekening','$status_bayar','$status_pesanan')";

    if(mysqli_query($conn,$sql)){
        mysqli_query($conn,"UPDATE pesanan 
            SET status_pesanan='$status_pesanan' 
            WHERE order_id='$order_id'");

        header("Location: detail_transaksi.php?id_pembayaran=$id_pembayaran");
        exit;
    }else{
        echo mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Transaksi Pembayaran</title>
<style>
/* ===== FONT & RESET ===== */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f0f2f5;
    margin: 0;
    padding: 0;
}

h3 {
    text-align: center;
    color: #333;
    margin-top: 30px;
}

/* ===== FORM CARD ===== */
.form-container {
    background: #fff;
    max-width: 450px;
    margin: 30px auto;
    padding: 30px 40px;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    border-top: 6px solid #4CAF50;
}

.form-container p {
    font-size: 16px;
    margin: 10px 0;
}

label {
    font-weight: bold;
    margin-top: 15px;
    display: block;
}

input, select {
    width: 100%;
    padding: 10px 12px;
    margin-top: 5px;
    border-radius: 8px;
    border: 1px solid #ccc;
    box-sizing: border-box;
    font-size: 15px;
}

input:focus, select:focus {
    outline: none;
    border-color: #4CAF50;
    box-shadow: 0 0 5px rgba(76,175,80,0.5);
}

button {
    width: 100%;
    padding: 12px;
    margin-top: 25px;
    border: none;
    border-radius: 10px;
    background: linear-gradient(90deg, #4CAF50, #45a049);
    color: white;
    font-size: 16px;
    cursor: pointer;
    transition: 0.3s;
}

button:hover {
    background: linear-gradient(90deg, #45a049, #39943f);
}

.qris-img {
    display: block;
    margin: 15px auto;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

</style>
</head>
<body>

<h3>Transaksi Pembayaran</h3>

<div class="form-container">
<p>Nama Pelanggan: <b><?= $p['nama_pelanggan']; ?></b></p>
<p>Total Bayar: <b>Rp <?= number_format($p['total_harga']); ?></b></p>

<form method="post">

<label>Tanggal Bayar</label>
<input type="date" name="tanggal_bayar" required>

<label>Jumlah Bayar</label>
<input type="number" name="jumlah_bayar" required>

<label>Metode Pembayaran</label>
<select name="metode" onchange="this.form.submit()" required>
    <option value="">-- Pilih --</option>
    <option value="Cash" <?= ($metode=='Cash')?'selected':'' ?>>Cash</option>
    <option value="QRIS" <?= ($metode=='QRIS')?'selected':'' ?>>QRIS</option>
    <option value="Transfer" <?= ($metode=='Transfer')?'selected':'' ?>>Transfer</option>
</select>

<?php if($metode == 'QRIS'){ ?>
    <b>Scan QRIS:</b>
    <img src="img/Qris.png" width="200" class="qris-img">
<?php } ?>

<?php if($metode == 'Transfer'){ ?>
    <label>Nomor Rekening Tujuan</label>
    <input type="text" name="rekening_tujuan"
           placeholder="BSI 7234674204" required>
<?php } ?>

<?php if($metode != ''){ ?>
    <button type="submit" name="simpan">Simpan Transaksi</button>
<?php } ?>

</form>
</div>

</body>
</html>
