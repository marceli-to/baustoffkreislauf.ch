// Editor guide in the CP: search, current section, zoom, mobile navigation.
(() => {
  const nav = document.getElementById('nav');
  const navLinks = [...nav.querySelectorAll('.nav__list a')];
  const linkFor = (id) => navLinks.find((a) => a.hash === '#' + id);

  // Search: filters the navigation and the finder tiles by the text of each section
  const input = document.querySelector('[data-search]');
  const empty = document.querySelector('[data-search-empty]');
  const sections = [...document.querySelectorAll('[data-spy]')].map((section) => ({
    id: section.id,
    text: (section.dataset.searchText || section.textContent).toLowerCase(),
  }));

  input.addEventListener('input', () => {
    const words = input.value.toLowerCase().split(/\s+/).filter(Boolean);
    const matches = new Set(
      sections.filter((s) => words.every((w) => s.text.includes(w))).map((s) => s.id),
    );

    navLinks.forEach((a) => (a.parentElement.hidden = words.length > 0 && !matches.has(a.hash.slice(1))));
    document.querySelectorAll('[data-search-item]').forEach((tile) => {
      tile.parentElement.hidden = words.length > 0 && !matches.has(tile.dataset.searchItem);
    });
    empty.hidden = words.length === 0 || matches.size > 0;
  });

  input.addEventListener('keydown', (event) => {
    if (event.key !== 'Enter') return;
    const first = navLinks.find((a) => !a.parentElement.hidden);
    if (first) {
      closeNav();
      location.hash = first.hash;
    }
  });

  // Highlight the section in view
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      navLinks.forEach((a) => a.removeAttribute('aria-current'));
      const link = linkFor(entry.target.id);
      if (link) {
        link.setAttribute('aria-current', 'true');
        link.scrollIntoView({ block: 'nearest' });
      }
    });
  }, { rootMargin: '-20% 0px -70% 0px' });
  document.querySelectorAll('[data-spy]').forEach((section) => observer.observe(section));

  // Zoom: the whole screenshot, scaled to fit the window but never above its
  // real size. Sized here so the dialog can wrap the image exactly.
  const dialog = document.querySelector('.zoom');
  const zoomed = dialog.querySelector('img');
  let source = null;
  const fit = () => {
    const width = +source.getAttribute('width');
    const height = +source.getAttribute('height');
    const frame = 2 * 16 + 2 * 11; // dialog margin, padding and border
    const scale = Math.min(1, (innerWidth - frame) / width, (innerHeight - frame) / height);
    zoomed.style.width = Math.floor(width * scale) + 'px';
    zoomed.style.height = Math.floor(height * scale) + 'px';
  };
  const open = (img) => {
    source = img;
    zoomed.src = img.currentSrc || img.src;
    fit();
    dialog.showModal();
  };
  addEventListener('resize', () => dialog.open && fit());
  document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-zoom], [data-zoom-target]');
    if (trigger) open(trigger.closest('.shot').querySelector('img'));
  });
  dialog.addEventListener('click', (event) => {
    if (event.target === dialog) dialog.close();
  });

  // Small screens: navigation as overlay
  const toggle = document.querySelector('.nav-toggle');
  const closeNav = () => {
    nav.removeAttribute('data-open');
    toggle.setAttribute('aria-expanded', 'false');
  };
  toggle.addEventListener('click', () => {
    const opening = !nav.hasAttribute('data-open');
    nav.toggleAttribute('data-open', opening);
    toggle.setAttribute('aria-expanded', String(opening));
    if (opening) input.focus();
  });
  nav.addEventListener('click', (event) => {
    if (event.target.closest('a')) closeNav();
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') closeNav();
  });
})();
