<?php
session_start();
require_once __DIR__ . '/config/Database.php';

if (isset($_SESSION['cliente_id'])) {
    header('Location: mi-cuenta.php');
    exit;
}

$error = '';
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre    = trim($_POST['nombre'] ?? '');
    $apellido  = trim($_POST['apellido'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $telefono  = trim($_POST['telefono'] ?? '');
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if (!$nombre)                                    $errores['nombre']    = 'El nombre es obligatorio.';
    elseif (strlen($nombre) < 2)                     $errores['nombre']    = 'Mínimo 2 caracteres.';

    if (!$apellido)                                  $errores['apellido']  = 'El apellido es obligatorio.';
    elseif (strlen($apellido) < 2)                   $errores['apellido']  = 'Mínimo 2 caracteres.';

    if (!$email)                                     $errores['email']     = 'El email es obligatorio.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores['email']  = 'El email no es válido.';

    if ($telefono && !preg_match('/^[\d\s\+\-\(\)]{7,20}$/', $telefono))
        $errores['telefono']  = 'Teléfono no válido.';

    if (!$password)                                  $errores['password']  = 'La contraseña es obligatoria.';
    elseif (strlen($password) < 8)                   $errores['password']  = 'Mínimo 8 caracteres.';
    elseif (!preg_match('/[A-Z]/', $password))       $errores['password']  = 'Debe tener al menos una mayúscula.';
    elseif (!preg_match('/[0-9]/', $password))       $errores['password']  = 'Debe tener al menos un número.';

    if (!$password2)                                 $errores['password2'] = 'Repetí la contraseña.';
    elseif ($password !== $password2)                $errores['password2'] = 'Las contraseñas no coinciden.';

    if (empty($errores)) {
        $conexion = Database::getConexion();
        $stmt = $conexion->prepare("SELECT id FROM clientes WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errores['email'] = 'Ya existe una cuenta con ese email.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conexion->prepare("INSERT INTO clientes (nombre, apellido, email, password, telefono) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $nombre, $apellido, $email, $hash, $telefono);

            $stmt->execute();
            $id = $conexion->insert_id;
            // Email de bienvenida
            require_once __DIR__ . '/includes/emails.php';
            emailBienvenida(['nombre' => $nombre, 'email' => $email]);
            $_SESSION['cliente_id']     = $id;
            $_SESSION['cliente_nombre'] = $nombre;
            $_SESSION['cliente_email']  = $email;
            header('Location: mi-cuenta.php');
            exit;
        }
    }
}

function err(string $campo): void
{
    global $errores;
    if (isset($errores[$campo])) {
        echo '<span class="field-error">⚠ ' . htmlspecialchars($errores[$campo]) . '</span>';
    }
}
function val(string $campo): void
{
    echo htmlspecialchars($_POST[$campo] ?? '');
}
function hasErr(string $campo): string
{
    global $errores;
    return isset($errores[$campo]) ? 'error' : '';
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta — Marlene STORE</title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Cormorant+Garamond:ital,wght@0,400;0,600&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        * {
            box-sizing: border-box;
        }

        .auth-wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--crema2);
            padding: 40px 24px;
        }

        .auth-card {
            background: white;
            border-radius: 16px;
            padding: 48px 40px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 20px 60px rgba(92, 61, 62, 0.1);
        }

        .auth-logo {
            text-align: center;
            margin-bottom: 32px;
        }

        .auth-logo a {
            text-decoration: none;
        }

        .auth-logo .logo-script {
            font-family: 'Great Vibes', cursive;
            font-size: 2.8rem;
            color: var(--marron);
            display: block;
        }

        .auth-logo .logo-store {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.75rem;
            font-weight: 900;
            letter-spacing: 5px;
            color: var(--dorado);
            display: block;
            margin-top: -10px;
        }

        .auth-titulo {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.5rem;
            color: var(--marron);
            text-align: center;
            margin-bottom: 8px;
        }

        .auth-subtitulo {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.7rem;
            color: #999;
            text-align: center;
            margin-bottom: 28px;
            letter-spacing: 1px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
            margin-bottom: 14px;
        }

        .form-group.full {
            grid-column: 1/-1;
        }

        .form-group label {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.58rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--marron);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .form-group label .req {
            color: var(--dorado);
        }

        .form-group input {
            padding: 11px 14px;
            border: 1.5px solid rgba(200, 152, 154, 0.3);
            border-radius: 6px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.85rem;
            color: var(--marron);
            transition: border-color 0.2s, box-shadow 0.2s;
            width: 100%;
            background: #FDFAF8;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--dorado);
            box-shadow: 0 0 0 3px rgba(201, 169, 110, 0.15);
            background: white;
        }

        .form-group input.error {
            border-color: #DC2626;
            background: #FFF8F8;
        }

        .form-group input.valido {
            border-color: #16A34A;
            background: #F8FFF8;
        }

        .field-error {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.62rem;
            color: #DC2626;
            font-weight: 600;
        }

        .password-wrap {
            position: relative;
        }

        .password-wrap input {
            padding-right: 44px;
        }

        .toggle-pass {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            color: #999;
            padding: 0;
        }

        .password-strength {
            height: 4px;
            border-radius: 99px;
            background: #eee;
            margin-top: 6px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            border-radius: 99px;
            width: 0%;
            transition: width 0.3s, background 0.3s;
        }

        .strength-text {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.58rem;
            color: #999;
            margin-top: 4px;
        }

        .requisitos {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4px;
            margin-top: 8px;
        }

        .req-item {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.58rem;
            color: #999;
            display: flex;
            align-items: center;
            gap: 4px;
            transition: color 0.2s;
        }

        .req-item.ok {
            color: #16A34A;
        }

        .req-item .req-icon {
            font-size: 0.7rem;
        }

        .btn-registrar {
            width: 100%;
            background: var(--marron);
            color: var(--crema);
            border: none;
            padding: 16px;
            border-radius: 6px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            cursor: pointer;
            margin-top: 8px;
            transition: background 0.3s, transform 0.2s;
            position: relative;
        }

        .btn-registrar:hover {
            background: var(--dorado);
            transform: translateY(-1px);
        }

        .btn-registrar:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 20px 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(200, 152, 154, 0.2);
        }

        .divider span {
            font-size: 0.65rem;
            color: #ccc;
            font-family: 'Montserrat', sans-serif;
        }

        .auth-footer {
            text-align: center;
            font-size: 0.8rem;
            color: #999;
        }

        .auth-footer a {
            color: var(--marron);
            font-weight: 700;
            text-decoration: none;
        }

        .auth-footer a:hover {
            color: var(--dorado);
        }

        .error-banner {
            background: #FEF2F2;
            border: 1px solid #FECACA;
            color: #991B1B;
            padding: 12px 16px;
            border-radius: 6px;
            font-size: 0.8rem;
            margin-bottom: 20px;
            text-align: center;
            font-family: 'Montserrat', sans-serif;
        }

        @media (max-width: 480px) {
            .auth-card {
                padding: 32px 24px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="auth-wrap">
        <div class="auth-card">

            <div class="auth-logo">
                <a href="index.php">
                    <span class="logo-script">Marlene</span>
                    <span class="logo-store">STORE</span>
                </a>
            </div>

            <h1 class="auth-titulo">Crear cuenta</h1>
            <p class="auth-subtitulo">Completá tus datos para registrarte</p>

            <?php if (!empty($errores) && $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                <div class="error-banner">⚠️ Revisá los campos marcados en rojo.</div>
            <?php endif; ?>

            <form method="POST" id="form-registro" novalidate>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nombre <span class="req">*</span></label>
                        <input type="text" name="nombre" id="nombre"
                            class="<?= hasErr('nombre') ?>"
                            value="<?php val('nombre') ?>"
                            placeholder="María"
                            autocomplete="given-name">
                        <?php err('nombre') ?>
                    </div>
                    <div class="form-group">
                        <label>Apellido <span class="req">*</span></label>
                        <input type="text" name="apellido" id="apellido"
                            class="<?= hasErr('apellido') ?>"
                            value="<?php val('apellido') ?>"
                            placeholder="González"
                            autocomplete="family-name">
                        <?php err('apellido') ?>
                    </div>
                </div>

                <div class="form-group">
                    <label>Email <span class="req">*</span></label>
                    <input type="email" name="email" id="email"
                        class="<?= hasErr('email') ?>"
                        value="<?php val('email') ?>"
                        placeholder="maria@ejemplo.com"
                        autocomplete="email">
                    <?php err('email') ?>
                </div>

                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="tel" name="telefono" id="telefono"
                        class="<?= hasErr('telefono') ?>"
                        value="<?php val('telefono') ?>"
                        placeholder="3704 123456"
                        autocomplete="tel">
                    <?php err('telefono') ?>
                </div>

                <div class="form-group">
                    <label>Contraseña <span class="req">*</span></label>
                    <div class="password-wrap">
                        <input type="password" name="password" id="password"
                            class="<?= hasErr('password') ?>"
                            placeholder="Mínimo 8 caracteres"
                            autocomplete="new-password">
                        <button type="button" class="toggle-pass" onclick="togglePass('password', this)">👁</button>
                    </div>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="strength-bar"></div>
                    </div>
                    <div class="strength-text" id="strength-text"></div>
                    <div class="requisitos">
                        <span class="req-item" id="req-len"><span class="req-icon">○</span> 8 caracteres</span>
                        <span class="req-item" id="req-upper"><span class="req-icon">○</span> Una mayúscula</span>
                        <span class="req-item" id="req-num"><span class="req-icon">○</span> Un número</span>
                        <span class="req-item" id="req-special"><span class="req-icon">○</span> Un símbolo (opcional)</span>
                    </div>
                    <?php err('password') ?>
                </div>

                <div class="form-group">
                    <label>Repetir contraseña <span class="req">*</span></label>
                    <div class="password-wrap">
                        <input type="password" name="password2" id="password2"
                            class="<?= hasErr('password2') ?>"
                            placeholder="Repetí tu contraseña"
                            autocomplete="new-password">
                        <button type="button" class="toggle-pass" onclick="togglePass('password2', this)">👁</button>
                    </div>
                    <span class="field-error" id="match-error" style="display:none;">⚠ Las contraseñas no coinciden.</span>
                    <?php err('password2') ?>
                </div>

                <button type="submit" class="btn-registrar" id="btn-submit">
                    Crear cuenta →
                </button>
            </form>

            <div class="divider"><span>¿ya tenés cuenta?</span></div>

            <div class="auth-footer">
                <a href="login-cliente.php">Iniciá sesión acá</a>
            </div>
        </div>
    </div>

    <script>
        // ─── Mostrar/ocultar contraseña ───
        function togglePass(id, btn) {
            const input = document.getElementById(id);
            if (input.type === 'password') {
                input.type = 'text';
                btn.textContent = '🙈';
            } else {
                input.type = 'password';
                btn.textContent = '👁';
            }
        }

        // ─── Fuerza de contraseña ───
        document.getElementById('password').addEventListener('input', function() {
            const val = this.value;
            const bar = document.getElementById('strength-bar');
            const text = document.getElementById('strength-text');

            const len = val.length >= 8;
            const upper = /[A-Z]/.test(val);
            const num = /[0-9]/.test(val);
            const special = /[^A-Za-z0-9]/.test(val);

            // Actualizar requisitos
            setReq('req-len', len);
            setReq('req-upper', upper);
            setReq('req-num', num);
            setReq('req-special', special);

            // Calcular fuerza
            const score = [len, upper, num, special].filter(Boolean).length;
            const colores = ['', '#EF4444', '#F59E0B', '#3B82F6', '#16A34A'];
            const textos = ['', 'Muy débil', 'Débil', 'Buena', 'Fuerte'];

            bar.style.width = val.length === 0 ? '0%' : (score * 25) + '%';
            bar.style.background = colores[score] || '#eee';
            text.textContent = val.length === 0 ? '' : textos[score];
            text.style.color = colores[score] || '#999';

            checkMatch();
        });

        function setReq(id, ok) {
            const el = document.getElementById(id);
            el.classList.toggle('ok', ok);
            el.querySelector('.req-icon').textContent = ok ? '✓' : '○';
        }

        // ─── Validar coincidencia en tiempo real ───
        document.getElementById('password2').addEventListener('input', checkMatch);

        function checkMatch() {
            const p1 = document.getElementById('password').value;
            const p2 = document.getElementById('password2').value;
            const err = document.getElementById('match-error');
            const input = document.getElementById('password2');

            if (p2.length === 0) {
                err.style.display = 'none';
                input.classList.remove('error', 'valido');
                return;
            }

            if (p1 === p2) {
                err.style.display = 'none';
                input.classList.remove('error');
                input.classList.add('valido');
            } else {
                err.style.display = 'block';
                input.classList.remove('valido');
                input.classList.add('error');
            }
        }

        // ─── Validación en tiempo real de campos ───
        ['nombre', 'apellido', 'telefono'].forEach(id => {
            const input = document.getElementById(id);
            if (!input) return;
            input.addEventListener('blur', function() {
                if (this.value.trim().length > 0) {
                    this.classList.remove('error');
                    this.classList.add('valido');
                }
            });
        });

        document.getElementById('email').addEventListener('blur', function() {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (re.test(this.value)) {
                this.classList.remove('error');
                this.classList.add('valido');
            } else if (this.value.length > 0) {
                this.classList.add('error');
                this.classList.remove('valido');
            }
        });
    </script>
</body>

</html>