<?php

namespace App\Model;

use Exception;

/**
 * User Model
 * This class represents the model for interacting with the user table in the database.
 * It extends the base model MModel, adding functionality for user operations.
 */
class User extends BaseModel
{
    private static $instance;   // class instance
    private ?string $sid;               // current session identifier
    private ?string $uid;               // current user identifier

    private string $tableUsersName = TABLE_PREFIX . 'users'; // table name with prefix
    private string $tableSessionsName = TABLE_PREFIX . 'sessions'; // table name with prefix

    /**
     * Gets an instance of the MUser class.
     *
     * @return User
     */
    public static function instance(): User
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct()
    {
        parent::__construct($this->tableUsersName, 'id_user');
        $this->sid = null;
        $this->uid = null;
    }

    /**
     * Cleans up unused sessions
     * Deletes all records from the sessions table that are older than 20 minutes ago.
     *
     * @return void
     * @throws Exception
     */
    public function clearSessions(): void
    {
        // Get the time 20 minutes ago from the current moment.
        $min = date('Y-m-d H:i:s', time() - 60 * 20);
        $where = 'time_last < ' . ':min';
        $params = ['min' => $min];
        $this->db->delete($this->tableSessionsName, $where, $params);
    }

    /**
     * Authenticates a user
     *
     * @param string $login
     * @param string $password
     * @param bool $remember
     * @return bool
     * @throws Exception
     */
    public function login(string $login, string $password, bool $remember = true): bool
    {
        // Fetch the user from the database
        $user = $this->getByLogin($login);

        if ($user === null) {
            return false;
        }

        $idUser = $user['id_user'];

        // Check the password
        if ($user['password'] !== $this->hash($password)) {
            return false;
        }

        // Login and password matched - remember the name and md5(password)
        if ($remember) {
            // Set cookies for 100 days
            $expire = time() + 3600 * 24 * 100;
            setcookie('login', $login, $expire, BASE_URL);
            setcookie('password', $this->hash($password), $expire, BASE_URL);
        }

        // Open a session and remember SID
        $this->sid = $this->openSession($idUser);
        return true;
    }

    /**
     * Logs out the user
     *
     * @return void
     */
    public function logout(): void
    {
        // Set cookies 'login' and 'password' with empty values,
        // expiry time 1 second ago and restrict the path BASE_URL.
        setcookie('login', '', time() - 1, BASE_URL);
        setcookie('password', '', time() - 1, BASE_URL);

        // Remove variables 'login' and 'password' from the $_COOKIE array.
        unset($_COOKIE['login']);
        unset($_COOKIE['password']);

        // Remove variable 'sid' from the $_SESSION array.
        unset($_SESSION['sid']);

        $this->sid = null;
        $this->uid = null;
    }

    /**
     * Gets a user by ID
     *
     * @param $id
     * @return array|null
     * @throws Exception
     */
    public function get($id = null): ?array
    {
        // If id_user is not specified, retrieve it from the current session.
        if ($id === null) {
            $id = $this->getUid();
        }

        if ($id === null) {
            return null;
        }

        $id = (int)$id;
        // Now simply return the user by id_user.
        $result = $this->db->select("SELECT * FROM $this->tableUsersName WHERE id_user = '$id'");
        return $result[0];
    }

    /**
     * Gets the user unique ID
     *
     * @return mixed
     * @throws Exception
     */
    public function getUid(): mixed
    {
        // check cache
        if ($this->uid !== null) {
            return $this->uid;
        }

        // Get it from the current session.
        $sid = $this->getSid();


        if ($sid === null) {
            return null;
        }

        $result = $this->db->select("SELECT id_user FROM $this->tableSessionsName WHERE sid = '$sid'");

        // If session not found - user is not authenticated.
        if (count($result) === 0) {
            return null;
        }

        // If found - remember it.
        $this->uid = $result[0]['id_user'];
        return $this->uid;
    }


    /**
     * Gets a user by login
     *
     * @param string $login
     * @return array|null
     */
    public function getByLogin(string $login): ?array
    {
        $query = "SELECT * FROM $this->tableUsersName WHERE login = :login";
        $result = $this->db->select($query, ['login' => $login]);
        return $result[0] ?? null;
    }

    /**
     * Hashes a string
     *
     * @param string $str
     * @return string
     */
    public function hash(string $str): string
    {
        return md5(md5($str . HASH_KEY));
    }

    /**
     * Returns the current session identifier
     *
     * @return string|null
     * @throws Exception
     */
    private function getSid(): ?string
    {
        // Check cache.
        if ($this->sid !== null) {
            return $this->sid;
        }

        // Look for SID in session.
        $sid = $_SESSION['sid'] ?? null;

        // If found, try to update time_last in the database.
        // Also check if the session exists in the database.
        if ($sid !== null) {
            $session = [];
            $session['time_last'] = date('Y-m-d H:i:s');

            $where = 'sid = ' . ':sid';
            $params = ['sid' => $sid];

            $affected_rows = $this->db->update($this->tableSessionsName, $session, $where, $params);

            if ($affected_rows === 0) {
                $query = "SELECT count(*) FROM $this->tableSessionsName WHERE sid = '$sid'";
                $result = $this->db->select($query);
                if ($result[0]['count(*)'] === 0) {
                    $sid = null;
                }
            }
        }
        // No session? Look for login and md5(password) in cookies.
        // Try to reconnect.
        if ($sid === null && isset($_COOKIE['login'])) {
            $user = $this->getByLogin($_COOKIE['login']);
            if ($user !== null && $user['password'] === $_COOKIE['password']) {
                $sid = $this->openSession($user['id_user']);
            }
        }
        // Cache it.
        if ($sid !== null) {
            $this->sid = $sid;
        }

        // Finally, return SID.
        return $sid;
    }

    /**
     * Opens a new session
     *
     * @param int $id_user
     * @return string
     * @throws Exception
     */
    private function openSession(int $id_user): string
    {
        // Generate SID
        $sid = $this->generateStr();

        // Insert SID into the database
        $now = date('Y-m-d H:i:s');
        $session = [];
        $session['id_user'] = $id_user;
        $session['sid'] = $sid;
        $session['time_start'] = $now;
        $session['time_last'] = $now;
        $this->db->insert(TABLE_PREFIX . 'sessions', $session);

        // Register session in PHP session
        $_SESSION['sid'] = $sid;

        // Return SID
        return $sid;
    }

    /**
     * Generates a random string
     *
     * @return string
     */
    private function generateStr(): string
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
        $code = "";
        $clen = strlen($chars) - 1;

        while (strlen($code) < 10) {
            $code .= $chars[mt_rand(0, $clen)];
        }

        return $code;
    }
}
