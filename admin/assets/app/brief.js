// Public website brief (/brief.php): steps, progress, autosave (only after real changes) and submit.
(function () {
  'use strict';

  // Light / dark toggle, remembered per browser (separate from the CRM's own setting).
  document.addEventListener('click', function (e) {
    var toggle = e.target.closest('[data-theme-toggle]');
    if (!toggle) return;
    e.preventDefault();
    var next = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-bs-theme', next);
    try { localStorage.setItem('brief-theme', next); } catch (err) {}
  });

  var form = document.getElementById('briefForm');
  if (!form) return;

  var cfg = window.BRIEF || {};
  var T = cfg.text || {};
  var steps = Array.prototype.slice.call(form.querySelectorAll('.wizard-step'));
  var total = steps.length;
  var stepInput = document.getElementById('briefCurrentStep');
  var progressFill = document.getElementById('briefProgressFill');
  var progressLabel = document.getElementById('briefProgressLabel');
  var saveStatus = document.getElementById('briefSaveStatus');
  var jump = document.getElementById('briefJump');
  var navItems = Array.prototype.slice.call(document.querySelectorAll('#briefStepList [data-go]'));
  var alertBox = document.getElementById('briefAlert');
  var current = parseInt(stepInput.value, 10) || 1;
  var dirty = false;
  var saving = null;

  // ---- Answer counting (sidebar badges + final summary) ----
  function fieldAnswered(wrap) {
    var controls = wrap.querySelectorAll('input, textarea, select');
    for (var i = 0; i < controls.length; i++) {
      var c = controls[i];
      if (c.type === 'checkbox' || c.type === 'radio') {
        if (c.checked) return true;
      } else if (c.value.trim() !== '') {
        return true;
      }
    }
    return false;
  }

  function stepStats(step) {
    var fields = step.querySelectorAll('.brief-field');
    var done = 0;
    var missing = [];
    fields.forEach(function (f) {
      var ok = fieldAnswered(f);
      if (ok) done++;
      if (!ok && f.hasAttribute('data-required')) {
        var label = f.querySelector('.form-label');
        missing.push(label ? label.textContent.trim() : '');
      }
    });
    return { done: done, total: fields.length, missing: missing };
  }

  function refreshCounts() {
    steps.forEach(function (step, i) {
      if (step.hasAttribute('data-summary')) return;
      var st = stepStats(step);
      var item = navItems[i];
      if (!item) return;
      var badge = item.querySelector('.brief-step-count');
      if (badge) badge.textContent = st.done + '/' + st.total;
      item.classList.toggle('brief-step-done', st.total > 0 && st.done === st.total);
    });
  }

  function renderSummary() {
    var box = document.getElementById('briefSummary');
    if (!box) return;
    var rows = '';
    var missingAll = [];
    steps.forEach(function (step, i) {
      if (step.hasAttribute('data-summary')) return;
      var st = stepStats(step);
      missingAll = missingAll.concat(st.missing);
      var state = st.done === st.total ? ['bg-green-lt', T.complete] : (st.done === 0 ? ['bg-secondary-lt', T.empty] : ['bg-yellow-lt', T.partial]);
      var title = step.querySelector('.card-title').textContent;
      rows += '<div class="list-group-item d-flex align-items-center gap-2">' +
        '<span class="flex-fill">' + (i + 1) + '. ' + escapeHtml(title) + '</span>' +
        '<span class="small text-secondary d-none d-sm-inline">' + st.done + '/' + st.total + ' ' + T.answered + '</span>' +
        '<span class="badge ' + state[0] + '">' + state[1] + '</span>' +
        '<button type="button" class="btn btn-sm" data-go="' + (i + 1) + '">' + T.go + '</button></div>';
    });
    box.innerHTML = '<div class="fw-medium mb-2">' + T.summaryTitle + '</div><div class="list-group">' + rows + '</div>' +
      (missingAll.length ? '<div class="alert alert-warning mt-3 mb-0">' + T.requiredMissing + ' ' + escapeHtml(missingAll.join(', ')) + '</div>' : '');
  }

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  // ---- Steps ----
  function showStep(n, scroll) {
    current = Math.max(1, Math.min(total, n));
    steps.forEach(function (step) {
      step.hidden = parseInt(step.dataset.step, 10) !== current;
    });
    stepInput.value = String(current);
    if (jump) jump.value = String(current);
    navItems.forEach(function (item) {
      item.classList.toggle('active', parseInt(item.dataset.go, 10) === current);
    });
    if (progressFill) progressFill.style.width = Math.round(current / total * 100) + '%';
    if (progressLabel) progressLabel.textContent = T.step + ' ' + current + ' ' + T.of + ' ' + total;
    if (steps[current - 1].hasAttribute('data-summary')) renderSummary();
    refreshCounts();
    if (scroll) steps[current - 1].scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function stepIsValid(step) {
    var controls = step.querySelectorAll('input, textarea, select');
    for (var i = 0; i < controls.length; i++) {
      if (!controls[i].checkValidity()) {
        controls[i].reportValidity();
        return false;
      }
    }
    var groups = step.querySelectorAll('.brief-field[data-required]');
    for (var j = 0; j < groups.length; j++) {
      var boxes = groups[j].querySelectorAll('input[type=checkbox], input[type=radio]');
      if (boxes.length && !fieldAnswered(groups[j])) {
        boxes[0].setCustomValidity(T.pickOne);
        boxes[0].reportValidity();
        boxes[0].addEventListener('change', function clear() { this.setCustomValidity(''); }, { once: true });
        return false;
      }
    }
    return true;
  }

  // ---- Saving (only when the client actually changed something) ----
  function setStatus(text) {
    if (saveStatus) saveStatus.textContent = text;
  }

  function save(action) {
    if (action === 'draft' && !dirty) return Promise.resolve({ success: true });
    dirty = false;
    setStatus(T.saving);
    var data = new FormData(form);
    data.set('action', action);
    saving = fetch(cfg.saveUrl, { method: 'POST', body: data, credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (result) {
        setStatus(result.success ? T.saved + ' ✓' : '');
        if (!result.success && action === 'draft') dirty = true;
        return result;
      })
      .catch(function () {
        dirty = true;
        setStatus(T.saveError);
        return { success: false, message: T.connError };
      });
    return saving;
  }

  form.addEventListener('input', function (e) {
    if (e.target.name === 'hp_url') return;
    dirty = true;
    // Typing an "Other" answer for a single-choice question clears the chosen option.
    if (e.target.hasAttribute('data-radio-other') && e.target.value.trim() !== '') {
      e.target.closest('.brief-field').querySelectorAll('input[type=radio]').forEach(function (r) { r.checked = false; });
    }
    refreshCounts();
  });
  form.addEventListener('change', function (e) {
    dirty = true;
    if (e.target.type === 'radio') {
      var other = e.target.closest('.brief-field').querySelector('[data-radio-other]');
      if (other) other.value = '';
    }
    refreshCounts();
  });

  // ---- Navigation ----
  document.addEventListener('click', function (e) {
    var go = e.target.closest('[data-go]');
    if (go) {
      e.preventDefault();
      save('draft');
      showStep(parseInt(go.dataset.go, 10) || 1, true);
      return;
    }
    if (e.target.closest('.brief-next')) {
      e.preventDefault();
      if (!stepIsValid(steps[current - 1])) return;
      save('draft');
      showStep(current + 1, true);
      return;
    }
    if (e.target.closest('.brief-prev')) {
      e.preventDefault();
      save('draft');
      showStep(current - 1, true);
      return;
    }
    // Switching language: save first so nothing typed is lost.
    var langLink = e.target.closest('[data-lang-link]');
    if (langLink && dirty) {
      e.preventDefault();
      save('draft').then(function () { window.location.href = langLink.href; });
    }
  });

  if (jump) {
    jump.addEventListener('change', function () {
      save('draft');
      showStep(parseInt(jump.value, 10) || 1, true);
    });
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    // Every step's required fields must be filled; jump to the first one that isn't.
    for (var i = 0; i < steps.length; i++) {
      if (stepStats(steps[i]).missing.length) {
        showStep(i + 1, true);
        stepIsValid(steps[i]);
        return;
      }
    }
    var btn = document.getElementById('briefSubmitBtn');
    var original = btn.innerHTML;
    btn.disabled = true;
    btn.textContent = T.sending;
    save('submit').then(function (result) {
      if (result.success) {
        window.location.reload();
        return;
      }
      alertBox.className = 'alert alert-danger';
      alertBox.textContent = result.message || T.connError;
      btn.disabled = false;
      btn.innerHTML = original;
    });
  });

  // Autosave every 30 s, and on leaving the page, but only if something changed.
  setInterval(function () { save('draft'); }, 30000);
  window.addEventListener('pagehide', function () {
    if (!dirty || !navigator.sendBeacon) return;
    var data = new FormData(form);
    data.set('action', 'draft');
    navigator.sendBeacon(cfg.saveUrl, data);
    dirty = false;
  });

  showStep(current, false);
})();
