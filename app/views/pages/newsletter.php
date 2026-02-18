<?php include __DIR__ . '/../layouts/header.php'; 
$pageTitle = 'Hírlevél';
?>

<h1 class="text-2xl font-bold mb-6">Hírlevél</h1>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-4">Hírlevél küldése</h2>
        <form id="newsletterForm">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Címzettek</label>
                <select name="recipients_type" class="w-full border rounded px-3 py-2">
                    <option value="all">Minden felhasználó</option>
                    <option value="subscribers">Csak feliratkozók</option>
                    <option value="users">Csak regisztráltak</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tárgy</label>
                <input type="text" name="subject" required class="w-full border rounded px-3 py-2">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tartalom</label>
                <textarea name="body" required rows="6" class="w-full border rounded px-3 py-2"></textarea>
            </div>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Küldés</button>
        </form>
        <div id="newsletterResult" class="mt-4"></div>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-4">SMTP beállítások</h2>
        <form id="smtpForm">
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">SMTP host</label>
                    <input type="text" name="smtp_host" value="<?= htmlspecialchars($smtp['smtp_host'] ?? '') ?>" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">SMTP port</label>
                    <input type="number" name="smtp_port" value="<?= $smtp['smtp_port'] ?? 587 ?>" class="w-full border rounded px-3 py-2">
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">SMTP felhasználó</label>
                <input type="text" name="smtp_user" value="<?= htmlspecialchars($smtp['smtp_user'] ?? '') ?>" class="w-full border rounded px-3 py-2">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">SMTP jelszó</label>
                <input type="password" name="smtp_pass" value="<?= htmlspecialchars($smtp['smtp_pass'] ?? '') ?>" class="w-full border rounded px-3 py-2">
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Feladó email</label>
                    <input type="email" name="smtp_from_email" value="<?= htmlspecialchars($smtp['smtp_from_email'] ?? '') ?>" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Feladó név</label>
                    <input type="text" name="smtp_from_name" value="<?= htmlspecialchars($smtp['smtp_from_name'] ?? 'IdeaForge') ?>" class="w-full border rounded px-3 py-2">
                </div>
            </div>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Mentés</button>
        </form>
        <div id="smtpResult" class="mt-4"></div>
    </div>
</div>

<h2 class="text-xl font-semibold mt-8 mb-4">Korábbi kampányok</h2>
<?php if (empty($campaigns)): ?>
<p class="text-gray-500">Még nem volt hírlevél.</p>
<?php else: ?>
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tárgy</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Címzettek</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Küldve</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php foreach ($campaigns as $c): ?>
            <tr>
                <td class="px-6 py-4"><?= htmlspecialchars($c['subject']) ?></td>
                <td class="px-6 py-4"><?= $c['recipient_count'] ?? 0 ?></td>
                <td class="px-6 py-4 text-sm text-gray-500"><?= date('Y.m.d H:i', strtotime($c['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<script>
document.getElementById('newsletterForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);
    
    const res = await fetch('/api/newsletter_send', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    });
    const result = await res.json();
    
    const div = document.getElementById('newsletterResult');
    if (result.status === 'ok') {
        div.innerHTML = '<div class="bg-green-100 text-green-700 px-4 py-2 rounded">Hírlevél elküldve! (' + result.sent_count + ' címzett)</div>';
        e.target.reset();
    } else {
        div.innerHTML = '<div class="bg-red-100 text-red-700 px-4 py-2 rounded">' + (result.error || 'Hiba') + '</div>';
    }
});

document.getElementById('smtpForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);
    data.smtp_port = parseInt(data.smtp_port);
    
    const res = await fetch('/api/save_smtp_settings', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    });
    const result = await res.json();
    
    const div = document.getElementById('smtpResult');
    if (result.status === 'ok') {
        div.innerHTML = '<div class="bg-green-100 text-green-700 px-4 py-2 rounded">Beállítások mentve!</div>';
    } else {
        div.innerHTML = '<div class="bg-red-100 text-red-700 px-4 py-2 rounded">' + (result.error || 'Hiba') + '</div>';
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
