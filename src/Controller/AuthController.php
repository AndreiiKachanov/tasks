<?php

namespace App\Controller;

use App\Model\User;
use Exception;

/**
 * Class AuthController
 *
 * Represents a controller responsible for user authentication and authorization.
 */
class AuthController extends BaseController
{
    /**
     * Performs authentication and redirects to the admin page upon successful login.
     *
     * @return void
     * @throws Exception
     */
    public function actionAdmin(): void
    {
        $mUser = User::instance();
        $mUser->clearSessions();

        if ($this->isPost()) {
            $fields['login'] = $login = isset($_POST['login']) ? trim($_POST['login']) : '';
            $fields['password'] = $password = $_POST['password'] ?? '';
            $fields['remember'] = $remember = isset($_POST['remember']);
            if (!empty($login) && !empty($password)) {
                if ($mUser->login($login, $password, $remember)) {
                    $this->redirect($_GET['redirect'] ?? '/');
                }
                $errors['auth'] = 'Неверный логи или пароль!';
            } else {
                $err = 'Заполните поле.';
                if (empty($login)) {
                    $errors['login'] = $err;
                }
                if (empty($password)) {
                    $errors['password'] = $err;
                }
            }
        }

        $this->title .= ' | User Authorization';
        $this->content = $this->template('src/view/auth/v_login.php', [
            'errors' => $errors ?? [],
            'fields' => $fields ?? [],
        ]);
    }

    /**
     * Logs out the user and redirects to the admin login page.
     *
     * @return void
     * @throws Exception
     */
    public function actionLogout(): void
    {
        $mUser = User::instance();
        $mUser->clearSessions();
        $mUser->logout();
        header('location: /admin');
        die;
    }
}
