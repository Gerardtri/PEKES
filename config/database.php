<?php
/**
 * PEKES Pet Shop — Configuración de Base de Datos
 * Compatible con MySQL / MariaDB (Hostinger) y SQLite local de respaldo.
 */

// Credenciales para Hostinger (completar con los datos creados en hPanel de Hostinger)
define('DB_DRIVER',   'mysql'); // 'mysql' para Hostinger, 'sqlite' para desarrollo local sin MySQL
define('DB_HOST',     'localhost');
define('DB_NAME',     'pekes_db'); // Cambiar por el nombre de la BD en Hostinger (ej: u123456789_pekes)
define('DB_USER',     'root');     // Cambiar por el usuario de la BD en Hostinger (ej: u123456789_pekesuser)
define('DB_PASS',     '');         // Cambiar por la contraseña de la BD en Hostinger
define('DB_CHARSET',  'utf8mb4');

// Ruta al archivo SQLite original de respaldo local
define('DB_SQLITE_PATH', dirname(__DIR__) . '/backend/pekes.db');

/**
 * Retorna la instancia singleton de la conexión PDO
 * @return PDO
 */
function get_db_connection() {
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    // Intento 1: Conectar a MySQL si el driver es mysql y hay base de datos configurada
    if (DB_DRIVER === 'mysql' && !empty(DB_NAME)) {
        try {
            $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            return $pdo;
        } catch (PDOException $e) {
            // Si estamos en entorno local y MySQL no está activo, hacer fallback a SQLite para desarrollo
            if (file_exists(DB_SQLITE_PATH)) {
                try {
                    $pdo = new PDO('sqlite:' . DB_SQLITE_PATH, null, null, $options);
                    // Habilitar claves foráneas en SQLite
                    $pdo->exec('PRAGMA foreign_keys = ON;');
                    return $pdo;
                } catch (PDOException $sqle) {
                    die('Error al conectar a SQLite de respaldo: ' . htmlspecialchars($sqle->getMessage()));
                }
            } else {
                die('Error de conexión a la base de datos MySQL: ' . htmlspecialchars($e->getMessage()));
            }
        }
    }

    // Intento 2: Conexión forzada a SQLite
    if (file_exists(DB_SQLITE_PATH)) {
        $pdo = new PDO('sqlite:' . DB_SQLITE_PATH, null, null, $options);
        $pdo->exec('PRAGMA foreign_keys = ON;');
        return $pdo;
    }

    die('No se pudo establecer conexión a ninguna base de datos disponible.');
}

