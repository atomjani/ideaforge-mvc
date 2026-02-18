<?php

class TaskController extends Controller
{
    private $taskModel;
    private $ideaModel;
    private $commentModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->taskModel = new Task();
        $this->ideaModel = new Idea();
        $this->commentModel = new Comment();
    }
    
    public function get_tasks()
    {
        $this->requireLogin();
        
        $ideaId = $this->getGet('idea_id');
        
        if ($ideaId) {
            $tasks = $this->taskModel->getByIdea($ideaId);
        } else {
            $tasks = $this->taskModel->getAll('created_at DESC');
        }
        
        $this->jsonResponse($tasks);
    }
    
    public function get_board()
    {
        $this->requireLogin();
        
        $ideaId = $this->getGet('idea_id');
        
        if (!$ideaId) {
            $this->jsonResponse(['error' => 'Hiányzó ötlet azonosító'], 400);
        }
        
        $idea = $this->ideaModel->getById($ideaId);
        
        if (!$idea) {
            $this->jsonResponse(['error' => 'Ötlet nem található'], 404);
        }
        
        if ($idea['user_id'] !== $this->user['id'] && !$this->isAdmin) {
            $this->jsonResponse(['error' => 'Nincs jogosultság'], 403);
        }
        
        $board = $this->taskModel->getByIdeaGroupedByStatus($ideaId);
        
        $this->jsonResponse([
            'board' => $board,
            'idea' => $idea
        ]);
    }
    
    public function create_task()
    {
        $this->requireLogin();
        
        $input = $this->getInput();
        $ideaId = $input['idea_id'] ?? '';
        $name = $input['name'] ?? '';
        $description = $input['description'] ?? $input['title'] ?? '';
        $priority = $input['priority'] ?? Task::PRIORITY_MUST_HAVE;
        $module = $input['module'] ?? '';
        $type = $input['type'] ?? Task::TYPE_FEATURE;
        $iceImpact = $input['ice_impact'] ?? $input['iceImpact'] ?? 5;
        $iceConfidence = $input['ice_confidence'] ?? $input['iceConfidence'] ?? 5;
        $iceEase = $input['ice_ease'] ?? $input['iceEase'] ?? 5;
        
        if (!$ideaId || (!$name && !$description)) {
            $this->jsonResponse(['error' => 'Hiányzó adatok'], 400);
        }
        
        $idea = $this->ideaModel->getById($ideaId);
        
        if (!$idea) {
            $this->jsonResponse(['error' => 'Ötlet nem található'], 404);
        }
        
        if ($idea['user_id'] !== $this->user['id'] && !$this->isAdmin) {
            $this->jsonResponse(['error' => 'Nincs jogosultság'], 403);
        }
        
        $id = $this->taskModel->createTask(
            $ideaId,
            $description,
            $priority,
            $module,
            $type,
            $iceImpact,
            $iceConfidence,
            $iceEase,
            $name
        );
        
        if (!empty($_POST) && isset($_SERVER['HTTP_REFERER'])) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        
        $this->jsonResponse(['id' => $id, 'status' => 'ok']);
    }
    
    public function update_task()
    {
        $this->requireLogin();
        
        $input = $this->getInput();
        $id = $input['id'] ?? '';
        
        if (!$id) {
            $this->jsonResponse(['error' => 'Hiányzó azonosító'], 400);
        }
        
        $task = $this->taskModel->getById($id);
        
        if (!$task) {
            $this->jsonResponse(['error' => 'Feladat nem található'], 404);
        }
        
        $idea = $this->ideaModel->getById($task['idea_id']);
        
        if ($idea['user_id'] !== $this->user['id'] && !$this->isAdmin) {
            $this->jsonResponse(['error' => 'Nincs jogosultság'], 403);
        }
        
        $data = [];
        
        if (isset($input['description'])) $data['description'] = $input['description'];
        if (isset($input['priority'])) $data['priority'] = $input['priority'];
        if (isset($input['status'])) $data['status'] = $input['status'];
        if (isset($input['module'])) $data['module'] = $input['module'];
        if (isset($input['type'])) $data['type'] = $input['type'];
        if (isset($input['ice_impact'])) $data['ice_impact'] = intval($input['ice_impact']);
        if (isset($input['ice_confidence'])) $data['ice_confidence'] = intval($input['ice_confidence']);
        if (isset($input['ice_ease'])) $data['ice_ease'] = intval($input['ice_ease']);
        
        $this->taskModel->updateTask($id, $data);
        
        if (!empty($_POST) && isset($_SERVER['HTTP_REFERER'])) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        
        $this->jsonResponse(['status' => 'ok']);
    }
    
    public function update_status()
    {
        $this->requireLogin();
        
        $input = $this->getInput();
        $id = $input['id'] ?? '';
        $status = $input['status'] ?? '';
        
        if (!$id || !$status) {
            $this->jsonResponse(['error' => 'Hiányzó adatok'], 400);
        }
        
        $task = $this->taskModel->getById($id);
        
        if (!$task) {
            $this->jsonResponse(['error' => 'Feladat nem található'], 404);
        }
        
        $idea = $this->ideaModel->getById($task['idea_id']);
        
        if ($idea['user_id'] !== $this->user['id'] && !$this->isAdmin) {
            $this->jsonResponse(['error' => 'Nincs jogosultság'], 403);
        }
        
        $this->taskModel->updateStatus($id, $status);
        
        $this->jsonResponse(['status' => 'ok']);
    }
    
    public function delete_task()
    {
        $this->requireLogin();
        
        $input = $this->getInput();
        $id = $input['id'] ?? '';
        
        if (!$id) {
            $this->jsonResponse(['error' => 'Hiányzó azonosító'], 400);
        }
        
        $task = $this->taskModel->getById($id);
        
        if (!$task) {
            $this->jsonResponse(['error' => 'Feladat nem található'], 404);
        }
        
        $idea = $this->ideaModel->getById($task['idea_id']);
        
        if ($idea['user_id'] !== $this->user['id'] && !$this->isAdmin) {
            $this->jsonResponse(['error' => 'Nincs jogosultság'], 403);
        }
        
        $this->taskModel->deleteTask($id);
        
        $this->jsonResponse(['status' => 'ok']);
    }
    
    public function get_comments()
    {
        $this->requireLogin();
        
        $taskId = $this->getGet('task_id');
        
        if (!$taskId) {
            $this->jsonResponse(['error' => 'Hiányzó feladat azonosító'], 400);
        }
        
        $task = $this->taskModel->getById($taskId);
        
        if (!$task) {
            $this->jsonResponse(['error' => 'Feladat nem található'], 404);
        }
        
        $idea = $this->ideaModel->getById($task['idea_id']);
        
        if ($idea['user_id'] !== $this->user['id'] && !$this->isAdmin) {
            $this->jsonResponse(['error' => 'Nincs jogosultság'], 403);
        }
        
        $comments = $this->commentModel->getByTask($taskId);
        
        $this->jsonResponse($comments);
    }
    
    public function add_comment()
    {
        $this->requireLogin();
        
        $input = $this->getInput();
        $taskId = $input['task_id'] ?? '';
        $text = $input['text'] ?? '';
        
        if (!$taskId || !$text) {
            $this->jsonResponse(['error' => 'Hiányzó adatok'], 400);
        }
        
        $task = $this->taskModel->getById($taskId);
        
        if (!$task) {
            $this->jsonResponse(['error' => 'Feladat nem található'], 404);
        }
        
        $idea = $this->ideaModel->getById($task['idea_id']);
        
        if ($idea['user_id'] !== $this->user['id'] && !$this->isAdmin) {
            $this->jsonResponse(['error' => 'Nincs jogosultság'], 403);
        }
        
        $id = $this->commentModel->createComment($taskId, $this->user['id'], $text);
        
        if (!empty($_POST) && isset($_SERVER['HTTP_REFERER'])) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        
        $this->jsonResponse(['id' => $id, 'status' => 'ok']);
    }
    
    public function delete_comment()
    {
        $this->requireLogin();
        
        $input = $this->getInput();
        $id = $input['id'] ?? '';
        
        if (!$id) {
            $this->jsonResponse(['error' => 'Hiányzó azonosító'], 400);
        }
        
        $comments = $this->commentModel->getAll();
        $comment = null;
        
        foreach ($comments as $c) {
            if ($c['id'] === $id) {
                $comment = $c;
                break;
            }
        }
        
        if (!$comment) {
            $this->jsonResponse(['error' => 'Hozzászólás nem található'], 404);
        }
        
        if ($comment['user_id'] !== $this->user['id'] && !$this->isAdmin) {
            $this->jsonResponse(['error' => 'Nincs jogosultság'], 403);
        }
        
        $this->commentModel->deleteComment($id);
        
        $this->jsonResponse(['status' => 'ok']);
    }
}
