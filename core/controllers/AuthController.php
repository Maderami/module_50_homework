<?php

namespace Core\Controllers;

use Core\Models\UserModel;
use Core\Lib\Session;
class AuthController
{
    private $db;
    private $twig;
    private $userModel;

    public function __construct($db, $twig)
    {
        $this->db = $db;
        $this->twig = $twig;
        $this->userModel = new UserModel($db);
    }

    public function loginAction() {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);

            $user = $this->userModel->findByUsername($username);

            if ($user && password_verify($password, $user['password'])) {
                Session::set('user', ['id' => $user['id'], 'login' => $user['username'], 'session_id' => session_id()]);
                header("Location: /");
                exit;
            } else {
                $error = "Неверное имя пользователя или пароль";
            }
        }

        echo $this->twig->render('auth/login.twig', [
            'sessionID'=>Session::get('user')['id'],
            'error' => $error ?? null
        ]);
    }

     public function registerAction()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);
            $confirm = trim($_POST['confirm']);

            if (empty($username) || empty($password)) {
                $error = "Все поля обязательны для заполнения";
            } elseif ($password !== $confirm) {
                $error = "Пароли не совпадают";
            } elseif ($this->userModel->findByUsername($username)) {
                $error = "Пользователь с таким именем уже существует";
            } else {
                $this->userModel->create($username, $password);
                header("Location: /");
                exit;
            }
        }

        echo $this->twig->render('auth/register.twig', [
            'error' => $error ?? null
        ]);
    }

    public function logoutAction()
    {
        session_destroy();
        header("Location: /");
        exit;
    }

}