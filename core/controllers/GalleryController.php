<?php

namespace Core\Controllers;

use Core\Configs\Auth;
use Core\Lib\Session;
use Core\Models\CommentModel;
use Core\Models\ImageModel;
use Core\Models\UserModel;
use Exception;

class GalleryController
{
    private $db;
    private $twig;
    private ImageModel $imageModel;
    private CommentModel $commentModel;
    private UserModel $userModel;

    public function __construct($db, $twig)
    {
        $this->db = $db;
        $this->twig = $twig;
        $this->imageModel = new ImageModel($db);
        $this->commentModel = new CommentModel($db);
        $this->userModel = new UserModel($db);
    }

    public function indexAction()
    {
        $images = $this->imageModel->getAll();
        echo $this->twig->render('main.twig', [
            'images' => $images,
            'sessionID' => (new Auth())->isLoggedIn(),

        ]);
    }

    public function showAction($id)
    {
        $imageId = (int)$_GET['id'];

        if ($imageId <= 0) {
            throw new Exception('Неверный ID элемента');
        }


        $image = $this->imageModel->getById($id);
        $username = $this->userModel->findByID($image['user_id']);
        if (!$image) {
            header('HTTP/1.0 404 Not Found');
            echo $this->twig->render('404.twig', ['content' => $image]);
            return;
        }

        $comments = $this->commentModel->getByImageId($id);

        echo $this->twig->render('ditailcard.twig', [
            'image' => $image,
            'username'=>$username['username'],
            'comments' => $comments,
            'sessionID' => (new Auth())->isLoggedIn(),
            'currentUserId' => Session::get('user')['id'] ?? null
        ]);

    }

    public function uploadAction()
    {
        if (!isset(Session::get('user')['id'])) {
            header('Location: /login');
            exit;
        }
        $errorUpload = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $file = $_FILES['file'];
            $description = $_POST['description'] ?? '';
            $imageId = $this->imageModel->upload(Session::get('user')['id'], $description);
            if ($imageId) {
                header("Location: /");
                exit;
            } else {
                $errorUpload = 'Файл не был загружен';
            }
        }

        echo $this->twig->render('upload.twig', [
            'error' => $errorUpload ?? null,
            'allowed_types' => ALLOWED_TYPES,
            'max_file_size' => MAX_FILE_SIZE,
            'sessionID' => (new Auth())->isLoggedIn(),
        ]);
    }

    public function commentImageAction($id)
    {
        if (!(new Auth())->isLoggedIn()) {
            header("Location: /login");
            exit;
        }

        $image = $this->imageModel->getById($id);
        if ($image && $image['user_id'] == Session::get('user')['id'] && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->commentModel->create($id, Session::get('user')['id'], $_POST['text']);
        }

        header("Location: /image/img_id={$id}");
        exit;
    }

    public function deleteImageAction($id)
    {
        if (!(new Auth())->isLoggedIn()) {
            header("Location: /login");
            exit;
        }

        $image = $this->imageModel->getById($id);
        if ($image && $image['user_id'] == Session::get('user')['id'] && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $filepath = __DIR__ . "/../../public/uploads/" . $image['filename'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            $this->imageModel->delete($id, Session::get('user')['id']);
        }

        header("Location: /");
        exit;
    }

    public function deleteCommentAction($id)
    {
        if (!(new Auth())->isLoggedIn()) {
            header("Location: /login");
            exit;
        }

        $this->commentModel->delete($id, Session::get('user')['id']);

        if (isset($_SERVER['HTTP_REFERER'])) {
            header("Location: " . $_SERVER['HTTP_REFERER']);
        } else {
            header("Location: /");
        }
        exit;
    }

}