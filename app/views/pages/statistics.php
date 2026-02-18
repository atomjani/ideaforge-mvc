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

<?php if ($stats['feedbackTotal'] > 0): ?>
<h2 class="text-lg md:text-xl font-semibold mb-3 md:mb-4">Felhasználói elégedettség</h2>
<div class="bg-white p-3 md:p-6 rounded-lg shadow mb-6">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <?php 
        function renderStars($rating, $color) {
            $fullStars = floor($rating);
            $hasHalf = ($rating - $fullStars) >= 0.5;
            $html = '<div class="text-' . $color . '">';
            for ($i = 1; $i <= 5; $i++) {
                if ($i <= $fullStars) {
                    $html .= '★';
                } elseif ($i == $fullStars + 1 && $hasHalf) {
                    $html .= '★';
                } else {
                    $html .= '<span class="text-gray-300">★</span>';
                }
            }
            $html .= '</div>';
            return $html;
        }
        ?>
        <div class="text-center">
            <div class="text-3xl md:text-4xl font-bold text-indigo-600"><?= $stats['avgOverall'] ?></div>
            <div class="text-xs text-gray-500">Összesített</div>
            <?= renderStars($stats['avgOverall'], 'yellow-400') ?>
        </div>
        <div class="text-center">
            <div class="text-3xl md:text-4xl font-bold text-blue-600"><?= $stats['avgIdeas'] ?></div>
            <div class="text-xs text-gray-500">Ötletek</div>
            <?= renderStars($stats['avgIdeas'], 'yellow-400') ?>
        </div>
        <div class="text-center">
            <div class="text-3xl md:text-4xl font-bold text-green-600"><?= $stats['avgTasks'] ?></div>
            <div class="text-xs text-gray-500">Feladatok</div>
            <?= renderStars($stats['avgTasks'], 'yellow-400') ?>
        </div>
        <div class="text-center">
            <div class="text-3xl md:text-4xl font-bold text-purple-600"><?= $stats['avgUi'] ?></div>
            <div class="text-xs text-gray-500">Kezelőfelület</div>
            <?= renderStars($stats['avgUi'], 'yellow-400') ?>
        </div>
    </div>
    
    <div class="flex justify-between items-center py-2 border-b">
        <span class="font-medium">Elégedett felhasználók (4-5 ★)</span>
        <span class="text-xl font-bold text-green-600"><?= $stats['satisfied'] ?></span>
    </div>
    <div class="flex justify-between items-center py-2">
        <span class="font-medium">Elégedetlen felhasználók (1-2 ★)</span>
        <span class="text-xl font-bold text-red-600"><?= $stats['dissatisfied'] ?></span>
    </div>
</div>

<h3 class="text-md font-semibold mb-3">Legutóbbi visszajelzések</h3>
<div class="space-y-3 mb-6">
    <?php foreach ($stats['feedbacks'] as $f): ?>
    <div class="bg-white p-4 rounded-lg shadow border-l-4 <?= $f['rating_overall'] >= 4 ? 'border-green-500' : ($f['rating_overall'] <= 2 ? 'border-red-500' : 'border-yellow-500') ?>">
        <div class="flex justify-between items-start">
            <div>
                <span class="text-xs font-bold uppercase <?= $f['type'] === 'bug' ? 'text-red-600' : ($f['type'] === 'idea' ? 'text-yellow-600' : 'text-blue-600') ?>">
                    <?= $f['type'] === 'opinion' ? 'Vélemény' : ($f['type'] === 'idea' ? 'Ötlet/Kérés' : 'Hiba') ?>
                </span>
                <?php if ($f['user_name']): ?>
                <span class="text-xs text-gray-500 ml-2">(<?= htmlspecialchars($f['user_name']) ?>)</span>
                <?php endif; ?>
            </div>
            <div class="flex items-center">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <span class="<?= $i <= $f['rating_overall'] ? 'text-yellow-500' : 'text-gray-300' ?>">★</span>
                <?php endfor; ?>
            </div>
        </div>
        <p class="mt-2 text-sm text-gray-700"><?= htmlspecialchars($f['message'] ?? '') ?></p>
        <p class="text-xs text-gray-400 mt-1"><?= date('Y.m.d H:i', strtotime($f['created_at'])) ?></p>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
