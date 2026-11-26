/*
 * Invoice editor: website-package and care-plan quick pickers, editable line items, live totals.
 * The server recomputes everything on save (Invoice::computeTotals) — this is only the preview.
 */
(function () {
  'use strict';

  var cfg = window.CRM_INVOICE;
  var form = document.getElementById('invoice-form');
  if (!cfg || !form) return;

  var tbody = document.getElementById('invoice-lines');
  var tpl = document.getElementById('line-template');
  var termsSelect = form.querySelector('[name="payment_terms"]');
  var termsTouched = cfg.termsChosen;
  var byId = {};
  cfg.catalog.forEach(function (p) { byId[p.id] = p; });

  var BILLING = { one_time: 'One-time', monthly: 'Monthly', yearly: 'Yearly' };
  var TAX = { data_processing: 'Taxed on ' + cfg.taxablePct + '%', taxable: 'Taxed 100%', exempt: 'Not taxable' };

  function money(n) {
    return '$' + (Math.round(n * 100) / 100).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }
  function field(row, name) { return row.querySelector('[data-f="' + name + '"]'); }

  /** Which quick picker a catalog item belongs to. */
  function roleOf(pkg) {
    if (!pkg) return 'other';
    if (pkg.category === 'website' && pkg.billing === 'one_time') return 'website';
    if (pkg.billing === 'monthly') return 'plan';
    return 'other';
  }

  function addLine(data) {
    var row = tpl.content.firstElementChild.cloneNode(true);
    var pkg = data.packageId ? byId[data.packageId] : null;
    row.dataset.role = roleOf(pkg);
    field(row, 'package_id').value = data.packageId || '';
    field(row, 'subscription_id').value = data.subscriptionId || '';
    field(row, 'billing').value = data.billing || 'one_time';
    field(row, 'tax_type').value = data.taxType || 'data_processing';
    field(row, 'description').value = data.description || '';
    field(row, 'details').value = data.details || '';
    field(row, 'quantity').value = data.quantity || 1;
    field(row, 'unit_price').value = (data.unitPrice || 0).toFixed(2);

    row.querySelector('[data-billing-badge]').textContent = BILLING[field(row, 'billing').value];
    var taxSelect = row.querySelector('[data-tax-select]');
    var taxLabel = row.querySelector('[data-tax-label]');
    if (pkg || data.subscriptionId) {
      taxLabel.textContent = TAX[field(row, 'tax_type').value]; // catalog lines keep the catalog's tax treatment
    } else {
      taxSelect.classList.remove('d-none'); // custom lines: choose it
      taxSelect.value = field(row, 'tax_type').value;
      taxSelect.addEventListener('change', function () { field(row, 'tax_type').value = taxSelect.value; recalc(); });
    }
    if (pkg && pkg.priceLabel && /From|–/.test(pkg.priceLabel)) {
      row.querySelector('[data-range-hint]').textContent = 'Catalog: ' + pkg.priceLabel + ' — set the final price';
    }
    // Auto-size the details box.
    var details = field(row, 'details');
    var grow = function () { details.style.height = 'auto'; details.style.height = (details.scrollHeight + 2) + 'px'; };
    details.addEventListener('input', grow);

    row.querySelector('[data-remove]').addEventListener('click', function () {
      row.remove();
      syncPickers();
      recalc();
    });
    tbody.appendChild(row);
    setTimeout(grow, 0);
    return row;
  }

  function lineFromCatalog(pkg) {
    return {
      packageId: pkg.id,
      description: pkg.name + (pkg.billing === 'monthly' ? ' (first month)' : (pkg.billing === 'yearly' ? ' (first year)' : '')),
      details: pkg.description,
      quantity: 1,
      unitPrice: pkg.price,
      billing: pkg.billing,
      taxType: pkg.taxType
    };
  }

  /** Picking a website package / plan replaces the current line of that kind. */
  function pick(role, id) {
    Array.prototype.forEach.call(tbody.querySelectorAll('.crm-line'), function (row) {
      if (row.dataset.role === role) row.remove();
    });
    if (id && byId[id]) {
      var row = addLine(lineFromCatalog(byId[id]));
      // Keep the website package first and the plan right after it.
      if (role === 'website') tbody.insertBefore(row, tbody.firstChild);
      if (role === 'plan') {
        var web = tbody.querySelector('.crm-line[data-role="website"]');
        tbody.insertBefore(row, web ? web.nextSibling : tbody.firstChild);
      }
    }
    recalc();
  }

  function syncPickers() {
    ['website', 'plan'].forEach(function (role) {
      var row = tbody.querySelector('.crm-line[data-role="' + role + '"]');
      var value = row ? field(row, 'package_id').value : '';
      var radio = form.querySelector('[data-picker="' + role + '"] input[value="' + value + '"]');
      if (radio) radio.checked = true;
    });
  }

  function recalc() {
    var subtotal = 0, taxable = 0, hasOneTime = false;
    Array.prototype.forEach.call(tbody.querySelectorAll('.crm-line'), function (row, i) {
      // Name the inputs in order so the server receives items[0][...], items[1][...]
      Array.prototype.forEach.call(row.querySelectorAll('[data-f]'), function (input) {
        input.name = 'items[' + i + '][' + input.dataset.f + ']';
      });
      var qty = parseFloat(field(row, 'quantity').value) || 0;
      var price = parseFloat(field(row, 'unit_price').value) || 0;
      var line = Math.round(qty * price * 100) / 100;
      row.querySelector('[data-amount]').textContent = money(line);
      subtotal += line;
      if (field(row, 'billing').value === 'one_time') hasOneTime = true;
      if (!cfg.taxExempt) {
        var t = field(row, 'tax_type').value;
        taxable += t === 'data_processing' ? line * cfg.taxablePct / 100 : (t === 'taxable' ? line : 0);
      }
    });
    taxable = Math.round(taxable * 100) / 100;
    var tax = Math.round(taxable * cfg.taxRate) / 100;
    var total = Math.round((subtotal + tax) * 100) / 100;

    document.getElementById('t-subtotal').textContent = money(subtotal);
    document.getElementById('t-taxable').textContent = money(taxable);
    document.getElementById('t-rate').textContent = cfg.taxExempt ? '0 — exempt' : cfg.taxRate;
    document.getElementById('t-tax').textContent = money(tax);
    document.getElementById('t-total').textContent = money(total);

    if (!termsTouched && termsSelect) {
      termsSelect.value = !hasOneTime ? 'full' : (total > 2000 ? '40_30_30' : '50_50');
    }
    renderSchedule(total);
    var emptyMsg = document.getElementById('lines-empty-msg');
    if (emptyMsg && tbody.querySelector('.crm-line')) emptyMsg.remove();
  }

  function renderSchedule(total) {
    var box = document.getElementById('t-schedule');
    var terms = cfg.terms[termsSelect ? termsSelect.value : 'full'];
    if (!terms || total <= 0) { box.innerHTML = ''; return; }
    var allocated = 0;
    var html = '<div class="text-secondary mb-1">' + terms.label + '</div>';
    terms.steps.forEach(function (s, i) {
      var amount = i === terms.steps.length - 1 ? Math.round((total - allocated) * 100) / 100 : Math.round(total * s.pct) / 100;
      allocated += amount;
      html += '<div class="d-flex justify-content-between"><span>' + s.label + ' (' + s.pct + '%)</span><span>' + money(amount) + '</span></div>';
    });
    box.innerHTML = html;
  }

  // ---- wire up
  form.addEventListener('change', function (e) {
    var picker = e.target.closest('[data-picker]');
    if (picker) { pick(picker.dataset.picker, parseInt(e.target.value, 10) || null); return; }
    if (e.target === termsSelect) { termsTouched = true; recalc(); }
  });
  form.addEventListener('input', function (e) {
    if (e.target.matches('[data-f="quantity"], [data-f="unit_price"]')) recalc();
  });

  document.getElementById('add-catalog').addEventListener('change', function (e) {
    var pkg = byId[parseInt(e.target.value, 10)];
    if (!pkg) return;
    var role = roleOf(pkg);
    if (role === 'other') { addLine(lineFromCatalog(pkg)); recalc(); }
    else { pick(role, pkg.id); syncPickers(); }
    e.target.value = '';
  });
  document.getElementById('add-custom').addEventListener('click', function () {
    var row = addLine({ description: '', quantity: 1, unitPrice: 0, billing: 'one_time', taxType: 'data_processing' });
    recalc();
    field(row, 'description').focus();
  });

  form.addEventListener('submit', function (e) {
    if (!tbody.querySelector('.crm-line')) {
      e.preventDefault();
      var msg = document.getElementById('lines-empty-msg');
      if (!msg) {
        msg = document.createElement('div');
        msg.id = 'lines-empty-msg';
        msg.className = 'alert alert-danger m-3';
        msg.textContent = 'Add at least one line to the invoice: pick a website package, a plan, a catalog item or a custom line.';
        tbody.closest('.card').insertBefore(msg, tbody.closest('.table-responsive'));
      }
      msg.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  });

  cfg.items.forEach(addLine);
  syncPickers();
  recalc();
})();
