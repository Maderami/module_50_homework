<?php

namespace Core\Configs;

use Core\Lib\Session;
use PDO;
use Core\Controllers\AuthController;


class Auth
{

    function isLoggedIn(): bool
    {
        return isset(Session::get('user')['id']);
    }

    function getUser($db, $id)
    {
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}