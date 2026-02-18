<?php include __DIR__ . '/../layouts/header.php'; 
$pageTitle = 'Ötletek';
?>

<h1 class="text-xl md:text-2xl font-bold mb-4">Ötleteim</h1>

<div class="flex flex-wrap gap-2 mb-4">
    <button onclick="document.getElementById('newIdeaForm').classList.toggle('hidden')" 
        class="bg-indigo-600 text-white px-3 py-2 rounded hover:bg-indigo-700 text-sm">
        + Új ötlet
    </button>
    
    <select id="filterPhase" onchange="filterIdeas()" class="border rounded px-3 py-2 text-sm">
        <option value="">Minden fázis</option>
        <option value="MVP_CREATION">MVP fázis</option>
        <option value="DEVELOPMENT">Fejlesztés fázis</option>
    </select>
    
    <select id="filterTag" onchange="filterIdeas()" class="border rounded px-3 py-2 text-sm">
        <option value="">Minden jelző</option>
        <option value="nyereséges">Nyereséges</option>
        <option value="népszerű">Népszerű</option>
    </select>
    
    <input type="text" id="searchIdeas" onkeyup="filterIdeas()" placeholder="Keresés..." 
           class="border rounded px-3 py-2 text-sm">
</div>

<div id="newIdeaForm" class="hidden bg-white p-6 rounded-lg shadow mb-6">
    <h2 class="text-lg font-semibold mb-4">Új ötlet létrehozása</h2>
    <form method="POST" action="/api.php?action=create_idea">
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Ötlet neve *</label>
            <input type="text" name="name" required class="w-full border rounded px-3 py-2">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Leírás *</label>
            <textarea name="description" rows="3" required class="w-full border rounded px-3 py-2"></textarea>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Milyen problémát old meg?</label>
            <textarea name="problem" rows="2" class="w-full border rounded px-3 py-2" placeholder="Milyen problémát old meg ez az ötlet?"></textarea>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Célközönség</label>
            <input type="text" name="target_audience" class="w-full border rounded px-3 py-2" placeholder="Ki a célközönség?">
        </div>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Mentés</button>
    </form>
</div>

<?php if (empty($ideas)): ?>
<p class="text-gray-500">Még nincs ötleted. Kattints a "Új ötlet" gombra!</p>
<?php else: ?>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php foreach ($ideas as $idea): 
    $ideaTags = '';
    if (!empty($idea['tags'])) {
        $tags = is_array($idea['tags']) ? $idea['tags'] : json_decode($idea['tags'], true) ?? [];
        $ideaTags = implode(',', $tags);
    }
    ?>
    <div class="bg-white p-4 rounded-lg shadow border-l-4 <?= $idea['phase'] === 'MVP_CREATION' ? 'border-indigo-500' : 'border-green-500' ?> idea-card" 
         data-phase="<?= $idea['phase'] ?>" 
         data-tags="<?= $ideaTags ?>"
         data-name="<?= strtolower(htmlspecialchars($idea['name'] ?? '')) ?>"
         data-description="<?= strtolower(htmlspecialchars($idea['description'] ?? '')) ?>">
        <div class="flex justify-between items-start">
            <h3 class="font-semibold text-lg">
                <a href="/idea?id=<?= $idea['id'] ?>" class="hover:text-indigo-600"><?= htmlspecialchars($idea['name']) ?></a>
            </h3>
            <span class="text-xs px-2 py-1 rounded <?= $idea['phase'] === 'MVP_CREATION' ? 'bg-indigo-100 text-indigo-700' : 'bg-green-100 text-green-700' ?>">
                <?= $idea['phase'] === 'MVP_CREATION' ? 'MVP' : 'Fejlesztés' ?>
            </span>
        </div>
        <p class="text-gray-600 text-sm mt-2 line-clamp-2"><?= htmlspecialchars($idea['description']) ?></p>
        
        <?php if (!empty($idea['problem'])): ?>
        <p class="text-gray-500 text-xs mt-2"><strong>Probléma:</strong> <?= htmlspecialchars($idea['problem']) ?></p>
        <?php endif; ?>
        
        <div class="mt-3 flex items-center justify-between text-sm">
            <span class="text-gray-500"><?= date('Y.m.d', strtotime($idea['created_at'])) ?></span>
            <div class="flex items-center gap-2">
                <?php 
                $tags = [];
                if (!empty($idea['tags'])) {
                    $tags = is_array($idea['tags']) ? $idea['tags'] : json_decode($idea['tags'], true) ?? [];
                }
                ?>
                <?php if (in_array('nyereséges', $tags)): ?>
                <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded">Nyereséges</span>
                <?php endif; ?>
                <?php if (in_array('népszerű', $tags)): ?>
                <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-1 rounded">Népszerű</span>
                <?php endif; ?>
                <?php if (isset($idea['task_count'])): ?>
                <span class="text-xs bg-gray-100 px-2 py-1 rounded">
                    <?= $idea['validated_count'] ?? 0 ?>/<?= $idea['task_count'] ?> feladat
                </span>
                <?php endif; ?>
                <?php if (!empty($idea['can_transition']) && $idea['phase'] === 'MVP_CREATION'): ?>
                <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded">Kész a fázisváltásra</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="mt-2 flex justify-between text-sm">
            <a href="/idea?id=<?= $idea['id'] ?>" class="text-indigo-600 hover:underline">Megnyitás</a>
            <div class="flex gap-2">
                <button id="shareBtn-<?= $idea['id'] ?>" onclick="openShareModal('<?= $idea['id'] ?>')" class="<?= !empty($idea['is_public']) ? 'text-green-600' : 'text-gray-500' ?> hover:underline">
                    <?= !empty($idea['is_public']) ? 'Megosztva' : 'Megosztás' ?>
                </button>
                <button onclick="editIdea('<?= $idea['id'] ?>')" class="text-indigo-600 hover:underline">Szerkesztés</button>
                <button onclick="deleteIdea('<?= $idea['id'] ?>')" class="text-red-600 hover:underline">Törlés</button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg max-w-lg w-full mx-4">
        <h2 class="text-lg font-semibold mb-4">Ötlet szerkesztése</h2>
        <form method="POST" action="/api.php?action=update_idea">
            <input type="hidden" name="id" id="editId">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Ötlet neve</label>
                <input type="text" name="name" id="editName" required class="w-full border rounded px-3 py-2">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Leírás</label>
                <textarea name="description" id="editDescription" rows="3" class="w-full border rounded px-3 py-2"></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Milyen problémát old meg?</label>
                <textarea name="problem" id="editProblem" rows="2" class="w-full border rounded px-3 py-2"></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Célközönség</label>
                <input type="text" name="target_audience" id="editTargetAudience" class="w-full border rounded px-3 py-2">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Jelzők</label>
                <div class="flex gap-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="tags[]" value="nyereséges" id="tag_nyereseges" class="mr-2">
                        <span class="text-sm">Nyereséges</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="tags[]" value="népszerű" id="tag_nepszeru" class="mr-2">
                        <span class="text-sm">Népszerű</span>
                    </label>
                </div>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Mentés</button>
                <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Mégse</button>
            </div>
        </form>
    </div>
</div>

<script>
let ideasData = <?= json_encode(array_column($ideas, null, 'id')) ?>;

async function editIdea(id) {
    const idea = ideasData[id];
    if (!idea) return;
    
    document.getElementById('editId').value = id;
    document.getElementById('editName').value = idea.name || '';
    document.getElementById('editDescription').value = idea.description || '';
    document.getElementById('editProblem').value = idea.problem || '';
    document.getElementById('editTargetAudience').value = idea.target_audience || '';
    
    const tags = idea.tags ? (typeof idea.tags === 'string' ? JSON.parse(idea.tags) : idea.tags) : [];
    document.getElementById('tag_nyereseges').checked = tags.includes('nyereséges');
    document.getElementById('tag_nepszeru').checked = tags.includes('népszerű');
    
    document.getElementById('editModal').classList.remove('hidden');
}

async function openShareModal(id) {
    const idea = ideasData[id];
    const modal = document.getElementById('shareModal');
    const currentType = idea.share_type || 'private';
    
    document.getElementById('shareIdeaId').value = id;
    document.querySelectorAll('input[name="share_type"]').forEach(radio => {
        radio.checked = radio.value === currentType;
        radio.onchange = () => toggleSpecificUsers();
    });
    
    const specificContainer = document.getElementById('specificUsersContainer');
    if (currentType === 'specific') {
        specificContainer.classList.remove('hidden');
        document.getElementById('specificEmails').value = idea.specific_emails || '';
    } else {
        specificContainer.classList.add('hidden');
    }
    
    await updateShareLink();
    
    modal.classList.remove('hidden');
}

function toggleSpecificUsers() {
    const shareType = document.querySelector('input[name="share_type"]:checked').value;
    const specificContainer = document.getElementById('specificUsersContainer');
    if (shareType === 'specific') {
        specificContainer.classList.remove('hidden');
    } else {
        specificContainer.classList.add('hidden');
    }
}

function closeShareModal() {
    const id = document.getElementById('shareIdeaId').value;
    const shareType = document.querySelector('input[name="share_type"]:checked').value;
    const idea = ideasData[id];
    
    const shareBtn = document.getElementById('shareBtn-' + id);
    if (shareBtn) {
        if (shareType !== 'private') {
            shareBtn.textContent = 'Megosztva';
            shareBtn.classList.remove('text-gray-500');
            shareBtn.classList.add('text-green-600');
        } else {
            shareBtn.textContent = 'Megosztás';
            shareBtn.classList.remove('text-green-600');
            shareBtn.classList.add('text-gray-500');
        }
    }
    
    document.getElementById('shareModal').classList.add('hidden');
}

async function updateShareLink() {
    const id = document.getElementById('shareIdeaId').value;
    const shareType = document.querySelector('input[name="share_type"]:checked').value;
    const specificEmails = document.getElementById('specificEmails').value;
    
    const params = new URLSearchParams({id: id, share_type: shareType});
    if (shareType === 'specific' && specificEmails) {
        params.append('specific_emails', specificEmails);
    }
    
    const res = await fetch('/api.php?action=share_idea', {
        method: 'POST',
        body: params
    });
    const result = await res.json();
    
    const linkContainer = document.getElementById('shareLinkContainer');
    const shareLinkInput = document.getElementById('shareLink');
    
    if (result.share_url) {
        linkContainer.classList.remove('hidden');
        shareLinkInput.value = result.share_url;
    } else {
        linkContainer.classList.add('hidden');
    }
    
    ideasData[id].is_public = result.is_public;
    ideasData[id].share_type = result.share_type;
    ideasData[id].specific_emails = result.specific_emails;
}

async function copyShareLink() {
    const shareLinkInput = document.getElementById('shareLink');
    try {
        await navigator.clipboard.writeText(shareLinkInput.value);
        alert('Link másolva a vágólapra!');
    } catch (err) {
        shareLinkInput.select();
        document.execCommand('copy');
        alert('Link másolva a vágólapra!');
    }
}

async function browserShare() {
    const shareLinkInput = document.getElementById('shareLink').value;
    const ideaId = document.getElementById('shareIdeaId').value;
    const idea = ideasData[ideaId];
    
    if (navigator.share) {
        try {
            await navigator.share({
                title: idea.name || 'Ötlet megosztása',
                text: idea.description ? idea.description.substring(0, 100) + '...' : 'Nézd meg ezt az ötletet!',
                url: shareLinkInput
            });
        } catch (err) {
            // User cancelled or error
        }
    } else {
        alert('A böngésződ nem támogatja a megosztási funkciót.');
    }
}

function filterIdeas() {
    const phase = document.getElementById('filterPhase').value;
    const tag = document.getElementById('filterTag').value;
    const search = document.getElementById('searchIdeas').value.toLowerCase();
    
    const cards = document.querySelectorAll('.idea-card');
    cards.forEach(card => {
        const cardPhase = card.dataset.phase || '';
        const cardTags = card.dataset.tags || '';
        const cardName = card.dataset.name || '';
        const cardDesc = card.dataset.description || '';
        
        let show = true;
        
        if (phase && cardPhase !== phase) show = false;
        if (tag && !cardTags.includes(tag)) show = false;
        if (search && !cardName.includes(search) && !cardDesc.includes(search)) show = false;
        
        card.style.display = show ? '' : 'none';
    });
}

</script>

<div id="shareModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg max-w-md w-full mx-4 p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Ötlet megosztása</h2>
            <button onclick="closeShareModal()" class="text-gray-500 hover:text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <input type="hidden" id="shareIdeaId" value="">
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Láthatóság</label>
            <div class="space-y-2">
                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                    <input type="radio" name="share_type" value="private" onchange="toggleSpecificUsers(); updateShareLink()" class="mr-3" checked>
                    <div class="flex-1">
                        <div class="font-medium">Privát</div>
                        <div class="text-sm text-gray-500">Csak te láthatod</div>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </label>
                
                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                    <input type="radio" name="share_type" value="specific" onchange="toggleSpecificUsers(); updateShareLink()" class="mr-3">
                    <div class="flex-1">
                        <div class="font-medium">Konkrét felhasználók</div>
                        <div class="text-sm text-gray-500">Megadott email címeknek</div>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </label>
                
                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                    <input type="radio" name="share_type" value="registered" onchange="toggleSpecificUsers(); updateShareLink()" class="mr-3">
                    <div class="flex-1">
                        <div class="font-medium">Regisztrált felhasználók</div>
                        <div class="text-sm text-gray-500">Bármely bejelentkezett felhasználó</div>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </label>
                
                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                    <input type="radio" name="share_type" value="public" onchange="toggleSpecificUsers(); updateShareLink()" class="mr-3">
                    <div class="flex-1">
                        <div class="font-medium">Nyilvános</div>
                        <div class="text-sm text-gray-500">Bárki, akinek van linkje</div>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </label>
            </div>
        </div>
        
        <div id="specificUsersContainer" class="hidden mb-4 p-4 bg-gray-50 rounded-lg">
            <label class="block text-sm font-medium text-gray-700 mb-2">Email címek ( vesszővel elválasztva)</label>
            <textarea id="specificEmails" rows="3" class="w-full border rounded px-3 py-2 text-sm" placeholder="email1@pelda.hu, email2@pelda.hu"></textarea>
            <p class="text-xs text-gray-500 mt-1">A megadott email címekkel rendelkező felhasználók érhetik el az ötletet.</p>
        </div>
        
        <div id="shareLinkContainer" class="hidden mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Megosztási link</label>
            <div class="flex gap-2">
                <input type="text" id="shareLink" readonly class="flex-1 border rounded px-3 py-2 text-sm bg-gray-50">
                <button onclick="copyShareLink()" class="bg-indigo-600 text-white px-3 py-2 rounded hover:bg-indigo-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </button>
                <button onclick="browserShare()" class="bg-green-600 text-white px-3 py-2 rounded hover:bg-green-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                    </svg>
                </button>
            </div>
        </div>
        
        <button onclick="closeShareModal()" class="w-full bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300">Bezárás</button>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
