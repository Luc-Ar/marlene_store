<?php
// Este archivo carga las clases automáticamente cuando las llamás
spl_autoload_register(function ($clase) {
    // 1. Buscamos en la carpeta config (para Database.php)
    $rutaConfig = __DIR__ . '/config/' . $clase . '.php';
    // 2. Buscamos en la carpeta models (para los Repositorios)
    $rutaModels = __DIR__ . '/models/' . $clase . '.php';

    if (file_exists($rutaConfig)) {
        require_once $rutaConfig;
    } elseif (file_exists($rutaModels)) {
        require_once $rutaModels;
    }
});
