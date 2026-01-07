<?php
include 'koneksi.php';

/* ================= DAFTAR LAYANAN ================= */
$daftar_layanan = [
    ['nama'=>'Cuci Kering Reguler','harga'=>8000],
    ['nama'=>'Cuci + Setrika','harga'=>10000],
    ['nama'=>'Cuci Kering Express','harga'=>15000],
    ['nama'=>'Setrika Saja','harga'=>6000],
    ['nama'=>'Bed Cover Besar','harga'=>35000],
    ['nama'=>'Gordyn Tebal','harga'=>45000],
];

/* ================= SIMPAN PESANAN ================= */
if(isset($_POST['simpan'])){
    $nama    = $_POST['nama_pelanggan'];
    $tgl     = $_POST['tanggal_pesanan'];
    $layanan = $_POST['jenis_layanan'];
    $berat   = $_POST['berat_kg'];
    $total   = $_POST['total_harga'];

    // STATUS SELALU BARU
    $status = 'Baru';

    $sql = "INSERT INTO pesanan
        (nama_pelanggan, tanggal_pesanan, jenis_layanan, berat_kg, total_harga, status_pesanan)
        VALUES
        ('$nama','$tgl','$layanan','$berat','$total','$status')";

    if(mysqli_query($conn,$sql)){
        $order_id = mysqli_insert_id($conn);
        header("Location: transaksi.php?order_id=$order_id");
        exit;
    }else{
        echo "Gagal menyimpan pesanan: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Buat Pesanan Laundry</title>
<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f0f2f5;
    margin: 0;
    padding: 0;
}

h2 {
    text-align: center;
    color: #333;
    margin-top: 30px;
}

/* ===== FORM CARD ===== */
.form-container {
    background: #fff;
    max-width: 500px;
    margin: 30px auto;
    padding: 30px 40px;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    border-top: 6px solid #4CAF50;
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
</style>
</head>
<body>

<h2>Buat Pesanan Laundry</h2>

<div class="form-container">
<form method="post">

<label>Nama Pelanggan</label>
<input type="text" name="nama_pelanggan" required>

<label>Tanggal Pesanan</label>
<input type="date" name="tanggal_pesanan" required>

<label>Jenis Layanan</label>
<select name="jenis_layanan" id="jenis_layanan" onchange="hitungTotal()" required>
    <option value="">-- Pilih --</option>
    <?php foreach($daftar_layanan as $l){ ?>
        <option value="<?= $l['nama']; ?>" data-harga="<?= $l['harga']; ?>">
            <?= $l['nama']; ?> (Rp <?= number_format($l['harga']); ?>)
        </option>
    <?php } ?>
</select>

<label>Berat / Jumlah (Kg)</label>
<input type="number" step="0.1" id="berat" name="berat_kg" oninput="hitungTotal()" required>

<label>Total Harga</label>
<input type="number" id="total_harga" name="total_harga" readonly required>

<button type="submit" name="simpan">Simpan & Lanjut Pembayaran</button>

</form>
</div>

<script>
function hitungTotal(){
    let layanan = document.getElementById('jenis_layanan');
    let harga = layanan.options[layanan.selectedIndex]?.dataset.harga || 0;
    let berat = document.getElementById('berat').value || 0;
    document.getElementById('total_harga').value = harga * berat;
}
</script>

</body>
</html>
