<?php
$pageTitle = $pageTitle ?? 'IdeaForge';
$user = $_SESSION['user_name'] ?? '';
$isAdmin = ($_SESSION['user_role'] ?? '') === 'admin';
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - IdeaForge</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
        }
        .btn-primary { background-color: var(--primary); color: white; }
        .btn-primary:hover { background-color: var(--primary-hover); }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen">
    <?php if ($user): ?>
    <nav class="bg-white shadow-sm border-b fixed w-full top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-14">
                <div class="flex items-center">
                    <button id="mobile-menu-btn" class="md:hidden mr-3 p-2" onclick="toggleMobileMenu()">
                        <i class="bi bi-list text-xl"></i>
                    </button>
                    <a href="/dashboard" class="text-lg font-bold text-indigo-600">IdeaForge</a>
                    <div class="hidden md:flex ml-6 space-x-1">
                        <a href="/dashboard" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-100">Dashboard</a>
                        <a href="/ideas" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-100">Ötletek</a>
                        <a href="/tasks" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-100">Feladatok</a>
                        <a href="/statistics" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-100">Statisztikák</a>
                        <a href="/feedback" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-100">Visszajelzés</a>
                        <?php if ($isAdmin): ?>
                        <a href="/analytics" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-100">Analitika</a>
                        <a href="/admin" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-100">Admin</a>
                        <a href="/newsletter" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-100">Hírlevél</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="text-sm text-gray-600 hidden sm:inline"><?= htmlspecialchars($user) ?></span>
                    <a href="/profile" class="p-2 text-gray-600 hover:text-gray-900"><i class="bi bi-person"></i></a>
                    <a href="/logout" class="p-2 text-gray-600 hover:text-red-600"><i class="bi bi-box-arrow-right"></i></a>
                </div>
            </div>
        </div>
        
        <!-- Mobile menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-white border-t">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="/dashboard" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-gray-100">Dashboard</a>
                <a href="/ideas" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-gray-100">Ötletek</a>
                <a href="/tasks" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-gray-100">Feladatok</a>
                <a href="/statistics" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-gray-100">Statisztikák</a>
                <a href="/feedback" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-gray-100">Visszajelzés</a>
                <?php if ($isAdmin): ?>
                <a href="/analytics" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-gray-100">Analitika</a>
                <a href="/admin" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-gray-100">Admin</a>
                <a href="/newsletter" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-gray-100">Hírlevél</a>
                <?php endif; ?>
                <a href="/profile" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-gray-100">Profil</a>
                <a href="/logout" class="block px-3 py-2 rounded-md text-base font-medium text-red-600 hover:bg-gray-100">Kijelentkezés</a>
            </div>
        </div>
    </nav>
    <div class="h-14"></div>
    <?php endif; ?>

    <main class="<?= $user ? 'max-w-7xl mx-auto px-4 py-4' : '' ?>">
    
<script>
function toggleMobileMenu() {
    const menu = document.getElementById('mobile-menu');
    menu.classList.toggle('hidden');
}
</script>
