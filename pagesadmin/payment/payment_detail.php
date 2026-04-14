<?php
include_once("../../config/koneksi.php");
session_start();
if (!isset($_SESSION['usr']) || !isset($_SESSION['role'])|| $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode([]);
    exit;
}
$cabang = $_SESSION['cabang'];

$id = $_GET['id'] ?? '';

if ($cabang === 'mataram') {
    // Bisa lihat semua cabang
    $stmt = $conn->prepare("SELECT * FROM bayar WHERE id_bayar = ?");
    $stmt->bind_param("s", $id);
} else {
    // Harus sesuai cabang
    $stmt = $conn->prepare("SELECT * FROM bayar WHERE id_bayar = ? AND cabang = ?");
    $stmt->bind_param("ss", $id, $cabang);
}
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode([]);
}