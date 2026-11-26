/*
 * Hover tooltips for the dashboard charts. Any element with
 *   data-tip-title="March 2026"  data-tip='[["var(--viz-1)","Collected","$1,200.00"], ...]'
 * shows a tooltip near the pointer (rows: color swatch, label, value). Text is set with textContent (no HTML).
 */
(function () {
  'use strict';

  var tip = document.createElement('div');
  tip.className = 'crm-tip';
  tip.setAttribute('role', 'tooltip');
  tip.hidden = true;
  document.body.appendChild(tip);
  var current = null;

  function render(el) {
    tip.textContent = '';
    var title = document.createElement('div');
    title.className = 'crm-tip-title';
    title.textContent = el.getAttribute('data-tip-title') || '';
    tip.appendChild(title);
    var rows = [];
    try { rows = JSON.parse(el.getAttribute('data-tip') || '[]'); } catch (e) {}
    rows.forEach(function (r) {
      var row = document.createElement('div');
      row.className = 'crm-tip-row';
      var sw = document.createElement('span');
      sw.className = 'crm-tip-swatch';
      sw.style.background = r[0];
      var label = document.createElement('span');
      label.className = 'crm-tip-label';
      label.textContent = r[1];
      var value = document.createElement('span');
      value.className = 'crm-tip-value';
      value.textContent = r[2];
      row.appendChild(sw); row.appendChild(label); row.appendChild(value);
      tip.appendChild(row);
    });
  }

  function place(e) {
    var pad = 14;
    var rect = tip.getBoundingClientRect();
    var x = e.clientX + pad;
    var y = e.clientY + pad;
    if (x + rect.width > window.innerWidth - 8) x = e.clientX - rect.width - pad;
    if (y + rect.height > window.innerHeight - 8) y = e.clientY - rect.height - pad;
    tip.style.transform = 'translate(' + Math.round(x) + 'px,' + Math.round(y) + 'px)';
  }

  document.addEventListener('pointermove', function (e) {
    var el = e.target.closest ? e.target.closest('[data-tip]') : null;
    if (el !== current) {
      if (current) current.classList.remove('is-hover');
      current = el;
      if (el) { el.classList.add('is-hover'); render(el); tip.hidden = false; } else { tip.hidden = true; }
    }
    if (el) place(e);
  });
  // Pointer left the window.
  document.documentElement.addEventListener('mouseleave', function () {
    tip.hidden = true;
    if (current) current.classList.remove('is-hover');
    current = null;
  });
  window.addEventListener('scroll', function () { tip.hidden = true; if (current) current.classList.remove('is-hover'); current = null; }, { passive: true });
})();
