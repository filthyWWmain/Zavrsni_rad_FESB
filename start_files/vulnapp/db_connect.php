<?php
// ============================================================
//  VulnApp — db_connect.php
//  NAMJERNO RANJIVA konekcija — za edukativne svrhe
//  Problem: greška otkriva detalje baze korisniku
// ============================================================

$host     = 'localhost';
$dbname   = 'webseclab';
$username = 'root';
$password = '';

// RANJIVO: mysqli_connect bez error handlinga
$conn = mysqli_connect($host, $username, $password, $dbname);

// RANJIVO: prikazuje detalje greške korisniku
if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');