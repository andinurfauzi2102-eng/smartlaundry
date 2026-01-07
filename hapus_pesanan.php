<?php
// File: hapus_pesanan.php

session_start();
include 'koneksi.php';

// ===============================================
// 1. CEK OTORISASI (ADMIN / KASIR)
// ===============================================
if (!isset($_SESSION['username']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'kasir')) {
    header("Location: login.php");
    exit();
}

// ===============================================
// 2. KONEKSI DATABASE
// ===============================================
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// ===============================================
// 3. PROSES HAPUS PESANAN
// ===============================================
if (isset($_GET['id']) && !empty($_GET['id'])) {

    $order_id = (int) $_GET['id'];

    // Mulai transaksi
    $conn->begin_transaction();

    try {
        // ---------------------------------------
        // HAPUS DETAIL PESANAN (TABEL ANAK)
        // ---------------------------------------
        $sql_detail = "DELETE FROM detail_pesanan WHERE pesanan_id = ?";
        $stmt_detail = $conn->prepare($sql_detail);
        if (!$stmt_detail) {
            throw new Exception("Prepare gagal (detail): " . $conn->error);
        }

        $stmt_detail->bind_param("i", $order_id);
        $stmt_detail->execute();
        $stmt_detail->close();

        // ---------------------------------------
        // HAPUS PESANAN (TABEL INDUK)
        // ---------------------------------------
        $sql_pesanan = "DELETE FROM pesanan WHERE order_id = ?";
        $stmt_pesanan = $conn->prepare($sql_pesanan);
        if (!$stmt_pesanan) {
            throw new Exception("Prepare gagal (pesanan): " . $conn->error);
        }

        $stmt_pesanan->bind_param("i", $order_id);

        if (!$stmt_pesanan->execute()) {
            throw new Exception("Gagal hapus pesanan: " . $stmt_pesanan->error);
        }

        $stmt_pesanan->close();

        // Commit jika semua sukses
        $conn->commit();
        $conn->close();

        header("Location: status_pesanan.php?action=deleted&id=" . urlencode($order_id));
        exit();

    } catch (Exception $e) {

        // Rollback jika error
        $conn->rollback();
        $conn->close();

        $msg = urlencode("❌ Gagal hapus pesanan #$order_id. Detail: " . $e->getMessage());
        header("Location: status_pesanan.php?error=db_error&msg=$msg");
        exit();
    }

} else {
    header("Location: status_pesanan.php?error=missing_id");
    exit();
}
?>
