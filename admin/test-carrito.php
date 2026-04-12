<?php
session_start();

// Usamos solo IDs que SI existen en tu tabla (1 y 2)
$_SESSION['carrito'] = [
    [
        'id' => 1,
        'nombre' => 'Mochila Kuromi',
        'precio' => 15000.00,
        'cantidad' => 1,
        'peso' => 1.000
    ],
    [
        'id' => 2,
        'nombre' => 'Mochila Frozen',
        'precio' => 15000.00,
        'cantidad' => 1,
        'peso' => 1.000
    ]
];

echo "<h1>Carrito de prueba corregido</h1>";
echo "<p>Productos listos: Mochila Kuromi y Frozen</p>";
echo "<a href='procesar-venta.php'>Simular Finalizar Compra >></a>";
