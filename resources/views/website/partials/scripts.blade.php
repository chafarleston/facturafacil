// Filtro por categoría + búsqueda
(function () {
    var activeCategory = 'all';
    var searchInput = document.getElementById('catalogSearch');
    var items = Array.prototype.slice.call(document.querySelectorAll('.product-item'));
    var empty = document.getElementById('catalogEmpty');

    function applyFilters() {
        var q = (searchInput ? searchInput.value : '').toLowerCase().trim();
        var visible = 0;
        items.forEach(function (item) {
            var catMatch = activeCategory === 'all' || item.dataset.category === activeCategory;
            var nameMatch = !q || (item.dataset.name || '').indexOf(q) !== -1;
            var show = catMatch && nameMatch;
            item.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        if (empty) empty.style.display = visible === 0 ? 'block' : 'none';
    }

    document.querySelectorAll('.filter-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.filter-btn').forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
            activeCategory = btn.dataset.filter;
            applyFilters();
        });
    });

    if (searchInput) searchInput.addEventListener('input', applyFilters);
})();

// Galería (lightbox)
(function () {
    var modalEl = document.getElementById('galleryModal');
    if (!modalEl || !window.bootstrap) return;
    var modal = new bootstrap.Modal(modalEl);
    var img = document.getElementById('galleryModalImg');
    document.querySelectorAll('.gallery-item').forEach(function (item) {
        item.addEventListener('click', function () {
            img.src = item.dataset.img;
            modal.show();
        });
    });
})();

// Animaciones al hacer scroll
(function () {
    var els = document.querySelectorAll('.reveal');
    if (!('IntersectionObserver' in window)) {
        els.forEach(function (el) { el.classList.add('visible'); });
        return;
    }
    var obs = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                obs.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });
    els.forEach(function (el) { obs.observe(el); });
})();

// Botón volver arriba
(function () {
    var btn = document.getElementById('toTop');
    if (!btn) return;
    window.addEventListener('scroll', function () {
        btn.classList.toggle('show', window.scrollY > 500);
    });
    btn.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });
})();

// Cierra el menú móvil al pulsar un enlace
if (window.bootstrap) {
    document.querySelectorAll('#navMenu .nav-link').forEach(function (link) {
        link.addEventListener('click', function () {
            var menu = document.getElementById('navMenu');
            if (menu && menu.classList.contains('show')) {
                bootstrap.Collapse.getOrCreateInstance(menu).hide();
            }
        });
    });
}
