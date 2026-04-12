<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Calculadora de Envío — Marlene Velazquez STORE</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Montserrat:wght@300;400;500;600;700;900&display=swap"
    rel="stylesheet">
  <style>
    :root {
      --rosa: #E8C4C4;
      --rosa-osc: #C8989A;
      --rosa-pro: #9E5F62;
      --crema: #FAF6F1;
      --crema2: #F2EBE0;
      --marron: #5C3D3E;
      --dorado: #C9A96E;
      --texto: #3A2526;
      --blanco: #FEFCFA;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Cormorant Garamond', serif;
      background: var(--crema);
      color: var(--texto);
    }

    .calc-wrap {
      max-width: 600px;
      margin: 0 auto;
      padding: 48px 24px;
    }

    .calc-eyebrow {
      font-family: 'Montserrat', sans-serif;
      font-size: 0.62rem;
      font-weight: 700;
      letter-spacing: 4px;
      text-transform: uppercase;
      color: var(--dorado);
      margin-bottom: 10px;
    }

    .calc-title {
      font-family: 'Great Vibes', cursive;
      font-size: 2.8rem;
      color: var(--marron);
      margin-bottom: 6px;
    }

    .calc-sub {
      font-size: 1rem;
      font-style: italic;
      color: #9a7070;
      margin-bottom: 36px;
      line-height: 1.6;
    }

    .calc-tabs {
      display: flex;
      border-bottom: 2px solid rgba(200, 152, 154, 0.2);
    }

    .calc-tab {
      flex: 1;
      padding: 14px 16px;
      border: none;
      background: var(--crema2);
      font-family: 'Montserrat', sans-serif;
      font-size: 0.6rem;
      font-weight: 700;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      color: var(--rosa-osc);
      cursor: pointer;
      transition: all 0.2s;
      border-bottom: 2px solid transparent;
      margin-bottom: -2px;
    }

    .calc-tab:first-child {
      border-radius: 4px 0 0 0;
    }

    .calc-tab:last-child {
      border-radius: 0 4px 0 0;
    }

    .calc-tab.activo {
      background: var(--blanco);
      color: var(--marron);
      border-bottom: 2px solid var(--dorado);
    }

    .calc-box {
      background: var(--blanco);
      border: 1px solid rgba(200, 152, 154, 0.25);
      border-top: none;
      border-radius: 0 0 4px 4px;
      padding: 32px;
    }

    .calc-panel {
      display: none;
    }

    .calc-panel.activo {
      display: block;
    }

    .calc-group {
      margin-bottom: 20px;
    }

    .calc-label {
      display: block;
      font-family: 'Montserrat', sans-serif;
      font-size: 0.62rem;
      font-weight: 700;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--rosa-pro);
      margin-bottom: 8px;
    }

    .calc-input,
    .calc-select {
      width: 100%;
      background: var(--crema2);
      border: 1px solid rgba(200, 152, 154, 0.35);
      border-radius: 2px;
      padding: 13px 16px;
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.05rem;
      color: var(--texto);
      outline: none;
      transition: border-color 0.2s;
    }

    .calc-input:focus,
    .calc-select:focus {
      border-color: var(--rosa-osc);
    }

    .calc-input:disabled,
    .calc-select:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }

    .calc-input-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }

    .calc-btn {
      width: 100%;
      background: var(--marron);
      color: var(--crema);
      border: none;
      padding: 16px;
      font-family: 'Montserrat', sans-serif;
      font-size: 0.7rem;
      font-weight: 700;
      letter-spacing: 3px;
      text-transform: uppercase;
      border-radius: 2px;
      cursor: pointer;
      transition: background 0.3s;
      margin-top: 8px;
    }

    .calc-btn:hover {
      background: var(--rosa-pro);
    }

    .calc-btn:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }

    .calc-status {
      font-family: 'Montserrat', sans-serif;
      font-size: 0.62rem;
      letter-spacing: 1px;
      color: var(--rosa-osc);
      margin-top: 6px;
      min-height: 18px;
      font-style: italic;
    }

    .loading-loc {
      font-family: 'Montserrat', sans-serif;
      font-size: 0.6rem;
      letter-spacing: 1px;
      color: var(--dorado);
      font-style: italic;
      margin-top: 4px;
      display: none;
    }

    .calc-resultado {
      display: none;
      margin-top: 28px;
      border-top: 1px solid rgba(200, 152, 154, 0.2);
      padding-top: 28px;
    }

    .calc-resultado.visible {
      display: block;
    }

    .resultado-titulo {
      font-family: 'Montserrat', sans-serif;
      font-size: 0.62rem;
      font-weight: 700;
      letter-spacing: 3px;
      text-transform: uppercase;
      color: var(--dorado);
      margin-bottom: 16px;
    }

    .resultado-zona {
      display: inline-block;
      background: var(--marron);
      color: var(--crema);
      font-family: 'Montserrat', sans-serif;
      font-size: 0.55rem;
      font-weight: 700;
      letter-spacing: 2px;
      text-transform: uppercase;
      padding: 4px 10px;
      border-radius: 2px;
      margin-bottom: 16px;
    }

    .resultado-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      margin-bottom: 20px;
    }

    .resultado-card {
      background: var(--crema2);
      border: 1px solid rgba(200, 152, 154, 0.2);
      border-radius: 2px;
      padding: 16px;
      text-align: center;
    }

    .resultado-card .servicio {
      font-family: 'Montserrat', sans-serif;
      font-size: 0.6rem;
      font-weight: 700;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--marron);
      margin-bottom: 6px;
    }

    .resultado-card .precio {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.6rem;
      font-weight: 600;
      color: var(--rosa-pro);
      margin-bottom: 4px;
    }

    .resultado-card .dias {
      font-size: 0.82rem;
      font-style: italic;
      color: #9a7070;
    }

    .resultado-nota {
      font-size: 0.85rem;
      font-style: italic;
      color: #9a7070;
      line-height: 1.6;
      text-align: center;
    }

    .error-msg {
      background: #fef0f0;
      border: 1px solid #f5c6c6;
      border-radius: 2px;
      padding: 12px 16px;
      font-family: 'Montserrat', sans-serif;
      font-size: 0.65rem;
      color: #c0392b;
      letter-spacing: 1px;
      display: none;
      margin-top: 12px;
    }

    .error-msg.visible {
      display: block;
    }
  </style>
</head>

<body>

  <div class="calc-wrap">
    <p class="calc-eyebrow">✦ Marlene Velazquez Store</p>
    <h2 class="calc-title">Calculá tu envío</h2>
    <p class="calc-sub">Calculamos el costo de envío desde Formosa Capital a todo el país.</p>

    <div class="calc-tabs">
      <button class="calc-tab activo" onclick="cambiarTab('cp')">📮 Tengo mi código postal</button>
      <button class="calc-tab" onclick="cambiarTab('manual')">🗺 Elegir provincia y localidad</button>
    </div>

    <div class="calc-box">

      <!-- ─── PANEL CP ─── -->
      <div class="calc-panel activo" id="panel-cp">
        <div class="calc-group">
          <label class="calc-label">Código Postal</label>
          <input type="text" class="calc-input" id="input-cp" placeholder="Ej: 1425" maxlength="8" inputmode="numeric">
          <p class="calc-status" id="status-cp"></p>
        </div>
        <div class="calc-input-row">
          <div class="calc-group">
            <label class="calc-label">Provincia</label>
            <input type="text" class="calc-input" id="cp-provincia" placeholder="Se completa solo">
          </div>
          <div class="calc-group">
            <label class="calc-label">Localidad</label>
            <input type="text" class="calc-input" id="cp-localidad" placeholder="Se completa solo">
          </div>
        </div>
        <button class="calc-btn" id="btn-calcular-cp" disabled onclick="mostrarResultado('cp')">Calcular envío
          →</button>
        <div class="error-msg" id="error-cp">⚠ No encontramos ese código postal. Probá con la otra opción.</div>
      </div>

      <!-- ─── PANEL MANUAL ─── -->
      <div class="calc-panel" id="panel-manual">
        <div class="calc-group">
          <label class="calc-label">Provincia</label>
          <select class="calc-select" id="select-provincia" onchange="cargarLocalidades()">
            <option value="">— Seleccioná tu provincia —</option>
            <option>Buenos Aires</option>
            <option>Catamarca</option>
            <option>Chaco</option>
            <option>Chubut</option>
            <option>Ciudad de Buenos Aires</option>
            <option>Córdoba</option>
            <option>Corrientes</option>
            <option>Entre Ríos</option>
            <option>Formosa</option>
            <option>Jujuy</option>
            <option>La Pampa</option>
            <option>La Rioja</option>
            <option>Mendoza</option>
            <option>Misiones</option>
            <option>Neuquén</option>
            <option>Río Negro</option>
            <option>Salta</option>
            <option>San Juan</option>
            <option>San Luis</option>
            <option>Santa Cruz</option>
            <option>Santa Fe</option>
            <option>Santiago del Estero</option>
            <option>Tierra del Fuego</option>
            <option>Tucumán</option>
          </select>
        </div>
        <div class="calc-group">
          <label class="calc-label">Localidad</label>
          <select class="calc-select" id="select-localidad" disabled onchange="habilitarBotonManual()">
            <option value="">— Primero elegí la provincia —</option>
          </select>
          <p class="loading-loc" id="loading-loc">Cargando localidades...</p>
        </div>
        <button class="calc-btn" id="btn-calcular-manual" disabled onclick="mostrarResultado('manual')">Calcular envío
          →</button>
      </div>

      <!-- ─── RESULTADO ─── -->
      <div class="calc-resultado" id="resultado">
        <p class="resultado-titulo">✦ Opciones de envío a tu domicilio</p>
        <span class="resultado-zona" id="resultado-zona"></span>
        <div class="resultado-grid">
          <div class="resultado-card">
            <p class="servicio">📦 OCA</p>
            <p class="precio" id="precio-oca">$0</p>
            <p class="dias" id="dias-oca"></p>
          </div>
          <div class="resultado-card">
            <p class="servicio">🏣 Correo Argentino</p>
            <p class="precio" id="precio-correo">$0</p>
            <p class="dias" id="dias-correo"></p>
          </div>
        </div>
        <p class="resultado-nota">
          * Precio estimado para paquetes de hasta 1kg. El precio final se confirma al momento del envío.<br>
          <strong>Consultanos por WhatsApp para coordinar tu pedido.</strong>
        </p>
      </div>

    </div>
  </div>

  <script>
    const ZONAS = {
      'Formosa': { zona: 'Zona 1 — Local', oca: 1800, correo: 1500, diasOca: '1 a 2 días hábiles', diasCorreo: '1 a 3 días hábiles' },
      'Chaco': { zona: 'Zona 1 — Cercana', oca: 2200, correo: 1900, diasOca: '1 a 3 días hábiles', diasCorreo: '2 a 4 días hábiles' },
      'Misiones': { zona: 'Zona 1 — Cercana', oca: 2400, correo: 2000, diasOca: '2 a 3 días hábiles', diasCorreo: '2 a 4 días hábiles' },
      'Salta': { zona: 'Zona 1 — Cercana', oca: 2400, correo: 2000, diasOca: '2 a 3 días hábiles', diasCorreo: '2 a 4 días hábiles' },
      'Jujuy': { zona: 'Zona 1 — Cercana', oca: 2400, correo: 2000, diasOca: '2 a 3 días hábiles', diasCorreo: '2 a 4 días hábiles' },
      'Santiago del Estero': { zona: 'Zona 2 — Interior', oca: 2800, correo: 2400, diasOca: '2 a 4 días hábiles', diasCorreo: '3 a 5 días hábiles' },
      'Tucumán': { zona: 'Zona 2 — Interior', oca: 2800, correo: 2400, diasOca: '2 a 4 días hábiles', diasCorreo: '3 a 5 días hábiles' },
      'Catamarca': { zona: 'Zona 2 — Interior', oca: 3000, correo: 2600, diasOca: '3 a 5 días hábiles', diasCorreo: '3 a 6 días hábiles' },
      'Corrientes': { zona: 'Zona 2 — Interior', oca: 2800, correo: 2400, diasOca: '2 a 4 días hábiles', diasCorreo: '3 a 5 días hábiles' },
      'Entre Ríos': { zona: 'Zona 2 — Interior', oca: 3000, correo: 2600, diasOca: '3 a 5 días hábiles', diasCorreo: '3 a 6 días hábiles' },
      'Córdoba': { zona: 'Zona 2 — Interior', oca: 3200, correo: 2800, diasOca: '3 a 5 días hábiles', diasCorreo: '4 a 6 días hábiles' },
      'Santa Fe': { zona: 'Zona 2 — Interior', oca: 3200, correo: 2800, diasOca: '3 a 5 días hábiles', diasCorreo: '4 a 6 días hábiles' },
      'La Rioja': { zona: 'Zona 2 — Interior', oca: 3200, correo: 2800, diasOca: '3 a 5 días hábiles', diasCorreo: '4 a 6 días hábiles' },
      'San Juan': { zona: 'Zona 3 — Oeste', oca: 3600, correo: 3200, diasOca: '4 a 6 días hábiles', diasCorreo: '5 a 7 días hábiles' },
      'Mendoza': { zona: 'Zona 3 — Oeste', oca: 3600, correo: 3200, diasOca: '4 a 6 días hábiles', diasCorreo: '5 a 7 días hábiles' },
      'San Luis': { zona: 'Zona 3 — Oeste', oca: 3600, correo: 3200, diasOca: '4 a 6 días hábiles', diasCorreo: '5 a 7 días hábiles' },
      'Buenos Aires': { zona: 'Zona 3 — Centro', oca: 3800, correo: 3400, diasOca: '3 a 5 días hábiles', diasCorreo: '4 a 7 días hábiles' },
      'Ciudad de Buenos Aires': { zona: 'Zona 3 — CABA', oca: 3800, correo: 3400, diasOca: '3 a 5 días hábiles', diasCorreo: '4 a 7 días hábiles' },
      'La Pampa': { zona: 'Zona 3 — Centro', oca: 4000, correo: 3600, diasOca: '4 a 6 días hábiles', diasCorreo: '5 a 7 días hábiles' },
      'Neuquén': { zona: 'Zona 4 — Patagonia', oca: 4800, correo: 4400, diasOca: '5 a 7 días hábiles', diasCorreo: '6 a 8 días hábiles' },
      'Río Negro': { zona: 'Zona 4 — Patagonia', oca: 4800, correo: 4400, diasOca: '5 a 7 días hábiles', diasCorreo: '6 a 8 días hábiles' },
      'Chubut': { zona: 'Zona 4 — Patagonia', oca: 5200, correo: 4800, diasOca: '5 a 8 días hábiles', diasCorreo: '6 a 9 días hábiles' },
      'Santa Cruz': { zona: 'Zona 4 — Patagonia', oca: 5800, correo: 5400, diasOca: '6 a 9 días hábiles', diasCorreo: '7 a 10 días hábiles' },
      'Tierra del Fuego': { zona: 'Zona 4 — Patagonia', oca: 6500, correo: 6000, diasOca: '7 a 10 días hábiles', diasCorreo: '8 a 12 días hábiles' },
    };

    // ─── Tabs ───
    function cambiarTab(tab) {
      document.querySelectorAll('.calc-tab').forEach((t, i) => {
        t.classList.toggle('activo', (tab === 'cp' && i === 0) || (tab === 'manual' && i === 1));
      });
      document.getElementById('panel-cp').classList.toggle('activo', tab === 'cp');
      document.getElementById('panel-manual').classList.toggle('activo', tab === 'manual');
      document.getElementById('resultado').classList.remove('visible');
    }

    // ─── Buscar por CP ───
    let debounceTimer = null;
    document.getElementById('input-cp').addEventListener('input', function () {
      const cp = this.value.trim();
      document.getElementById('cp-provincia').value = '';
      document.getElementById('cp-localidad').value = '';
      document.getElementById('btn-calcular-cp').disabled = true;
      document.getElementById('resultado').classList.remove('visible');
      document.getElementById('error-cp').classList.remove('visible');
      document.getElementById('status-cp').textContent = '';
      if (cp.length < 4) return;
      document.getElementById('status-cp').textContent = 'Buscando...';
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(() => buscarCP(cp), 600);
    });

    async function buscarCP(cp) {
      try {
        const res = await fetch(`https://api.zippopotam.us/ar/${cp}`);
        if (res.ok) {
          const data = await res.json();
          if (data.places && data.places.length > 0) {
            const principal = data.places.find(p => p['state abbreviation'] === 'P') || data.places[0];
            document.getElementById('cp-provincia').value = principal.state;
            document.getElementById('cp-localidad').value = principal['place name'];
            document.getElementById('btn-calcular-cp').disabled = false;
            document.getElementById('status-cp').textContent = '✓ Ubicación encontrada';
            document.getElementById('cp-provincia').addEventListener('input', () => {
              document.getElementById('btn-calcular-cp').disabled = document.getElementById('cp-provincia').value.trim() === '';
            });
            return;
          }
        }
        document.getElementById('status-cp').textContent = '';
        document.getElementById('error-cp').classList.add('visible');
      } catch (e) {
        document.getElementById('status-cp').textContent = '';
        document.getElementById('error-cp').textContent = '⚠ Error de conexión. Probá con la otra opción.';
        document.getElementById('error-cp').classList.add('visible');
      }
    }

    // ─── Cargar localidades ───
    async function cargarLocalidades() {
      const provincia = document.getElementById('select-provincia').value;
      const selectLoc = document.getElementById('select-localidad');
      const loading = document.getElementById('loading-loc');

      document.getElementById('btn-calcular-manual').disabled = true;
      document.getElementById('resultado').classList.remove('visible');
      selectLoc.disabled = true;
      selectLoc.innerHTML = '<option value="">Cargando...</option>';

      if (!provincia) {
        selectLoc.innerHTML = '<option value="">— Primero elegí la provincia —</option>';
        return;
      }

      loading.style.display = 'block';

      try {
        const nombreAPI = provincia === 'Ciudad de Buenos Aires' ? 'Ciudad Autónoma de Buenos Aires' : provincia;
        const url = `https://apis.datos.gob.ar/georef/api/localidades?provincia=${encodeURIComponent(nombreAPI)}&max=500&orden=nombre&campos=nombre`;
        const res = await fetch(url);
        const data = await res.json();
        loading.style.display = 'none';

        if (data.localidades && data.localidades.length > 0) {
          const nombres = [...new Set(data.localidades.map(l => l.nombre))].sort();
          selectLoc.innerHTML = '<option value="">— Seleccioná tu localidad —</option>';
          nombres.forEach(nombre => {
            const opt = document.createElement('option');
            opt.value = nombre;
            opt.textContent = nombre;
            selectLoc.appendChild(opt);
          });
          selectLoc.disabled = false;
        } else {
          selectLoc.innerHTML = '<option value="">No se encontraron localidades</option>';
        }
      } catch (e) {
        loading.style.display = 'none';
        selectLoc.innerHTML = '<option value="">Error — intentá de nuevo</option>';
      }
    }

    function habilitarBotonManual() {
      document.getElementById('btn-calcular-manual').disabled = document.getElementById('select-localidad').value === '';
    }

    // ─── Mostrar resultado ───
    function mostrarResultado(modo) {
      const provincia = modo === 'cp'
        ? document.getElementById('cp-provincia').value
        : document.getElementById('select-provincia').value;

      const provNorm = provincia.trim().replace(/\b\w/g, l => l.toUpperCase());
      let zonaData = null;
      for (const key of Object.keys(ZONAS)) {
        if (provNorm.toUpperCase().includes(key.toUpperCase()) || key.toUpperCase().includes(provNorm.toUpperCase())) {
          zonaData = ZONAS[key];
          break;
        }
      }
      if (!zonaData) {
        zonaData = { zona: 'Zona 3 — Interior', oca: 3800, correo: 3400, diasOca: '3 a 6 días hábiles', diasCorreo: '4 a 7 días hábiles' };
      }

      document.getElementById('resultado-zona').textContent = zonaData.zona;
      document.getElementById('precio-oca').textContent = '$' + zonaData.oca.toLocaleString('es-AR');
      document.getElementById('precio-correo').textContent = '$' + zonaData.correo.toLocaleString('es-AR');
      document.getElementById('dias-oca').textContent = zonaData.diasOca;
      document.getElementById('dias-correo').textContent = zonaData.diasCorreo;
      document.getElementById('resultado').classList.add('visible');
      document.getElementById('resultado').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
  </script>
</body>

</html>