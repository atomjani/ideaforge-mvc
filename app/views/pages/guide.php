<?php include __DIR__ . '/../layouts/header.php'; 
$pageTitle = 'Útmutató';
?>

<h1 class="text-2xl font-bold mb-6">Útmutató</h1>

<div class="prose max-w-none">
    <h2>Ötletek kezelése</h2>
    <p>Az IdeaForge-ban két fázisban dolgozhatsz az ötleteiden:</p>
    <ul class="list-disc pl-5">
        <li><strong>MVP</strong> - Minimum Viable Product, az ötlet alapvető verziója</li>
        <li><strong>Development</strong> - Fejlesztési fázis, amikor az ötletet fejlesztjük</li>
    </ul>

    <h2 class="mt-6">Feladatok (Kanban)</h2>
    <p>A Kanban táblán három oszlop van:</p>
    <ul class="list-disc pl-5">
        <li><strong>Backlog</strong> - Feldolgozásra váró feladatok</li>
        <li><strong>Folyamatban</strong> - Aktuálisan dolgozott feladatok</li>
        <li><strong>Validálva</strong> - Befejezett és ellenőrzött feladatok</li>
    </ul>

    <h2 class="mt-6">Tippek</h2>
    <ul class="list-disc pl-5">
        <li>Kezd kicsiben - MVP fázisban</li>
        <li>Bontsd feladataidra a nagyobb ötleteket</li>
        <li>Rendszeresen frissítsd a feladatok állapotát</li>
    </ul>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
