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
function enviarFormulario() {
  const nombre = document.querySelector('.cf-group input[type="text"]');
  const telefono = document.querySelector('.cf-group input[type="tel"]');

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

  alert('¡Gracias por tu mensaje! Te respondemos pronto 🌸');
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
