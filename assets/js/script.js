/* =========================================
   MARLENE VELAZ STORE — script.js
   ========================================= */

// ─── Scroll suave al hacer click en "Lo quiero" de los productos ───
function scrollToContact() {
  const contacto = document.getElementById('contacto');
  if (contacto) {
    contacto.scrollIntoView({ behavior: 'smooth' });
  }
}

// ─── Confirmación del formulario de contacto ───
// ─── Envío del formulario de contacto por WhatsApp ───
// ─── Envío del formulario de contacto por WhatsApp ───
function enviarFormulario() {
  const nombre = document.getElementById('cf-nombre');
  const telefono = document.getElementById('cf-telefono');
  const categoria = document.getElementById('cf-categoria');
  const mensaje = document.getElementById('cf-mensaje');

  if (nombre && nombre.value.trim() === '') {
    alert('Por favor, ingresá tu nombre 🌸');
    nombre.focus();
    return;
  }

  if (telefono && telefono.value.trim() === '') {
    alert('Por favor, ingresá tu número de WhatsApp 🌸');
    telefono.focus();
    return;
  }

  let texto = `Hola! Soy ${nombre.value.trim()}.\n`;
  texto += `Mi teléfono: ${telefono.value.trim()}\n`;
  if (categoria && categoria.value) {
    texto += `Me interesa: ${categoria.value}\n`;
  }
  if (mensaje && mensaje.value.trim()) {
    texto += `Mensaje: ${mensaje.value.trim()}`;
  }

  const url = 'https://wa.me/5493704097831?text=' + encodeURIComponent(texto);

  // En vez de window.open() (que muchos navegadores bloquean como
  // popup sin avisar), simulamos el click en un link real — eso
  // ningún navegador lo bloquea, porque es indistinguible de un
  // click genuino del usuario.
  const link = document.createElement('a');
  link.href = url;
  link.target = '_blank';
  link.rel = 'noopener';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);

  // Limpiar el formulario después de enviar
  nombre.value = '';
  telefono.value = '';
  if (categoria) categoria.value = '';
  if (mensaje) mensaje.value = '';
}

// ─── Asignar eventos al cargar la página ───
document.addEventListener('DOMContentLoaded', function () {

  // Botones "Lo quiero" en productos
  const prodBtns = document.querySelectorAll('.prod-btn');
  prodBtns.forEach(function (btn) {
    btn.addEventListener('click', scrollToContact);
  });

  // Botón enviar formulario
  const submitBtn = document.querySelector('.cf-submit');
  if (submitBtn) {
    submitBtn.addEventListener('click', enviarFormulario);
  }
  // Contador de caracteres del mensaje de contacto
  const mensajeInput = document.getElementById('cf-mensaje');
  const contador = document.getElementById('cf-contador');
  if (mensajeInput && contador) {
    mensajeInput.addEventListener('input', function () {
      contador.textContent = mensajeInput.value.length + ' / 500';
    });
  }
  // ─── Animación de entrada de las cards al hacer scroll ───
  const observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateY(0)';
      }
    });
  }, { threshold: 0.1 });

  // Observar cat-cards y prod-cards
  const animatedCards = document.querySelectorAll('.cat-card, .prod-card');
  animatedCards.forEach(function (card) {
    card.style.opacity = '0';
    card.style.transform = 'translateY(30px)';
    card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    observer.observe(card);
  });

});
