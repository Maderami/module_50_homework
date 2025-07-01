<?php

namespace Core\Configs;

require_once __DIR__ . '/../../vendor/autoload.php';
// Настройки загрузки файлов
define('UPLOAD_DIR', __DIR__ . '/../../public/uploads');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'application/pdf']);
define('APP_ROOT', dirname(__DIR__.'/../', 2));
class Config
{
    public static function getConfig(): \Twig\Environment
    {
        // Настройки Twig
        $loader = new \Twig\Loader\FilesystemLoader([
            __DIR__ . '/../views/templates'
        ]);
        $twig = new \Twig\Environment($loader, [
            'cache' => false,
            'debug' => true
        ]);


        return $twig;
    }
}
