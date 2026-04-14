<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usr'])) {
    header('Location: ../gates/login.php');
    exit;
}
class DataUser {
    private $conn;
    private $user_id;

    public function __construct($conn, $user_id) {
        $this->conn = $conn;
        $this->user_id = $user_id;
    }

    public function getProfile() {
        $stmt = $this->conn->prepare("SELECT user_id, nama, nohp, alamat, jkel, agama, rl FROM karyawan WHERE user_id = ?");
        $stmt->bind_param("s", $this->user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}