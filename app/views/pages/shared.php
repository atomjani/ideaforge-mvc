<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($idea['name']) ?> - IdeaForge</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen">
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-14">
                <div class="flex items-center">
                    <a href="/" class="text-lg font-bold text-indigo-600">IdeaForge</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 py-6">
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h1 class="text-2xl font-bold"><?= htmlspecialchars($idea['name']) ?></h1>
                    <span class="text-sm px-2 py-1 rounded <?= $idea['phase'] === 'MVP_CREATION' ? 'bg-indigo-100 text-indigo-700' : 'bg-green-100 text-green-700' ?>">
                        <?= $idea['phase'] === 'MVP_CREATION' ? 'MVP létrehozás' : 'Fejlesztés' ?>
                    </span>
                    <?php 
                    $tags = [];
                    if (!empty($idea['tags'])) {
                        $tags = is_array($idea['tags']) ? $idea['tags'] : json_decode($idea['tags'], true) ?? [];
                    }
                    ?>
                    <?php if (in_array('nyereséges', $tags)): ?>
                    <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded ml-2">Nyereséges</span>
                    <?php endif; ?>
                    <?php if (in_array('népszerű', $tags)): ?>
                    <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-1 rounded ml-2">Népszerű</span>
                    <?php endif; ?>
                </div>
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
            
            <?php 
            $totalTasks = 0;
            $validatedTasks = 0;
            foreach ($tasks as $statusTasks) {
                $totalTasks += count($statusTasks);
                $validatedTasks += count($statusTasks);
            }
            ?>
            <div class="flex items-center gap-4 text-sm text-gray-500">
                <span>Feladatok: <?= $totalTasks ?></span>
                <span>Létrehozva: <?= date('Y.m.d', strtotime($idea['created_at'])) ?></span>
            </div>
        </div>

        <h2 class="text-xl font-bold mb-4">Feladatok</h2>
        
        <div class="overflow-x-auto">
            <div class="flex gap-4 min-w-max pb-4">
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
                    <div class="rounded-lg <?= $info['color'] ?> p-3">
                        <h3 class="font-semibold text-xs md:text-sm mb-3 text-center md:text-left"><?= $info['title'] ?> (<?= count($tasks[$status]) ?>)</h3>
                        <div class="space-y-2">
                            <?php if (!empty($tasks[$status])): ?>
                            <?php foreach ($tasks[$status] as $task): ?>
                            <div class="bg-white rounded shadow-sm p-3">
                                <p class="text-xs md:text-sm text-gray-800 mb-1"><?= htmlspecialchars($task['description'] ?? $task['name'] ?? '') ?></p>
                                <?php if (!empty($task['module'])): ?>
                                <p class="text-xs text-gray-500"><?= htmlspecialchars($task['module']) ?></p>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="mt-8 text-center text-sm text-gray-500">
            <a href="/" class="text-indigo-600 hover:underline">Készítsd el a saját ötletedet az IdeaForge-on!</a>
        </div>
    </main>

    <footer class="bg-white border-t mt-auto">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex justify-between items-center text-sm text-gray-500">
                <div class="space-x-4">
                    <a href="/impresszum" class="hover:text-gray-700">Impresszum</a>
                    <a href="/privacy" class="hover:text-gray-700">Adatvédelem</a>
                </div>
                <div>&copy; 2026 IdeaForge</div>
            </div>
        </div>
    </footer>
</body>
</html>
