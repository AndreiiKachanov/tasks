<?php

namespace App\Router;

use Exception;

/**
 * Class MRout - Represents a simple router for mapping URLs to controller actions.
 *
 * This class parses the provided URL and determines the corresponding controller and action.
 * It then initiates the specified controller and invokes the designated action.
 */
class Router
{
    private string $controller;
    private string $action;
    private array $params;

    /**
     * Constructor to initialize the router with the provided URL.
     *
     * @param string $url
     */
    public function __construct(string $url)
    {
        $info = array_filter(explode('/', $url));
        $this->params = [];

        foreach ($info as $v) {
            if ($v != '') {
                $this->params[] = $v;
            }
        }
        $this->params[0] = $this->params[0] ?? null;

        $this->action = 'action';

        switch ($this->params[0]) {
            case 'tasks':
            case null:
                $this->controller = 'TaskController';
                $this->action = 'actionIndex';
                break;
            case 'preview':
                $this->controller = 'TaskController';
                $this->action = 'actionPreview';
                break;
            case 'my-tasks':
                $this->controller = 'TaskController';
                $this->action = 'actionMyTasks';
                break;
            case 'task':
                $this->controller = 'TaskController';
                $this->action = 'actionShow';
                break;
            case 'add':
                $this->controller = 'TaskController';
                $this->action = 'actionAdd';
                break;
            case 'edit':
                $this->controller = 'TaskController';
                $this->action = 'actionEdit';
                break;
            case 'admin':
                $this->controller = 'AuthController';
                $this->action = 'actionAdmin';
                break;
            case 'logout':
                $this->controller = 'AuthController';
                $this->action = 'actionLogout';
                break;
            case 'views':
                $this->controller = 'ViewController';
                $this->action = 'actionIndex';
                break;

            // no suitable routes - error 404
            default:
                $this->controller = 'TaskController';
                $this->action = 'action404';
        }
    }

    /**
     * Initiates the request to the determined controller and action.
     *
     * @return void
     */
    public function request(): void
    {
        try {
            $controllerClass = 'App\\Controller\\' . $this->controller;
            $controller = new $controllerClass();
            $controller->go($this->action, $this->params);
        } catch (Exception $e) {
            echo 'Error. Check log file.';
            $errorMsg = sprintf("Error in %s, line %d. %s", $e->getFile(), $e->getLine(), $e->getMessage());
            error_log($errorMsg . "\n", 3, LOG_FILE_DEST);
        }
    }
}
