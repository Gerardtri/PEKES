<?php
/**
 * PEKES Pet Shop — Conexión a Base de Datos MySQL / MariaDB
 * Optimizado para despliegue en Hostinger (hPanel / phpMyAdmin)
 */

// Credenciales de la base de datos MySQL en Hostinger
// En Hostinger se crean en: hPanel -> Bases de datos -> Bases de datos MySQL
define('DB_HOST',    'localhost');
define('DB_NAME',    'pekes_db');       // Ejemplo en Hostinger: u123456789_pekes
define('DB_USER',    'root');           // Ejemplo en Hostinger: u123456789_pekesuser
define('DB_PASS',    '');               // Contraseña creada en Hostinger
define('DB_CHARSET', 'utf8mb4');

/**
 * Retorna la instancia singleton de la conexión PDO a MySQL.
 * Si falla, retorna null para permitir el funcionamiento del catálogo de respaldo (public/data/products.json).
 * @param bool $die_on_error Si es true, detiene la ejecución mostrando mensaje de error (útil para el admin).
 * @return PDO|null
 */
function get_db_connection($die_on_error = false) {
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        if ($die_on_error) {
            error_log('Error de conexión PDO MySQL: ' . $e->getMessage());
            die('
                <div style="font-family: -apple-system, BlinkMacSystemFont, sans-serif; max-width: 550px; margin: 80px auto; padding: 32px; background: #FFF5F5; border: 1px solid #FED7D7; border-radius: 16px; color: #742A2A; text-align: center;">
                    <h2 style="margin-top: 0; color: #9B2C2C;">Configuración de Base de Datos Requerida</h2>
                    <p style="font-size: 15px; line-height: 1.6; color: #4A5568;">
                        No fue posible conectar con la base de datos MySQL. Por favor configura las credenciales de tu base de datos en <strong>config/database.php</strong> e importa el archivo <strong>database.sql</strong> en phpMyAdmin.
                    </p>
                    <code style="display: block; background: #fff; padding: 12px; border-radius: 8px; font-size: 13px; color: #2D3748; margin-top: 16px;">
                        ' . htmlspecialchars($e->getMessage()) . '
                    </code>
                </div>
            ');
        }
        return null;
    }
}


