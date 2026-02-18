<?php include __DIR__ . '/../layouts/header.php'; 
$pageTitle = 'Profil';
?>

<h1 class="text-2xl font-bold mb-6">Profilom</h1>

<div class="max-w-2xl space-y-6">
    <?php if (isset($message)): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
        <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-4">Személyes adatok</h2>
        <form method="POST">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Név</label>
                <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required class="w-full border rounded px-3 py-2">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled class="w-full border rounded px-3 py-2 bg-gray-100">
            </div>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Mentés</button>
        </form>
    </div>

    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-4">Jelszó változtatása</h2>
        <form method="POST">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Jelenlegi jelszó</label>
                <input type="password" name="old_password" required class="w-full border rounded px-3 py-2">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Új jelszó</label>
                <input type="password" name="new_password" required minlength="6" class="w-full border rounded px-3 py-2">
            </div>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Jelszó megváltoztatása</button>
        </form>
    </div>

    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-4 text-red-600">Fiók törlése</h2>
        <p class="text-gray-600 mb-4">A fiók törlése végleges. Minden ötleted és feladatod törlődik.</p>
        <form method="POST" action="/api/delete_account" onsubmit="return confirm('Biztosan törlöd a fiókod?');">
            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Fiók törlése</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
