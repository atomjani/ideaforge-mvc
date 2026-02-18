<?php include __DIR__ . '/../layouts/header.php'; 
$pageTitle = 'Visszajelzés';
$isAdmin = ($_SESSION['user_role'] ?? '') === 'admin';
?>

<h1 class="text-2xl font-bold mb-6">Visszajelzés</h1>

<?php if (isset($message)): ?>
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<?php if (isset($error)): ?>
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
    <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<?php 
$userRating = $userRating ?? [];
$ratingOverall = $userRating['rating_overall'] ?? 0;
$ratingIdeas = $userRating['rating_ideas'] ?? 0;
$ratingTasks = $userRating['rating_tasks'] ?? 0;
$ratingUi = $userRating['rating_ui'] ?? 0;
?>

<div class="bg-white p-6 rounded-lg shadow mb-8">
    <h2 class="text-lg font-semibold mb-4">Értékelés</h2>
    <form method="POST" id="ratingForm">
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Értékelés</label>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <label class="block text-xs text-gray-500 mb-1">Összesített</label>
                    <div class="flex justify-center gap-1">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <button type="button" class="text-2xl <?= $i <= $ratingOverall ? 'text-yellow-500' : 'text-gray-300' ?> hover:text-yellow-500" onclick="setRating('overall', <?= $i ?>)" id="star-overall-<?= $i ?>">★</button>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="rating_overall" id="rating-overall" value="<?= $ratingOverall ?>">
                </div>
                <div class="text-center">
                    <label class="block text-xs text-gray-500 mb-1">Ötletek</label>
                    <div class="flex justify-center gap-1">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <button type="button" class="text-2xl <?= $i <= $ratingIdeas ? 'text-yellow-500' : 'text-gray-300' ?> hover:text-yellow-500" onclick="setRating('ideas', <?= $i ?>)" id="star-ideas-<?= $i ?>">★</button>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="rating_ideas" id="rating-ideas" value="<?= $ratingIdeas ?>">
                </div>
                <div class="text-center">
                    <label class="block text-xs text-gray-500 mb-1">Feladatok</label>
                    <div class="flex justify-center gap-1">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <button type="button" class="text-2xl <?= $i <= $ratingTasks ? 'text-yellow-500' : 'text-gray-300' ?> hover:text-yellow-500" onclick="setRating('tasks', <?= $i ?>)" id="star-tasks-<?= $i ?>">★</button>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="rating_tasks" id="rating-tasks" value="<?= $ratingTasks ?>">
                </div>
                <div class="text-center">
                    <label class="block text-xs text-gray-500 mb-1">Kezelőfelület</label>
                    <div class="flex justify-center gap-1">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <button type="button" class="text-2xl <?= $i <= $ratingUi ? 'text-yellow-500' : 'text-gray-300' ?> hover:text-yellow-500" onclick="setRating('ui', <?= $i ?>)" id="star-ui-<?= $i ?>">★</button>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="rating_ui" id="rating-ui" value="<?= $ratingUi ?>">
                </div>
            </div>
        </div>
        <button type="submit" name="save_rating" value="1" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Értékelés mentése</button>
    </form>
</div>

<div class="bg-white p-6 rounded-lg shadow mb-8">
    <h2 class="text-lg font-semibold mb-4">Visszajelzés</h2>
    <form method="POST">
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Típus</label>
            <div class="flex flex-wrap gap-2 sm:gap-3">
                <label class="flex items-center px-3 py-2 border rounded-lg cursor-pointer hover:bg-gray-50 text-sm">
                    <input type="radio" name="type" value="opinion" class="mr-2" checked>
                    <span>Vélemény</span>
                </label>
                <label class="flex items-center px-3 py-2 border rounded-lg cursor-pointer hover:bg-gray-50 text-sm">
                    <input type="radio" name="type" value="idea" class="mr-2">
                    <span>Ötlet/Kérés</span>
                </label>
                <label class="flex items-center px-3 py-2 border rounded-lg cursor-pointer hover:bg-gray-50 text-sm">
                    <input type="radio" name="type" value="bug" class="mr-2">
                    <span>Hiba</span>
                </label>
            </div>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Üzenet (opcionális)</label>
            <textarea name="message" rows="4" class="w-full border rounded px-3 py-2" placeholder="Írd le a visszajelzésed..."></textarea>
        </div>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Visszajelzés küldése</button>
    </form>
</div>

<script>
function setRating(type, rating) {
    document.getElementById('rating-' + type).value = rating;
    for (let i = 1; i <= 5; i++) {
        const star = document.getElementById('star-' + type + '-' + i);
        if (i <= rating) {
            star.classList.remove('text-gray-300');
            star.classList.add('text-yellow-500');
        } else {
            star.classList.remove('text-yellow-500');
            star.classList.add('text-gray-300');
        }
    }
}
</script>

<h2 class="text-xl font-semibold mb-4"><?= $isAdmin ? 'Összes visszajelzés' : 'Saját visszajelzések' ?></h2>

<?php if (empty($feedbacks)): ?>
<p class="text-gray-500">Még nincs visszajelzés.</p>
<?php else: ?>
<div class="space-y-4">
    <?php foreach ($feedbacks as $f): ?>
    <div class="bg-white p-4 rounded-lg shadow border-l-4 <?= $f['type'] === 'bug' ? 'border-red-500' : ($f['type'] === 'idea' ? 'border-yellow-500' : 'border-blue-500') ?>">
        <div class="flex justify-between items-start">
            <div>
                <span class="text-xs font-bold uppercase <?= $f['type'] === 'bug' ? 'text-red-600' : ($f['type'] === 'idea' ? 'text-yellow-600' : 'text-blue-600') ?>">
                    <?= $f['type'] === 'opinion' ? 'Vélemény' : ($f['type'] === 'idea' ? 'Ötlet/Kérés' : 'Hiba') ?>
                </span>
                <?php if ($isAdmin && isset($f['user_name'])): ?>
                <span class="text-xs text-gray-500 ml-2">(<?= htmlspecialchars($f['user_name']) ?>)</span>
                <?php endif; ?>
            </div>
            <div class="flex items-center gap-1">
                <?php if ($f['rating_overall'] > 0): ?>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <span class="<?= $i <= $f['rating_overall'] ? 'text-yellow-500' : 'text-gray-300' ?>">★</span>
                <?php endfor; ?>
                <?php endif; ?>
                <span class="text-xs text-gray-500 ml-2"><?= date('Y.m.d H:i', strtotime($f['created_at'])) ?></span>
            </div>
        </div>
        <p class="mt-2 text-gray-700"><?= htmlspecialchars($f['message']) ?></p>
        <?php if ($f['rating_overall'] > 0): ?>
        <div class="mt-2 flex gap-4 text-xs text-gray-500">
            <?php if ($f['rating_ideas'] > 0): ?><span>Ötletek: <?= $f['rating_ideas'] ?>/5 ★</span><?php endif; ?>
            <?php if ($f['rating_tasks'] > 0): ?><span>Feladatok: <?= $f['rating_tasks'] ?>/5 ★</span><?php endif; ?>
            <?php if ($f['rating_ui'] > 0): ?><span>UI: <?= $f['rating_ui'] ?>/5 ★</span><?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
