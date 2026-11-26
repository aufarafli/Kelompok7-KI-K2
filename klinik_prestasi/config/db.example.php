<?php
date_default_timezone_set('Asia/Jakarta');

$host = 'localhost';
$port = '5433'; // sesuaikan port PostgreSQL di komputer lokal (umumnya 5432 atau 5433)
$dbname = 'klinikprestasi';
$user = 'postgres';
$password = ''; // isi password PostgreSQL lokal masing-masing

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Koneksi gagal: " . pg_last_error());
}

if ($conn) {
    pg_query($conn, "SET TIME ZONE 'Asia/Jakarta'");
}

pg_query($conn, "SET search_path TO public");