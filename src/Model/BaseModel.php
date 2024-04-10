<?php

namespace App\Model;

use App\Validation\Validation;
use Exception;

/**
 * Class MModel - Represents the base model for interacting with a database table.
 * This class provides basic CRUD (Create, Read, Update, Delete) operations for database records.
 * Additionally, this class loads a validation module for validating data before performing CRUD operations.
 */
class BaseModel
{
    protected string $table;       // Table name
    protected string $pk;          // Primary key
    protected MSQL $db;            // Database module
    protected array $errors;       // List of errors
    private ?Validation $valid;   // Validation module

    /**
     * @param string $table
     * @param string $pk
     */
    public function __construct(string $table, string $pk)
    {
        $this->table = $table;
        $this->pk = $pk;
        $this->errors = [];
        $this->valid = null;
        $this->db = MSQL::instance();
    }

    /**
     * Retrieve a record from the database by its ID.
     *
     * @param int $id
     * @return array|null
     */
    public function get(int $id): ?array
    {
        //для данных, которые вводит пользователь отправляем параметризованный запрос - защита от sql инъекций
        $query = "SELECT $this->table.* FROM $this->table WHERE $this->pk = :id";
        $result = $this->db->select($query, ['id' => $id]);
        return $result[0] ?? [];
    }


    /**
     * Add a new record to the database.
     *
     * @param array $fields
     * @return false|string
     * @throws Exception
     */
    public function add(array $fields): false|string
    {
        $this->errors = [];  // обнуляем список ошибок
        $valid = $this->loadValidation(); // подгружаем модуль валидации

        $valid->execute($fields);
        if ($valid->good()) {
            $result = $valid->getObj();
            return $this->db->insert($this->table, $result);
        }

        $this->errors = $valid->errors();
        return false;
    }

    /**
     * Update an existing record in the database.
     *
     * @param string|int $id
     * @param array $fields
     * @return bool
     * @throws Exception
     */
    public function edit(string|int $id, array $fields): bool
    {
        $this->errors = [];            // обнуляем список ошибок
        $valid = $this->loadValidation(); // подгружаем модуль валидации

        $valid->execute($fields);
        if ($valid->good()) {
            $where = $this->pk . ' = ' . ':' . $this->pk; //id = :id
            $params = [$this->pk => $id];
            $this->db->update($this->table, $valid->getObj(), $where, $params);
            return true;
        }

        $this->errors = $valid->errors();
        return false;
    }

    /**
     * Delete a record from the database.
     *
     * @param string|int $id
     * @return int
     * @throws Exception
     */
    public function delete(string|int $id): int
    {
        $where = $this->pk . ' = ' . ':' . $this->pk;
        $params = [$this->pk => $id];
        return $this->db->delete($this->table, $where, $params);
    }

    /**
     * Get the list of errors.
     *
     * @return array
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Load the validation module if not already loaded.
     *
     * @return Validation|null
     */
    private function loadValidation(): ?Validation
    {
        if ($this->valid === null) {
            $this->valid = new Validation($this->table);
        }

        return $this->valid;
    }
}
