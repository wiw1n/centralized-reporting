    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.11/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.11/js/dataTables.bootstrap5.min.js"></script>
<script>
    var BASE_URL = <?= json_encode(base_url()) ?>;
    // CSRF protection regenerates the token on every POST (csrf_regenerate = TRUE in
    // config.php), so a page that fires more than one AJAX POST without a full reload needs to
    // re-sync this after each request or the next one gets rejected. See syncCsrfToken() below.
    var CSRF_TOKEN_NAME = <?= json_encode($this->security->get_csrf_token_name()) ?>;
    var CSRF_COOKIE_NAME = <?= json_encode($this->config->item('cookie_prefix') . $this->config->item('csrf_cookie_name')) ?>;

    function syncCsrfToken() {
        var match = document.cookie.match(new RegExp('(?:^|;\\s*)' + CSRF_COOKIE_NAME + '=([^;]+)'));
        if (match) {
            document.querySelectorAll('input[name="' + CSRF_TOKEN_NAME + '"]').forEach(function (el) {
                el.value = decodeURIComponent(match[1]);
            });
        }
    }
</script>
<script src="<?= base_url('assets/js/app.js') ?>"></script>
<script>
    (function () {
        var toggleBtn = document.getElementById('sidebarToggle');
        var sidebar = document.getElementById('appSidebar');
        if (!toggleBtn || !sidebar) {
            return;
        }
        toggleBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            sidebar.classList.toggle('show');
        });
        document.addEventListener('click', function (e) {
            if (sidebar.classList.contains('show') && !sidebar.contains(e.target) && e.target !== toggleBtn) {
                sidebar.classList.remove('show');
            }
        });
    })();
</script>
<?php
$controller_js = strtolower($this->router->fetch_class()) . '.js';
if (is_file(FCPATH . 'assets/js/' . $controller_js)):
?>
<script src="<?= base_url('assets/js/' . $controller_js) ?>"></script>
<?php endif; ?>
</body>
</html>
