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

  // Home hero: the progress line under the active tab fills, then the next slide shows
  var hero = document.querySelector('.az-hero[data-az-duration]');
  if (hero && window.UIkit) {
    hero.addEventListener('animationend', function (e) {
      if (e.animationName === 'azFill') UIkit.slideshow(hero).show('next');
    });
  }

  // Announcement bar: rotate messages, close and remember
  var rotate = document.querySelector('[data-az-rotate]');
  if (rotate && rotate.children.length > 1) {
    var slide = 0;
    setInterval(function () {
      rotate.children[slide].classList.remove('is-active');
      slide = (slide + 1) % rotate.children.length;
      rotate.children[slide].classList.add('is-active');
    }, 4000);
  }
  var barClose = document.querySelector('.az-topbar-close');
  if (barClose) {
    barClose.addEventListener('click', function () {
      document.documentElement.classList.add('az-no-bar');
      try { localStorage.setItem('azBarClosed', '1'); } catch (e) {}
    });
  }

  // Desktop menu: the image beside the menu follows the hovered section / link
  var menuEl = document.getElementById('az-menu');
  if (menuEl && menuEl.querySelector('[data-az-layer]')) {
    var layers = menuEl.querySelectorAll('[data-az-layer]');
    var showLayer = function (key) {
      layers.forEach(function (l) { l.classList.toggle('is-active', l.dataset.azLayer === String(key)); });
    };
    var sectionImg = function () {
      var a = menuEl.querySelector('.az-menu-sections .uk-active a') || menuEl.querySelector('.az-menu-sections a');
      return a ? a.dataset.azImg : null;
    };
    menuEl.addEventListener('mouseover', function (e) {
      var t = e.target.closest('[data-az-img]');
      if (t) showLayer(t.dataset.azImg);
    });
    menuEl.querySelector('.az-menu-panels').addEventListener('mouseleave', function () {
      var k = sectionImg();
      if (k) showLayer(k);
    });
  }

  // Opening the customer panel from inside the menu: close the menu first
  document.querySelectorAll('#az-menu [uk-toggle*="#az-user"]').forEach(function (el) {
    el.addEventListener('click', function () {
      if (window.UIkit) UIkit.modal('#az-menu').hide();
    });
  });

  // Notifications: mark all as read (sample only; the club extension will persist it)
  document.querySelectorAll('[data-az-readall]').forEach(function (readAll) {
    readAll.addEventListener('click', function () {
      document.querySelectorAll('.az-note.is-unread').forEach(function (n) { n.classList.remove('is-unread'); n.dataset.group = 'read'; });
      document.querySelectorAll('.az-bell-badge, .az-account-nav em').forEach(function (b) { b.remove(); });
      var label = document.querySelector('[data-az-unread-label]');
      if (label) label.textContent = 'همه پیام‌ها خوانده شده';
      document.querySelectorAll('[data-az-readall]').forEach(function (b) { b.remove(); });
    });
  });

  // Account lists: filter tabs (orders by status, messages by read state)
  document.querySelectorAll('[data-az-filter]').forEach(function (tabs) {
    tabs.addEventListener('click', function (e) {
      var tab = e.target.closest('[data-filter]');
      if (!tab) return;
      e.preventDefault();
      tabs.querySelectorAll('[data-filter]').forEach(function (t) { t.classList.toggle('is-active', t === tab); });
      var shown = 0;
      document.querySelectorAll('[data-group]').forEach(function (row) {
        var on = tab.dataset.filter === 'all' || row.dataset.group === tab.dataset.filter;
        row.hidden = !on;
        if (on) shown++;
      });
      var empty = document.querySelector('.az-order-empty');
      if (empty) empty.hidden = shown > 0;
    });
  });

  // Order detail: copy the parcel tracking code
  document.querySelectorAll('[data-az-copy]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var text = btn.parentElement.querySelector('[data-az-copy-text]').textContent.replace(/[۰-۹]/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d); });
      if (navigator.clipboard) navigator.clipboard.writeText(text);
      notify('کد رهگیری کپی شد');
    });
  });

  // Footer columns collapse into an accordion on mobile
  document.querySelectorAll('.az-footer-head').forEach(function (head) {
    head.addEventListener('click', function () {
      if (window.matchMedia('(min-width: 960px)').matches) return;
      var col = head.closest('.az-footer-col');
      col.classList.toggle('is-open');
      head.setAttribute('aria-expanded', col.classList.contains('is-open'));
    });
  });

  // Category grid density: 4 / 5 / 6 columns on desktop, 1 / 2 on mobile, each remembered
  var grid = document.getElementById('az-grid');
  var switches = document.querySelectorAll('.az-grid-switch button');
  var desktop = window.matchMedia('(min-width: 960px)');
  var colsKey = function () { return desktop.matches ? 'azColsDesktop' : 'azColsMobile'; };
  var colsDefault = function () { return desktop.matches ? '4' : '2'; };

  function setCols(cols, remember) {
    grid.className = grid.className.replace(/\bcols-\d\b/g, '').trim() + ' cols-' + cols;
    switches.forEach(function (b) { b.classList.toggle('is-active', b.dataset.cols === String(cols)); });
    if (remember) { try { localStorage.setItem(colsKey(), cols); } catch (e) {} }
  }

  function restoreCols() {
    var saved = null;
    try { saved = localStorage.getItem(colsKey()); } catch (e) {}
    setCols(saved || colsDefault(), false);
  }

  if (grid && switches.length) {
    restoreCols();
    switches.forEach(function (b) { b.addEventListener('click', function () { setCols(b.dataset.cols, true); }); });
    if (desktop.addEventListener) desktop.addEventListener('change', restoreCols);
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

  // Live search overlay (same JSON contract as mod_netparsisearch)
  var search = document.getElementById('az-search');

  if (search) {
    var input = search.querySelector('.az-search-input');
    var results = search.querySelector('.az-search-results');
    var endpoint = search.dataset.endpoint;
    var min = parseInt(search.dataset.min || '2', 10);
    var timer = null;
    var controller = null;
    var labels = { hcategories: 'دسته‌بندی‌ها', products: 'محصولات', categories: 'دسته‌بندی‌ها', articles: 'مطالب' };

    var esc = function (s) {
      return String(s || '').replace(/[&<>"']/g, function (c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
      });
    };
    var fold = function (s) {
      return String(s || '').replace(/ي/g, 'ی').replace(/ك/g, 'ک').toLowerCase().trim();
    };
    var mark = function (title, q) {
      var out = esc(title);
      fold(q).split(/\s+/).filter(Boolean).forEach(function (term) {
        var re = new RegExp('(' + esc(term).replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
        out = out.replace(re, '<mark>$1</mark>');
      });
      return out;
    };
    var faNum = function (n) {
      return String(n).replace(/\d/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'[d]; });
    };

    var openSearch = function () {
      if (window.UIkit) { var m = UIkit.modal('#az-menu'); if (m && m.isToggled()) m.hide(); }
      search.classList.add('is-open');
      search.setAttribute('aria-hidden', 'false');
      document.documentElement.classList.add('az-lock');
      setTimeout(function () { input.focus(); }, 80);
    };
    var closeSearch = function () {
      search.classList.remove('is-open');
      search.setAttribute('aria-hidden', 'true');
      document.documentElement.classList.remove('az-lock');
    };

    var render = function (data, q) {
      search.classList.remove('is-loading');
      if (!data || !data.total) {
        results.innerHTML = '<div class="az-sr-empty"><strong>نتیجه‌ای برای «' + esc(q) + '» پیدا نشد</strong><span>املای کلمه را بررسی کنید یا عبارت کوتاه‌تری بنویسید.</span></div>';
        return;
      }
      var html = '<div class="az-sr-head"><span>' + faNum(data.total) + ' نتیجه برای «' + esc(q) + '»</span>' +
        '<a href="' + search.querySelector('form').action + '?page=search&q=' + encodeURIComponent(q) + '">مشاهده همه نتایج</a></div>';
      var i = 0;
      data.groups.forEach(function (group) {
        html += '<div class="az-sr-group az-sr-' + group.key + '"><div class="az-search-label">' + (labels[group.key] || group.key) + '</div>';
        if (group.key === 'products') {
          html += '<div class="az-sr-grid">';
          group.items.forEach(function (item) {
            html += '<a class="az-sr-item" style="--i:' + (i++) + '" href="' + esc(item.url) + '">' +
              '<span class="az-sr-img">' + (item.image ? '<img src="' + esc(item.image) + '" alt="" onload="this.classList.add(\'is-loaded\')">' : '') + '</span>' +
              '<span class="az-sr-title">' + mark(item.title, q) + '</span>' +
              (item.price ? '<span class="az-sr-price">' + item.price + '</span>' : '') + '</a>';
          });
          html += '</div>';
        } else {
          html += '<div class="az-search-chips">';
          group.items.forEach(function (item) {
            html += '<a style="--i:' + (i++) + '" href="' + esc(item.url) + '">' + mark(item.title, q) + '</a>';
          });
          html += '</div>';
        }
        html += '</div>';
      });
      results.innerHTML = html;
    };

    var run = function () {
      var q = input.value.trim();
      search.classList.toggle('has-query', q.length >= min);
      if (controller) controller.abort();
      if (q.length < min) { results.innerHTML = ''; search.classList.remove('is-loading'); return; }
      search.classList.add('is-loading');
      controller = window.AbortController ? new AbortController() : null;
      fetch(endpoint + '&q=' + encodeURIComponent(q), controller ? { signal: controller.signal } : {})
        .then(function (r) { return r.json(); })
        .then(function (data) { if (input.value.trim() === q) render(data, q); })
        .catch(function (err) {
          if (err.name === 'AbortError') return;
          search.classList.remove('is-loading');
          results.innerHTML = '<div class="az-sr-empty"><strong>خطا در جستجو</strong><span>دوباره تلاش کنید.</span></div>';
        });
    };

    input.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(run, 250); });

    document.addEventListener('click', function (e) {
      var t = e.target.closest('[data-az-search]');
      if (t) { e.preventDefault(); openSearch(); return; }
      if (e.target.closest('[data-az-search-close]')) { closeSearch(); return; }
      var term = e.target.closest('[data-az-term]');
      if (term) { input.value = term.dataset.azTerm; input.focus(); run(); }
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && search.classList.contains('is-open')) closeSearch();
      var typing = /INPUT|TEXTAREA|SELECT/.test(document.activeElement.tagName);
      if (e.key === '/' && !typing && !search.classList.contains('is-open')) { e.preventDefault(); openSearch(); }
    });
  }

  document.querySelectorAll('[data-az-add]').forEach(function (btn) {
    btn.addEventListener('click', function () { notify('به سبد خرید اضافه شد'); });
  });
});
