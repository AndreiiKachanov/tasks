<?php

namespace App\Controller;

use App\Model\User;
use App\Model\View;
use Exception;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Base controller.
 */
abstract class BaseController extends Controller
{
    protected string $title = '';   // Page title
    protected string $content = ''; // Page content
    protected bool $needLogin = false; // Indicates if login is required for this controller
    protected ?array $user = null; // Current user
    protected string $keywords = ''; // Meta keywords
    protected string $description = ''; // Meta description
    protected array $styles = [ // Array of CSS stylesheets
                                '../libs/bootstrap-4.0.0/dist/css/bootstrap.min.css',
                                '../libs/fontawesome-free-6.5.1-web/css/all.min.css',
                                '../css/styles.css'
    ];
    protected array $scripts = [ // Array of JavaScript files
                                 '../libs/jquery/jquery.min.js',
                                 '../libs/bootstrap-4.0.0/dist/js/bootstrap.min.js',
    ];

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function __construct()
    {

        // Get current user
        $this->user = User::instance()->get();

        // logging others users
        if (!$this->user) {
            View::instance()->add($_SERVER);
        }
    }

    /**
     * Method executed before processing the request.
     *
     * @return void
     */
    protected function before(): void
    {
        if ($this->needLogin && $this->user === null) {
            $this->redirect('/admin', $_SERVER['REQUEST_URI']);
        }

        $this->title = "Main | Task manager";
        $this->content = '';
    }

    /**
     * Generates the base template
     *
     * @return void
     */
    protected function render(): void
    {
        $vars = [
            'title' => $this->title,
            'content' => $this->content,
            'user' =>  $this->user,
            'keywords' => $this->keywords,
            'description' => $this->description,
            'styles' => $this->styles,
            'scripts' => $this->scripts,
        ];

        $page = $this->template('src/view/base_templates/v_main.php', $vars);
        echo $page;

        // Clearing success message
        unset($_SESSION['success']);
    }
}
