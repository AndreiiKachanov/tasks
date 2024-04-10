<?php

namespace App\Controller;

use App\Model\Task as MTask;
use App\Pagination\Pagination;
use Exception;

/**
 *  Class TaskController
 *
 * Represents a controller responsible for handling tasks-related functionality in the application.
 */
class TaskController extends BaseController
{
    protected const STRING FIELDS = 'id, img, author, email, status';
    protected string $tableName;

    public function __construct()
    {
        // Set the table name for tasks
        $this->tableName = MTask::$tableName;
        parent::__construct();
    }

    /**
     * Executed before template generation
     *
     * @return void
     */
    protected function before(): void
    {
        parent::before();
    }

    /**
     * Generates content for the main page - a list of all tasks
     *
     * @return void
     */
    protected function actionIndex(): void
    {
        $sortField = $_GET['sort'] ?? 'id';
        $sortOrder = $_GET['order'] ?? 'desc';
        $sorting = ['field' => $sortField, 'order' => $sortOrder];

        $this->keywords = 'all tasks, task manager, todo manager, task list, task app';
        $this->description = 'task manager';

        // Generating page content with pagination
        $page = $this->generatePageContent(TASKS_PER_PAGE, self::FIELDS, new Pagination($this->tableName, 'tasks'), false, $sorting);
        $this->content = $this->template('src/view/tasks/v_index.php', $page);
    }

    /**
     * Generates content for the "My Tasks" page
     *
     * @return void
     */
    protected function actionMyTasks(): void
    {
        if (!$this->user) {
            $this->p404();
        }

        $sortField = $_GET['sort'] ?? 'id';
        $sortOrder = $_GET['order'] ?? 'desc';
        $sorting = ['field' => $sortField, 'order' => $sortOrder];

        $this->keywords = 'my tasks, task manager, my task list';
        $this->description = 'my task list';
        // Generating page content with pagination
        $page = $this->generatePageContent(TASKS_PER_PAGE, self::FIELDS, new Pagination($this->tableName, 'my-tasks'), true, $sorting);
        $this->content = $this->template('src/view/tasks/v_index.php', $page);
    }

    /**
     * Adds a new task.
     *
     * @return void
     * @throws Exception
     */
    protected function actionAdd(): void
    {
        $fields = [];
        $errors = [];

        if ($this->isPost()) {
            $dataToInsert = array_merge($_POST, $_FILES);
            $mTask = MTask::instance();

            if ($mTask->add($dataToInsert)) {
                $_SESSION['success'] = 'Задача успешно добавлена';
                $this->redirect('/');
            }
            $errors = $mTask->errors();
            $fields = $_POST;
        }

        // Editor
        $this->scripts[] = '../libs/ckeditor/ckeditor.js';
        // Validation
        $this->scripts[] = '../libs/jquery/jquery.validate.min.js';
        // Needed for validation of file extensions, size, etc.
        $this->scripts[] = '../libs/jquery/additional-methods.min.js';
        // Custom script
        $this->scripts[] = '../js/formHandling.js';

        $this->title = 'Task Manager | Add Task';
        $this->content = $this->template('src/view/tasks/v_add.php', [
            'errors' => $errors,
            'fields' => $fields
        ]);
    }

    /**
     * Displays a specific task.
     *
     * @return void
     */
    protected function actionShow(): void
    {
        $taskId = (int)$this->params[1] ?? $this->p404();

        $mTask = MTask::instance();
        $task = $mTask->get($taskId);

        if (!$task) {
            $this->p404();
        }
        $this->title = 'View Task';
        $this->content = $this->template('src/view/tasks/v_task.php', [
            'item' => $task
        ]);
    }

    /**
     * Edits an existing task.
     *
     * @return void
     * @throws Exception
     */
    protected function actionEdit(): void
    {
        $errors = [];
        // Is the user authenticated?
        if (!$this->user) {
            $this->p404();
        }
        // Task ID from the URI
        $taskId = (int)$this->params[1] ?? $this->p404();
        $mTask = MTask::instance();
        $task = $mTask->get($taskId);

        if (!$task) {
            $this->p404();
        }

        $returnBack = isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] !== '' ? $_SERVER['HTTP_REFERER'] : '/';

        if (!empty($_POST['delete'])) {
            $mTask->delete($taskId);
            $_SESSION['success'] = 'Задача удачно удалена';
            $this->redirect($_POST['redirect']);
        }

        if (!empty($_POST['save'])) {
            if ($mTask->edit($taskId, $_POST)) {
                $_SESSION['success'] = 'Задача удачно обновлена';
                $this->redirect($_POST['redirect']);
            }
            $errors = $mTask->errors();
            $task = $_POST;
        }

        // Editor
        $this->scripts[] = '../libs/ckeditor/ckeditor.js';
        // Validation
        $this->scripts[] = '../libs/jquery/jquery.validate.min.js';
        // Needed for validation of file extensions, size, etc.
        $this->scripts[] = '../libs/jquery/additional-methods.min.js';
        // Custom script
        $this->scripts[] = '../js/formHandling.js';

        $this->title = 'Edit Task';
        $this->content = $this->template('src/view/tasks/v_edit.php', [
            'errors' => $errors,
            'fields' => $task,
            'status' => ['Не определено', 'Выполнено', 'Выполняется', 'Просрочено'],
            'returnBack' => $returnBack
        ]);
    }

    /**
     * Handles AJAX request to preview task content.
     *
     * @return void
     * @throws Exception
     */
    protected function actionPreview(): void
    {
        if (!$this->isAjax()) {
            $this->p404();
        }

        if (isset($_FILES['file']) && $_FILES['file']['size'] > 0) {
            $task['img'] =  MTask::instance()->saveImage(IMG_DIR_PREV, $_FILES['file']);
        }

        $task['author'] = $_POST['author'];
        $task['email'] = $_POST['email'];
        $task['status'] = 'Не определено';
        $task['content'] = $_POST['content'];
        echo $this->content = $this->template('src/view/tasks/v_preview.php', ['item' => $task]);
        die;
    }

    /**
     * Displays a 404 error page.
     *
     * @return void
     */
    protected function action404(): void
    {
        $this->title = '404 - Not Found';
        $this->content = $this->template('src/view/v_404.php');
    }
}
