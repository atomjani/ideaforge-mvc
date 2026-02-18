<?php include __DIR__ . '/../layouts/header.php'; 
$pageTitle = 'Admin';
?>

<h1 class="text-xl md:text-2xl font-bold mb-4 md:mb-6">Admin panel</h1>

<div class="grid grid-cols-2 md:grid-cols-4 gap-2 md:gap-6 mb-4 md:mb-8">
    <div class="bg-white p-3 md:p-6 rounded-lg shadow text-center">
        <div class="text-gray-500 text-xs md:text-sm">Ötletek</div>
        <div class="text-2xl md:text-3xl font-bold text-indigo-600"><?= $stats['totalIdeas'] ?></div>
    </div>
    <div class="bg-white p-3 md:p-6 rounded-lg shadow text-center">
        <div class="text-gray-500 text-xs md:text-sm">Feladatok</div>
        <div class="text-2xl md:text-3xl font-bold text-blue-600"><?= $stats['totalTasks'] ?></div>
    </div>
    <div class="bg-white p-3 md:p-6 rounded-lg shadow text-center">
        <div class="text-gray-500 text-xs md:text-sm">Befejezett</div>
        <div class="text-2xl md:text-3xl font-bold text-green-600"><?= $stats['completedTasks'] ?></div>
    </div>
    <div class="bg-white p-3 md:p-6 rounded-lg shadow text-center">
        <div class="text-gray-500 text-xs md:text-sm">Felhasználók</div>
        <div class="text-2xl md:text-3xl font-bold text-purple-600"><?= $stats['totalUsers'] ?></div>
    </div>
</div>

<h2 class="text-lg md:text-xl font-semibold mb-3 md:mb-4">Felhasználók</h2>

<div class="bg-white rounded-lg shadow overflow-x-auto">
    <table class="min-w-full">
        <thead class="bg-gray-50 hidden md:table-header-group">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Név</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Szerep</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Regisztrálva</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Műveletek</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php foreach ($users as $u): ?>
            <tr class="block md:table-row border-b md:border-0 mb-2 md:mb-0 p-3 md:p-0 bg-gray-50 md:bg-white">
                <td class="px-3 py-2 md:px-4 md:py-4 block md:table-cell">
                    <span class="md:hidden font-medium text-gray-500 mr-2">Név:</span>
                    <?= htmlspecialchars($u['name']) ?>
                </td>
                <td class="px-3 py-2 md:px-4 md:py-4 block md:table-cell">
                    <span class="md:hidden font-medium text-gray-500 mr-2">Email:</span>
                    <?= htmlspecialchars($u['email']) ?>
                </td>
                <td class="px-3 py-2 md:px-4 md:py-4 block md:table-cell">
                    <span class="md:hidden font-medium text-gray-500 mr-2">Szerep:</span>
                    <span class="px-2 py-1 text-xs rounded <?= $u['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800' ?>">
                        <?= $u['role'] ?>
                    </span>
                </td>
                <td class="px-3 py-2 md:px-4 md:py-4 block md:table-cell text-sm text-gray-500">
                    <span class="md:hidden font-medium text-gray-500 mr-2">Regisztrálva:</span>
                    <?= date('Y.m.d', strtotime($u['created_at'])) ?>
                </td>
                <td class="px-3 py-2 md:px-4 md:py-4 block md:table-cell">
                    <span class="md:hidden font-medium text-gray-500 mr-2">Műveletek:</span>
                    <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                    <button onclick="deleteUser('<?= $u['id'] ?>')" class="text-red-600 hover:underline text-sm">Törlés</button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
async function deleteUser(id) {
    if (!confirm('Biztosan törlöd ezt a felhasználót?')) return;
    
    const res = await fetch('/api.php?action=delete_account', {
        method: 'POST',
        body: new URLSearchParams({user_id: id})
    });
    const data = await res.json();
    
    if (data.status === 'ok') {
        location.reload();
    } else {
        alert(data.error || 'Hiba történt');
    }
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
