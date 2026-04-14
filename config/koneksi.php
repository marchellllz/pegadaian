<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
if(basename($_SERVER['PHP_SELF']) == basename(__FILE__)){
    exit('Access Denied');
}

$host = "127.0.0.1"; //ganti localhost kalau deploy
$user = "sobatgadai";
$pass = "GadaiHebat9995!";
$db= "sobatgadai";

$conn = new mysqli($host, $user, $pass, $db);
if($conn->connect_error){
    die("Koneksi gagal: Access Denied");
}

?>