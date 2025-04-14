<?php
session_start();
require_once '../inc/cnx.php';

// Check if user is logged in and has uniformado role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'uniformado') {
    header('Location: ../index.php');
    exit;
}

// Get uniformado information
$stmt = $pdo->prepare("SELECT p.nombre, p.apellido, j.nombre as jerarquia, u.numero_placa 
                      FROM users us 
                      JOIN personal p ON us.id = p.user_id 
                      JOIN uniformados u ON p.id = u.personal_id 
                      JOIN jerarquias j ON u.jerarquia_id = j.id 
                      WHERE us.id = ?");
$stmt->execute([$_SESSION['user_id']]);
$uniformado = $stmt->fetch();

// Get upcoming guard duties
$stmt = $pdo->prepare("SELECT g.fecha, pg.nombre as puesto 
                      FROM guardias g 
                      JOIN uniformados u ON g.uniformado_id = u.id 
                      JOIN personal p ON u.personal_id = p.id 
                      JOIN users us ON p.user_id = us.id 
                      JOIN puestos_guardia pg ON g.puesto_id = pg.id 
                      WHERE us.id = ? AND g.fecha >= CURDATE() 
                      ORDER BY g.fecha ASC LIMIT 5");
$stmt->execute([$_SESSION['user_id']]);
$guardias = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal del Uniformado - Sistema RRHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Sistema RRHH - Portal del Uniformado</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#"><i class="bi bi-house"></i> Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="bi bi-calendar-check"></i> Mis Guardias</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="bi bi-file-earmark-text"></i> Solicitudes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="bi bi-person"></i> Mi Perfil</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <span class="navbar-text me-3">
                        Bienvenido, <?php echo htmlspecialchars($uniformado['nombre'] ?? $_SESSION['username']); ?>
                    </span>
                    <a href="../logout.php" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Información Personal</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($uniformado): ?>
                            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($uniformado['nombre'] . ' ' . $uniformado['apellido']); ?></p>
                            <p><strong>Jerarquía:</strong> <?php echo htmlspecialchars($uniformado['jerarquia']); ?></p>
                            <p><strong>Número de Placa:</strong> <?php echo htmlspecialchars($uniformado['numero_placa']); ?></p>
                        <?php else: ?>
                            <p>No se encontró información personal. Por favor contacte al administrador.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Acciones Rápidas</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="#" class="btn btn-outline-success">
                                <i class="bi bi-calendar-plus"></i> Solicitar Permiso
                            </a>
                            <a href="#" class="btn btn-outline-success">
                                <i class="bi bi-file-earmark-plus"></i> Subir Justificativo
                            </a>
                            <a href="#" class="btn btn-outline-success">
                                <i class="bi bi-calendar-week"></i> Ver Calendario de Guardias
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Próximas Guardias</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($guardias): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Puesto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($guardias as $guardia): ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y', strtotime($guardia['fecha'])); ?></td>
                                                <td><?php echo htmlspecialchars($guardia['puesto']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p>No tiene guardias programadas próximamente.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Notificaciones</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Bienvenido al nuevo sistema de gestión de guardias.
                        </div>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> Recuerde que debe solicitar sus permisos con al menos 48 horas de anticipación.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>