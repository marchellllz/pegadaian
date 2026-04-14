<?php
header('Content-Type: application/json');

// koneksi database
include "../../../config/koneksi.php";

$sql = "SELECT tarif 
        FROM dfbunga 
        WHERE bunga = 'admin' 
        LIMIT 1";

$result = $conn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    echo json_encode([
        'tarif' => (float)$row['tarif']
    ]);
} else {
    // fallback kalau data gak ada
    echo json_encode([
        'tarif' => 0
    ]);
}

$conn->close();