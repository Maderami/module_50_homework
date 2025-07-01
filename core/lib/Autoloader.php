<?php
class Autoloader
{
    /**
     * Ассоциативный массив, где ключ - префикс пространства имен,
     * а значение - массив базовых директорий для классов в этом пространстве имен
     */
    private static array $prefixes = [];

    /**
     * Регистрирует автозагрузчик с помощью SPL
     */
    public static function register(): void
    {
        spl_autoload_register([self::class, 'loadClass']);
    }

    /**
     * Добавляет базовую директорию для указанного префикса пространства имен
     */
    public static function addNamespace(string $prefix, string $baseDir, bool $prepend = false): void
    {
        // Нормализуем префикс пространства имен
        $prefix = trim($prefix, '\\') . '\\';

        // Нормализуем базовую директорию с завершающим разделителем
        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . '/';

        // Инициализируем массив префиксов, если нужно
        if (isset(self::$prefixes[$prefix]) === false) {
            self::$prefixes[$prefix] = [];
        }

        // Сохраняем базовую директорию для префикса пространства имен
        if ($prepend) {
            array_unshift(self::$prefixes[$prefix], $baseDir);
        } else {
            array_push(self::$prefixes[$prefix], $baseDir);
        }
    }

    /**
     * Загружает класс по его полному имени
     */
    public static function loadClass(string $class): bool
    {
        // Текущий префикс пространства имен
        $prefix = $class;

        // Работаем назад по полному имени класса для поиска соответствия
        while (false !== $pos = strrpos($prefix, '\\')) {
            // Сохраняем префикс до последнего разделителя
            $prefix = substr($class, 0, $pos + 1);

            // Остаток после префикса
            $relativeClass = substr($class, $pos + 1);

            // Пытаемся загрузить файл для префикса и относительного класса
            $mappedFile = self::loadMappedFile($prefix, $relativeClass);
            if ($mappedFile) {
                return $mappedFile;
            }

            // Удаляем последний разделитель для следующей итерации
            $prefix = rtrim($prefix, '\\');
        }

        // Ничего не найдено
        return false;
    }

    /**
     * Загружает файл, соответствующий префиксу пространства имен и относительному классу
     */
    protected static function loadMappedFile(string $prefix, string $relativeClass): bool
    {
        // Проверяем наличие префикса в зарегистрированных
        if (isset(self::$prefixes[$prefix]) === false) {
            return false;
        }

        // Ищем в базовых директориях для этого префикса
        foreach (self::$prefixes[$prefix] as $baseDir) {
            // Заменяем разделители пространства имен на разделители директорий,
            // добавляем .php
            $file = $baseDir
                . str_replace('\\', '/', $relativeClass)
                . '.php';

            // Если файл существует, подключаем его
            if (self::requireFile($file)) {
                return true;
            }
        }

        // Ничего не найдено
        return false;
    }

    /**
     * Если файл существует, подключает его
     */
    protected static function requireFile(string $file): bool
    {
        if (file_exists($file)) {
            require $file;
            return true;
        }
        return false;
    }
}