(function () {
  'use strict';

  // Light / dark theme toggle (remembered per browser).
  document.addEventListener('click', function (e) {
    var toggle = e.target.closest('[data-theme-toggle]');
    if (!toggle) return;
    e.preventDefault();
    var next = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-bs-theme', next);
    try { localStorage.setItem('crm-theme', next); } catch (err) {}
  });

  // Copy-to-clipboard buttons: <button data-copy="text">.
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-copy]');
    if (!btn) return;
    e.preventDefault();
    var text = btn.getAttribute('data-copy');
    var done = function () {
      var original = btn.innerHTML;
      btn.innerHTML = '<i class="ti ti-check me-1"></i>Copied';
      setTimeout(function () { btn.innerHTML = original; }, 1500);
    };
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(text).then(done);
    } else {
      var ta = document.createElement('textarea');
      ta.value = text;
      ta.style.position = 'fixed';
      ta.style.opacity = '0';
      document.body.appendChild(ta);
      ta.select();
      try { document.execCommand('copy'); done(); } catch (err) {}
      document.body.removeChild(ta);
    }
  });

  // Forms that need confirmation: <form data-confirm="Are you sure?">.
  document.addEventListener('submit', function (e) {
    var form = e.target.closest('form[data-confirm]');
    if (form && !window.confirm(form.getAttribute('data-confirm'))) {
      e.preventDefault();
    }
  });

  // Show / hide password: <a data-toggle-password="#inputId">.
  document.addEventListener('click', function (e) {
    var link = e.target.closest('[data-toggle-password]');
    if (!link) return;
    e.preventDefault();
    var input = document.querySelector(link.getAttribute('data-toggle-password'));
    if (input) input.type = input.type === 'password' ? 'text' : 'password';
  });
})();
