<?php
session_start();

// Simulamos que el usuario eligió un par de productos
$_SESSION['carrito'] = [
    [
        'id' => 1,
        'nombre' => 'Mochila Marlene Reforzada',
        'precio' => 12500.50,
        'cantidad' => 1,
        'peso' => 1.200
    ],
    [
        'id' => 5,
        'nombre' => 'Cartuchera Estampada',
        'precio' => 3200.00,
        'cantidad' => 2,
        'peso' => 0.150
    ]
];

echo "<h1>Carrito de prueba cargado</h1>";
echo "<p>Productos en sesión: " . count($_SESSION['carrito']) . "</p>";
echo "<a href='procesar-venta.php'>Simular Finalizar Compra >></a>";
