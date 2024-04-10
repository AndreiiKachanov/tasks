<?php

namespace App\Controller;

use App\Model\View;
use App\Pagination\Pagination;

/**
 *  Class ViewController
 *
 * Represents the controller responsible for site views.
 */
class ViewController extends BaseController
{
    protected const string FIELDS = 'id, ip, request_uri, ip_info, browser, platform, device, device_version, is_mobile, is_tablet, is_desktop, is_robot, created_at';
    protected string $tableName;

    public function __construct()
    {
        $this->tableName = View::$tableName;
        parent::__construct();
    }

    /**
     * Executed before template generation
     *
     * @return void
     */
    protected function before(): void
    {
        $this->needLogin = true;
        parent::before();
    }

    /**
     * @return void
     */
    protected function actionIndex(): void
    {
        // Generating page content with pagination
        $page = $this->generatePageContent(20, self::FIELDS, new Pagination($this->tableName, 'views'), false, null);
        $this->content = $this->template('src/view/views/v_index.php', $page);
    }
}
