/*
 * Minimal signature pad (mouse, pen and touch). Markup:
 *   <div data-signature-pad>
 *     <canvas></canvas>
 *     <button type="button" data-signature-clear>Clear</button>
 *     <input type="hidden" name="signature">
 *   </div>
 * The hidden input gets a PNG data URI (empty while nothing is drawn).
 */
(function () {
  'use strict';

  function init(root) {
    var canvas = root.querySelector('canvas');
    var input = root.querySelector('input[type="hidden"]');
    var clearBtn = root.querySelector('[data-signature-clear]');
    var ctx = canvas.getContext('2d');
    var drawing = false;
    var hasInk = false;
    var last = null;

    function resize() {
      var ratio = Math.max(window.devicePixelRatio || 1, 1);
      var rect = canvas.getBoundingClientRect();
      var saved = hasInk ? canvas.toDataURL('image/png') : null;
      canvas.width = Math.round(rect.width * ratio);
      canvas.height = Math.round(rect.height * ratio);
      ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
      ctx.lineWidth = 2.4;
      ctx.lineCap = 'round';
      ctx.lineJoin = 'round';
      ctx.strokeStyle = '#0f172a';
      if (saved) {
        var img = new Image();
        img.onload = function () { ctx.drawImage(img, 0, 0, rect.width, rect.height); };
        img.src = saved;
      }
    }

    function point(e) {
      var rect = canvas.getBoundingClientRect();
      return { x: e.clientX - rect.left, y: e.clientY - rect.top };
    }

    function update() {
      input.value = hasInk ? canvas.toDataURL('image/png') : '';
      root.classList.toggle('has-ink', hasInk);
      input.dispatchEvent(new Event('change', { bubbles: true }));
    }

    canvas.addEventListener('pointerdown', function (e) {
      e.preventDefault();
      canvas.setPointerCapture(e.pointerId);
      drawing = true;
      last = point(e);
      ctx.beginPath();
      ctx.arc(last.x, last.y, ctx.lineWidth / 2, 0, Math.PI * 2);
      ctx.fillStyle = ctx.strokeStyle;
      ctx.fill();
      hasInk = true;
    });
    canvas.addEventListener('pointermove', function (e) {
      if (!drawing) return;
      e.preventDefault();
      var p = point(e);
      ctx.beginPath();
      ctx.moveTo(last.x, last.y);
      ctx.lineTo(p.x, p.y);
      ctx.stroke();
      last = p;
    });
    ['pointerup', 'pointercancel', 'pointerleave'].forEach(function (type) {
      canvas.addEventListener(type, function () {
        if (!drawing) return;
        drawing = false;
        update();
      });
    });

    if (clearBtn) {
      clearBtn.addEventListener('click', function () {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        hasInk = false;
        update();
      });
    }

    var resizeTimer;
    window.addEventListener('resize', function () {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(resize, 150);
    });
    // Canvases inside modals have no size until shown.
    document.addEventListener('shown.bs.modal', function (e) { if (e.target.contains(canvas)) resize(); });
    resize();
  }

  document.querySelectorAll('[data-signature-pad]').forEach(init);
})();
