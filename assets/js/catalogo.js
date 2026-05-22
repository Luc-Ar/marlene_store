/* =========================================
   MARLENE STORE — catalogo.js
   ========================================= */

const WA_NUMBER = '5493704097831';
let carrito = [];

// ─── Agregar producto al carrito ───
const WA_NUMBER = '5493704097831';

// ─── Agregar al carrito via API ───
function agregarAlCarrito(productoId) {
  fetch('/marlene-store/carrito-api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ accion: 'agregar', producto_id: productoId, cantidad: 1 })
  })
    .then(r => r.json())
    .then(data => {
      if (data.ok) {
        actualizarBadge(data.total);
        cargarCarrito();
        abrirCarrito();
      } else {
        alert('❌ ' + (data.error || 'Error al agregar'));
      }
    });
}

// ─── Cargar carrito desde sesión ───
function cargarCarrito() {
  fetch('/marlene-store/carrito-api.php?accion=obtener')
    .then(r => r.json())
    .then(data => {
      if (data.ok) {
        actualizarBadge(data.cantidad);
        renderCarrito(data.items, data.total);
      }
    });
}

// ─── Renderizar items del carrito ───
function renderCarrito(items, total) {
  const itemsContainer = document.getElementById('carrito-items');
  const footer = document.getElementById('carrito-footer');
  const badgeTotal = document.getElementById('carrito-total');
  const subtotal = document.getElementById('carrito-subtotal');

  if (!itemsContainer) return;

  const cantidad = items.reduce((a, i) => a + i.cantidad, 0);
  if (badgeTotal) badgeTotal.textContent = cantidad;
  if (subtotal) subtotal.textContent = '$' + total.toLocaleString('es-AR');

  itemsContainer.innerHTML = '';

  if (items.length === 0) {
    if (footer) footer.style.display = 'none';
    itemsContainer.innerHTML = `
            <div class="carrito-vacio">
                <p>🛒</p>
                <p>Tu carrito está vacío</p>
            </div>`;
    return;
  }

  if (footer) footer.style.display = 'block';

  items.forEach(item => {
    const div = document.createElement('div');
    div.className = 'carrito-item';
    div.innerHTML = `
            <img src="${item.imagen}" alt="${item.nombre}" style="width:50px;height:50px;object-fit:contain;border-radius:4px;flex-shrink:0;">
            <div class="carrito-item-info">
                <strong>${item.nombre}</strong>
                <span>$${(item.precio).toLocaleString('es-AR')}</span>
            </div>
            <div class="carrito-item-qty">
                <button class="qty-btn" onclick="cambiarCantidad(${item.id}, ${item.cantidad - 1})">−</button>
                <span class="qty-num">${item.cantidad}</span>
                <button class="qty-btn" onclick="cambiarCantidad(${item.id}, ${item.cantidad + 1})">+</button>
            </div>
            <button class="carrito-eliminar" onclick="quitarItem(${item.id})">🗑</button>
        `;
    itemsContainer.appendChild(div);
  });
}

// ─── Actualizar badge del nav ───
function actualizarBadge(total) {
  const badge = document.getElementById('carrito-badge');
  if (badge) {
    badge.textContent = total;
    badge.classList.toggle('visible', total > 0);
  }
}

// ─── Cambiar cantidad ───
function cambiarCantidad(id, cantidad) {
  fetch('/marlene-store/carrito-api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ accion: 'actualizar', producto_id: id, cantidad: cantidad })
  })
    .then(r => r.json())
    .then(() => cargarCarrito());
}

// ─── Quitar item ───
function quitarItem(id) {
  fetch('/marlene-store/carrito-api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ accion: 'quitar', producto_id: id })
  })
    .then(r => r.json())
    .then(() => cargarCarrito());
}

// ─── Vaciar carrito ───
function vaciarCarrito() {
  fetch('/marlene-store/carrito-api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ accion: 'vaciar' })
  })
    .then(r => r.json())
    .then(() => cargarCarrito());
}
// ─── Enviar pedido por WhatsApp ───
async function enviarPorWhatsapp() {
  if (carrito.length === 0) return;

  // Armar mensaje WhatsApp
  let mensaje = '¡Hola Marlene! 👋 Me gustaría consultar por los siguientes productos:\n\n';
  carrito.forEach(item => {
    mensaje += `🎒 *${item.nombre}* — Cantidad: ${item.cantidad}\n`;
  });
  const totalPesos = carrito.reduce((acc, i) => acc + ((i.precio || 0) * i.cantidad), 0);
  mensaje += `\n*Total: $${totalPesos.toLocaleString('es-AR')}*`;
  mensaje += '\n\n¿Me podés confirmar disponibilidad? 🌸';

  // Guardar pedido en la BD
  try {
    const pedidoData = {
      nombre: 'Cliente WhatsApp',
      telefono: 'WhatsApp',
      productos: carrito.map(i => ({
        nombre: i.nombre,
        precio: i.precio || 0,
        cantidad: i.cantidad,
        id: 0
      })),
      total: totalPesos,
      metodo_pago: 'whatsapp'
    };
    await fetch('/marlene-store/api/guardar-pedido.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(pedidoData)
    });
  } catch (e) {
    console.log('Error al guardar pedido:', e);
  }

  // Abrir WhatsApp
  const url = `https://wa.me/${WA_NUMBER}?text=${encodeURIComponent(mensaje)}`;
  window.open(url, '_blank');
}

// ─── Filtros por subcategoría ───

document.addEventListener('DOMContentLoaded', () => {

  // ─── Filtros (botones generados por PHP desde BD) ───
  const secciones = document.querySelectorAll('.catalogo-section');

  document.querySelectorAll('.filtro-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('activo'));
      btn.classList.add('activo');

      const seccion = btn.dataset.seccion;
      secciones.forEach(s => {
        s.classList.toggle('oculto', seccion !== 'todos' && s.id !== seccion);
      });
    });
  });

  // ─── Animación de entrada ───
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateY(0)';
      }
    });
  }, { threshold: 0.1 });

  document.querySelectorAll('.cat-prod-card').forEach(card => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(24px)';
    card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    observer.observe(card);
  });

});