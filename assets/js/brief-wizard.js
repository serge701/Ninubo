(function () {
  'use strict';

  const yearEl = document.getElementById('year');
  if (yearEl) {
    yearEl.textContent = new Date().getFullYear();
  }

  const form = document.getElementById('briefForm');
  if (!form) {
    return;
  }

  const base = window.NINUBO_BASE || '';
  const lang = window.NINUBO_BRIEF_LANG === 'en' ? 'en' : 'es';
  const i18n = {
    es: { saving: 'Guardando...', saved: 'Guardado ✓', saveError: 'No se pudo guardar. Revise su conexión.', sending: 'Enviando...', connError: 'Error de conexión.' },
    en: { saving: 'Saving...', saved: 'Saved ✓', saveError: 'Could not save. Check your connection.', sending: 'Sending...', connError: 'Connection error.' }
  }[lang];
  const steps = Array.from(form.querySelectorAll('.wizard-step'));
  const totalSteps = steps.length;
  const currentStepInput = document.getElementById('briefCurrentStep');
  const progressFill = document.getElementById('briefProgressFill');
  const progressLabel = document.getElementById('briefProgressLabel');
  const jumpSelect = document.getElementById('briefJump');
  const saveStatus = document.getElementById('briefSaveStatus');
  const alertBox = document.getElementById('briefAlert');

  let current = parseInt(currentStepInput.value, 10) || 1;
  if (current < 1 || current > totalSteps) {
    current = 1;
  }

  function showStep(n) {
    current = Math.max(1, Math.min(totalSteps, n));
    steps.forEach((step) => {
      step.classList.toggle('is-active', parseInt(step.dataset.step, 10) === current);
    });
    currentStepInput.value = String(current);
    if (jumpSelect) {
      jumpSelect.value = String(current);
    }
    if (progressFill) {
      progressFill.style.width = Math.round((current / totalSteps) * 100) + '%';
    }
    if (progressLabel) {
      const title = steps[current - 1].querySelector('h2');
      const prefix = lang === 'en' ? 'Step ' + current + ' of ' + totalSteps : 'Paso ' + current + ' de ' + totalSteps;
      progressLabel.textContent = prefix + (title ? ': ' + title.textContent : '');
    }
    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function stepIsValid(step) {
    const controls = step.querySelectorAll('input, textarea, select');
    for (const el of controls) {
      if (!el.checkValidity()) {
        el.reportValidity();
        return false;
      }
    }
    return true;
  }

  async function saveDraft(action) {
    if (saveStatus) {
      saveStatus.textContent = i18n.saving;
    }
    try {
      const formData = new FormData(form);
      formData.set('action', action);
      const response = await fetch(base + '/api/brief-guardar.php', {
        method: 'POST',
        body: formData
      });
      const result = await response.json();
      if (saveStatus) {
        saveStatus.textContent = result.success ? i18n.saved : '';
      }
      return result;
    } catch {
      if (saveStatus) {
        saveStatus.textContent = i18n.saveError;
      }
      return { success: false, message: i18n.connError };
    }
  }

  form.addEventListener('click', (e) => {
    const nextBtn = e.target.closest('.brief-next');
    const prevBtn = e.target.closest('.brief-prev');

    if (nextBtn) {
      e.preventDefault();
      const step = steps[current - 1];
      if (!stepIsValid(step)) {
        return;
      }
      saveDraft('draft');
      showStep(current + 1);
    } else if (prevBtn) {
      e.preventDefault();
      saveDraft('draft');
      showStep(current - 1);
    }
  });

  if (jumpSelect) {
    jumpSelect.addEventListener('change', () => {
      saveDraft('draft');
      showStep(parseInt(jumpSelect.value, 10) || 1);
    });
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const step = steps[current - 1];
    if (!stepIsValid(step)) {
      return;
    }
    const submitBtn = document.getElementById('briefSubmitBtn');
    const original = submitBtn ? submitBtn.textContent : '';
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = i18n.sending;
    }

    const result = await saveDraft('submit');

    if (alertBox) {
      alertBox.textContent = result.message || '';
      alertBox.classList.add('is-visible', result.success ? 'form-alert--success' : 'form-alert--error');
    }

    if (result.success) {
      window.location.reload();
      return;
    }

    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.textContent = original;
    }
  });

  showStep(current);

  // Autoguardado periódico por si el cliente se queda mucho tiempo en un paso.
  setInterval(() => saveDraft('draft'), 45000);
})();
