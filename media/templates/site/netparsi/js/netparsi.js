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

  // Notifications: mark all as read (a real customer: saved through the amujcontact plugin, data-url)
  document.querySelectorAll('[data-az-readall]').forEach(function (readAll) {
    readAll.addEventListener('click', function () {
      if (readAll.dataset.url) {
        var body = new FormData();
        body.append('form', 'readall');
        body.append(readAll.dataset.token, '1');
        fetch(readAll.dataset.url, { method: 'POST', body: body, credentials: 'same-origin' }).catch(function () {});
      }
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

  // Birthday form (dashboard modal and the club's birthday popup); a real customer: posted to the club extension (form action)
  document.querySelectorAll('[data-az-birthday-form]').forEach(function (bdayForm) {
    bdayForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var d = bdayForm.querySelector('[data-part=day]'), m = bdayForm.querySelector('[data-part=month]'), y = bdayForm.querySelector('[data-part=year]');
      var error = bdayForm.querySelector('[data-error]');
      if (!d.value || !m.value || !y.value) {
        error.textContent = 'روز، ماه و سال تولد را انتخاب کنید.';
        return;
      }
      var done = function () {
        var cell = document.querySelector('[data-az-birthday]');
        if (cell) {
          cell.textContent = d.options[d.selectedIndex].text + ' ' + m.options[m.selectedIndex].text + ' ' + y.options[y.selectedIndex].text;
          cell.classList.add('is-new');
        }
        var hint = document.querySelector('[data-az-birthday-hint]');
        if (hint) hint.remove();
        var modal = bdayForm.closest('[uk-modal]');
        if (modal && window.UIkit) UIkit.modal(modal).hide();
        notify('تاریخ تولد شما ثبت شد');
      };
      if (!bdayForm.getAttribute('action')) return done();
      error.textContent = '';
      fetch(bdayForm.getAttribute('action'), { method: 'POST', body: new FormData(bdayForm), credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (res && res.result == 1) return done();
          error.textContent = (res && res.message) || 'تاریخ تولد ذخیره نشد؛ دوباره امتحان کنید.';
        })
        .catch(function () { error.textContent = 'تاریخ تولد ذخیره نشد؛ دوباره امتحان کنید.'; });
    });
  });

  // Contact form (sample: validates and shows a thank-you; the form extension sends it)
  var contactForm = document.querySelector('[data-az-contact]');
  if (contactForm) {
    contactForm.addEventListener('submit', function (e) {
      e.preventDefault();
      // (form.name is the form's own name attribute, so fields are read by selector)
      var field = function (n) { return contactForm.querySelector('[name="' + n + '"]'); };
      var errorEl = contactForm.querySelector('[data-error]');
      var phone = field('phone').value.replace(/[۰-۹]/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d); }).replace(/\D/g, '');
      var ok = field('name').value.trim().length > 2 && /^09\d{9}$/.test(phone) && field('message').value.trim().length > 3;
      errorEl.textContent = ok ? '' : 'لطفاً نام، شماره موبایل (مثل ۰۹۱۲۳۴۵۶۷۸۹) و متن پیام را کامل وارد کنید.';
      if (!ok) return;
      var btn = contactForm.querySelector('button[type="submit"]');
      btn.classList.add('is-loading');
      btn.disabled = true;
      var finish = function () {
        contactForm.querySelectorAll('.az-field, button[type="submit"]').forEach(function (el) { el.hidden = true; });
        contactForm.querySelector('.az-contact-done').hidden = false;
      };
      // Static demo page has no action: just show the thank-you
      if (!contactForm.getAttribute('action')) { setTimeout(finish, 800); return; }
      fetch(contactForm.action, { method: 'POST', body: new FormData(contactForm), credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (res.ok) { finish(); return; }
          errorEl.textContent = res.message;
        })
        .catch(function () { errorEl.textContent = 'ارسال پیام انجام نشد. لطفاً دوباره امتحان کنید.'; })
        .then(function () { btn.classList.remove('is-loading'); btn.disabled = false; });
    });
  }

  // Footer newsletter: email or mobile, saved by the amujcontact plugin
  var newsForm = document.querySelector('[data-az-newsletter]');
  if (newsForm) {
    var newsMsg = document.querySelector('[data-az-newsletter-msg]');
    newsForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var input = newsForm.querySelector('[name="contact"]');
      var value = input.value.trim();
      var digits = value.replace(/[۰-۹]/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d); }).replace(/[\s-]/g, '');
      var say = function (text, ok) { newsMsg.textContent = text; newsMsg.classList.toggle('is-error', !ok); };
      if (!/^09\d{9}$/.test(digits) && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) { say('یک ایمیل یا شماره موبایل معتبر وارد کنید.', false); input.focus(); return; }
      var btn = newsForm.querySelector('button[type="submit"]');
      btn.classList.add('is-loading'); btn.disabled = true;
      fetch(newsForm.action, { method: 'POST', body: new FormData(newsForm), credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (res) { say(res.message, res.ok); if (res.ok) input.value = ''; })
        .catch(function () { say('ثبت عضویت انجام نشد. لطفاً دوباره امتحان کنید.', false); })
        .then(function () { btn.classList.remove('is-loading'); btn.disabled = false; });
    });
  }

  // Swipe rows (category / account menus): bring the active item into view
  document.querySelectorAll('.az-cat-nav .is-active, .az-account-nav .is-active').forEach(function (a) {
    var row = a.parentElement;
    if (row.scrollWidth > row.clientWidth) {
      row.scrollLeft += a.getBoundingClientRect().left - row.getBoundingClientRect().left - (row.clientWidth - a.offsetWidth) / 2;
    }
  });

  // Product page: choosing a color brings that color's photo to the front of the gallery
  var gallery = document.querySelector('[data-az-gallery]');
  document.querySelectorAll('[data-az-color]').forEach(function (swatch) {
    swatch.addEventListener('click', function () {
      var label = document.querySelector('[data-az-color-label]');
      if (label) label.textContent = swatch.dataset.name;
      var photo = gallery && gallery.querySelector('[data-color="' + swatch.dataset.azColor + '"]');
      if (!photo || photo === gallery.firstElementChild) return;
      photo.querySelectorAll('img').forEach(function (i) { i.removeAttribute('loading'); });
      gallery.prepend(photo);
      photo.animate([{ opacity: 0 }, { opacity: 1 }], { duration: 450, easing: 'ease-out' });
      gallery.scrollTo({ left: 0, behavior: 'smooth' });
      var top = gallery.getBoundingClientRect().top;
      if (top < 0) window.scrollBy({ top: top - 140, behavior: 'smooth' });
    });
  });

  // Checkout: credit toggle + discount code (sample: AMUJ10 = 10% off) update the payable total
  var totals = document.querySelector('[data-az-totals]');
  if (totals) {
    var toman = function (n) {
      return Math.round(n).toLocaleString('en-US').replace(/,/g, '٬').replace(/\d/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'[d]; }) + ' تومان';
    };
    var state = { discount: 0, credit: 0 };
    var creditBox = document.querySelector('[data-az-credit]');
    var coupon = document.querySelector('[data-az-coupon]');
    var msg = document.querySelector('[data-az-coupon-msg]');

    var refresh = function () {
      var sub = Number(totals.dataset.subtotal), ship = Number(totals.dataset.shipping);
      var dRow = totals.querySelector('[data-az-row-discount]'), cRow = totals.querySelector('[data-az-row-credit]');
      dRow.hidden = !state.discount; cRow.hidden = !state.credit;
      dRow.lastElementChild.textContent = '− ' + toman(state.discount);
      cRow.lastElementChild.textContent = '− ' + toman(state.credit);
      totals.querySelector('[data-az-pay]').textContent = toman(Math.max(0, sub + ship - state.discount - state.credit));
    };

    document.querySelectorAll('input[name="ship"]').forEach(function (radio) {
      radio.addEventListener('change', function () {
        var cost = Number(radio.value);
        totals.dataset.shipping = cost;
        totals.querySelector('[data-az-ship-label]').textContent = '(' + radio.dataset.label + ')';
        totals.querySelector('[data-az-ship-cost]').textContent = cost ? toman(cost) : 'رایگان';
        refresh();
      });
    });

    if (creditBox) {
      creditBox.addEventListener('change', function () {
        state.credit = creditBox.checked ? Number(creditBox.dataset.azCredit) : 0;
        refresh();
      });
    }
    if (coupon) {
      coupon.addEventListener('submit', function () {
        var code = coupon.coupon.value.trim().toUpperCase();
        msg.classList.remove('is-ok', 'is-error');
        if (code === 'AMUJ10') {
          state.discount = Number(totals.dataset.subtotal) * 0.1;
          msg.textContent = 'کد تخفیف ۱۰٪ اعمال شد.';
          msg.classList.add('is-ok');
        } else {
          state.discount = 0;
          msg.textContent = code ? 'این کد تخفیف معتبر نیست.' : 'کد تخفیف را وارد کنید.';
          msg.classList.add('is-error');
        }
        refresh();
      });
    }
  }

  var latin = function (s) {
    return String(s).replace(/[۰-۹]/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d); })
      .replace(/[٠-٩]/g, function (d) { return '٠١٢٣٤٥٦٧٨٩'.indexOf(d); });
  };
  var persian = function (s) { return String(s).replace(/\d/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'[d]; }); };

  // Sign in with the SMS auth component: each step is its own page posting to the component
  // (html/com_auth). Here the mobile is formatted, the code boxes are joined and the resend timer runs.
  var authLive = document.querySelector('[data-az-auth-live]');
  if (authLive) {
    var liveBtn = authLive.querySelector('button[type="submit"]');
    var liveError = authLive.querySelector('[data-error]');
    var sending = function () { liveBtn.classList.add('is-loading'); liveBtn.disabled = true; };

    // Step 1: mobile
    var liveMobile = authLive.querySelector('.az-auth-mobile');
    if (liveMobile) {
      var mobileDigits = function () { return latin(liveMobile.value).replace(/\D/g, '').slice(0, 11); };
      liveMobile.addEventListener('input', function () {
        var v = mobileDigits();
        liveMobile.value = persian(v.replace(/^(\d{4})(\d{0,3})(\d{0,4}).*/, function (m, a, b, c) { return [a, b, c].filter(Boolean).join(' '); }));
        liveError.textContent = '';
        liveBtn.disabled = v.length !== 11;
      });
      authLive.addEventListener('submit', function (e) {
        var v = mobileDigits();
        if (!/^09\d{9}$/.test(v)) { e.preventDefault(); liveError.textContent = 'شماره موبایل معتبر نیست. نمونه: ۰۹۱۲۳۴۵۶۷۸۹'; return; }
        authLive.mobilenum.value = v;
        sending();
      });
    }

    // Step 2: code boxes -> codenum, sent as soon as all digits are in
    var liveBoxes = authLive.querySelectorAll('.az-otp input');
    if (liveBoxes.length) {
      var liveCode = function () { return Array.prototype.map.call(liveBoxes, function (b) { return latin(b.value); }).join(''); };
      var liveSync = function () {
        liveBtn.disabled = liveCode().length !== liveBoxes.length;
        liveBoxes.forEach(function (b) { b.classList.toggle('is-filled', !!b.value); });
      };
      liveBoxes.forEach(function (box, i) {
        box.addEventListener('input', function () {
          var digits = latin(box.value).replace(/\D/g, '');
          if (digits.length > 1) { // pasted or autofilled code
            digits.slice(0, liveBoxes.length - i).split('').forEach(function (d, k) { liveBoxes[i + k].value = persian(d); });
            liveBoxes[Math.min(i + digits.length, liveBoxes.length) - 1].focus();
          } else {
            box.value = persian(digits);
            if (digits && liveBoxes[i + 1]) liveBoxes[i + 1].focus();
          }
          liveError.textContent = '';
          liveSync();
          if (liveCode().length === liveBoxes.length && !liveBtn.classList.contains('is-loading')) authLive.requestSubmit();
        });
        box.addEventListener('keydown', function (e) {
          if (e.key === 'Backspace' && !box.value && liveBoxes[i - 1]) { liveBoxes[i - 1].focus(); liveBoxes[i - 1].value = ''; liveSync(); }
        });
      });
      authLive.addEventListener('submit', function (e) {
        if (liveCode().length !== liveBoxes.length) { e.preventDefault(); return; }
        authLive.codenum.value = liveCode();
        sending();
      });

      var liveTimer = authLive.querySelector('[data-az-timer]');
      var left = parseInt(liveTimer.dataset.left, 10) || 0;
      if (left > 0) {
        var tick = function () {
          liveTimer.querySelector('b').textContent = persian(Math.floor(left / 60) + ':' + String(left % 60).padStart(2, '0'));
          if (left-- <= 0) {
            clearInterval(tickId);
            liveTimer.hidden = true;
            authLive.querySelector('[data-az-resend]').hidden = false;
          }
        };
        var tickId = setInterval(tick, 1000);
        tick();
      }
    }

    // Step 3: new customer's name (+ email when the component asks for it)
    var liveName = authLive.querySelector('#az-name'); // (form.name is the form's own name attribute)
    if (liveName) {
      authLive.addEventListener('submit', function (e) {
        var email = authLive.querySelector('#az-email');
        var okName = liveName.value.trim().length >= 3;
        var okEmail = !email || (!email.value.trim() && !email.required) || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim());
        authLive.querySelector('[data-error-name]').textContent = okName ? '' : 'نام و نام خانوادگی را وارد کنید.';
        if (email) authLive.querySelector('[data-error-email]').textContent = okEmail ? '' : 'ایمیل معتبر وارد کنید.';
        if (!okName || !okEmail) { e.preventDefault(); return; }
        sending();
      });
    }
  }

  // Sign in / sign up: mobile -> SMS code -> (new customer) name + email.
  // Demo only (static login page): code 11111 = existing customer.
  var auth = document.querySelector('[data-az-auth]');
  if (auth) {
    var steps = auth.querySelectorAll('.az-auth-step');
    var dots = auth.querySelectorAll('.az-auth-steps li');
    var mobile = '';
    var timerId = null;

    var show = function (n) {
      steps.forEach(function (s) { s.classList.toggle('is-active', s.dataset.step === String(n)); });
      dots.forEach(function (d, i) { d.classList.toggle('is-active', i < n); });
      var focus = auth.querySelector('.az-auth-step.is-active input');
      if (focus) setTimeout(function () { focus.focus(); }, 250);
    };
    var busy = function (btn, then) {
      btn.classList.add('is-loading'); btn.disabled = true;
      setTimeout(function () { btn.classList.remove('is-loading'); btn.disabled = false; then(); }, 700);
    };
    var done = function (title) {
      auth.querySelector('[data-az-done-title]').textContent = title;
      show(4);
      setTimeout(function () { window.location.href = auth.dataset.success; }, 1800);
    };

    // Step 1
    var f1 = auth.querySelector('[data-step="1"]');
    var phoneInput = f1.querySelector('input');
    phoneInput.addEventListener('input', function () {
      var v = latin(phoneInput.value).replace(/\D/g, '').slice(0, 11);
      phoneInput.value = persian(v.replace(/^(\d{4})(\d{0,3})(\d{0,4}).*/, function (m, a, b, c) { return [a, b, c].filter(Boolean).join(' '); }));
      f1.querySelector('[data-error]').textContent = '';
      f1.querySelector('button[type="submit"]').disabled = v.length !== 11;
    });
    f1.addEventListener('submit', function (e) {
      e.preventDefault();
      var v = latin(phoneInput.value).replace(/\D/g, '');
      if (!/^09\d{9}$/.test(v)) { f1.querySelector('[data-error]').textContent = 'شماره موبایل معتبر نیست. نمونه: ۰۹۱۲۳۴۵۶۷۸۹'; return; }
      mobile = v;
      busy(f1.querySelector('button[type="submit"]'), function () {
        auth.querySelector('[data-az-phone]').textContent = phoneInput.value;
        show(2); startTimer();
      });
    });

    // Step 2
    var f2 = auth.querySelector('[data-step="2"]');
    var boxes = f2.querySelectorAll('.az-otp input');
    var code = function () { return Array.prototype.map.call(boxes, function (b) { return latin(b.value); }).join(''); };
    var syncOtp = function () {
      f2.querySelector('button[type="submit"]').disabled = code().length !== boxes.length;
      f2.querySelector('[data-error]').textContent = '';
      boxes.forEach(function (b) { b.classList.toggle('is-filled', !!b.value); });
    };
    boxes.forEach(function (box, i) {
      box.addEventListener('input', function () {
        var digits = latin(box.value).replace(/\D/g, '');
        if (digits.length > 1) { // pasted or autofilled code
          digits.slice(0, boxes.length - i).split('').forEach(function (d, k) { boxes[i + k].value = persian(d); });
          boxes[Math.min(i + digits.length, boxes.length) - 1].focus();
        } else {
          box.value = persian(digits);
          if (digits && boxes[i + 1]) boxes[i + 1].focus();
        }
        syncOtp();
        if (code().length === boxes.length) f2.requestSubmit();
      });
      box.addEventListener('keydown', function (e) {
        if (e.key === 'Backspace' && !box.value && boxes[i - 1]) { boxes[i - 1].focus(); boxes[i - 1].value = ''; syncOtp(); }
      });
    });
    f2.addEventListener('submit', function (e) {
      e.preventDefault();
      if (code().length !== boxes.length) return;
      busy(f2.querySelector('button[type="submit"]'), function () {
        clearInterval(timerId);
        if (code() === '11111') { done('خوش آمدید سارا'); } else { show(3); }
      });
    });
    auth.querySelector('[data-az-back]').addEventListener('click', function () {
      clearInterval(timerId); boxes.forEach(function (b) { b.value = ''; }); syncOtp(); show(1);
    });

    var startTimer = function () {
      var left = 120;
      var timer = auth.querySelector('[data-az-timer]');
      var resend = auth.querySelector('[data-az-resend]');
      timer.hidden = false; resend.hidden = true;
      clearInterval(timerId);
      var tick = function () {
        timer.querySelector('b').textContent = persian(Math.floor(left / 60) + ':' + String(left % 60).padStart(2, '0'));
        if (left-- <= 0) { clearInterval(timerId); timer.hidden = true; resend.hidden = false; }
      };
      tick(); timerId = setInterval(tick, 1000);
    };
    auth.querySelector('[data-az-resend]').addEventListener('click', function () {
      startTimer(); notify('کد تأیید دوباره ارسال شد');
    });

    // Step 3
    var f3 = auth.querySelector('[data-step="3"]');
    f3.addEventListener('submit', function (e) {
      e.preventDefault();
      var name = f3.name.value.trim(), email = f3.email.value.trim();
      var okName = name.length >= 3, okEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
      f3.querySelector('[data-error-name]').textContent = okName ? '' : 'نام و نام خانوادگی را وارد کنید.';
      f3.querySelector('[data-error-email]').textContent = okEmail ? '' : 'ایمیل معتبر وارد کنید.';
      if (!okName || !okEmail) return;
      busy(f3.querySelector('button[type="submit"]'), function () { done('خوش آمدید ' + name.split(/\s+/)[0]); });
    });
  }

  // Added-to-cart panel: product page button (needs a size) and card quick sizes open it
  var addedPanel = document.getElementById('az-added');
  var faDigits = function (n) { return String(n).replace(/\d/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'[d]; }); };
  var cartCount = (function () {
    var bag = document.querySelector('.az-header .az-bag');
    var raw = bag ? bag.textContent.replace(/[۰-۹]/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d); }) : '0';
    return parseInt(raw, 10) || 0;
  })();

  var openAdded = function (data) {
    if (!addedPanel || !window.UIkit) { notify('به سبد خرید اضافه شد'); return; }
    cartCount = typeof data.count === 'number' ? data.count : cartCount + 1;
    document.querySelectorAll('.az-bag').forEach(function (b) { b.textContent = faDigits(cartCount); b.classList.remove('is-bump'); void b.offsetWidth; b.classList.add('is-bump'); });
    addedPanel.querySelector('[data-az-added-img]').src = data.img || '';
    addedPanel.querySelector('[data-az-added-name]').textContent = data.name || '';
    addedPanel.querySelector('[data-az-added-meta]').textContent = [data.color, data.size ? 'سایز ' + data.size : ''].filter(Boolean).join(' | ');
    addedPanel.querySelector('[data-az-added-price]').textContent = data.price || '';
    addedPanel.querySelector('[data-az-added-count]').textContent = faDigits(cartCount) + ' کالا';
    UIkit.offcanvas(addedPanel).show();
  };

  // HikaShop cart: posts the chosen variant, then opens the panel. Without a variant id (static demo) only the panel opens.
  var cartUrl = document.body.dataset.azCart;
  var addToCart = function (variantId, data, btn) {
    if (!cartUrl || !variantId) { openAdded(data); return; }
    if (btn) { btn.disabled = true; btn.classList.add('is-busy'); }
    var body = new URLSearchParams({ product_id: variantId, quantity: 1, add: 1 });
    fetch(cartUrl, { method: 'POST', body: body, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (!res || !res.ret) throw new Error((res && res.message) || '');
        // HikaShop returns the whole cart: the header count follows the real quantity
        if (res.products) {
          data.count = res.products.reduce(function (n, p) { return n + (p.quantity || 0); }, 0);
        }
        openAdded(data);
      })
      .catch(function (err) { notify(err && err.message ? err.message : 'افزودن به سبد انجام نشد. دوباره تلاش کنید.'); })
      .then(function () { if (btn) { btn.disabled = false; btn.classList.remove('is-busy'); } });
  };

  // Product page: sizes that are sold out in the chosen colour
  var productBox = document.querySelector('[data-az-product][data-az-variants]');
  var variantMap = productBox ? JSON.parse(productBox.dataset.azVariants || '{}') : {};
  var chosenColor = function () {
    var s = productBox && productBox.querySelector('[data-az-color].is-active');
    return s ? s.dataset.value : '0';
  };
  var markSizes = function () {
    if (!productBox) return;
    productBox.querySelectorAll('[data-az-sizes] li[data-value]').forEach(function (li) {
      if (li.dataset.value === '0') return;
      var v = variantMap[chosenColor() + '|' + li.dataset.value];
      var out = !v || !v[1];
      li.classList.toggle('is-out', out);
      if (out) li.classList.remove('is-active');
      var tag = li.querySelector('[data-az-out]');
      if (tag) tag.hidden = !out;
    });
  };
  if (productBox) {
    productBox.querySelectorAll('[data-az-color]').forEach(function (s) {
      s.addEventListener('click', function () { setTimeout(markSizes, 0); });
    });
  }

  document.querySelectorAll('[data-az-add]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var box = btn.closest('[data-az-product]');
      var sizes = box && box.querySelector('[data-az-sizes]');
      var chosen = sizes && sizes.querySelector('li.is-active:not(.is-out) span');
      var error = box && box.querySelector('[data-az-size-error]');
      if (sizes && !chosen) {
        if (error) error.hidden = false;
        sizes.classList.remove('is-shake'); void sizes.offsetWidth; sizes.classList.add('is-shake');
        return;
      }
      if (error) error.hidden = true;
      var label = document.querySelector('[data-az-color-label]');
      var sizeLi = chosen && chosen.closest('li');
      var variant = box.dataset.azVariants ? variantMap[chosenColor() + '|' + (sizeLi ? sizeLi.dataset.value : '0')] : null;
      addToCart(variant ? variant[0] : 0, { name: box.dataset.name, price: box.dataset.price, img: box.dataset.img,
                  color: label ? label.textContent : '', size: chosen ? chosen.textContent : '' }, btn);
    });
  });
  document.querySelectorAll('[data-az-sizes]').forEach(function (sizes) {
    sizes.addEventListener('click', function () {
      var error = sizes.closest('[data-az-product]').querySelector('[data-az-size-error]');
      if (error) error.hidden = true;
    });
  });

  // Wishlist page: remove items (sample; HikaShop wishlist stores it)
  var wish = document.querySelector('[data-az-wishlist]');
  if (wish) {
    wish.addEventListener('click', function (e) {
      var btn = e.target.closest('.az-wish-remove');
      if (!btn) return;
      var item = btn.closest('.az-wish-item');
      item.classList.add('is-leaving');
      setTimeout(function () {
        item.remove();
        var left = wish.querySelectorAll('.az-wish-item').length;
        var count = document.querySelector('[data-az-wish-count]');
        if (count) count.textContent = '(' + faDigits(left) + ' کالا)';
        if (!left) document.querySelector('.az-wish-empty').hidden = false;
      }, 300);
      notify('از علاقه‌مندی‌ها حذف شد');
    });
  }

  // Mobile menu: numbered groups open and close like an accordion
  document.querySelectorAll('.az-menu-group-title').forEach(function (head) {
    head.addEventListener('click', function () {
      if (window.matchMedia('(min-width: 960px)').matches) return;
      var group = head.closest('.az-menu-group');
      group.classList.toggle('is-open');
      head.setAttribute('aria-expanded', group.classList.contains('is-open'));
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

  // HikaShop cart page: quantity and remove are saved in HikaShop, then the page reloads with new totals
  var cartPage = document.querySelector('[data-az-cart-page]');
  if (cartPage && cartUrl) {
    var saveLine = function (row, quantity) {
      row.classList.add('is-busy');
      var body = new URLSearchParams({ cart_product_id: row.dataset.azLine, quantity: quantity });
      fetch(cartUrl, { method: 'POST', body: body, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (res && res.messages && res.messages.length) notify(res.messages[0].msg || res.messages[0]);
          window.location.reload();
        })
        .catch(function () { row.classList.remove('is-busy'); notify('ذخیره سبد انجام نشد. دوباره تلاش کنید.'); });
    };
    var timer;
    cartPage.addEventListener('click', function (e) {
      var row = e.target.closest('[data-az-line]');
      if (!row) return;
      if (e.target.closest('[data-az-remove]')) { saveLine(row, 0); return; }
      if (e.target.closest('.az-qty button')) {
        // the quantity handler above already updated the number; save after a short pause
        clearTimeout(timer);
        timer = setTimeout(function () { saveLine(row, row.querySelector('.az-qty span').dataset.n); }, 500);
      }
    });
  }

  function notify(message) {
    if (window.UIkit) UIkit.notification({ message: message, pos: 'bottom-center', timeout: 2000 });
  }

  // Wishlist heart: HikaShop products are saved in the customer's wishlist (amujcontact plugin, form=wish);
  // guests go to the mobile login; demo cards only toggle
  function toggleWish(btn, card) {
    var id = card && card.dataset.id;
    var body = document.body.dataset;
    if (id && body.azLogin) {
      window.location.href = body.azLogin;
      return;
    }
    if (!id || !body.azWish) {
      btn.classList.toggle('is-active');
      notify(btn.classList.contains('is-active') ? 'به علاقه‌مندی‌ها اضافه شد' : 'از علاقه‌مندی‌ها حذف شد');
      return;
    }
    var data = new FormData();
    data.append('form', 'wish');
    data.append('product_id', id);
    if (btn.dataset.op) data.append('op', btn.dataset.op);
    data.append(body.azToken, '1');
    btn.disabled = true;
    fetch(body.azWish, { method: 'POST', body: data, credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        btn.disabled = false;
        if (!res || !res.ok) { notify((res && res.message) || 'انجام نشد'); return; }
        document.querySelectorAll('.az-card[data-id="' + id + '"] .az-card-fav').forEach(function (b) { b.classList.toggle('is-active', res.active); });
        notify(res.message);
        document.dispatchEvent(new CustomEvent('az:wish', { detail: { id: id, active: res.active, count: res.count } }));
      })
      .catch(function () { btn.disabled = false; notify('انجام نشد'); });
  }

  // Wishlist page of a real customer: "حذف" removes through the same endpoint; removed cards leave the grid
  var wishPage = document.querySelector('[data-az-wish-page]');
  if (wishPage) {
    wishPage.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-az-wish-remove]');
      if (!btn) return;
      var item = btn.closest('.az-wish-item');
      var fav = item.querySelector('.az-card-fav');
      fav.dataset.op = 'remove';
      toggleWish(fav, item.querySelector('.az-card'));
    });
    document.addEventListener('az:wish', function (e) {
      if (e.detail.active) return;
      var item = wishPage.querySelector('.az-wish-item[data-id="' + e.detail.id + '"]');
      if (item) item.remove();
      var left = wishPage.querySelectorAll('.az-wish-item').length;
      var count = document.querySelector('[data-az-wish-count]');
      if (count) { count.textContent = '(' + persian(left) + ' کالا)'; count.hidden = !left; }
      wishPage.hidden = !left;
      document.querySelector('[data-az-wish-empty]').hidden = left > 0;
    });
  }

  // Joomla system messages: success / info cards close themselves after a few seconds (errors and warnings stay)
  var sysMessages = document.getElementById('system-message-container');
  if (sysMessages) {
    var autoClose = function () {
      sysMessages.querySelectorAll('joomla-alert:not([data-az-timed])').forEach(function (alert) {
        alert.setAttribute('data-az-timed', '1');
        if (['success', 'info', 'message', 'notice'].indexOf(alert.getAttribute('type')) < 0) return;
        setTimeout(function () {
          if (typeof alert.close === 'function') alert.close(); else alert.remove();
        }, 6000);
      });
    };
    autoClose();
    new MutationObserver(autoClose).observe(sysMessages, { childList: true });
  }

  // Filter panel: a chosen radio (price range, sort) can be clicked again to clear it
  document.querySelectorAll('input[type=radio][data-az-uncheck]').forEach(function (radio) {
    radio.addEventListener('mousedown', function () { radio.dataset.was = radio.checked ? '1' : ''; });
    radio.addEventListener('click', function () {
      if (radio.dataset.was) { radio.checked = false; radio.dataset.was = ''; }
    });
  });

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
      var card = size.closest('.az-card');
      addToCart(parseInt(size.dataset.variant || '0', 10), { name: card.dataset.name, price: card.dataset.price, img: card.dataset.img,
                  color: card.dataset.color || '', size: size.textContent });
    } else if (fav) {
      toggleWish(fav, fav.closest('.az-card'));
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

  // Checkout (HikaShop): prices that HikaShop renders itself come with Latin digits; blocks are re-rendered by AJAX
  var coLive = document.querySelector('.az-co-live');
  if (coLive) {
    var coDigits = function () {
      coLive.querySelectorAll('.hikashop_checkout_shipping_cost, .hikashop_checkout_payment_cost').forEach(function (el) {
        var t = persian(el.textContent).replace(/,/g, '٬');
        if (t !== el.textContent) el.textContent = t;
      });
    };
    coDigits();
    new MutationObserver(coDigits).observe(coLive, { childList: true, subtree: true });
  }

});
