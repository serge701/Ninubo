(function () {
  window.ninuboTrack = function (eventName, parameters) {
    const data = parameters || {};
    if (typeof window.gtag === 'function') {
      window.gtag('event', eventName, data);
      return;
    }
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({ event: eventName, ...data });
  };

  const GA_CONSENT_KEY = 'ninubo_cookie_consent';

  function ninuboLoadAnalytics() {
    if (!window.NINUBO_GA_ID || window.ninuboAnalyticsLoaded) return;
    window.ninuboAnalyticsLoaded = true;
    window.dataLayer = window.dataLayer || [];
    window.gtag = window.gtag || function () { window.dataLayer.push(arguments); };
    window.gtag('js', new Date());
    window.gtag('config', window.NINUBO_GA_ID);
    const script = document.createElement('script');
    script.async = true;
    script.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(window.NINUBO_GA_ID);
    document.head.appendChild(script);
  }

  (function initCookieConsent() {
    const banner = document.getElementById('cookieBanner');
    if (!window.NINUBO_GA_ID || !banner) return;

    let consent = null;
    try { consent = localStorage.getItem(GA_CONSENT_KEY); } catch (e) { /* private mode, etc. */ }

    if (consent === 'accepted') {
      ninuboLoadAnalytics();
      return;
    }
    if (consent === 'rejected') return;

    banner.hidden = false;

    const acceptBtn = document.getElementById('cookieAccept');
    const rejectBtn = document.getElementById('cookieReject');

    if (acceptBtn) {
      acceptBtn.addEventListener('click', () => {
        try { localStorage.setItem(GA_CONSENT_KEY, 'accepted'); } catch (e) {}
        banner.hidden = true;
        ninuboLoadAnalytics();
      });
    }
    if (rejectBtn) {
      rejectBtn.addEventListener('click', () => {
        try { localStorage.setItem(GA_CONSENT_KEY, 'rejected'); } catch (e) {}
        banner.hidden = true;
      });
    }
  })();

  const navToggle = document.querySelector('.nav-toggle');
  const navLinks = document.querySelector('.nav-links');
  const yearEl = document.getElementById('year');
  const form = document.querySelector('.contact-form');

  if (yearEl) {
    yearEl.textContent = new Date().getFullYear();
  }

  if (navToggle && navLinks) {
    navToggle.addEventListener('click', () => {
      const open = navToggle.getAttribute('aria-expanded') === 'true';
      navToggle.setAttribute('aria-expanded', String(!open));
      navLinks.classList.toggle('is-open');
    });

    navLinks.querySelectorAll('a').forEach((link) => {
      link.addEventListener('click', () => {
        navToggle.setAttribute('aria-expanded', 'false');
        navLinks.classList.remove('is-open');
      });
    });
  }

  document.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => {
      const href = link.getAttribute('href') || '';
      if (href.startsWith('tel:')) window.ninuboTrack('contact', { method: 'phone' });
      if (href.startsWith('mailto:')) window.ninuboTrack('contact', { method: 'email' });
      if (href.includes('wa.me')) window.ninuboTrack('contact', { method: 'whatsapp' });
      if (link.closest('.language-switcher')) window.ninuboTrack('language_select', { language: link.lang || 'unknown' });
      if (link.classList.contains('btn') && href.includes('contacto.php')) window.ninuboTrack('cta_contact_click', { location: window.location.pathname });
    });
  });

  const networkCanvas = document.querySelector('.hero-network');
  if (networkCanvas) {
    const ctx = networkCanvas.getContext('2d');
    const colors = ['56, 189, 248', '99, 102, 241', '34, 211, 238']; // primary, indigo, accent (RGB)
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let nodes = [];
    let width = 0;
    let height = 0;
    let dpr = Math.min(window.devicePixelRatio || 1, 2);
    let rafId = null;

    function buildNodes() {
      const area = width * height;
      const count = Math.max(24, Math.min(70, Math.round(area / 16000)));
      nodes = Array.from({ length: count }, () => ({
        x: Math.random() * width,
        y: Math.random() * height,
        vx: (Math.random() - 0.5) * 0.25,
        vy: (Math.random() - 0.5) * 0.25,
        r: Math.random() * 1.6 + 1.2,
        color: colors[Math.floor(Math.random() * colors.length)]
      }));
    }

    function resize() {
      const hero = networkCanvas.closest('.page-hero');
      width = hero ? hero.clientWidth : window.innerWidth;
      height = hero ? hero.clientHeight : 400;
      networkCanvas.width = width * dpr;
      networkCanvas.height = height * dpr;
      networkCanvas.style.width = width + 'px';
      networkCanvas.style.height = height + 'px';
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
      buildNodes();
    }

    function drawFrame(animate) {
      ctx.clearRect(0, 0, width, height);
      const maxDist = Math.min(180, width / 6);

      for (let i = 0; i < nodes.length; i++) {
        const a = nodes[i];
        if (animate) {
          a.x += a.vx;
          a.y += a.vy;
          if (a.x < 0 || a.x > width) a.vx *= -1;
          if (a.y < 0 || a.y > height) a.vy *= -1;
        }
        for (let j = i + 1; j < nodes.length; j++) {
          const b = nodes[j];
          const dx = a.x - b.x;
          const dy = a.y - b.y;
          const dist = Math.sqrt(dx * dx + dy * dy);
          if (dist < maxDist) {
            ctx.strokeStyle = `rgba(${a.color}, ${0.18 * (1 - dist / maxDist)})`;
            ctx.lineWidth = 1;
            ctx.beginPath();
            ctx.moveTo(a.x, a.y);
            ctx.lineTo(b.x, b.y);
            ctx.stroke();
          }
        }
      }
      for (const n of nodes) {
        ctx.fillStyle = `rgba(${n.color}, 0.85)`;
        ctx.beginPath();
        ctx.arc(n.x, n.y, n.r, 0, Math.PI * 2);
        ctx.fill();
      }
    }

    function loop() {
      drawFrame(true);
      rafId = requestAnimationFrame(loop);
    }

    function start() {
      if (rafId || reduceMotion) return;
      rafId = requestAnimationFrame(loop);
    }

    function stop() {
      if (rafId) {
        cancelAnimationFrame(rafId);
        rafId = null;
      }
    }

    resize();
    drawFrame(false);
    if (!reduceMotion) start();

    let resizeTimer;
    window.addEventListener('resize', () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(() => {
        resize();
        drawFrame(false);
      }, 150);
    });

    document.addEventListener('visibilitychange', () => {
      if (document.hidden) stop();
      else start();
    });
  }

  if (form) {
    const alert = form.querySelector('.form-alert');
    const submitBtn = form.querySelector('button[type="submit"]');

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (!submitBtn) return;

      const original = submitBtn.textContent;
      submitBtn.disabled = true;
      submitBtn.textContent = 'Enviando…';

      if (alert) {
        alert.classList.remove('is-visible', 'form-alert--success', 'form-alert--error');
      }

      try {
        const base = window.NINUBO_BASE || '';
        const response = await fetch(base + '/api/contacto.php', {
          method: 'POST',
          body: new FormData(form)
        });
        const data = await response.json();

        if (alert) {
          alert.textContent = data.message;
          alert.classList.add('is-visible', data.success ? 'form-alert--success' : 'form-alert--error');
        }

        if (data.success) {
          const formData = new FormData(form);
          window.ninuboTrack('generate_lead', {
            form_id: 'contact_form',
            service: formData.get('asunto') || 'general',
            language: document.documentElement.lang
          });
          form.reset();
        }
      } catch {
        if (alert) {
          alert.textContent = 'Error de conexión. Intenta de nuevo o escríbenos a contacto@ninubo.com.';
          alert.classList.add('is-visible', 'form-alert--error');
        }
      } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = original;
      }
    });
  }
})();
