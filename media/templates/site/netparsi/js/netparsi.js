document.addEventListener('DOMContentLoaded', function () {
  // Smart header: compact after a short scroll, hides while scrolling down, returns on scroll up
  var body = document.body;
  var lastY = window.scrollY;
  var ticking = false;

  function onScroll() {
    var y = window.scrollY;
    var delta = y - lastY;
    body.classList.toggle('az-scrolled', y > 60);
    if (Math.abs(delta) > 6) {
      body.classList.toggle('az-header-hidden', delta > 0 && y > 320);
      lastY = y;
    }
    ticking = false;
  }

  window.addEventListener('scroll', function () {
    if (!ticking) { window.requestAnimationFrame(onScroll); ticking = true; }
  }, { passive: true });
  onScroll();

  // Category grid density (2 / 4 / 6 columns), remembered per browser
  var grid = document.getElementById('az-grid');
  var switches = document.querySelectorAll('.az-grid-switch button');

  function setCols(cols) {
    if (!grid) return;
    grid.classList.remove('cols-2', 'cols-4', 'cols-6');
    grid.classList.add('cols-' + cols);
    switches.forEach(function (b) { b.classList.toggle('is-active', b.dataset.cols === String(cols)); });
    try { localStorage.setItem('azCols', cols); } catch (e) {}
  }

  if (grid && switches.length) {
    var saved = null;
    try { saved = localStorage.getItem('azCols'); } catch (e) {}
    setCols(saved || 4);
    switches.forEach(function (b) { b.addEventListener('click', function () { setCols(b.dataset.cols); }); });
  }

  // Single-choice selectors: sizes, colors, filter chips
  document.querySelectorAll('[data-az-choice]').forEach(function (group) {
    group.addEventListener('click', function (e) {
      var item = e.target.closest('li, button, .az-swatch');
      if (!item || !group.contains(item) || item.classList.contains('is-out')) return;
      if (group.dataset.azChoice === 'multi') {
        item.classList.toggle('is-active');
        return;
      }
      group.querySelectorAll('.is-active').forEach(function (el) { el.classList.remove('is-active'); });
      item.classList.add('is-active');
    });
  });

  // Quantity steppers
  document.querySelectorAll('.az-qty').forEach(function (qty) {
    var out = qty.querySelector('span');
    qty.addEventListener('click', function (e) {
      var btn = e.target.closest('button');
      if (!btn) return;
      var n = parseInt(out.dataset.n || '1', 10) + (btn.dataset.step === '+' ? 1 : -1);
      n = Math.max(1, n);
      out.dataset.n = n;
      out.textContent = String(n).replace(/\d/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'[d]; });
    });
  });

  function notify(message) {
    if (window.UIkit) UIkit.notification({ message: message, pos: 'bottom-center', timeout: 2000 });
  }

  // Product cards: quick size picker, wishlist
  document.addEventListener('click', function (e) {
    var quick = e.target.closest('.az-card-quick');
    var size = e.target.closest('.az-card-sizes li');
    var fav = e.target.closest('.az-card-fav');
    var openCard = e.target.closest('.az-card.is-open');

    document.querySelectorAll('.az-card.is-open').forEach(function (card) {
      if (card !== openCard || size) card.classList.remove('is-open');
    });

    if (quick) {
      quick.closest('.az-card').classList.add('is-open');
    } else if (size && !size.classList.contains('is-out')) {
      notify('سایز ' + size.textContent + ' به سبد خرید اضافه شد');
    } else if (fav) {
      fav.classList.toggle('is-active');
      notify(fav.classList.contains('is-active') ? 'به علاقه‌مندی‌ها اضافه شد' : 'از علاقه‌مندی‌ها حذف شد');
    }
  });

  document.querySelectorAll('[data-az-add]').forEach(function (btn) {
    btn.addEventListener('click', function () { notify('به سبد خرید اضافه شد'); });
  });
});
