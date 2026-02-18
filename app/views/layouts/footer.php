    </main>

    <script>
    (function() {
        var sessionId = sessionStorage.getItem('analytics_session');
        if (!sessionId) {
            sessionId = Math.random().toString(36).substring(2) + Date.now().toString(36);
            sessionStorage.setItem('analytics_session', sessionId);
        }
        
        var pageName = window.location.pathname || '/';
        var referer = document.referrer || '';
        
        fetch('/api.php?action=log_analytics', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'session_id=' + encodeURIComponent(sessionId) +
                  '&page_name=' + encodeURIComponent(pageName) +
                  '&current_url=' + encodeURIComponent(window.location.href) +
                  '&referer=' + encodeURIComponent(referer)
        }).catch(function() {});
    })();
    </script>

    <footer class="bg-white border-t mt-auto">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex justify-between items-center text-sm text-gray-500">
                <div class="space-x-4">
                    <a href="/feedback" class="hover:text-gray-700">Visszajelzés</a>
                    <a href="/guide" class="hover:text-gray-700">Útmutató</a>
                    <a href="/impresszum" class="hover:text-gray-700">Impresszum</a>
                    <a href="/privacy" class="hover:text-gray-700">Adatvédelem</a>
                    <a href="/terms" class="hover:text-gray-700">ÁSZF</a>
                </div>
                <div>&copy; <?= date('Y') ?> IdeaForge</div>
            </div>
        </div>
    </footer>
</body>
</html>
