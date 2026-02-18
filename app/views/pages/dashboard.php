<?php include __DIR__ . '/../layouts/header.php'; 
$pageTitle = 'Dashboard';
?>

<h1 class="text-xl md:text-2xl font-bold mb-4 md:mb-6">Dashboard</h1>

<div class="grid grid-cols-3 gap-2 md:gap-6 mb-4 md:mb-8">
    <div class="bg-white p-3 md:p-6 rounded-lg shadow text-center">
        <div class="text-gray-500 text-xs md:text-sm">Ötletek</div>
        <div class="text-2xl md:text-3xl font-bold text-indigo-600"><?= $totalIdeas ?></div>
    </div>
    <div class="bg-white p-3 md:p-6 rounded-lg shadow text-center">
        <div class="text-gray-500 text-xs md:text-sm">Feladatok</div>
        <div class="text-2xl md:text-3xl font-bold text-blue-600"><?= $totalTasks ?></div>
    </div>
    <div class="bg-white p-3 md:p-6 rounded-lg shadow text-center">
        <div class="text-gray-500 text-xs md:text-sm">Befejezett</div>
        <div class="text-2xl md:text-3xl font-bold text-green-600"><?= $completedTasks ?></div>
    </div>
</div>

<h2 class="text-lg md:text-xl font-semibold mb-3 md:mb-4">Legutóbbi ötletek</h2>

<?php if (empty($recentIdeas)): ?>
<p class="text-gray-500 text-sm">Még nincs ötleted. <a href="/ideas" class="text-indigo-600 hover:underline">Hozz létre egyet!</a></p>
<?php else: ?>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-4">
    <?php foreach ($recentIdeas as $idea): ?>
    <div class="bg-white p-3 md:p-4 rounded-lg shadow border-l-4 border-indigo-500">
        <h3 class="font-semibold text-sm md:text-base"><?= htmlspecialchars($idea['name'] ?? $idea['title'] ?? '') ?></h3>
        <p class="text-gray-600 text-xs md:text-sm mt-1 line-clamp-2"><?= htmlspecialchars(mb_substr($idea['description'] ?? '', 0, 80)) ?></p>
        <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
            <span class="<?= ($idea['phase'] ?? '') === 'DEVELOPMENT' ? 'text-green-600' : 'text-indigo-600' ?>">
                <?= ($idea['phase'] ?? '') === 'DEVELOPMENT' ? 'Fejlesztés' : 'MVP' ?>
            </span>
            <span><?= date('Y.m.d', strtotime($idea['created_at'])) ?></span>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="mt-4 md:mt-6 flex flex-wrap gap-2">
    <a href="/ideas" class="bg-indigo-600 text-white px-3 md:px-4 py-2 rounded hover:bg-indigo-700 text-sm">Ötletek</a>
    <a href="/tasks" class="bg-blue-600 text-white px-3 md:px-4 py-2 rounded hover:bg-blue-700 text-sm">Feladatok</a>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
