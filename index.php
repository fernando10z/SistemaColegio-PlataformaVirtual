<?php

session_start();
require_once 'Sistemas/conexion/bd.php';

// Obtener datos del colegio (id = 1)
$colegio_nombre = '';
$colegio_ruc    = '';
$colegio_foto   = '';

try {
    $stmt_cp = $conexion->prepare("SELECT nombre, ruc, foto FROM colegio_principal WHERE id = 1 LIMIT 1");
    $stmt_cp->execute();
    $colegio = $stmt_cp->fetch(PDO::FETCH_ASSOC);
    if ($colegio) {
        $colegio_nombre = isset($colegio['nombre']) ? $colegio['nombre'] : '';
        $colegio_ruc    = isset($colegio['ruc']) ? $colegio['ruc'] : '';
        $colegio_foto   = isset($colegio['foto']) ? $colegio['foto'] : '';
    }
} catch (PDOException $e) {
    error_log("Error fetching colegio_principal: " . $e->getMessage());
}

// Variables solicitadas (nombre, ruc, foto)
$nombre = $colegio_nombre;
$ruc    = $colegio_ruc;
$foto   = $colegio_foto;

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
    <meta name="theme-color" content="#1e293b">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #1e293b;
            --primary-dark: #0f172a;
            --accent: #3b82f6;
            --accent-hover: #2563eb;
            --surface: #ffffff;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --error: #ef4444;
            --success: #22c55e;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Inter', 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            background: var(--surface);
            color: var(--primary);
            line-height: 1.6;
        }

        .container {
            width: 100%;
            display: flex;
            min-height: 100vh;
        }

        /* Panel izquierdo - Visual */
        .left-panel {
            flex: 1.2;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px;
            overflow: hidden;
        }

        /* Patrón geométrico minimalista */
        .left-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: 
                radial-gradient(circle at 25% 25%, rgba(255,255,255,0.2) 0%, transparent 50%),
                radial-gradient(circle at 75% 75%, rgba(255,255,255,0.15) 0%, transparent 50%);
            background-size: 100% 100%;
        }

        .left-panel::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50%;
            background: linear-gradient(to top, rgba(15,23,42,0.2), transparent);
        }

        .brand-container {
            position: relative;
            z-index: 1;
            text-align: center;
            color: white;
            animation: fadeInUp 0.8s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .brand-icon {
            width: 100px;
            height: 100px;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 32px;
            border: 1px solid rgba(255,255,255,0.3);
        }

        .brand-icon svg {
            width: 56px;
            height: 56px;
            color: white;
        }

        .brand-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 16px;
            letter-spacing: -0.5px;
        }

        .brand-subtitle {
            font-size: 18px;
            opacity: 0.9;
            font-weight: 400;
            margin-bottom: 48px;
        }

        .features {
            display: flex;
            flex-direction: column;
            gap: 24px;
            max-width: 400px;
        }

        .feature {
            display: flex;
            align-items: center;
            gap: 16px;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .feature-icon {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .feature-text {
            flex: 1;
        }

        .feature-text h3 {
            font-size: 16px;
            margin-bottom: 4px;
            font-weight: 600;
        }

        .feature-text p {
            font-size: 14px;
            opacity: 0.9;
            line-height: 1.4;
        }

        /* Panel derecho - Formulario */
        .right-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            background: var(--gray-50);
        }

        .login-container {
            width: 100%;
            max-width: 440px;
        }

        .login-header {
            margin-bottom: 40px;
        }

        .login-header h2 {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 8px;
        }

        .login-header p {
            color: var(--gray-500);
            font-size: 16px;
        }

        /* Mensajes */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-10px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        /* Formulario */
        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: var(--gray-700);
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            font-size: 15px;
            border: 1px solid var(--gray-200);
            background: var(--surface);
            border-radius: 8px;
            transition: all 0.2s ease;
            outline: none;
        }

        .form-input:hover {
            border-color: var(--gray-300);
        }

        .form-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray-400);
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
        }

        .password-toggle:hover {
            color: var(--gray-600);
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
            accent-color: var(--accent);
            cursor: pointer;
        }

        .checkbox-label {
            color: var(--gray-600);
            cursor: pointer;
            user-select: none;
        }

        .link {
            color: var(--accent);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .link:hover {
            color: var(--accent-hover);
        }

        /* Botón principal */
        .btn-primary {
            width: 100%;
            padding: 12px 24px;
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
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-primary:disabled {
            opacity: 0.5;
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

        /* Separador */
        .divider {
            display: flex;
            align-items: center;
            margin: 32px 0;
            gap: 16px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--gray-200);
        }

        .divider span {
            color: var(--gray-400);
            font-size: 13px;
            font-weight: 500;
        }

        /* Link de registro */
        .register-section {
            text-align: center;
            padding: 24px;
            background: var(--surface);
            border-radius: 8px;
            border: 1px solid var(--gray-200);
        }

        .register-section p {
            color: var(--gray-600);
            font-size: 15px;
            margin-bottom: 8px;
        }

        .register-section .link {
            font-size: 16px;
        }

        /* Footer */
        .footer {
            margin-top: 48px;
            text-align: center;
            font-size: 13px;
            color: var(--gray-400);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .left-panel {
                display: none;
            }
            
            .right-panel {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }
            
            .login-container {
                background: var(--surface);
                padding: 40px;
                border-radius: 16px;
                box-shadow: 0 20px 40px rgba(0,0,0,0.1);
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
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Panel izquierdo - Visual -->
        <div class="left-panel">
            <div class="brand-container">
                <div class="brand-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <path d="M12 2L2 7L12 12L22 7L12 2Z"></path>
                        <path d="M2 17L12 22L22 17"></path>
                        <path d="M2 12L12 17L22 12"></path>
                    </svg>
                </div>
                <h1 class="brand-title">Bienvenido de vuelta</h1>
                <p class="brand-subtitle">Sistema de Gestión Educativa</p>
                
                <div class="features">
                    <div class="feature">
                        <div class="feature-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                                <line x1="8" y1="21" x2="16" y2="21"></line>
                                <line x1="12" y1="17" x2="12" y2="21"></line>
                            </svg>
                        </div>
                        <div class="feature-text">
                            <h3>Plataforma Digital</h3>
                            <p>Acceso completo al sistema educativo desde cualquier dispositivo</p>
                        </div>
                    </div>
                    
                    <div class="feature">
                        <div class="feature-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <div class="feature-text">
                            <h3>Comunidad Educativa</h3>
                            <p>Conecta con docentes, estudiantes y apoderados en un solo lugar</p>
                        </div>
                    </div>
                    
                    <div class="feature">
                        <div class="feature-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                        </div>
                        <div class="feature-text">
                            <h3>Seguro y Confiable</h3>
                            <p>Tus datos están protegidos con los más altos estándares de seguridad</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel derecho - Formulario -->
        <div class="right-panel">
            <div class="login-container">
                <div class="login-header">
                    <h2>Iniciar sesión</h2>
                    <p>Ingresa tus credenciales para acceder al sistema</p>
                </div>

                <!-- Mensajes de error/éxito -->
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-error">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <?php echo htmlspecialchars($success_message); ?>
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
                            <button type="button" class="password-toggle" id="togglePassword">
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

                <!-- <div class="divider">
                    <span>¿Eres nuevo?</span>
                </div>

                <div class="register-section">
                    <p>¿Primera vez en nuestra plataforma?</p>
                    <a href="registro.php" class="link">Solicita tu acceso aquí</a>
                </div> -->

                <div class="footer">
                    <p>© <?php echo date('Y'); ?> I.E. Andrés Avelino Cáceres</p>
                    <p>Todos los derechos reservados</p>
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