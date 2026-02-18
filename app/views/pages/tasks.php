<?php include __DIR__ . '/../layouts/header.php'; 
$pageTitle = 'Feladatok';
?>

<h1 class="text-xl md:text-2xl font-bold mb-4">Feladatok</h1>

<div class="flex flex-wrap gap-2 mb-4">
    <select id="filterStatus" onchange="filterTasks()" class="border rounded px-3 py-2 text-sm">
        <option value="">Minden státusz</option>
        <option value="BACKLOG">Backlog</option>
        <option value="REVIEW">Áttekintés</option>
        <option value="READY_FOR_DEV">Fejlesztésre kész</option>
        <option value="IN_PROGRESS">Folyamatban</option>
        <option value="LIVE_TESTING">Tesztelés</option>
        <option value="VALIDATED">Validálva</option>
    </select>
    
    <select id="filterIdea" onchange="filterTasks()" class="border rounded px-3 py-2 text-sm">
        <option value="">Minden ötlet</option>
        <?php foreach ($ideas as $idea): ?>
        <option value="<?= $idea['id'] ?>"><?= htmlspecialchars($idea['name'] ?? $idea['title'] ?? '') ?></option>
        <?php endforeach; ?>
    </select>
    
    <input type="text" id="searchTasks" onkeyup="filterTasks()" placeholder="Keresés..." 
           class="border rounded px-3 py-2 text-sm">
</div>

<div class="overflow-x-auto">
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
        
        $allTasks = [];
        foreach ($statuses as $status => $info) {
            $allTasks[$status] = $$status;
        }
        ?>
        
        <?php foreach ($statuses as $status => $info): ?>
        <div class="w-56 md:w-72 flex-shrink-0">
            <div class="rounded-lg <?= $info['color'] ?> p-2 md:p-3">
                <h3 class="font-semibold text-xs md:text-sm mb-2 text-center md:text-left">
                    <?= $info['title'] ?> (<?= count($allTasks[$status]) ?>)
                </h3>
                <div class="space-y-2 task-card-container" data-status="<?= $status ?>">
                    <?php if (!empty($allTasks[$status])): ?>
                    <?php foreach ($allTasks[$status] as $task): ?>
                    <div class="bg-white rounded shadow-sm p-2 md:p-3 task-card" 
                         data-status="<?= $task['status'] ?>"
                         data-idea="<?= $task['idea_id'] ?>"
                         data-name="<?= strtolower(htmlspecialchars($task['description'] ?? '')) ?>">
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
                        </div>
                        <p class="text-xs md:text-sm text-gray-800 mb-1 line-clamp-2"><?= htmlspecialchars($task['description'] ?? $task['title'] ?? '') ?></p>
                        <p class="text-xs text-gray-500 mb-1"><?= htmlspecialchars($task['idea_title'] ?? '') ?></p>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">ICE: <?= ($task['ice_impact'] + $task['ice_confidence'] + $task['ice_ease']) ?></span>
                            <form method="POST" action="/api.php?action=update_task" class="inline">
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

<script>
function filterTasks() {
    const status = document.getElementById('filterStatus').value;
    const idea = document.getElementById('filterIdea').value;
    const search = document.getElementById('searchTasks').value.toLowerCase();
    
    const cards = document.querySelectorAll('.task-card');
    cards.forEach(card => {
        const cardStatus = card.dataset.status || '';
        const cardIdea = card.dataset.idea || '';
        const cardName = card.dataset.name || '';
        
        let show = true;
        
        if (status && cardStatus !== status) show = false;
        if (idea && cardIdea !== idea) show = false;
        if (search && !cardName.includes(search)) show = false;
        
        card.style.display = show ? '' : 'none';
    });
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
