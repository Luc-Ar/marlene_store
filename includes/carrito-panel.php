<!-- OVERLAY -->
<div class="carrito-overlay" id="carrito-overlay" onclick="cerrarCarrito()"></div>

<!-- PANEL LATERAL -->
<div class="carrito-panel" id="carrito-panel">
    <div class="carrito-panel-header">
        <h2>Mi pedido</h2>
        <button class="carrito-cerrar" onclick="cerrarCarrito()">✕</button>
    </div>
    <div class="carrito-items" id="carrito-items">
        <div class="carrito-vacio">
            <p>🛒</p>
            <p>Tu carrito está vacío</p>
        </div>
    </div>
    <div class="carrito-panel-footer" id="carrito-footer" style="display:none;">
        <p class="carrito-resumen">
            TOTAL DE PRODUCTOS: <span id="carrito-total">0</span>
        </p>
        <p class="carrito-resumen">
            TOTAL: <span id="carrito-subtotal">$0</span>
        </p>
        <button class="carrito-wa-btn" onclick="enviarPorWhatsapp()">
            💬 ENVIAR PEDIDO POR WHATSAPP
        </button>
        <button class="btn-vaciar" onclick="vaciarCarrito()">VACIAR CARRITO</button>
    </div>
</div>

<!-- BOTÓN FLOTANTE -->
<button class="carrito-btn-flotante" onclick="abrirCarrito()">
    🛒 CARRITO
    <span class="carrito-badge" id="carrito-badge">0</span>
</button>

<!-- BOTÓN WHATSAPP FLOTANTE -->
<a href="https://wa.me/5493704097831" target="_blank" class="wa-btn">
    💬 WHATSAPP
</a>