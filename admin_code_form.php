<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema RRHH - Verificación de Código</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .code-container {
            max-width: 500px;
            margin: 100px auto;
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
        <div class="code-container">
            <div class="logo">
                <h1>Sistema RRHH</h1>
                <p>Verificación de Código de Administrador</p>
            </div>
            
            <?php if (isset($code_error) && $code_error): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> <?php echo $code_error; ?>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <p class="card-text">
                        Para registrarse como administrador, necesita un código de autorización.
                        Por favor, ingrese el código proporcionado por el administrador del sistema.
                    </p>
                    
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="admin_code" class="form-label">Código de Administrador</label>
                            <input type="password" class="form-control" id="admin_code" name="admin_code" required>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="index.php" class="btn btn-secondary me-md-2">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Verificar Código
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>