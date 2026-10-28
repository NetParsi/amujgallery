document.addEventListener('DOMContentLoaded', function () {
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

  // Demo add-to-cart feedback
  document.querySelectorAll('.az-card-add, [data-az-add]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (window.UIkit) UIkit.notification({ message: 'به سبد خرید اضافه شد', pos: 'bottom-center', timeout: 2000 });
    });
  });
});
