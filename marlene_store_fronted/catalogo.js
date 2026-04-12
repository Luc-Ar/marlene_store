/* =========================================
   MARLENE VELAZQUEZ STORE — catalogo.js
   ========================================= */

const WA_NUMBER = '5493704097831';
let carrito = [];

// ─── Agregar producto al carrito ───
function agregarAlCarrito(btn, nombre, imagen, subcategoria, precio) {
  const existente = carrito.find(i => i.nombre === nombre);
  if (existente) {
    existente.cantidad++;
  } else {
    carrito.push({ nombre, imagen, subcategoria, cantidad: 1, precio });
  }
  btn.textContent = '✓ Agregado';
  btn.classList.add('agregado');
  setTimeout(() => {
    btn.textContent = '+ Agregar';
    btn.classList.remove('agregado');
  }, 1500);
  actualizarCarrito();
  abrirCarrito();
}

// ─── Actualizar UI del carrito ───
function actualizarCarrito() {
  const total = carrito.reduce((acc, i) => acc + i.cantidad, 0);

  // Actualizar badge
  const badge = document.getElementById('carrito-badge');
  badge.textContent = total;
  badge.classList.toggle('visible', total > 0);

  // Actualizar totales
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

// ─── Cambiar cantidad ───
function cambiarCantidad(index, delta) {
  carrito[index].cantidad += delta;
  if (carrito[index].cantidad <= 0) carrito.splice(index, 1);
  actualizarCarrito();
}

// ─── Eliminar item ───
function eliminarItem(index) {
  carrito.splice(index, 1);
  actualizarCarrito();
}

// ─── Vaciar carrito ───
function vaciarCarrito() {
  carrito = [];
  actualizarCarrito();
}

// ─── Abrir panel ───
function abrirCarrito() {
  document.getElementById('carrito-panel').classList.add('abierto');
  document.getElementById('carrito-overlay').classList.add('abierto');
  document.querySelector('.wa-btn').style.display = 'none';
}

// ─── Cerrar panel ───
function cerrarCarrito() {
  document.getElementById('carrito-panel').classList.remove('abierto');
  document.getElementById('carrito-overlay').classList.remove('abierto');
  document.querySelector('.wa-btn').style.display = 'flex';
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

  const filtros = [
    { label: 'Todos', id: null },
    { label: '🎒 Infantiles', id: 'sec-infantiles' },
    { label: '📚 Escolares', id: 'sec-escolares' },
    { label: '💼 Adultos', id: 'sec-adultos' },
  ];

  const secciones = ['sec-infantiles', 'sec-escolares', 'sec-adultos'];
  const container = document.getElementById('filtros-container');

  filtros.forEach(f => {
    const btn = document.createElement('button');
    btn.className = 'filtro-btn' + (f.id === null ? ' activo' : '');
    btn.textContent = f.label;
    btn.addEventListener('click', () => {
      document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('activo'));
      btn.classList.add('activo');
      secciones.forEach(s => {
        document.getElementById(s).classList.toggle('oculto', f.id !== null && s !== f.id);
      });
    });
    container.appendChild(btn);
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
