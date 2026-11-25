<?php
$host = 'localhost';
$port = '5433'; // ganti ke '5433' jika PostgreSQL 19 memakai port 5433
$dbname = 'klinikprestasi';
$user = 'postgres';
$password = 'aufarafli08'; 

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Koneksi gagal: " . pg_last_error());
}

pg_query($conn, "SET search_path TO public");
?>