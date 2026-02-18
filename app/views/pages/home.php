<?php include __DIR__ . '/../layouts/header.php'; 
$pageTitle = 'Főoldal';
?>

<div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-indigo-500 to-purple-600 text-white">
    <div class="text-center max-w-2xl px-4">
        <h1 class="text-5xl font-bold mb-6">IdeaForge</h1>
        <p class="text-xl mb-8">Kezeld ötleteid és feladataid egy helyen. Egyszerűen és hatékonyan.</p>
        
        <div class="flex justify-center space-x-4">
            <a href="/login" class="bg-white text-indigo-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">Bejelentkezés</a>
            <a href="/register" class="border-2 border-white text-white px-6 py-3 rounded-lg font-semibold hover:bg-white/10 transition">Regisztráció</a>
        </div>
        
        <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
            <div>
                <div class="text-4xl mb-2">💡</div>
                <h3 class="font-semibold mb-1">Ötletek</h3>
                <p class="text-sm opacity-80">Gyűjtsd és fejleszd ötleteidet</p>
            </div>
            <div>
                <div class="text-4xl mb-2">📋</div>
                <h3 class="font-semibold mb-1">Feladatok</h3>
                <p class="text-sm opacity-80">Kövesd nyomon a tennivalókat</p>
            </div>
            <div>
                <div class="text-4xl mb-2">📊</div>
                <h3 class="font-semibold mb-1">Statisztikák</h3>
                <p class="text-sm opacity-80">Lásd a haladást</p>
            </div>
        </div>
    </div>
    
    <footer class="absolute bottom-4 text-white/60 text-sm">
        &copy; <?= date('Y') ?> IdeaForge | <a href="/impresszum" class="hover:text-white">Impresszum</a> | <a href="/privacy" class="hover:text-white">Adatvédelem</a>
    </footer>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
