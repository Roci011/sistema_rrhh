<?php
session_start();
require_once '../inc/cnx.php';

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../index.php');
    exit;
}

// Get admin information
$stmt = $pdo->prepare("SELECT * FROM personal WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$admin = $stmt->fetch();

// Get system statistics
$stats = [
    'total_users' => 0,
    'total_uniformados' => 0,
    'total_guardias' => 0,
    'total_licencias' => 0
];

// Count total users
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$stats['total_users'] = $stmt->fetchColumn();

// Count uniformados
$stmt = $pdo->query("SELECT COUNT(*) FROM uniformados");
$stats['total_uniformados'] = $stmt->fetchColumn();

// Count guardias (if table exists)
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM guardias");
    $stats['total_guardias'] = $stmt->fetchColumn();
} catch (PDOException $e) {
    // Table might not exist yet
}

// Count licencias (if table exists)
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM licencias");
    $stats['total_licencias'] = $stmt->fetchColumn();
} catch (PDOException $e) {
    // Table might not exist yet
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Sistema RRHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .stat-card {
            border-left: 4px solid #0d6efd;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .menu-card {
            height: 100%;
            transition: transform 0.3s;
        }
        .menu-card:hover {
            transform: translateY(-5px);
        }
        .menu-icon {
            font-size: 2rem;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Sistema RRHH - Administración</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php"><i class="bi bi-house"></i> Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../perfiladmin.php"><i class="bi bi-person"></i> Mi Perfil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../admin.php"><i class="bi bi-shield-lock"></i> Gestión de Administradores</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <span class="navbar-text me-3">
                        Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </span>
                    <a href="../logout.php" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title">Panel de Administración</h2>
                        <p class="card-text">Bienvenido al sistema de gestión de personal y guardias.</p>
                        <?php if (!$admin): ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i> No ha completado su perfil de administrador. 
                                <a href="../perfiladmin.php" class="alert-link">Complete su perfil ahora</a>.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <h5 class="card-title">Usuarios</h5>
                        <h2 class="display-4"><?php echo $stats['total_users']; ?></h2>
                        <p class="card-text text-muted">Total de usuarios en el sistema</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <h5 class="card-title">Uniformados</h5>
                        <h2 class="display-4"><?php echo $stats['total_uniformados']; ?></h2>
                        <p class="card-text text-muted">Personal uniformado activo</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <h5 class="card-title">Guardias</h5>
                        <h2 class="display-4"><?php echo $stats['total_guardias']; ?></h2>
                        <p class="card-text text-muted">Guardias programadas</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <h5 class="card-title">Licencias</h5>
                        <h2 class="display-4"><?php echo $stats['total_licencias']; ?></h2>
                        <p class="card-text text-muted">Licencias activas</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card menu-card">
                    <div class="card-body text-center">
                        <div class="menu-icon text-primary">
                            <i class="bi bi-people"></i>
                        </div>
                        <h5 class="card-title">Gestión de Personal</h5>
                        <p class="card-text">Administre la información del personal uniformado.</p>
                        <a href="personal.php" class="btn btn-primary">Acceder</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card menu-card">
                    <div class="card-body text-center">
                        <div class="menu-icon text-primary">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <h5 class="card-title">Gestión de Guardias</h5>
                        <p class="card-text">Programe y administre las guardias del personal.</p>
                        <a href="guardias.php" class="btn btn-primary">Acceder</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card menu-card">
                    <div class="card-body text-center">
                        <div class="menu-icon text-primary">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <h5 class="card-title">Licencias y Permisos</h5>
                        <p class="card-text">Gestione las licencias y permisos del personal.</p>
                        <a href="licencias.php" class="btn btn-primary">Acceder</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card menu-card">
                    <div class="card-body text-center">
                        <div class="menu-icon text-primary">
                            <i class="bi bi-bar-chart"></i>
                        </div>
                        <h5 class="card-title">Reportes</h5>
                        <p class="card-text">Genere reportes y estadísticas del sistema.</p>
                        <a href="reportes.php" class="btn btn-primary">Acceder</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card menu-card">
                    <div class="card-body text-center">
                        <div class="menu-icon text-primary">
                            <i class="bi bi-gear"></i>
                        </div>
                        <h5 class="card-title">Configuración</h5>
                        <p class="card-text">Configure los parámetros del sistema.</p>
                        <a href="configuracion.php" class="btn btn-primary">Acceder</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card menu-card">
                    <div class="card-body text-center">
                        <div class="menu-icon text-primary">
                            <i class="bi bi-shield-lock"></i>
                        </div>
                        <h5 class="card-title">Administradores</h5>
                        <p class="card-text">Gestione los usuarios administradores del sistema.</p>
                        <a href="../admin.php" class="btn btn-primary">Acceder</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="bg-light py-3 mt-5">
        <div class="container text-center">
            <p class="text-muted mb-0">Sistema RRHH &copy; <?php echo date('Y'); ?> - Todos los derechos reservados</p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>