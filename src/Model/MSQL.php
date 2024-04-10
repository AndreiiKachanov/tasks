<?php

namespace App\Model;

use Exception;
use PDO;
use PDOException;

/**
 * Class MMySQL - Represents a simple MySQL database wrapper using PDO.
 */
class MSQL
{
    private static $instance;
    private PDO $db;

    /**
     * Singleton method to get the instance of the MSQL class
     *
     * @return MSQL
     */
    public static function instance(): MSQL
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        setlocale(LC_ALL, 'ru_RU.UTF8');
        $this->db = new PDO('mysql:host=' . MYSQL_SERVER . ';dbname=' . MYSQL_DB, MYSQL_USER, MYSQL_PASSWORD);
        $this->db->exec('SET NAMES UTF8');
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /**
     * Perform a SELECT query on the database
     *
     * @param string $query
     * @param array $params
     * @return array
     */
    public function select(string $query, array $params = []): array
    {
        $q = $this->db->prepare($query);
        foreach ($params as $key => $value) {
            $paramType = is_numeric($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $q->bindParam(":$key", $value, $paramType);
        }

        $q->execute();
        if ($q->errorCode() !== PDO::ERR_NONE) {
            $info = $q->errorInfo();
            die($info[2]);
        }

        return $q->fetchAll();
    }

    /**
     * Perform an INSERT query on the database
     *
     * @param string $table
     * @param array $object
     * @return false|string
     * @throws Exception
     */
    public function insert(string $table, array $object): false|string
    {
        //dd($object);
        $columns = [];
        $masks = [];
        foreach ($object as $key => $value) {
            $columns[] = $key;
            $masks[] = ":$key";
        }

        $columnsImplode = implode(',', $columns);
        $masksImplode = implode(',', $masks);

        $query = "INSERT INTO $table ($columnsImplode) VALUES ($masksImplode)";
        $q = $this->db->prepare($query);

        //Привязка параметров
        foreach ($object as $key => &$value) {
            $q->bindParam(":$key", $value);
        }

        if (!$q->execute()) {
            $info = $q->errorInfo();
            throw new Exception('Error adding a record to the database. ' . $info[2]);
        }

        // Проверка на успешное выполнение запроса
        if ($q->rowCount() === 0) {
            return false; // Нет вставленных записей
        }

        return $this->db->lastInsertId();
    }

    /**
     * Perform an UPDATE query on the database
     *
     * @param string $table
     * @param array $object
     * @param string $where
     * @param array $params
     * @return int
     * @throws Exception
     */
    public function update(string $table, array $object, string $where, array $params): int
    {
        $sets = [];
        foreach ($object as $key => $value) {
            $sets[] = "$key=:$key";
        }

        $setsImplode = implode(',', $sets);
        $query = "UPDATE $table SET $setsImplode WHERE $where";
        $q = $this->db->prepare($query);

        //Bind fields
        foreach ($object as $key => &$value) {
            $q->bindParam(":$key", $value);
        }

        // Bind parameters Where
        foreach ($params as $param => $par) {
            $q->bindValue(":$param", $par);
        }


        if (!$q->execute()) {
            $info = $q->errorInfo();
            throw new Exception('Error updating a record to the database. ' . $info[2]);
        }

        return $q->rowCount();
    }


    /**
     * Perform a DELETE query on the database
     *
     * @param string $table
     * @param string $where
     * @param array $params
     * @return int
     * @throws Exception
     */
    public function delete(string $table, string $where, array $params): int
    {
        try {
            // Prepare the DELETE SQL statement
            $query = "DELETE FROM $table WHERE $where";
            $q = $this->db->prepare($query);

            // Bind parameters
            foreach ($params as $param => $value) {
                $q->bindValue(":$param", $value);
            }

            if (!$q->execute()) {
                $info = $q->errorInfo();
                throw new Exception('Error deleting a record to the database. ' . $info[2]);
            }

            // Return the number of rows affected by the deletion
            return $q->rowCount();
        } catch (PDOException $e) {
            throw new Exception('PDO Exception: ' . $e->getMessage());
        }
    }
}
