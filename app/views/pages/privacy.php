<?php include __DIR__ . '/../layouts/header.php'; 
$pageTitle = 'Adatvédelem';
?>

<h1 class="text-2xl font-bold mb-6">Adatvédelmi tájékoztató</h1>

<div class="prose max-w-none bg-white p-6 rounded-lg shadow">
    <h2>1. Adatkezelő</h2>
    <p>Az adatkezelő: IdeaForge (info@ideaforge.uzletinovekedes.hu)</p>

    <h2 class="mt-6">2. Kezelt adatok</h2>
    <ul class="list-disc pl-5">
        <li>Email cím</li>
        <li>Név</li>
        <li>Jelszó (titkosítva)</li>
        <li>Ötletek és feladatok</li>
    </ul>

    <h2 class="mt-6">3. Adatkezelés célja</h2>
    <p>Az adatok kizárólag a szolgáltatás nyújtásához használjuk fel.</p>

    <h2 class="mt-6">4. Adatok törlése</h2>
    <p>A felhasználó bármikor kérheti adatai törlését a profil oldalon.</p>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
