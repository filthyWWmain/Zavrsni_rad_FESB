<?php
// ============================================================
//  SecureApp — logout.php
//  Sigurna odjava: CSRF provjera, potpuno uništavanje sesije,
//  brisanje session cookieja
// ============================================================
session_start();

// CSRF provjera — sprječava CSRF logout napad
// Odjava mora biti POST zahtjev s važećim CSRF tokenom
if (
    $_SERVER['REQUEST_METHOD'] !== 'POST' ||
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    // Nevaljan zahtjev — preusmjeri bez odjave
    header('Location: dashboard.php');
    exit;
}

// Uništi sve session varijable
$_SESSION = [];

// Uništi session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

// Uništi sesiju na serveru
session_destroy();

// Preusmjeri na login
header('Location: ../main.php');
exit;
