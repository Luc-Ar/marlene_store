/* =========================================
   MARLENE STORE — catalogo.js
   ========================================= */

const WA_NUMBER = '5493704097831';
let carrito = JSON.parse(localStorage.getItem('marlene_carrito') || '[]');

function agregarAlCarrito(btn, nombre, imagen, subcategoria, precio) {
  const existente = carrito.find(i => i.nombre === nombre);
  if (existente) {
    existente.cantidad++;
  } else {
    carrito.push({ nombre, imagen, subcategoria, cantidad: 1, precio });
  }
  actualizarCarrito();
  abrirCarrito();
  btn.textContent = '✓ Agregado';
  btn.classList.add('agregado');
  setTimeout(() => {
    btn.textContent = '+ Agregar';
    btn.classList.remove('agregado');
  }, 1500);
}
actualizarCarrito();
function actualizarCarrito() {
  const total = carrito.reduce((acc, i) => acc + i.cantidad, 0);
  const badge = document.getElementById('carrito-badge');
  badge.textContent = total;
  badge.classList.toggle('visible', total > 0);
  document.getElementById('carrito-total').textContent = total;
  const totalPesos = carrito.reduce((acc, i) => acc + ((i.precio || 0) * i.cantidad), 0);
  document.getElementById('carrito-subtotal').textContent = '$' + totalPesos.toLocaleString('es-AR');
  const footer = document.getElementById('carrito-footer');
  const itemsContainer = document.getElementById('carrito-items');
  itemsContainer.innerHTML = '';
  if (carrito.length === 0) {
    footer.style.display = 'none';
    itemsContainer.innerHTML = `
      <div class="carrito-vacio">
        <p>🛒</p>
        <p>Tu carrito está vacío</p>
      </div>`;
    return;
  }
  footer.style.display = 'block';
  carrito.forEach((item, index) => {
    const div = document.createElement('div');
    div.className = 'carrito-item';
    div.innerHTML = `
      <img src="${item.imagen}" alt="${item.nombre}" style="width:50px;height:50px;object-fit:contain;border-radius:4px;flex-shrink:0;">
      <div class="carrito-item-info">
        <strong>${item.nombre}</strong>
        <span>${item.subcategoria}</span>
      </div>
      <div class="carrito-item-qty">
        <button class="qty-btn" onclick="cambiarCantidad(${index}, -1)">−</button>
        <span class="qty-num">${item.cantidad}</span>
        <button class="qty-btn" onclick="cambiarCantidad(${index}, 1)">+</button>
      </div>
      <button class="carrito-eliminar" onclick="eliminarItem(${index})">🗑</button>
    `;
    itemsContainer.appendChild(div);
  });
}

function cambiarCantidad(index, delta) {
  carrito[index].cantidad += delta;
  if (carrito[index].cantidad <= 0) carrito.splice(index, 1);
  actualizarCarrito();
}

function eliminarItem(index) {
  carrito.splice(index, 1);
  actualizarCarrito();
}

function vaciarCarrito() {
  carrito = [];
  actualizarCarrito();
}

function abrirCarrito() {
  document.getElementById('carrito-panel').classList.add('abierto');
  document.getElementById('carrito-overlay').classList.add('abierto');
  const waBtn = document.querySelector('.wa-btn');
  if (waBtn) waBtn.style.display = 'none';
}

function cerrarCarrito() {
  document.getElementById('carrito-panel').classList.remove('abierto');
  document.getElementById('carrito-overlay').classList.remove('abierto');
  const waBtn = document.querySelector('.wa-btn');
  if (waBtn) waBtn.style.display = 'flex';
}

function enviarPorWhatsapp() {
  if (carrito.length === 0) return;
  let mensaje = '¡Hola Marlene! 👋 Me gustaría consultar por los siguientes productos:\n\n';
  carrito.forEach(item => {
    mensaje += `🎒 *${item.nombre}* (${item.subcategoria}) — Cantidad: ${item.cantidad}\n`;
  });
  const totalPesos = carrito.reduce((acc, i) => acc + ((i.precio || 0) * i.cantidad), 0);
  mensaje += `\n*Total: $${totalPesos.toLocaleString('es-AR')}*`;
  mensaje += '\n\n¿Me podés confirmar disponibilidad? ¡Gracias! 🌸';
  const url = `https://wa.me/${WA_NUMBER}?text=${encodeURIComponent(mensaje)}`;
  window.open(url, '_blank');
}
function irAlCheckout() {
  if (carrito.length === 0) return;
  fetch('/carrito-api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ accion: 'sincronizar', items: carrito })
  })
    .then(r => r.json())
    .then(data => {
      if (data.ok) window.location.href = '/checkout.php';
      else alert('Error al procesar el carrito. Intentá de nuevo.');
    })
    .catch(() => {
      alert('Error de conexión. Intentá de nuevo.');
    });
}

// ─── Filtros dinámicos desde BD ───
document.addEventListener('DOMContentLoaded', () => {
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
  actualizarCarrito();
});
