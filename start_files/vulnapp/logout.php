<?php
// ============================================================
//  VulnApp — logout.php
//  NAMJERNO RANJIVO: nema CSRF zaštite na odjavnom linku
//  Napadač može prisiliti odjavu (CSRF logout napad)
// ============================================================
session_start();

// RANJIVO: odjava bez CSRF provjere
// Napadač može ugraditi <img src="vulnapp/logout.php"> na drugoj stranici
// i automatski odjaviti korisnika

session_unset();
session_destroy();

header('Location: ../main.php');
exit;
