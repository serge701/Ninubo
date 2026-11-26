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

  // Forms that need confirmation, shown in a Tabler modal (never the browser's confirm box):
  //   <form data-confirm="Message" data-confirm-title="Title" data-confirm-button="Delete" data-confirm-variant="danger">
  // Variant: "danger" (default for delete / void / cancel / reset wording) or "primary".
  var confirmModal = null;
  var pendingForm = null;

  function buildConfirmModal() {
    var el = document.createElement('div');
    el.className = 'modal modal-blur fade';
    el.id = 'crm-confirm';
    el.tabIndex = -1;
    el.setAttribute('aria-hidden', 'true');
    el.innerHTML =
      '<div class="modal-dialog modal-sm modal-dialog-centered" role="document"><div class="modal-content">' +
      '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>' +
      '<div class="modal-status"></div>' +
      '<div class="modal-body text-center py-4">' +
      '<i class="ti crm-confirm-icon mb-2"></i>' +
      '<h3 class="crm-confirm-title"></h3>' +
      '<div class="text-secondary crm-confirm-message"></div>' +
      '</div>' +
      '<div class="modal-footer"><div class="w-100"><div class="row">' +
      '<div class="col"><button type="button" class="btn w-100" data-bs-dismiss="modal">Cancel</button></div>' +
      '<div class="col"><button type="button" class="btn w-100 crm-confirm-ok"></button></div>' +
      '</div></div></div>' +
      '</div></div>';
    document.body.appendChild(el);
    el.querySelector('.crm-confirm-ok').addEventListener('click', function () {
      var form = pendingForm;
      pendingForm = null;
      confirmModal.hide();
      if (form) {
        form.dataset.confirmed = '1';
        if (form.requestSubmit) form.requestSubmit(); else form.submit();
      }
    });
    el.addEventListener('shown.bs.modal', function () { el.querySelector('.crm-confirm-ok').focus(); });
    return new window.tabler.Modal(el);
  }

  document.addEventListener('submit', function (e) {
    var form = e.target.closest('form[data-confirm]');
    if (!form) return;
    if (form.dataset.confirmed === '1') { delete form.dataset.confirmed; return; } // already confirmed
    e.preventDefault();

    // Fallback if Tabler's JS isn't available for some reason.
    if (!window.tabler || !window.tabler.Modal) {
      if (window.confirm(form.getAttribute('data-confirm'))) { form.dataset.confirmed = '1'; form.submit(); }
      return;
    }
    confirmModal = confirmModal || buildConfirmModal();
    var el = document.getElementById('crm-confirm');
    var message = form.getAttribute('data-confirm');
    var button = form.getAttribute('data-confirm-button') || 'Confirm';
    var variant = form.getAttribute('data-confirm-variant') ||
      (/delete|void|cancel|reset|remove/i.test(button + ' ' + message) ? 'danger' : 'primary');
    var icon = variant === 'danger' ? 'ti-alert-triangle text-danger' : 'ti-circle-check text-primary';

    el.querySelector('.modal-status').className = 'modal-status bg-' + variant;
    el.querySelector('.crm-confirm-icon').className = 'ti ' + icon + ' crm-confirm-icon mb-2';
    el.querySelector('.crm-confirm-title').textContent = form.getAttribute('data-confirm-title') || 'Are you sure?';
    el.querySelector('.crm-confirm-message').textContent = message;
    var ok = el.querySelector('.crm-confirm-ok');
    ok.className = 'btn btn-' + variant + ' w-100 crm-confirm-ok';
    ok.textContent = button;

    pendingForm = form;
    confirmModal.show();
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

(function () {
  'use strict';

  // Activity composer: the due-date field only applies to follow-ups.
  var composer = document.querySelector('.crm-composer');
  if (composer) {
    var due = composer.querySelector('[data-due-field]');
    var sync = function () {
      var type = composer.querySelector('input[name="type"]:checked');
      if (due) due.classList.toggle('is-visible', !!type && type.value === 'task');
    };
    composer.addEventListener('change', sync);
    sync();
  }

  // "New → Follow-up / Note" menu items pre-select the type and focus the composer.
  document.addEventListener('click', function (e) {
    var link = e.target.closest('[data-focus-composer]');
    if (!link || !composer) return;
    e.preventDefault();
    var radio = composer.querySelector('input[name="type"][value="' + link.getAttribute('data-focus-composer') + '"]');
    if (radio) { radio.checked = true; radio.dispatchEvent(new Event('change', { bubbles: true })); }
    composer.scrollIntoView({ behavior: 'smooth', block: 'center' });
    var body = composer.querySelector('textarea');
    if (body) setTimeout(function () { body.focus(); }, 300);
  });
})();

(function () {
  'use strict';

  // US phone mask for every <input type="tel"> in the CRM: "(956) 555-0123".
  // The server normalizes and validates again (us_phone() in app/helpers.php).
  function formatPhone(digits) {
    if (digits.length === 0) return '';
    if (digits.length < 4) return '(' + digits;
    if (digits.length < 7) return '(' + digits.slice(0, 3) + ') ' + digits.slice(3);
    return '(' + digits.slice(0, 3) + ') ' + digits.slice(3, 6) + '-' + digits.slice(6, 10);
  }

  function digitsOf(value) {
    var d = String(value).replace(/\D/g, '');
    // Pasted "+1 956 555 0123" / "1-956-..." -> drop the country code.
    if (d.length === 11 && d.charAt(0) === '1') d = d.slice(1);
    return d.slice(0, 10);
  }

  function applyMask(input) {
    var caret = input.selectionStart == null ? input.value.length : input.selectionStart;
    // How many digits are before the caret, so it stays in the same place after re-formatting.
    var digitsBefore = input.value.slice(0, caret).replace(/\D/g, '').length;
    var formatted = formatPhone(digitsOf(input.value));
    if (formatted === input.value) return;
    input.value = formatted;

    var pos = 0;
    var seen = 0;
    while (pos < formatted.length && seen < digitsBefore) {
      if (/\d/.test(formatted.charAt(pos))) seen++;
      pos++;
    }
    if (document.activeElement === input) input.setSelectionRange(pos, pos);
  }

  document.addEventListener('input', function (e) {
    if (e.target.matches && e.target.matches('input[type="tel"]')) applyMask(e.target);
  });

  // Format values already in the form (e.g. older records, or a re-shown form after a validation error).
  document.querySelectorAll('input[type="tel"]').forEach(function (input) {
    if (input.value) applyMask(input);
  });
})();
