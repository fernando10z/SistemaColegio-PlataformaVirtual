<?php

session_start();
require_once 'Sistemas/conexion/bd.php';

// Obtener datos del colegio (id = 1)
$colegio_nombre = '';
$colegio_ruc    = '';
$colegio_foto   = '';

try {
    $stmt_cp = $conexion->prepare("SELECT nombre, ruc, foto, direccion, refran FROM colegio_principal WHERE id = 1 LIMIT 1");
    $stmt_cp->execute();
    $colegio = $stmt_cp->fetch(PDO::FETCH_ASSOC);
    if ($colegio) {
        $colegio_nombre = isset($colegio['nombre']) ? $colegio['nombre'] : '';
        $colegio_ruc    = isset($colegio['ruc']) ? $colegio['ruc'] : '';
        $colegio_foto   = isset($colegio['foto']) ? $colegio['foto'] : '';
        $colegio_direccion = isset($colegio['direccion']) ? $colegio['direccion'] : '';
        $refran = isset($colegio['refran']) ? $colegio['refran'] : '';
    }
} catch (PDOException $e) {
    error_log("Error fetching colegio_principal: " . $e->getMessage());
}

// Variables solicitadas (nombre, ruc, foto)
$nombre = $colegio_nombre;
$ruc    = $colegio_ruc;
$foto   = $colegio_foto;
$direccion = $colegio_direccion;
$refran = $refran;

$error_message = "";
$success_message = "";

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);
    
    // Validaciones básicas
    if (empty($email) || empty($password)) {
        $error_message = "Por favor, complete todos los campos.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Por favor, ingrese un email válido.";
    } else {
        try {
            // Consulta para obtener el usuario por email
            $stmt = $conexion->prepare("
                SELECT id, codigo_usuario, username, email, password_hash, 
                       nombres, apellidos, rol_id, activo, 
                       foto_url, telefono
                FROM usuarios 
                WHERE email = :email 
                LIMIT 1
            ");
            
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuario) {
                // Verificar si el usuario está activo
                if ($usuario['activo'] != 1) {
                    $error_message = "Su cuenta está inactiva. Contacte al administrador.";
                } else {
                    // Verificar la contraseña
                    if (password_verify($password, $usuario['password_hash'])) {
                        // Login exitoso - Crear sesión
                        $_SESSION['usuario_id'] = $usuario['id'];
                        $_SESSION['codigo_usuario'] = $usuario['codigo_usuario'];
                        $_SESSION['username'] = $usuario['username'];
                        $_SESSION['email'] = $usuario['email'];
                        $_SESSION['nombres'] = $usuario['nombres'];
                        $_SESSION['apellidos'] = $usuario['apellidos'];
                        $_SESSION['rol_id'] = $usuario['rol_id'];
                        $_SESSION['foto_url'] = $usuario['foto_url'];
                        $_SESSION['telefono'] = $usuario['telefono'];
                        $_SESSION['login_time'] = time();
                        
                        // Actualizar último acceso (opcional)
                        $update_stmt = $conexion->prepare("
                            UPDATE usuarios 
                            SET ultimo_acceso = NOW() 
                            WHERE id = :id
                        ");
                        $update_stmt->bindParam(':id', $usuario['id'], PDO::PARAM_INT);
                        $update_stmt->execute();
                        
                        // Redirigir según el rol
                        switch ($usuario['rol_id']) {
                            case 2: // Director
                                header("Location: Sistemas/index.php");
                                break;
                            case 3: // Subdirector
                                header("Location: Sistemas/index.php");
                                break;
                            case 4: // Docente
                                header("Location: Sistemas/index.php");
                                break;
                            case 6: // Apoderado
                                header("Location: Sistemas/index.php");
                                break;
                            default:
                                header("Location: Sistemas/index.php");
                        }
                        exit();
                    } else {
                        $error_message = "Credenciales incorrectas.";
                    }
                }
            } else {
                $error_message = "Credenciales incorrectas.";
            }
            
        } catch (PDOException $e) {
            $error_message = "Error del sistema. Intente nuevamente.";
            // Log del error para desarrollo
            error_log("Error de login: " . $e->getMessage());
        }
    }
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso - <?php echo $nombre?></title>
    <?php
    $favicon = !empty($foto) ? htmlspecialchars($foto) : 'assets/favicons/favicon-32x32.png';
    ?>
    <link rel="short icon" type="image/png" sizes="32x32" href="<?php echo $favicon; ?>">
    <meta name="theme-color" content="#1a5f7a">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            /* Colores institucionales del escudo */
            --primary: #1a5f7a;
            --primary-dark: #134556;
            --primary-light: #2a7a96;
            --accent: #c8102e;
            --accent-hover: #a00d25;
            --gold: #d4af37;
            
            /* Neutrales */
            --surface: #ffffff;
            --gray-50: #fafafa;
            --gray-100: #f5f5f5;
            --gray-200: #eeeeee;
            --gray-300: #e0e0e0;
            --gray-400: #bdbdbd;
            --gray-500: #9e9e9e;
            --gray-600: #757575;
            --gray-700: #616161;
            --gray-800: #424242;
            --gray-900: #212121;
            
            /* Estados */
            --error: #d32f2f;
            --error-light: #ffebee;
            --success: #388e3c;
            --success-light: #e8f5e9;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Inter', 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            min-height: 100vh;
            display: flex;
            background: var(--gray-50);
            color: var(--gray-900);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            width: 100%;
            display: flex;
            min-height: 100vh;
        }

        /* Panel izquierdo - Institucional */
        .left-panel {
            flex: 1;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px 40px;
            overflow: hidden;
        }

        /* Patrón sutil de fondo */
        .left-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: 
                radial-gradient(circle at 20% 30%, rgba(255,255,255,0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(255,255,255,0.06) 0%, transparent 50%);
            pointer-events: none;
        }

        .institutional-content {
            position: relative;
            z-index: 1;
            text-align: center;
            color: white;
            max-width: 480px;
            animation: fadeInUp 0.8s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Logo institucional */
        .school-logo {
            width: 140px;
            height: 140px;
            margin: 0 auto 32px;
            background: white;
            border-radius: 16px;
            padding: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .school-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .school-name {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 12px;
            letter-spacing: -0.3px;
            line-height: 1.3;
        }

        .school-motto {
            font-size: 16px;
            opacity: 0.9;
            font-weight: 400;
            margin-bottom: 40px;
            line-height: 1.5;
        }

        .institutional-info {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 24px;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            opacity: 0.95;
        }

        .info-icon {
            width: 20px;
            height: 20px;
            opacity: 0.8;
        }

        /* Panel derecho - Formulario */
        .right-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            background: white;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
        }

        .login-header {
            margin-bottom: 40px;
        }

        .login-header h2 {
            font-size: 26px;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 8px;
            letter-spacing: -0.3px;
        }

        .login-header p {
            color: var(--gray-600);
            font-size: 15px;
        }

        /* Mensajes */
        .alert {
            padding: 14px 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 14px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            animation: slideIn 0.3s ease-out;
            line-height: 1.5;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-error {
            background: var(--error-light);
            color: var(--error);
            border: 1px solid #ffcdd2;
        }

        .alert-success {
            background: var(--success-light);
            color: var(--success);
            border: 1px solid #c8e6c9;
        }

        .alert svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
            margin-top: 1px;
        }

        /* Formulario */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            font-size: 15px;
            border: 1.5px solid var(--gray-300);
            background: var(--surface);
            border-radius: 8px;
            transition: all 0.2s ease;
            outline: none;
            color: var(--gray-900);
        }

        .form-input::placeholder {
            color: var(--gray-400);
        }

        .form-input:hover {
            border-color: var(--gray-400);
        }

        .form-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(26, 95, 122, 0.1);
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray-500);
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
            border-radius: 4px;
        }

        .password-toggle:hover {
            color: var(--gray-700);
            background: var(--gray-100);
        }

        .password-toggle svg {
            width: 20px;
            height: 20px;
        }

        /* Opciones del formulario */
        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
            font-size: 14px;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .checkbox {
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .checkbox-label {
            color: var(--gray-700);
            cursor: pointer;
            user-select: none;
        }

        .link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .link:hover {
            color: var(--primary-dark);
        }

        /* Botón principal */
        .btn-primary {
            width: 100%;
            padding: 13px 24px;
            font-size: 15px;
            font-weight: 600;
            color: white;
            background: var(--accent);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover {
            background: var(--accent-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(200, 16, 46, 0.3);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .btn-primary.loading {
            color: transparent;
        }

        .btn-primary.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin: -10px 0 0 -10px;
            border: 2px solid transparent;
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Footer */
        .footer {
            margin-top: 48px;
            padding-top: 24px;
            border-top: 1px solid var(--gray-200);
            text-align: center;
            font-size: 13px;
            color: var(--gray-500);
        }

        .footer p {
            margin-bottom: 4px;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .left-panel {
                display: none;
            }
            
            .right-panel {
                background: var(--gray-50);
            }
            
            .login-container {
                background: white;
                padding: 40px;
                border-radius: 16px;
                box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            }

            /* Mostrar logo en mobile */
            .login-container::before {
                content: '';
                display: block;
                width: 80px;
                height: 80px;
                margin: 0 auto 32px;
                background: url('<?php echo htmlspecialchars($foto); ?>') center/contain no-repeat;
            }
        }

        @media (max-width: 480px) {
            .right-panel {
                padding: 20px;
            }
            
            .login-container {
                padding: 32px 24px;
            }
            
            .form-options {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start;
            }

            .login-header h2 {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Panel izquierdo - Institucional -->
        <div class="left-panel">
            <div class="institutional-content">
                <div class="school-logo">
                    <img src="<?php echo htmlspecialchars($foto); ?>" alt="Escudo de <?php echo htmlspecialchars($nombre); ?>">
                </div>
                
                <h1 class="school-name">
                    <?php echo htmlspecialchars($nombre); ?>
                </h1>
                
                <p class="school-motto">
                    Sistema de Gestión Educativa Digital
                </p>
                
                <div class="institutional-info">
                    <div class="info-item">
                        <svg class="info-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                        <span><?php echo htmlspecialchars($direccion); ?></span>
                    </div>
                    <div class="info-item">
                        <svg class="info-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                        </svg>
                        <span><?php echo htmlspecialchars($refran); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel derecho - Formulario -->
        <div class="right-panel">
            <div class="login-container">
                <div class="login-header">
                    <h2>Iniciar sesión</h2>
                    <p>Ingresa tus credenciales para acceder</p>
                </div>

                <!-- Mensajes de error/éxito -->
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-error">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <span><?php echo htmlspecialchars($error_message); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span><?php echo htmlspecialchars($success_message); ?></span>
                    </div>
                <?php endif; ?>

                <!-- Formulario -->
                <form id="loginForm" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <div class="form-group">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <div class="input-wrapper">
                            <input 
                                type="email" 
                                class="form-input" 
                                id="email" 
                                name="email"
                                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                placeholder="nombre@aac.edu.pe"
                                required
                                autocomplete="email"
                                autofocus
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="input-wrapper">
                            <input 
                                type="password" 
                                class="form-input" 
                                id="password" 
                                name="password"
                                placeholder="Ingresa tu contraseña"
                                required
                                autocomplete="current-password"
                            >
                            <button type="button" class="password-toggle" id="togglePassword" aria-label="Mostrar contraseña">
                                <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg class="eye-off-icon" style="display: none;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-options">
                        <div class="checkbox-wrapper">
                            <input type="checkbox" id="remember" name="remember" class="checkbox">
                            <label for="remember" class="checkbox-label">Recordarme</label>
                        </div>
                        <a href="forgot_password.php" class="link">¿Olvidaste tu contraseña?</a>
                    </div>

                    <button type="submit" class="btn-primary" id="submitBtn">
                        Iniciar sesión
                    </button>
                </form>

                <div class="footer">
                    <p><strong>I.E. <?php echo htmlspecialchars($nombre); ?></strong></p>
                    <p>© <?php echo date('Y'); ?> Todos los derechos reservados</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = togglePassword.querySelector('.eye-icon');
        const eyeOffIcon = togglePassword.querySelector('.eye-off-icon');

        togglePassword.addEventListener('click', () => {
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
            
            if (type === 'text') {
                eyeIcon.style.display = 'none';
                eyeOffIcon.style.display = 'block';
            } else {
                eyeIcon.style.display = 'block';
                eyeOffIcon.style.display = 'none';
            }
        });

        // Form submission
        const loginForm = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');

        loginForm.addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();

            if (!email || !password) {
                e.preventDefault();
                return;
            }

            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
            submitBtn.textContent = '';
        });

        // Auto-hide alerts
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.animation = 'slideIn 0.3s ease-out reverse';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
    </script>
</body>
</html>