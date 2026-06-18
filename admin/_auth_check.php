<?php
// admin/_auth_check.php — Tüm admin dosyalarına eklenecek yetki kontrolü
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ' . URL_ROOT . '/index.php?login_required=1');
    exit;
}
require_once __DIR__ . '/../config/db.php';
