<!-- ─── PANEL CARRITO ─── -->
<div id="carrito-overlay" onclick="cerrarCarrito()"></div>

<div id="carrito-panel">
    <div class="carrito-header">
        <h3>🛒 Tu carrito <span id="carrito-total">0</span> items</h3>
        <button onclick="cerrarCarrito()" class="carrito-cerrar">✕</button>
    </div>
    <div id="carrito-items">
        <div class="carrito-vacio">
            <p>🛒</p>
            <p>Tu carrito está vacío</p>
        </div>
    </div>
    <div id="carrito-footer" style="display:none;">
        <div class="carrito-subtotal-wrap">
            <span>Total:</span>
            <strong id="carrito-subtotal">$0</strong>
        </div>
        <button onclick="enviarPorWhatsapp()" class="btn-wsp-carrito">
            💬 Consultar por WhatsApp
        </button>
        <button onclick="vaciarCarrito()" class="btn-vaciar">Vaciar carrito</button>
    </div>
</div>

<!-- Botón flotante carrito -->
<button onclick="abrirCarrito()" class="carrito-flotante">
    🛒 <span id="carrito-badge" class="carrito-badge">0</span>
</button>