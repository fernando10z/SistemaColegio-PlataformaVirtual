<?php

require_once 'Sistemas/conexion/bd.php';



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
    <title>Login - ANDRÉS AVELINO CÁCERES</title>
    <link rel="shortcut icon" type="image/png" href="assets/images/logos/favicon.png" />
    <link rel="stylesheet" href="assets/css/styles.min.css" />
    <style>
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .password-toggle {
            position: relative;
        }
        .password-toggle-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        .loading .btn {
            position: relative;
        }
        .loading .btn::after {
            content: "";
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border: 2px solid transparent;
            border-top: 2px solid #ffffff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }
        .logo-container {
            text-align: center;
            margin-bottom: 2rem;
        }
        .logo-text {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2c3e50;
            text-decoration: none;
        }
        .logo-icon {
            vertical-align: middle;
            font-size: 2rem;
            margin-right: 8px;
            color: #3498db;
        }
    </style>
</head>

<body>
    <!--  Body Wrapper -->
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" 
         data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
        <div class="position-relative overflow-hidden text-bg-light min-vh-100 d-flex align-items-center justify-content-center">
            <div class="d-flex align-items-center justify-content-center w-100">
                <div class="row justify-content-center w-100">
                    <div class="col-md-8 col-lg-6 col-xxl-4">
                        <div class="card mb-0 shadow">
                            <div class="card-body p-4">
                                <div class="logo-container">
                                    <a href="./login.php" class="logo-text">
                                        <iconify-icon icon="mdi:school" class="logo-icon"></iconify-icon>
                                        ANDRÉS AVELINO CÁCERES
                                    </a>
                                </div>
                                
                                <p class="text-center">Iniciar Sesion</p>
                                
                                <!-- Mostrar mensajes de error o éxito -->
                                <?php if (!empty($error_message)): ?>
                                    <div class="alert alert-danger" role="alert">
                                        <iconify-icon icon="mdi:alert-circle"></iconify-icon>
                                        <?php echo htmlspecialchars($error_message); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($success_message)): ?>
                                    <div class="alert alert-success" role="alert">
                                        <iconify-icon icon="mdi:check-circle"></iconify-icon>
                                        <?php echo htmlspecialchars($success_message); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <form id="loginForm" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Correo Electrónico:</label>
                                        <input type="email" 
                                               class="form-control" 
                                               id="email" 
                                               name="email"
                                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                               required
                                               autocomplete="email"
                                               placeholder="ejemplo@aac.edu.pe">
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="password" class="form-label">Contraseña:</label>
                                        <div class="password-toggle">
                                            <input type="password" 
                                                   class="form-control" 
                                                   id="password" 
                                                   name="password"
                                                   required
                                                   autocomplete="current-password"
                                                   placeholder="Ingrese su contraseña">
                                            <iconify-icon icon="mdi:eye" class="password-toggle-icon" id="togglePassword"></iconify-icon>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <div class="form-check">
                                            <input class="form-check-input primary" 
                                                   type="checkbox" 
                                                   id="remember" 
                                                   name="remember">
                                            <label class="form-check-label text-dark" for="remember">
                                                Recordar este dispositivo
                                            </label>
                                        </div>
                                        <a class="text-primary fw-bold" href="forgot_password.php">¿Olvidaste tu contraseña?</a>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary w-100 py-3 fs-4 mb-4">
                                        Iniciar Sesión
                                    </button>
                                    
                                    <div class="d-flex align-items-center justify-content-center">
                                        <p class="fs-5 mb-0 fw-bold">¿Nuevo usuario?</p>
                                        <a class="text-primary fw-bold ms-2" href="registro.php">Solicitar una cuenta</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Información adicional -->
                        <div class="text-center mt-4">
                            <small class="text-muted">
                                © <?php echo date('Y'); ?> Institución Educativa Andrés Avelino Cáceres. 
                                <br>Todos los derechos reservados.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Toggle password visibility
            $('#togglePassword').click(function() {
                const passwordField = $('#password');
                const icon = $(this);
                
                if (passwordField.attr('type') === 'password') {
                    passwordField.attr('type', 'text');
                    icon.attr('icon', 'mdi:eye-off');
                } else {
                    passwordField.attr('type', 'password');
                    icon.attr('icon', 'mdi:eye');
                }
            });
            
            // Form submission with loading state
            $('#loginForm').on('submit', function(e) {
                const form = $(this);
                const submitBtn = form.find('button[type="submit"]');
                
                // Add loading state
                form.addClass('loading');
                submitBtn.prop('disabled', true);
                
                // Simple client-side validation
                const email = $('#email').val().trim();
                const password = $('#password').val().trim();
                
                if (!email || !password) {
                    e.preventDefault();
                    form.removeClass('loading');
                    submitBtn.prop('disabled', false);
                    alert('Por favor, complete todos los campos.');
                    return false;
                }
                
                if (!isValidEmail(email)) {
                    e.preventDefault();
                    form.removeClass('loading');
                    submitBtn.prop('disabled', false);
                    alert('Por favor, ingrese un email válido.');
                    return false;
                }
            });
            
            // Email validation function
            function isValidEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
            
            // Focus on first empty field
            if ($('#email').val() === '') {
                $('#email').focus();
            } else {
                $('#password').focus();
            }
        });
    </script>
</body>
</html>