<?php include __DIR__ . '/../layouts/header.php'; 
$pageTitle = 'Analitika';
?>

<h1 class="text-xl md:text-2xl font-bold mb-4 md:mb-6">Analitika</h1>

<div class="mb-6">
    <form method="GET" class="flex gap-2">
        <select name="days" class="border rounded px-3 py-2" onchange="this.form.submit()">
            <option value="7" <?= $stats['days'] == 7 ? 'selected' : '' ?>>Utolsó 7 nap</option>
            <option value="14" <?= $stats['days'] == 14 ? 'selected' : '' ?>>Utolsó 14 nap</option>
            <option value="30" <?= $stats['days'] == 30 ? 'selected' : '' ?>>Utolsó 30 nap</option>
            <option value="60" <?= $stats['days'] == 60 ? 'selected' : '' ?>>Utolsó 60 nap</option>
            <option value="90" <?= $stats['days'] == 90 ? 'selected' : '' ?>>Utolsó 90 nap</option>
        </select>
    </form>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-2 md:gap-6 mb-6">
    <div class="bg-white p-3 md:p-6 rounded-lg shadow text-center">
        <div class="text-gray-500 text-xs md:text-sm">Egyedi látogatók</div>
        <div class="text-2xl md:text-3xl font-bold text-indigo-600"><?= number_format($stats['uniqueVisitors']) ?></div>
    </div>
    <div class="bg-white p-3 md:p-6 rounded-lg shadow text-center">
        <div class="text-gray-500 text-xs md:text-sm">Oldal megtekintések</div>
        <div class="text-2xl md:text-3xl font-bold text-blue-600"><?= number_format($stats['pageViews']) ?></div>
    </div>
    <div class="bg-white p-3 md:p-6 rounded-lg shadow text-center">
        <div class="text-gray-500 text-xs md:text-sm">Bounce rate</div>
        <div class="text-2xl md:text-3xl font-bold text-orange-600"><?= $stats['bounceRate'] ?>%</div>
    </div>
    <div class="bg-white p-3 md:p-6 rounded-lg shadow text-center">
        <div class="text-gray-500 text-xs md:text-sm">Regisztrációk</div>
        <div class="text-2xl md:text-3xl font-bold text-green-600"><?= count($stats['registrations']) ?></div>
    </div>
</div>

<h2 class="text-lg md:text-xl font-semibold mb-3 md:mb-4">Forgalmi források</h2>
<div class="bg-white p-3 md:p-6 rounded-lg shadow mb-6 overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b">
                <th class="text-left py-2">Forrás</th>
                <th class="text-right py-2">Látogatások</th>
                <th class="text-right py-2">Egyedi látogatók</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($stats['trafficSources'] as $source): ?>
            <tr class="border-b">
                <td class="py-2"><?= htmlspecialchars($source['source']) ?></td>
                <td class="text-right py-2"><?= number_format($source['visits']) ?></td>
                <td class="text-right py-2"><?= number_format($source['unique_visits']) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($stats['trafficSources'])): ?>
            <tr>
                <td colspan="3" class="py-4 text-center text-gray-500">Nincs adat</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<h2 class="text-lg md:text-xl font-semibold mb-3 md:mb-4">Leglátogatottabb oldalak</h2>
<div class="bg-white p-3 md:p-6 rounded-lg shadow mb-6 overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b">
                <th class="text-left py-2">Oldal</th>
                <th class="text-right py-2">Megtekintések</th>
                <th class="text-right py-2">Egyedi látogatók</th>
                <th class="text-right py-2">Átlagos idő (mp)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($stats['topPages'] as $page): ?>
            <?php 
            $avgTime = 0;
            foreach ($stats['avgTimeOnPage'] as $time) {
                if ($time['page_name'] == $page['page_name']) {
                    $avgTime = round($time['avg_time']);
                    break;
                }
            }
            ?>
            <tr class="border-b">
                <td class="py-2"><?= htmlspecialchars($page['page_name'] ?: '/') ?></td>
                <td class="text-right py-2"><?= number_format($page['views']) ?></td>
                <td class="text-right py-2"><?= number_format($page['unique_views']) ?></td>
                <td class="text-right py-2"><?= $avgTime > 0 ? $avgTime . ' mp' : '-' ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($stats['topPages'])): ?>
            <tr>
                <td colspan="4" class="py-4 text-center text-gray-500">Nincs adat</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<h2 class="text-lg md:text-xl font-semibold mb-3 md:mb-4">Napi statisztikák</h2>
<div class="bg-white p-3 md:p-6 rounded-lg shadow mb-6 overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b">
                <th class="text-left py-2">Dátum</th>
                <th class="text-right py-2">Oldal megtekintések</th>
                <th class="text-right py-2">Egyedi látogatók</th>
                <th class="text-right py-2">Regisztrációk</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $regByDate = [];
            foreach ($stats['registrations'] as $reg) {
                $regByDate[$reg['date']] = $reg['registrations'];
            }
            ?>
            <?php foreach ($stats['dailyStats'] as $day): ?>
            <tr class="border-b">
                <td class="py-2"><?= date('Y.m.d', strtotime($day['date'])) ?></td>
                <td class="text-right py-2"><?= number_format($day['page_views']) ?></td>
                <td class="text-right py-2"><?= number_format($day['unique_visitors']) ?></td>
                <td class="text-right py-2"><?= $regByDate[$day['date']] ?? 0 ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($stats['dailyStats'])): ?>
            <tr>
                <td colspan="4" class="py-4 text-center text-gray-500">Nincs adat</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
