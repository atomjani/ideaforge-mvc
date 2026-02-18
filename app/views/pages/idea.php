<?php include __DIR__ . '/../layouts/header.php'; 
$pageTitle = $idea['name'] ?? 'Ötlet';
?>

<style>
.overflow-x-auto {
    -webkit-overflow-scrolling: touch;
    scroll-behavior: smooth;
}
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<div class="mb-4">
    <a href="/ideas" class="text-indigo-600 hover:underline">&larr; Vissza az ötletekhez</a>
</div>

<?php if (!$idea): ?>
<p class="text-red-500">Az ötlet nem található.</p>
<?php else: ?>

<div class="bg-white rounded-lg shadow p-6 mb-6">
    <div class="flex justify-between items-start mb-4">
        <div>
            <h1 class="text-2xl font-bold"><?= htmlspecialchars($idea['name']) ?></h1>
            <span class="text-sm px-2 py-1 rounded <?= (!empty($idea['phase']) && $idea['phase'] === 'MVP_CREATION') ? 'bg-indigo-100 text-indigo-700' : 'bg-green-100 text-green-700' ?>">
                <?= (!empty($idea['phase']) && $idea['phase'] === 'MVP_CREATION') ? 'MVP létrehozás' : 'Fejlesztés' ?>
            </span>
        </div>
        
        <?php if ((empty($idea['phase']) || $idea['phase'] === 'MVP_CREATION') && !empty($canTransition)): ?>
        <button onclick="transitionPhase()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 flex items-center gap-2">
            <i class="bi bi-check-circle"></i> Fejlesztési fázisba lépés
        </button>
        <?php endif; ?>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div>
            <h3 class="font-semibold text-sm text-gray-500 mb-1">Leírás</h3>
            <p class="text-gray-700"><?= nl2br(htmlspecialchars($idea['description'])) ?></p>
        </div>
        <?php if (!empty($idea['problem'])): ?>
        <div>
            <h3 class="font-semibold text-sm text-gray-500 mb-1">Probléma</h3>
            <p class="text-gray-700"><?= nl2br(htmlspecialchars($idea['problem'])) ?></p>
        </div>
        <?php endif; ?>
        <?php if (!empty($idea['target_audience'])): ?>
        <div>
            <h3 class="font-semibold text-sm text-gray-500 mb-1">Célközönség</h3>
            <p class="text-gray-700"><?= nl2br(htmlspecialchars($idea['target_audience'])) ?></p>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="flex items-center gap-4 text-sm text-gray-500">
        <span>Feladatok: <?= $taskCount ?? 0 ?></span>
        <span>Validált: <?= $validatedCount ?? 0 ?></span>
        <?php if (empty($idea['phase']) || $idea['phase'] === 'MVP_CREATION'): ?>
        <span class="<?= !empty($canTransition) ? 'text-green-600' : 'text-orange-600' ?>">
            <?= !empty($canTransition) ? 'Készen áll a fázisváltásra' : 'Még nem kész a fázisváltásra' ?>
        </span>
        <?php endif; ?>
    </div>
</div>

<div class="mb-4 flex justify-between items-center">
    <h2 class="text-xl font-bold">Kanban tábla</h2>
    <button onclick="document.getElementById('newTaskForm').classList.toggle('hidden')" 
        class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
        + Új feladat
    </button>
</div>

<div id="newTaskForm" class="hidden bg-white p-4 rounded-lg shadow mb-4">
    <h3 class="font-semibold mb-3">Új feladat hozzáadása</h3>
    <form method="POST" action="/api.php?action=create_task">
        <input type="hidden" name="idea_id" value="<?= $idea['id'] ?>">
        <input type="hidden" name="priority" value="MUST_HAVE">
<?php $isDevelopment = !empty($idea['phase']) && $idea['phase'] === 'DEVELOPMENT'; ?>
        <?php if ($isDevelopment): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Típus</label>
                <select name="type" class="w-full border rounded px-3 py-2">
                    <option value="FEATURE">Funkció</option>
                    <option value="BUG">Hiba</option>
                </select>
            </div>
        </div>
        <?php else: ?>
        <input type="hidden" name="type" value="FEATURE">
        <?php endif; ?>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Feladat neve *</label>
            <input type="text" name="name" required class="w-full border rounded px-3 py-2" placeholder="Feladat neve">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Leírás</label>
            <textarea name="description" rows="2" class="w-full border rounded px-3 py-2" placeholder="Feladat leírása"></textarea>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Modul</label>
                <input type="text" name="module" class="w-full border rounded px-3 py-2" placeholder="pl. Backend, Frontend, UI">
            </div>
        </div>
<?php $isDevelopment = !empty($idea['phase']) && $idea['phase'] === 'DEVELOPMENT'; ?>
        <?php if ($isDevelopment): ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Hatás (1-10)</label>
                <input type="number" name="ice_impact" min="1" max="10" value="5" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bizonyosság (1-10)</label>
                <input type="number" name="ice_confidence" min="1" max="10" value="5" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Könnyűség (1-10)</label>
                <input type="number" name="ice_ease" min="1" max="10" value="5" class="w-full border rounded px-3 py-2">
            </div>
        </div>
        <?php endif; ?>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Feladat hozzáadása</button>
    </form>
</div>

<div class="overflow-x-auto -mx-4 px-4 md:mx-0 md:px-0">
    <div class="flex gap-2 md:gap-4 min-w-max pb-4">
        <?php 
        $statuses = [
            'BACKLOG' => ['title' => 'Backlog', 'color' => 'bg-gray-100'],
            'REVIEW' => ['title' => 'Áttekintés', 'color' => 'bg-yellow-100'],
            'READY_FOR_DEV' => ['title' => 'Fejl.kész', 'color' => 'bg-blue-100'],
            'IN_PROGRESS' => ['title' => 'Folyamatban', 'color' => 'bg-purple-100'],
            'LIVE_TESTING' => ['title' => 'Tesztelés', 'color' => 'bg-orange-100'],
            'VALIDATED' => ['title' => 'Validálva', 'color' => 'bg-green-100']
        ];
        
        foreach ($statuses as $status => $info): ?>
        <div class="w-56 md:w-72 flex-shrink-0">
            <div class="rounded-lg <?= $info['color'] ?> p-2">
                <h3 class="font-semibold text-xs md:text-sm mb-2 text-center md:text-left"><?= $info['title'] ?></h3>
                <div class="space-y-2" id="status-<?= $status ?>">
                    <?php if (!empty($board[$status])): ?>
                    <?php foreach ($board[$status] as $task): ?>
                    <div class="bg-white rounded shadow-sm p-2 cursor-pointer hover:shadow-md transition-shadow" 
                         onclick="openTaskDetail('<?= $task['id'] ?>')">
                        <div class="flex justify-between items-start mb-1">
                            <span class="text-xs px-1.5 py-0.5 rounded <?= 
                                $task['priority'] === 'MUST_HAVE' ? 'bg-red-100 text-red-700' : 
                                ($task['priority'] === 'IMPORTANT' ? 'bg-orange-100 text-orange-700' : 
                                ($task['priority'] === 'NICE_TO_HAVE' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700'))
                            ?>">
                                <?= $task['priority'] === 'MUST_HAVE' ? 'Kötelező' : 
                                   ($task['priority'] === 'IMPORTANT' ? 'Fontos' : 
                                   ($task['priority'] === 'NICE_TO_HAVE' ? 'Jó' : 'Nem kell')) ?>
                            </span>
                            <span class="text-xs px-1.5 py-0.5 rounded <?= $task['type'] === 'FEATURE' ? 'bg-indigo-100 text-indigo-700' : 'bg-red-100 text-red-700' ?>">
                                <?= $task['type'] === 'FEATURE' ? 'Funkció' : 'Hiba' ?>
                            </span>
                        </div>
                        <p class="text-xs md:text-sm text-gray-800 mb-1 line-clamp-2"><?= htmlspecialchars($task['description']) ?></p>
                        <?php if (!empty($task['module'])): ?>
                        <p class="text-xs text-gray-500 mb-1"><i class="bi bi-collection"></i> <?= htmlspecialchars($task['module']) ?></p>
                        <?php endif; ?>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">ICE: <?= ($task['ice_impact'] + $task['ice_confidence'] + $task['ice_ease']) ?></span>
                            <form method="POST" action="/api.php?action=update_task" class="status-form inline">
                                <input type="hidden" name="id" value="<?= $task['id'] ?>">
                                <select name="status" onchange="this.form.submit()" class="text-xs border rounded px-1 py-0.5">
                                    <option value="BACKLOG" <?= $task['status']=='BACKLOG'?'selected':'' ?>>Backlog</option>
                                    <option value="REVIEW" <?= $task['status']=='REVIEW'?'selected':'' ?>>Áttekintés</option>
                                    <option value="READY_FOR_DEV" <?= $task['status']=='READY_FOR_DEV'?'selected':'' ?>>Fejl.kész</option>
                                    <option value="IN_PROGRESS" <?= $task['status']=='IN_PROGRESS'?'selected':'' ?>>Folyamatban</option>
                                    <option value="LIVE_TESTING" <?= $task['status']=='LIVE_TESTING'?'selected':'' ?>>Tesztelés</option>
                                    <option value="VALIDATED" <?= $task['status']=='VALIDATED'?'selected':'' ?>>Validálva</option>
                                </select>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div id="taskDetailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-2">
    <div class="bg-white rounded-lg max-w-2xl w-full mx-2 max-h-[90vh] overflow-y-auto">
        <div class="p-4 md:p-6">
            <div class="flex justify-between items-start mb-4">
                <h2 class="text-lg md:text-xl font-bold">Feladat részletei</h2>
                <button onclick="closeTaskDetail()" class="text-gray-500 hover:text-gray-700 p-1">
                    <i class="bi bi-x-lg text-xl"></i>
                </button>
            </div>
            
            <div id="taskDetailContent">
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Leírás</label>
                    <textarea id="taskDescription" rows="2" class="w-full border rounded px-3 py-2 text-sm"></textarea>
                </div>
                
        <?php $isDevelopment = !empty($idea['phase']) && $idea['phase'] === 'DEVELOPMENT'; ?>
        <?php if ($isDevelopment): ?>
                <div class="grid grid-cols-2 gap-2 md:gap-4 mb-3 md:mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prioritás</label>
                        <select id="taskPriority" class="w-full border rounded px-3 py-2 text-sm">
                            <option value="MUST_HAVE">Kötelező</option>
                            <option value="IMPORTANT">Fontos</option>
                            <option value="NICE_TO_HAVE">Jó lenne</option>
                            <option value="NOT_NEEDED">Nem kell</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Típus</label>
                        <select id="taskType" class="w-full border rounded px-3 py-2">
                            <option value="FEATURE">Funkció</option>
                            <option value="BUG">Hiba</option>
                        </select>
                    </div>
                </div>
                <?php else: ?>
                <input type="hidden" id="taskPriority" value="MUST_HAVE">
                <input type="hidden" id="taskType" value="FEATURE">
                <?php endif; ?>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Modul</label>
                    <input type="text" id="taskModule" class="w-full border rounded px-3 py-2">
                </div>
                
        <?php $isDevelopment = !empty($idea['phase']) && $idea['phase'] === 'DEVELOPMENT'; ?>
        <?php if ($isDevelopment): ?>
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Hatás</label>
                        <input type="number" id="taskIceImpact" min="1" max="10" class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bizonyosság</label>
                        <input type="number" id="taskIceConfidence" min="1" max="10" class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Könnyűség</label>
                        <input type="number" id="taskIceEase" min="1" max="10" class="w-full border rounded px-3 py-2">
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Státusz</label>
                    <select id="taskStatus" class="w-full border rounded px-3 py-2">
                        <option value="BACKLOG">Backlog</option>
                        <option value="REVIEW">Áttekintés</option>
                        <option value="READY_FOR_DEV">Fejlesztésre kész</option>
                        <option value="IN_PROGRESS">Folyamatban</option>
                        <option value="LIVE_TESTING">Éles tesztelés</option>
                        <option value="VALIDATED">Validálva</option>
                    </select>
                </div>
                
                <div class="flex gap-2 mb-4">
                    <button onclick="saveTask()" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Mentés</button>
                    <button onclick="deleteTask(currentTaskId)" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Törlés</button>
                </div>
                
                <hr class="my-4">
                
                <h3 class="font-semibold mb-3">Megjegyzések</h3>
                <div id="commentsList" class="space-y-3 mb-4 max-h-40 overflow-y-auto"></div>
                
                <form method="POST" action="/api.php?action=add_comment" id="commentForm" class="flex gap-2">
                    <input type="hidden" name="task_id" id="commentTaskId" value="">
                    <input type="text" name="text" id="commentText" placeholder="Új megjegyzés..." class="flex-1 border rounded px-3 py-2">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Küldés</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const ideaId = '<?= $idea['id'] ?>';
const ideaPhase = '<?= $idea['phase'] ?>';
let currentTaskId = null;
let tasksData = {};

<?php foreach ($board as $status => $tasks): ?>
<?php foreach ($tasks as $task): ?>
tasksData['<?= $task['id'] ?>'] = <?= json_encode($task) ?>;
<?php endforeach; ?>
<?php endforeach; ?>



function openTaskDetail(taskId) {
    currentTaskId = taskId;
    const task = tasksData[taskId];
    if (!task) return;
    
    document.getElementById('taskDescription').value = task.description || '';
    document.getElementById('taskPriority').value = task.priority || 'MUST_HAVE';
    document.getElementById('taskType').value = task.type || 'FEATURE';
    document.getElementById('taskModule').value = task.module || '';
    document.getElementById('taskIceImpact').value = task.ice_impact || 5;
    document.getElementById('taskIceConfidence').value = task.ice_confidence || 5;
    document.getElementById('taskIceEase').value = task.ice_ease || 5;
    document.getElementById('taskStatus').value = task.status || 'BACKLOG';
    document.getElementById('commentTaskId').value = taskId;
    
    loadComments(taskId);
    
    document.getElementById('taskDetailModal').classList.remove('hidden');
}

function closeTaskDetail() {
    document.getElementById('taskDetailModal').classList.add('hidden');
    currentTaskId = null;
}

async function saveTask() {
    if (!currentTaskId) return;
    
    const data = {
        id: currentTaskId,
        description: document.getElementById('taskDescription').value,
        priority: document.getElementById('taskPriority').value,
        type: document.getElementById('taskType').value,
        module: document.getElementById('taskModule').value,
        ice_impact: parseInt(document.getElementById('taskIceImpact').value),
        ice_confidence: parseInt(document.getElementById('taskIceConfidence').value),
        ice_ease: parseInt(document.getElementById('taskIceEase').value),
        status: document.getElementById('taskStatus').value
    };
    
    const res = await fetch('/api.php?action=update_task', {
        method: 'POST',
        body: new URLSearchParams(data)
    });
    const result = await res.json();
    
    if (result.status === 'ok') {
        location.reload();
    } else {
        alert(result.error || 'Hiba történt a mentéskor');
    }
}

async function deleteTask(taskId) {
    if (!confirm('Biztosan törlöd a feladatot?')) return;
    
    const res = await fetch('/api.php?action=delete_task', {
        method: 'POST',
        body: new URLSearchParams({id: taskId})
    });
    const result = await res.json();
    
    if (result.status === 'ok') {
        if (currentTaskId === taskId) {
            closeTaskDetail();
        }
        location.reload();
    } else {
        alert(result.error || 'Hiba történt');
    }
}

async function quickStatusChange(taskId, newStatus) {
    const res = await fetch('/api.php?action=update_task', {
        method: 'POST',
        body: new URLSearchParams({id: taskId, status: newStatus})
    });
    
    const result = await res.json();
    
    if (result.status === 'ok') {
        location.reload();
    } else {
        alert(result.error || 'Hiba történt a státusz változtatásakor');
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

async function loadComments(taskId) {
    const res = await fetch('/api.php?action=get_comments&task_id=' + taskId);
    const comments = await res.json();
    
    const container = document.getElementById('commentsList');
    if (comments.error) {
        container.innerHTML = '<p class="text-red-500">Hiba történt</p>';
        return;
    }
    container.innerHTML = comments.map(c => `
        <div class="bg-gray-50 rounded p-2">
            <p class="text-sm">${escapeHtml(c.text)}</p>
            <p class="text-xs text-gray-500">${escapeHtml(c.user_name || 'Ismeretlen')} - ${new Date(c.created_at).toLocaleString('hu-HU')}</p>
        </div>
    `).join('');
}

async function addComment() {
    if (!currentTaskId) return;
    
    const text = document.getElementById('commentText').value.trim();
    if (!text) return;
    
    const res = await fetch('/api.php?action=add_comment', {
        method: 'POST',
        body: new URLSearchParams({task_id: currentTaskId, text: text})
    });
    const result = await res.json();
    
    if (result.status === 'ok') {
        document.getElementById('commentText').value = '';
        loadComments(currentTaskId);
    } else {
        alert(result.error || 'Hiba történt');
    }
}

async function transitionPhase() {
    if (!confirm('Át szeretnéd lépni a fejlesztési fázisba? Ezután további funkciókat adhatsz hozzá.')) return;
    
    const res = await fetch('/api.php?action=transition_phase', {
        method: 'POST',
        body: new URLSearchParams({id: ideaId})
    });
    const result = await res.json();
    
    if (result.status === 'ok') {
        location.reload();
    } else {
        alert(result.error || 'Hiba történt');
    }
}
</script>

<?php endif; ?>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
