<?php

namespace App\Pagination;

use App\Model\MSQL;

/**
 * Pagination Module
 */
class Pagination
{
    private MSQL $db;            // Reference to the database driver
    private int $on_page;        // Number of records per page
    private string $url_self;       // URL address from the root without page number (/pages/all/)
    private int $page_num;       // Current page number
    private string $fields;         // Fields to select
    private array $query = [
        'table' => '',       // Table name
        'join' => '',        // Join clause
        'left_join' => '',   // Left join clause
        'right_join' => '',  // Right join clause
        'where' => '',       // Where clause
        'group_by' => '',    // Group by clause
        'having' => '',      // Having clause
        'order_by' => ''     // Order by clause
    ];

    /**
     * Initialize object properties
     *
     * @param string $table
     * @param string $urlSelf
     */
    public function __construct(string $table, string $urlSelf)
    {
        $this->db = MSQL::instance();
        $this->query['table'] = $table;
        $this->fields = '*';
        $this->on_page = 2;
        $this->url_self = $urlSelf . '/';
        $this->page_num = 1;
    }

    /**
     * Get a list of records from the table
     *
     * @return array
     */
    public function page(): array
    {
        $shift = ($this->page_num - 1) * $this->on_page;
        if ($shift < 0) {
            $shift = 0;
        }

        $query = "SELECT $this->fields FROM {$this->concatenation()} LIMIT $shift, $this->on_page";
        return $this->db->select($query);
    }

    /**
     * Get the number of records in the table
     *
     * @return int
     */
    public function paginationCount(): int
    {
        return count($this->db->select("SELECT * FROM {$this->concatenation()}"));
    }

    /**
     * Concatenate the database query string
     *
     * @return string
     */
    private function concatenation(): string
    {
        return implode(' ', $this->query);
    }

    /**
     * Generate data for Template (v_navbar.php)
     *
     * @return array
     */
    public function navparams(): array
    {
        $count    = $this->paginationCount();
        $max_page = ceil($count / $this->on_page);
        $left     = $this->page_num - 2;
        $right    = $this->page_num + 2;

        while ($left <= 0) {
            $left++;
            $right++;
        }

        while ($right > $max_page) {
            $left--;
            $right--;
        }

        return [
            'on_page'  => $this->on_page,
            'count'    => $count,
            'left'     => $left,
            'right'    => $right,
            'max_page' => $max_page,
            'page_num' => $this->page_num,
            'url_self' => $this->url_self
        ];
    }

    /**
     * Set object properties
     *
     * @param $name
     * @param $args
     * @return $this
     */
    public function __call($name, $args)
    {
        if (in_array($name, array_keys($this->query))) {
            $this->query["$name"] .= strtoupper(str_replace('_', ' ', $name)) . ' ' . $args[0];
        } else {
            $this->$name = $args[0];
        }

        return $this;
    }
}
