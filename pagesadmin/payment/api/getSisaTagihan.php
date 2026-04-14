<?php
header('Content-Type: application/json');
include_once("../../../config/koneksi.php");

$noGadai = $_GET['no_gadai'] ?? '';

$sql = "
SELECT 
    (
        IFNULL(g.nilai_taksir,0)
      + IFNULL(g.bunga,0)
      + IFNULL(g.biaya_adm,0)
      + IFNULL(g.denda,0)
    )
    - IFNULL(SUM(b.jumlah_bayar),0)
    AS sisa_tagihan
FROM gadai g
LEFT JOIN bayar b 
    ON b.no_gadai = g.no_gadai
WHERE g.no_gadai = ?
GROUP BY g.no_gadai
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $noGadai);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode([
    'sisa_tagihan' => (float)($row['sisa_tagihan'] ?? 0)
]);

$conn->close();