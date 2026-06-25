<?php
session_start();
require_once __DIR__ . '/includes/error-handler.php';
unset($_SESSION['cliente_id']);
unset($_SESSION['cliente_nombre']);
unset($_SESSION['cliente_email']);
header('Location: /index.php');
exit;
