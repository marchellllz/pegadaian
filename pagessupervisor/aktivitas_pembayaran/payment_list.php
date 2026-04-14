<?php
include_once("../../config/koneksi.php");
session_start();
if (!isset($_SESSION['usr']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'supervisor') {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([]);
    exit;
}

$bulan = $_GET['bulan'] ?? '';
$tahun = $_GET['tahun'] ?? '';

// Query: ambil data bayar + nama_nasabah dari tabel gadai (left join biar tetap tampil walau gadai kosong)
$sql = "SELECT b.id_bayar, b.no_gadai, b.tanggal_bayar, b.status_bayar, g.nama_nasabah
        FROM bayar b
        LEFT JOIN gadai g ON g.no_gadai = b.no_gadai";

$where = [];
$params = [];
$types  = "";

// Filter cabang (jika bukan pusat)
if ($_SESSION['cabang'] !== 'mataram') {
    $where[] = "b.cabang = ?";
    $params[] = $_SESSION['cabang'];
    $types   .= "s";
}
// Filter bulan
if (!empty($bulan)) {
    $where[] = "MONTH(b.tanggal_bayar) = ?";
    $params[] = (int)$bulan;
    $types   .= "i";
}

// Filter tahun
if (!empty($tahun)) {
    $where[] = "YEAR(b.tanggal_bayar) = ?";
    $params[] = (int)$tahun;
    $types   .= "i";
}

// Gabungkan WHERE jika ada filter
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY b.tanggal_bayar DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
    exit;
}

// Bind param kalau ada filter
if (!empty($params)) {
    // bind_param expects types string then variables by reference
    $bind_names[] = $types;
    for ($i = 0; $i < count($params); $i++) {
        $bind_name = 'bind' . $i;
        $$bind_name = $params[$i];
        $bind_names[] = &$$bind_name;
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_names);
}

$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    // pastikan nama_nasabah selalu ada (bisa null kalau ga ketemu)
    if (empty($row['nama_nasabah'])) {
        $row['nama_nasabah'] = null; // dikirim null, frontend bisa handle
    }
    $data[] = $row;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($data, JSON_UNESCAPED_UNICODE);