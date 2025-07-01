<?php


namespace Core\Configs;

use PDO;

class DataBase {


    public function connection() {
        // Подключение к БД
        $dbConfig = $this->getDataBaseConfig();
        $db = new PDO(
            "pgsql:host={$dbConfig['host']};dbname={$dbConfig['dbname']}",
            $dbConfig['user'],
            $dbConfig['password']
        );
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    }

    protected function getDataBaseConfig()
    {
        return [
            'host' => 'localhost',
            'dbname' => 'gallery',
            'user' => 'postgres',
            'password' => 'root'
        ];
    }
}