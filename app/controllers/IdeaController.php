<?php

class IdeaController extends Controller
{
    private $ideaModel;
    private $taskModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->ideaModel = new Idea();
        $this->taskModel = new Task();
    }
    
    public function get_ideas()
    {
        $this->requireLogin();
        
        if ($this->isAdmin) {
            $ideas = $this->ideaModel->getAllIdeas();
        } else {
            $ideas = $this->ideaModel->getUserIdeas($this->user['id']);
        }
        
        foreach ($ideas as &$idea) {
            $idea['task_count'] = $this->taskModel->countTotalTasks($idea['id']);
            $idea['validated_count'] = $this->taskModel->countValidatedTasks($idea['id']);
            $idea['can_transition'] = $this->ideaModel->canTransitionToDevelopment($idea['id']);
        }
        
        $this->jsonResponse($ideas);
    }
    
    public function get_idea()
    {
        $this->requireLogin();
        
        $id = $this->getGet('id');
        
        if (!$id) {
            $this->jsonResponse(['error' => 'Hiányzó azonosító'], 400);
        }
        
        $idea = $this->ideaModel->getById($id);
        
        if (!$idea) {
            $this->jsonResponse(['error' => 'Ötlet nem található'], 404);
        }
        
        if ($idea['user_id'] !== $this->user['id'] && !$this->isAdmin) {
            $this->jsonResponse(['error' => 'Nincs jogosultság'], 403);
        }
        
        $idea['task_count'] = $this->taskModel->countTotalTasks($idea['id']);
        $idea['validated_count'] = $this->taskModel->countValidatedTasks($idea['id']);
        $idea['can_transition'] = $this->ideaModel->canTransitionToDevelopment($idea['id']);
        
        $this->jsonResponse($idea);
    }
    
    public function create_idea()
    {
        $this->requireLogin();
        
        $input = $this->getInput();
        $name = $input['name'] ?? $input['title'] ?? '';
        $description = $input['description'] ?? '';
        $problem = $input['problem'] ?? '';
        $targetAudience = $input['target_audience'] ?? $input['targetAudience'] ?? '';
        
        if (!$name || !$description) {
            $this->jsonResponse(['error' => 'Hiányzó adatok'], 400);
        }
        
        $id = $this->ideaModel->createIdea($this->user['id'], $name, $description, $problem, $targetAudience);
        
        $this->logActivity('create_idea', $id);
        
        if (!empty($_POST) && isset($_SERVER['HTTP_REFERER'])) {
            header('Location: /ideas');
            exit;
        }
        
        $this->jsonResponse(['id' => $id, 'status' => 'ok']);
    }
    
    public function update_idea()
    {
        $this->requireLogin();
        
        $input = $this->getInput();
        $id = $input['id'] ?? '';
        $name = $input['name'] ?? $input['title'] ?? '';
        $description = $input['description'] ?? '';
        $problem = $input['problem'] ?? '';
        $targetAudience = $input['target_audience'] ?? $input['targetAudience'] ?? '';
        
        if (!$id) {
            $this->jsonResponse(['error' => 'Hiányzó azonosító'], 400);
        }
        
        $idea = $this->ideaModel->getById($id);
        
        if (!$idea) {
            $this->jsonResponse(['error' => 'Ötlet nem található'], 404);
        }
        
        if ($idea['user_id'] !== $this->user['id'] && !$this->isAdmin) {
            $this->jsonResponse(['error' => 'Nincs jogosultság'], 403);
        }
        
        $data = [];
        if ($name) $data['name'] = $name;
        if ($description) $data['description'] = $description;
        if (isset($problem)) $data['problem'] = $problem;
        if (isset($targetAudience)) $data['target_audience'] = $targetAudience;
        if (isset($input['tags'])) {
            $tagsInput = $input['tags'];
            if (is_array($tagsInput)) {
                $data['tags'] = json_encode($tagsInput);
            } elseif (is_string($tagsInput)) {
                $decoded = json_decode($tagsInput, true);
                $data['tags'] = json_encode($decoded ?? []);
            }
        }
        
        $this->ideaModel->updateIdea($id, $data);
        
        $this->logActivity('update_idea', $id);
        
        if (!empty($_POST) && isset($_SERVER['HTTP_REFERER'])) {
            header('Location: /ideas');
            exit;
        }
        
        $this->jsonResponse(['status' => 'ok']);
    }
    
    public function update_tags()
    {
        $this->requireLogin();
        
        $input = $this->getInput();
        $id = $input['id'] ?? '';
        $tags = $input['tags'] ?? [];
        
        if (!$id) {
            $this->jsonResponse(['error' => 'Hiányzó azonosító'], 400);
        }
        
        $idea = $this->ideaModel->getById($id);
        
        if (!$idea) {
            $this->jsonResponse(['error' => 'Ötlet nem található'], 404);
        }
        
        if ($idea['user_id'] !== $this->user['id'] && !$this->isAdmin) {
            $this->jsonResponse(['error' => 'Nincs jogosultság'], 403);
        }
        
        $this->ideaModel->updateIdea($id, ['tags' => json_encode($tags)]);
        
        $this->jsonResponse(['status' => 'ok', 'tags' => $tags]);
    }
    
    public function transition_phase()
    {
        $this->requireLogin();
        
        $input = $this->getInput();
        $id = $input['id'] ?? '';
        
        if (!$id) {
            $this->jsonResponse(['error' => 'Hiányzó azonosító'], 400);
        }
        
        $idea = $this->ideaModel->getById($id);
        
        if (!$idea) {
            $this->jsonResponse(['error' => 'Ötlet nem található'], 404);
        }
        
        if ($idea['user_id'] !== $this->user['id'] && !$this->isAdmin) {
            $this->jsonResponse(['error' => 'Nincs jogosultság'], 403);
        }
        
        if ($idea['phase'] !== Idea::PHASE_MVP) {
            $this->jsonResponse(['error' => 'Az ötlet már a fejlesztési fázisban van'], 400);
        }
        
        if (!$this->ideaModel->canTransitionToDevelopment($id)) {
            $this->jsonResponse(['error' => 'Minden feladatnak validálva kell lennie a fázisváltáshoz'], 400);
        }
        
        $result = $this->ideaModel->transitionToDevelopment($id);
        
        if ($result) {
            $this->logActivity('transition_to_development', $id);
            $this->jsonResponse(['status' => 'ok', 'phase' => Idea::PHASE_DEVELOPMENT]);
        } else {
            $this->jsonResponse(['error' => 'Hiba a fázisváltás során'], 500);
        }
    }
    
    public function share_idea()
    {
        $this->requireLogin();
        
        $input = $this->getInput();
        $id = $input['id'] ?? '';
        $shareType = $input['share_type'] ?? 'private';
        $specificEmails = $input['specific_emails'] ?? '';
        
        $validTypes = ['private', 'public', 'registered', 'specific'];
        if (!in_array($shareType, $validTypes)) {
            $shareType = 'private';
        }
        
        if (!$id) {
            $this->jsonResponse(['error' => 'Hiányzó azonosító'], 400);
        }
        
        $idea = $this->ideaModel->getById($id);
        
        if (!$idea) {
            $this->jsonResponse(['error' => 'Ötlet nem található'], 404);
        }
        
        if ($idea['user_id'] !== $this->user['id'] && !$this->isAdmin) {
            $this->jsonResponse(['error' => 'Nincs jogosultság'], 403);
        }
        
        $isPublic = in_array($shareType, ['public', 'registered', 'specific']);
        $shareToken = $isPublic ? ($idea['share_token'] ?: bin2hex(random_bytes(16))) : null;
        
        $updateData = [
            'is_public' => $isPublic ? 1 : 0,
            'share_token' => $shareToken,
            'share_type' => $shareType
        ];
        
        if ($shareType === 'specific' && $specificEmails) {
            $updateData['specific_emails'] = $specificEmails;
        } elseif ($shareType !== 'specific') {
            $updateData['specific_emails'] = null;
        }
        
        $this->ideaModel->updateIdea($id, $updateData);
        
        $shareUrl = $isPublic ? 'http://ideaforge.uzletinovekedes.hu/shared/' . $shareToken : null;
        
        $this->jsonResponse([
            'status' => 'ok', 
            'is_public' => $isPublic,
            'share_type' => $shareType,
            'specific_emails' => $shareType === 'specific' ? $specificEmails : null,
            'share_url' => $shareUrl
        ]);
    }
    
    public function can_transition()
    {
        $this->requireLogin();
        
        $id = $this->getGet('id');
        
        if (!$id) {
            $this->jsonResponse(['error' => 'Hiányzó azonosító'], 400);
        }
        
        $idea = $this->ideaModel->getById($id);
        
        if (!$idea) {
            $this->jsonResponse(['error' => 'Ötlet nem található'], 404);
        }
        
        if ($idea['user_id'] !== $this->user['id'] && !$this->isAdmin) {
            $this->jsonResponse(['error' => 'Nincs jogosultság'], 403);
        }
        
        $canTransition = $this->ideaModel->canTransitionToDevelopment($id);
        $taskCount = $this->taskModel->countTotalTasks($id);
        $validatedCount = $this->taskModel->countValidatedTasks($id);
        
        $this->jsonResponse([
            'can_transition' => $canTransition,
            'task_count' => $taskCount,
            'validated_count' => $validatedCount,
            'current_phase' => $idea['phase']
        ]);
    }
    
    public function delete_idea()
    {
        $this->requireLogin();
        
        $input = $this->getInput();
        $id = $input['id'] ?? '';
        
        if (!$id) {
            $this->jsonResponse(['error' => 'Hiányzó azonosító'], 400);
        }
        
        $idea = $this->ideaModel->getById($id);
        
        if (!$idea) {
            $this->jsonResponse(['error' => 'Ötlet nem található'], 404);
        }
        
        if ($idea['user_id'] !== $this->user['id'] && !$this->isAdmin) {
            $this->jsonResponse(['error' => 'Nincs jogosultság'], 403);
        }
        
        $this->ideaModel->deleteIdea($id);
        
        $this->logActivity('delete_idea', $id);
        
        $this->jsonResponse(['status' => 'ok']);
    }
    
    public function search_ideas()
    {
        $this->requireLogin();
        
        $query = $this->getGet('q') ?? '';
        
        if (!$query) {
            $this->jsonResponse([]);
        }
        
        $ideas = $this->ideaModel->search($query);
        $this->jsonResponse($ideas);
    }
    
    private function generateUuid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
    
    private function logActivity($action, $ideaId)
    {
        try {
            $stmt = $this->db->getConnection()->prepare(
                "INSERT INTO activity_log (id, user_id, action, details, ip_address) VALUES (?, ?, ?, ?, ?)"
            );
            
            $id = $this->generateUuid();
            $details = json_encode(['idea_id' => $ideaId]);
            
            $stmt->execute([
                $id,
                $this->user['id'],
                $action,
                $details,
                $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
        } catch (Exception $e) {
        }
    }
}
