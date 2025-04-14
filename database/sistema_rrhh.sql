
-- Database: sistema_rrhh

-- Users table (for system access)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin', 'uniformado') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Personal information table
CREATE TABLE personal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    documento VARCHAR(20) NOT NULL UNIQUE,
    fecha_nacimiento DATE,
    direccion TEXT,
    telefono VARCHAR(20),
    email_personal VARCHAR(100),
    fecha_ingreso DATE NOT NULL,
    estado ENUM('activo', 'inactivo', 'suspendido') DEFAULT 'activo',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Ranks/hierarchy table
CREATE TABLE jerarquias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    nivel INT NOT NULL COMMENT 'Nivel numérico para ordenar jerarquías',
    descripcion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Uniformed personnel (police) table
CREATE TABLE uniformados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    personal_id INT NOT NULL,
    jerarquia_id INT NOT NULL,
    numero_placa VARCHAR(20) UNIQUE,
    antiguedad INT NOT NULL COMMENT 'Años de servicio',
    disponible BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (personal_id) REFERENCES personal(id) ON DELETE CASCADE,
    FOREIGN KEY (jerarquia_id) REFERENCES jerarquias(id)
);

-- Guard posts/locations
CREATE TABLE puestos_guardia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    ubicacion TEXT,
    descripcion TEXT,
    jerarquia_minima INT COMMENT 'Nivel mínimo de jerarquía requerido',
    jerarquia_maxima INT COMMENT 'Nivel máximo de jerarquía permitido',
    activo BOOLEAN DEFAULT TRUE
);

-- Leave types (vacation, sick leave, etc.)
CREATE TABLE tipos_ausencia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    descripcion TEXT,
    requiere_justificativo BOOLEAN DEFAULT FALSE
);

-- Leave requests
CREATE TABLE solicitudes_ausencia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uniformado_id INT NOT NULL,
    tipo_ausencia_id INT NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    estado ENUM('pendiente', 'aprobada', 'rechazada') DEFAULT 'pendiente',
    justificativo TEXT,
    documento_adjunto VARCHAR(255) COMMENT 'Path to uploaded document',
    fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    aprobado_por INT COMMENT 'ID of admin who approved/rejected',
    fecha_respuesta TIMESTAMP NULL,
    FOREIGN KEY (uniformado_id) REFERENCES uniformados(id) ON DELETE CASCADE,
    FOREIGN KEY (tipo_ausencia_id) REFERENCES tipos_ausencia(id),
    FOREIGN KEY (aprobado_por) REFERENCES users(id)
);

-- Guard duty schedule
CREATE TABLE guardias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    puesto_id INT NOT NULL,
    uniformado_id INT,
    estado ENUM('programada', 'completada', 'incumplida') DEFAULT 'programada',
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (puesto_id) REFERENCES puestos_guardia(id),
    FOREIGN KEY (uniformado_id) REFERENCES uniformados(id) ON DELETE SET NULL
);

-- Guard duty assignment rules
CREATE TABLE reglas_asignacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jerarquia_id INT NOT NULL,
    dias_mes VARCHAR(100) COMMENT 'JSON array or range of days (e.g., "1-10", "11-20")',
    prioridad INT DEFAULT 0,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (jerarquia_id) REFERENCES jerarquias(id)
);

-- Notifications table
CREATE TABLE notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    mensaje TEXT NOT NULL,
    leido BOOLEAN DEFAULT FALSE,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert basic data
INSERT INTO jerarquias (nombre, nivel, descripcion) VALUES
('Comisario General', 1, 'Máxima jerarquía policial'),
('Comisario', 2, 'Segunda jerarquía policial'),
('Subcomisario', 3, 'Tercera jerarquía policial'),
('Oficial Principal', 4, 'Cuarta jerarquía policial'),
('Oficial', 5, 'Quinta jerarquía policial'),
('Suboficial', 6, 'Sexta jerarquía policial');

INSERT INTO tipos_ausencia (nombre, descripcion, requiere_justificativo) VALUES
('Vacaciones', 'Período de descanso anual', FALSE),
('Licencia médica', 'Ausencia por enfermedad', TRUE),
('Permiso especial', 'Ausencia por motivos personales', TRUE),
('Comisión de servicio', 'Ausencia por asignación a otra unidad', FALSE);

-- Insert admin user (password: admin123)
INSERT INTO users (username, password, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@sistema.com', 'admin');