<?php
session_start();
require_once 'inc/cnx.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header('Location: admin/index.php');
    } else {
        header('Location: uniformado/index.php');
    }
    exit;
}

// Check for admin registration code
$admin_code = 'ADMIN2023'; // This should be stored securely in a configuration file
$code_valid = false;

if (isset($_GET['code']) && $_GET['code'] === $admin_code) {
    $code_valid = true;
} else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admin_code']) && $_POST['admin_code'] === $admin_code) {
    $code_valid = true;
}

if (!$code_valid) {
    // Show code verification form
    $code_error = '';
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admin_code'])) {
        $code_error = 'Código de registro inválido';
    }
    
    include 'admin_code_form.php';
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $email = $_POST['email'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $apellido = $_POST['apellido'] ?? '';
    $documento = $_POST['documento'] ?? '';
    $fecha_ingreso = $_POST['fecha_ingreso'] ?? '';
    
    // Validate required fields
    if (empty($username) || empty($password) || empty($confirm_password) || empty($email) || 
        empty($nombre) || empty($apellido) || empty($documento) || empty($fecha_ingreso)) {
        $error = 'Por favor complete todos los campos obligatorios';
    } elseif ($password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El correo electrónico no es válido';
    } else {
        try {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->rowCount() > 0) {
                $error = 'El nombre de usuario ya existe';
            } else {
                // Check if email already exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->rowCount() > 0) {
                    $error = 'El correo electrónico ya está registrado';
                } else {
                    // Check if documento already exists
                    $stmt = $pdo->prepare("SELECT id FROM personal WHERE documento = ?");
                    $stmt->execute([$documento]);
                    if ($stmt->rowCount() > 0) {
                        $error = 'El documento de identidad ya está registrado';
                    } else {
                        // Begin transaction
                        $pdo->beginTransaction();
                        
                        // Hash password
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        
                        // Create new admin user
                        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, 'admin')");
                        $stmt->execute([$username, $hashed_password, $email]);
                        $user_id = $pdo->lastInsertId();
                        
                        // Create personal record for admin
                        $stmt = $pdo->prepare("INSERT INTO personal (user_id, nombre, apellido, documento, fecha_ingreso) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$user_id, $nombre, $apellido, $documento, $fecha_ingreso]);
                        
                        // Commit transaction
                        $pdo->commit();
                        
                        $success = 'Registro de administrador completado correctamente. Ahora puede iniciar sesión.';
                    }
                }
            }
        } catch (PDOException $e) {
            // Rollback transaction on error
            $pdo->rollBack();
            $error = 'Error al registrar: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema RRHH - Registro de Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .register-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo h1 {
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <div class="logo">
                <h1>Sistema RRHH</h1>
                <p>Registro de Administrador</p>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> <?php echo $success; ?>
                    <div class="mt-2">
                        <a href="index.php" class="btn btn-sm btn-success">Ir a Iniciar Sesión</a>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!$success): ?>
                <form method="post" action="">
                    <input type="hidden" name="admin_code" value="<?php echo htmlspecialchars($admin_code); ?>">
                    
                    <h5 class="mb-3">Información de Cuenta</h5>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="username" class="form-label">Nombre de Usuario *</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="col-md-4">
                            <label for="password" class="form-label">Contraseña *</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="form-text">Mínimo 6 caracteres</div>
                        </div>
                        <div class="col-md-4">
                            <label for="confirm_password" class="form-label">Confirmar Contraseña *</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo Electrónico *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <hr>
                    <h5 class="mb-3">Información Personal</h5>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="nombre" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="col-md-6">
                            <label for="apellido" class="form-label">Apellido *</label>
                            <input type="text" class="form-control" id="apellido" name="apellido" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="documento" class="form-label">Documento de Identidad *</label>
                            <input type="text" class="form-control" id="documento" name="documento" required>
                        </div>
                        <div class="col-md-6">
                            <label for="fecha_ingreso" class="form-label">Fecha de Ingreso *</label>
                            <input type="date" class="form-control" id="fecha_ingreso" name="fecha_ingreso" required>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <a href="index.php" class="btn btn-secondary me-md-2">
                            <i class="bi bi-arrow-left"></i> Volver a Inicio de Sesión
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-person-plus"></i> Registrarse como Administrador
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>