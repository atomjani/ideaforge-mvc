<?php include __DIR__ . '/../layouts/header.php'; 
$pageTitle = 'Statisztikák';
?>

<h1 class="text-xl md:text-2xl font-bold mb-4 md:mb-6">Statisztikák</h1>

<div class="grid grid-cols-2 md:grid-cols-4 gap-2 md:gap-6 mb-4 md:mb-8">
    <div class="bg-white p-3 md:p-6 rounded-lg shadow text-center">
        <div class="text-gray-500 text-xs md:text-sm">Ötletek</div>
        <div class="text-2xl md:text-3xl font-bold text-indigo-600"><?= $stats['totalIdeas'] ?? 0 ?></div>
    </div>
    <div class="bg-white p-3 md:p-6 rounded-lg shadow text-center">
        <div class="text-gray-500 text-xs md:text-sm">Feladatok</div>
        <div class="text-2xl md:text-3xl font-bold text-blue-600"><?= $stats['totalTasks'] ?? 0 ?></div>
    </div>
    <div class="bg-white p-3 md:p-6 rounded-lg shadow text-center">
        <div class="text-gray-500 text-xs md:text-sm">Befejezett</div>
        <div class="text-2xl md:text-3xl font-bold text-green-600"><?= $stats['completedTasks'] ?? 0 ?></div>
    </div>
    <div class="bg-white p-3 md:p-6 rounded-lg shadow text-center">
        <div class="text-gray-500 text-xs md:text-sm">Felhasználók</div>
        <div class="text-2xl md:text-3xl font-bold text-purple-600"><?= $stats['totalUsers'] ?? 0 ?></div>
    </div>
</div>

<h2 class="text-lg md:text-xl font-semibold mb-3 md:mb-4">Ötletek fázisok szerint</h2>
<div class="bg-white p-3 md:p-6 rounded-lg shadow mb-6">
    <?php 
    $mvpCount = 0;
    $devCount = 0;
    if (!empty($stats['ideasByPhase'])) {
        foreach ($stats['ideasByPhase'] as $phase) {
            if ($phase['phase'] === 'MVP_CREATION') $mvpCount = $phase['count'];
            if ($phase['phase'] === 'DEVELOPMENT') $devCount = $phase['count'];
        }
    }
    ?>
    <div class="flex justify-between items-center py-2 border-b">
        <span class="font-medium">MVP fázis</span>
        <span class="text-xl md:text-2xl font-bold text-indigo-600"><?= $mvpCount ?></span>
    </div>
    <div class="flex justify-between items-center py-2">
        <span class="font-medium">Fejlesztés fázis</span>
        <span class="text-xl md:text-2xl font-bold text-green-600"><?= $devCount ?></span>
    </div>
</div>

<h2 class="text-lg md:text-xl font-semibold mb-3 md:mb-4">Ötlet sikeresség</h2>
<div class="bg-white p-3 md:p-6 rounded-lg shadow mb-6">
    <div class="flex justify-between items-center py-2 border-b">
        <span class="font-medium">Nyereséges ötletek</span>
        <span class="text-xl md:text-2xl font-bold text-green-600"><?= $stats['profitableCount'] ?? 0 ?></span>
    </div>
    <div class="flex justify-between items-center py-2">
        <span class="font-medium">Népszerű ötletek</span>
        <span class="text-xl md:text-2xl font-bold text-yellow-600"><?= $stats['popularCount'] ?? 0 ?></span>
    </div>
</div>

<?php if ($stats['totalTasks'] > 0): ?>
<h2 class="text-lg md:text-xl font-semibold mb-3 md:mb-4">Feladatok státusz szerint</h2>
<div class="bg-white p-3 md:p-6 rounded-lg shadow mb-6">
    <?php
    $statuses = $stats['tasksByStatus'] ?? [];
    $statusMap = [
        'BACKLOG' => 'Backlog',
        'REVIEW' => 'Áttekintés',
        'READY_FOR_DEV' => 'Fejlesztésre kész',
        'IN_PROGRESS' => 'Folyamatban',
        'LIVE_TESTING' => 'Tesztelés',
        'VALIDATED' => 'Validálva'
    ];
    $statusColors = [
        'BACKLOG' => 'bg-gray-100 text-gray-700',
        'REVIEW' => 'bg-yellow-100 text-yellow-700',
        'READY_FOR_DEV' => 'bg-blue-100 text-blue-700',
        'IN_PROGRESS' => 'bg-purple-100 text-purple-700',
        'LIVE_TESTING' => 'bg-orange-100 text-orange-700',
        'VALIDATED' => 'bg-green-100 text-green-700'
    ];
    foreach ($statuses as $status): ?>
    <div class="flex justify-between items-center py-2 border-b last:border-0">
        <span class="px-2 py-1 rounded text-xs <?= $statusColors[$status['status']] ?? 'bg-gray-100' ?>">
            <?= $statusMap[$status['status']] ?? $status['status'] ?>
        </span>
        <span class="text-xl font-bold"><?= $status['count'] ?></span>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
