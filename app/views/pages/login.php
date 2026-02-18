<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">Bejelentkezés</h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                vagy <a href="/register" class="font-medium text-indigo-600 hover:text-indigo-500">regisztrálj</a>
            </p>
        </div>
        
        <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            <?= htmlspecialchars($success) ?>
        </div>
        <?php endif; ?>
        
        <form class="mt-8 space-y-6" action="/login" method="POST">
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="email" class="sr-only">Email</label>
                    <input id="email" name="email" type="email" required 
                        class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                        placeholder="Email cím">
                </div>
                <div>
                    <label for="password" class="sr-only">Jelszó</label>
                    <input id="password" name="password" type="password" required 
                        class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                        placeholder="Jelszó">
                </div>
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Bejelentkezés
                </button>
            </div>
            
            <div class="text-center">
                <a href="/" class="text-sm text-gray-600 hover:text-gray-900">Vissza a főoldalra</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
