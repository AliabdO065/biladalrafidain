document.addEventListener('DOMContentLoaded', function () {
  // FAQ accordion
  document.querySelectorAll('.lk-faq-item').forEach(function (item) {
    var q = item.querySelector('.lk-faq-q');
    q.addEventListener('click', function () {
      var wasOpen = item.classList.contains('open');
      document.querySelectorAll('.lk-faq-item.open').forEach(function (i) { i.classList.remove('open'); });
      if (!wasOpen) item.classList.add('open');
    });
  });

  // Language switcher dropdown
  var langSwitch = document.querySelector('.lk-lang-switch');
  if (langSwitch) {
    var langBtn = langSwitch.querySelector('.lk-lang-current');
    langBtn.setAttribute('aria-expanded', 'false');
    langBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      langSwitch.classList.toggle('open');
      langBtn.setAttribute('aria-expanded', String(langSwitch.classList.contains('open')));
    });
    document.addEventListener('click', function () {
      langSwitch.classList.remove('open');
      langBtn.setAttribute('aria-expanded', 'false');
    });
    langSwitch.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        langSwitch.classList.remove('open');
        langBtn.setAttribute('aria-expanded', 'false');
        langBtn.focus();
      }
    });
  }

  // Only hide reveal targets after an observer exists; content remains visible without JavaScript.
  if ('IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    var revealObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.remove('lk-reveal-pending');
          revealObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.06 });
    document.querySelectorAll('.mk-feature, .mk-how, .mk-trust, .catalog-card, .catalog-factory-card').forEach(function (element) {
      element.classList.add('lk-reveal', 'lk-reveal-pending');
      revealObserver.observe(element);
    });
  }

  // Callback form stepper
  var form = document.getElementById('lk-callback-form');
  if (!form) return;

  var panels = form.querySelectorAll('.lk-callback-panel');
  var dots = form.querySelectorAll('.lk-callback-dot');
  var step = 1;

  function showStep(n) {
    panels.forEach(function (p) { p.classList.toggle('active', parseInt(p.dataset.step, 10) === n); });
    dots.forEach(function (d) { d.classList.toggle('active', parseInt(d.dataset.step, 10) <= n); });
    step = n;
  }

  form.querySelectorAll('.lk-option-btn[data-problem]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      form.querySelectorAll('.lk-option-btn[data-problem]').forEach(function (b) { b.classList.remove('selected'); });
      btn.classList.add('selected');
      form.querySelector('#lk-problem-type').value = btn.dataset.problem;
      showStep(2);
    });
  });

  var nextFromStep2 = form.querySelector('[data-action="next-2"]');
  if (nextFromStep2) {
    nextFromStep2.addEventListener('click', function () {
      var postal = form.querySelector('#lk-postal-code');
      if (!postal.value.trim()) { postal.focus(); return; }
      showStep(3);
    });
  }

  form.querySelectorAll('[data-action="back"]').forEach(function (btn) {
    btn.addEventListener('click', function () { showStep(step - 1); });
  });

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var submitBtn = form.querySelector('[data-action="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = form.dataset.sendingLabel;

    fetch(form.action, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
        'Accept': 'application/json',
      },
      body: new FormData(form),
    })
      .then(function (res) {
        if (!res.ok) throw new Error('Callback request failed');
        return res.json();
      })
      .then(function (data) {
        if (!data.success) throw new Error('Callback request was not saved');
        showStep(4);
      })
      .catch(function () {
        submitBtn.disabled = false;
        submitBtn.textContent = form.dataset.submitLabel;
        alert(form.dataset.errorMessage);
      });
  });

  showStep(1);
});
