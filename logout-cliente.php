<?php
require_once __DIR__ . '/includes/error-handler.php';
session_start();
unset($_SESSION['cliente_id']);
unset($_SESSION['cliente_nombre']);
unset($_SESSION['cliente_email']);
header('Location: index.php');
exit;
