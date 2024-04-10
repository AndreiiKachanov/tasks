<?php

namespace App\Controller;

use App\Pagination\Pagination;

/**
 * Base controller class
 *
 * Defines a basic controller class with some abstract methods and common functionalities for handling
 * requests and generating HTML templates.
 */
abstract class Controller
{
    // Array with parameters - equivalent to $_GET
    protected array $params;

    // Generate the external template
    abstract protected function render();

    // Function executed before the main method
    abstract protected function before();

    /**
     * Initiates the specified action and renders the template.
     *
     * @param string $action
     * @param array $params
     * @return void
     */
    public function go(string $action, array $params): void
    {
        $this->params = $params;
        $this->before();
        $this->$action();
        $this->render();
    }

    /**
     * Was the request made using the POST method?
     *
     * @return bool
     *
     */
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Was the request made using AJAX?
     *
     * @return bool
     */
    protected function isAjax(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }

    /**
     * In case of a request to a non-existent page, send a 404 error header
     *
     * @param string $url
     * @param string|null $redirectUrl
     * @return void
     */
    protected function redirect(string $url, ?string $redirectUrl = null): void
    {
        if ($url === '404') {
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            require_once 'src/view/v_404.php';
            die;
        }

        if (!is_null($redirectUrl)) {
            // Параметры запроса для передачи на страницу перенаправления
            $queryParams = http_build_query([
                'redirect' => $redirectUrl
            ]);

            $url = $url . '?' . $queryParams;
        }
        header("Location: $url");
        die;
    }

    /**
     * Generate HTML template as a string
     *
     * @param string $fileName
     * @param array $vars
     * @return false|string
     */
    protected function template(string $fileName, array $vars = []): false|string
    {

        // Convert array keys into variables
        foreach ($vars as $key => $value) {
            $$key = $value;
        }

        // Generate HTML into a string
        ob_start();
        require_once "$fileName";
        return ob_get_clean();
    }

    /**
     * Redirects to the 404 page.
     *
     * @return void
     */
    protected function p404(): void
    {
        $c = new TaskController();
        $c->go('action404', []);
        die;
    }

    /**
     * Generates page content with pagination.
     *
     * @param int $itemsPerPage
     * @param string $fields
     * @param Pagination $mPagination
     * @param bool $onlyMyItems
     * @param array|null $sorting
     * @return array
     */
    protected function generatePageContent(
        int $itemsPerPage,
        string $fields,
        Pagination $mPagination,
        bool $onlyMyItems = false,
        ?array $sorting = ['field' => 'id', 'order' => 'desc'],
    ): array {

        $pageNumber = (int)($this->params[1] ?? 1);
        $items = $mPagination->fields($fields);

        if ($onlyMyItems) {
            $userId = $this->user['id_user'];
            $items->where("id_user=$userId");
        }

        $items = $items
            ->order_by(($sorting['field'] ?? 'id') . ' ' . ($sorting['order'] ?? 'desc'))
            ->on_page($itemsPerPage)
            ->page_num($pageNumber)
            ->page();


        $navParams = $mPagination->navparams();

        // Generating pagination
        $navbar = $this->template('src/view/v_navbar.php', [
            'params' => $navParams,
            'sorting' => $sorting,
        ]);

        return [
            'navParams' => $navParams,
            'navbar' => $navbar,
            'items' => $items,
            'user' => $this->user,
            'sorting' => $sorting,
        ];
    }
}
