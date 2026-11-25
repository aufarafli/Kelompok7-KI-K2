<?php
$host = 'localhost';
$port = '5432';
$dbname = 'klinikprestasi';
$user = 'postgres';
$password = 'ISI_PASSWORD_POSTGRES_DISINI';

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
if (!$conn) {
    die("Koneksi gagal: " . pg_last_error());
}
pg_query($conn, "SET search_path TO public");
?>