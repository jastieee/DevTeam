(function () {
  const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  const canAnimate = typeof window.anime === "function" && !reduceMotion;

  const body = document.body;
  const menuToggle = document.querySelector("[data-menu-toggle]");
  const mobilePanel = document.querySelector("[data-mobile-panel]");

  function closeMenu() {
    if (!menuToggle || !mobilePanel) return;
    menuToggle.setAttribute("aria-expanded", "false");
    mobilePanel.classList.remove("open");
    body.classList.remove("menu-open");
  }

  if (menuToggle && mobilePanel) {
    menuToggle.addEventListener("click", () => {
      const open = menuToggle.getAttribute("aria-expanded") === "true";
      menuToggle.setAttribute("aria-expanded", String(!open));
      mobilePanel.classList.toggle("open", !open);
      body.classList.toggle("menu-open", !open);
    });

    mobilePanel.querySelectorAll("a").forEach((link) => {
      link.addEventListener("click", closeMenu);
    });
  }

  // Animated scroll driven by requestAnimationFrame so it runs even when the OS
  // forces prefers-reduced-motion (which disables native behavior:"smooth").
  function animatedScrollTo(top) {
    const start = window.scrollY;
    const distance = top - start;
    if (Math.abs(distance) < 1) return;
    const duration = Math.min(900, Math.max(350, Math.abs(distance) * 0.5));
    const startTime = performance.now();
    // easeInOutCubic
    const ease = (t) => (t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2);

    function step(now) {
      const elapsed = now - startTime;
      const progress = Math.min(1, elapsed / duration);
      window.scrollTo(0, start + distance * ease(progress));
      if (progress < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  }

  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", (event) => {
      const selector = anchor.getAttribute("href");
      if (!selector || selector === "#") return;
      const target = document.querySelector(selector);
      if (!target) return;
      event.preventDefault();
      closeMenu();
      const top = target.getBoundingClientRect().top + window.scrollY - 70;
      animatedScrollTo(top);
    });
  });

  const navLinks = Array.from(document.querySelectorAll("[data-nav-link]"));
  const sections = Array.from(document.querySelectorAll("[data-nav-section]"));

  if ("IntersectionObserver" in window && navLinks.length && sections.length) {
    const navObserver = new IntersectionObserver(
      (entries) => {
        const active = entries.find((entry) => entry.isIntersecting);
        if (!active) return;
        const id = active.target.id;
        navLinks.forEach((link) => {
          link.classList.toggle("active", link.getAttribute("href") === "#" + id);
        });
      },
      { threshold: 0.45 }
    );

    sections.forEach((section) => navObserver.observe(section));
  }

  document.querySelectorAll("[data-image-carousel]").forEach((carousel) => {
    const panels = Array.from(carousel.querySelectorAll("[data-carousel-panel]"));
    if (!panels.length) return;
    let switchTimer;

    function activatePanel(activePanel) {
      if (activePanel.classList.contains("is-active")) return;

      window.clearTimeout(switchTimer);
      carousel.classList.add("is-switching");

      if (canAnimate) {
        window.anime.remove(carousel.querySelectorAll(".industry-panel-copy"));
      }

      panels.forEach((panel) => {
        const active = panel === activePanel;
        panel.classList.toggle("is-active", active);
        panel.setAttribute("aria-expanded", String(active));
        panel.setAttribute("aria-pressed", String(active));
      });

      switchTimer = window.setTimeout(() => {
        carousel.classList.remove("is-switching");

        if (!canAnimate) return;

        const copy = activePanel.querySelector(".industry-panel-copy");
        if (!copy) return;

        window.anime({
          targets: copy,
          opacity: [0, 1],
          translateY: [16, 0],
          duration: 460,
          easing: "easeOutCubic",
        });
      }, 640);
    }

    panels.forEach((panel, index) => {
      panel.addEventListener("click", () => activatePanel(panel));
      panel.addEventListener("keydown", (event) => {
        if (event.key !== "ArrowRight" && event.key !== "ArrowLeft") return;
        event.preventDefault();
        const direction = event.key === "ArrowRight" ? 1 : -1;
        const next = panels[(index + direction + panels.length) % panels.length];
        next.focus();
        activatePanel(next);
      });
    });

    // Autoplay: advance through the panels on a timer, crossfading each image.
    // Pauses while the user hovers/focuses the carousel or the tab is hidden.
    const autoplayDelay = 5000;
    let autoplayTimer;

    function advancePanel() {
      const currentIndex = panels.findIndex((panel) => panel.classList.contains("is-active"));
      const next = panels[(currentIndex + 1) % panels.length];
      activatePanel(next);
    }

    function startAutoplay() {
      if (panels.length < 2) return;
      stopAutoplay();
      autoplayTimer = window.setInterval(advancePanel, autoplayDelay);
    }

    function stopAutoplay() {
      window.clearInterval(autoplayTimer);
    }

    carousel.addEventListener("mouseenter", stopAutoplay);
    carousel.addEventListener("mouseleave", startAutoplay);
    carousel.addEventListener("focusin", stopAutoplay);
    carousel.addEventListener("focusout", startAutoplay);
    document.addEventListener("visibilitychange", () => {
      if (document.hidden) stopAutoplay();
      else startAutoplay();
    });

    startAutoplay();
  });

  document.querySelectorAll("[data-projects-carousel]").forEach((carousel) => {
    const track = carousel.querySelector("[data-projects-track]");
    const slides = Array.from(carousel.querySelectorAll("[data-projects-slide]"));
    const dots = Array.from(carousel.querySelectorAll("[data-projects-dot]"));
    const prevBtn = carousel.querySelector("[data-projects-prev]");
    const nextBtn = carousel.querySelector("[data-projects-next]");
    if (!track || !slides.length) return;

    let current = slides.findIndex((s) => s.classList.contains("is-active"));
    if (current < 0) current = 0;

    const viewport = track.parentElement;

    function render() {
      // Center the active slide so the previous/next slides peek on each side.
      // offsetLeft/offsetWidth are layout values (unaffected by the scale on
      // side slides), so centering stays accurate regardless of zoom state.
      const slide = slides[current];
      const offset = viewport.clientWidth / 2 - (slide.offsetLeft + slide.offsetWidth / 2);
      track.style.transform = `translateX(${offset}px)`;
      slides.forEach((s, i) => s.classList.toggle("is-active", i === current));
      dots.forEach((dot, i) => {
        const active = i === current;
        dot.classList.toggle("is-active", active);
        dot.setAttribute("aria-selected", String(active));
      });
    }

    // Keep the active slide centered when the viewport is resized.
    window.addEventListener("resize", render);

    function goTo(index) {
      current = (index + slides.length) % slides.length;
      render();
    }

    render();

    if (prevBtn) prevBtn.addEventListener("click", () => goTo(current - 1));
    if (nextBtn) nextBtn.addEventListener("click", () => goTo(current + 1));
    dots.forEach((dot, i) => dot.addEventListener("click", () => goTo(i)));

    carousel.addEventListener("keydown", (event) => {
      if (event.key === "ArrowRight") {
        event.preventDefault();
        goTo(current + 1);
      } else if (event.key === "ArrowLeft") {
        event.preventDefault();
        goTo(current - 1);
      }
    });

    if (slides.length < 2) return;

    const autoplayDelay = 6500;
    let autoplayTimer;

    function startAutoplay() {
      stopAutoplay();
      autoplayTimer = window.setInterval(() => goTo(current + 1), autoplayDelay);
    }

    function stopAutoplay() {
      window.clearInterval(autoplayTimer);
    }

    carousel.addEventListener("mouseenter", stopAutoplay);
    carousel.addEventListener("mouseleave", startAutoplay);
    carousel.addEventListener("focusin", stopAutoplay);
    carousel.addEventListener("focusout", startAutoplay);
    document.addEventListener("visibilitychange", () => {
      if (document.hidden) stopAutoplay();
      else startAutoplay();
    });

    startAutoplay();
  });

  // Interactive particle-network background. Nodes drift, link to nearby nodes,
  // and react to the mouse (nearby nodes are pulled toward the cursor and lit up).
  document.querySelectorAll("[data-tech-bg]").forEach((canvas) => {
    const ctx = canvas.getContext("2d");
    if (!ctx) return;

    const host = canvas.parentElement;
    let width = 0;
    let height = 0;
    let dpr = Math.min(window.devicePixelRatio || 1, 2);
    let particles = [];
    const pointer = { x: -9999, y: -9999, active: false };
    const LINK_DIST = 130;
    const MOUSE_DIST = 200;

    function resize() {
      const rect = host.getBoundingClientRect();
      width = rect.width;
      height = rect.height;
      dpr = Math.min(window.devicePixelRatio || 1, 2);
      canvas.width = Math.round(width * dpr);
      canvas.height = Math.round(height * dpr);
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

      // Density scales with area, capped for performance.
      const count = Math.min(110, Math.round((width * height) / 13000));
      particles = Array.from({ length: count }, () => ({
        x: Math.random() * width,
        y: Math.random() * height,
        vx: (Math.random() - 0.5) * 0.35,
        vy: (Math.random() - 0.5) * 0.35,
      }));
    }

    function step() {
      ctx.clearRect(0, 0, width, height);

      for (const p of particles) {
        // Gentle pull toward the pointer when it is near.
        if (pointer.active) {
          const dx = pointer.x - p.x;
          const dy = pointer.y - p.y;
          const dist = Math.hypot(dx, dy);
          if (dist < MOUSE_DIST && dist > 0.01) {
            const force = (1 - dist / MOUSE_DIST) * 0.6;
            p.vx += (dx / dist) * force * 0.05;
            p.vy += (dy / dist) * force * 0.05;
          }
        }

        p.x += p.vx;
        p.y += p.vy;
        // Damp so the pointer pull doesn't accelerate forever.
        p.vx *= 0.99;
        p.vy *= 0.99;

        // Wrap around the edges.
        if (p.x < -20) p.x = width + 20;
        else if (p.x > width + 20) p.x = -20;
        if (p.y < -20) p.y = height + 20;
        else if (p.y > height + 20) p.y = -20;
      }

      // Links between nearby particles.
      for (let i = 0; i < particles.length; i++) {
        const a = particles[i];
        for (let j = i + 1; j < particles.length; j++) {
          const b = particles[j];
          const dx = a.x - b.x;
          const dy = a.y - b.y;
          const dist = Math.hypot(dx, dy);
          if (dist < LINK_DIST) {
            ctx.strokeStyle = `rgba(150, 160, 240, ${(1 - dist / LINK_DIST) * 0.22})`;
            ctx.lineWidth = 1;
            ctx.beginPath();
            ctx.moveTo(a.x, a.y);
            ctx.lineTo(b.x, b.y);
            ctx.stroke();
          }
        }

        // Links to the pointer, brighter for a "reach" effect.
        if (pointer.active) {
          const dxm = a.x - pointer.x;
          const dym = a.y - pointer.y;
          const dm = Math.hypot(dxm, dym);
          if (dm < MOUSE_DIST) {
            ctx.strokeStyle = `rgba(174, 180, 240, ${(1 - dm / MOUSE_DIST) * 0.5})`;
            ctx.lineWidth = 1;
            ctx.beginPath();
            ctx.moveTo(a.x, a.y);
            ctx.lineTo(pointer.x, pointer.y);
            ctx.stroke();
          }
        }
      }

      // Nodes.
      for (const p of particles) {
        ctx.fillStyle = "rgba(190, 198, 255, 0.85)";
        ctx.beginPath();
        ctx.arc(p.x, p.y, 1.7, 0, Math.PI * 2);
        ctx.fill();
      }

      rafId = requestAnimationFrame(step);
    }

    let rafId = null;
    function start() {
      if (rafId == null) rafId = requestAnimationFrame(step);
    }
    function stop() {
      if (rafId != null) {
        cancelAnimationFrame(rafId);
        rafId = null;
      }
    }

    // The canvas sits behind the content (z-index), so track the pointer on the
    // window and translate it into the section's coordinate space.
    window.addEventListener("pointermove", (event) => {
      const rect = host.getBoundingClientRect();
      const x = event.clientX - rect.left;
      const y = event.clientY - rect.top;
      if (x >= 0 && x <= rect.width && y >= 0 && y <= rect.height) {
        pointer.x = x;
        pointer.y = y;
        pointer.active = true;
      } else {
        pointer.active = false;
        pointer.x = -9999;
        pointer.y = -9999;
      }
    });

    window.addEventListener("resize", resize);
    document.addEventListener("visibilitychange", () => {
      if (document.hidden) stop();
      else start();
    });

    // Only animate while the section is on screen.
    if ("IntersectionObserver" in window) {
      const io = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => (entry.isIntersecting ? start() : stop()));
        },
        { threshold: 0 }
      );
      io.observe(host);
    } else {
      start();
    }

    resize();
    start();
  });

  if (!canAnimate) return;

  const heroTargets = [
    ...document.querySelectorAll(".hero .eyebrow, .hero-summary, .hero-actions, .hero-metrics, .flow-panel"),
    ...document.querySelectorAll(".hero-title .word"),
  ];

  if (heroTargets.length) {
    window.anime.set(heroTargets, { opacity: 0, translateY: 24 });

    window.anime
      .timeline({ easing: "easeOutExpo" })
      .add({
        targets: ".hero .eyebrow",
        opacity: [0, 1],
        translateY: [16, 0],
        duration: 650,
      })
      .add(
        {
          targets: ".hero-title .word",
          opacity: [0, 1],
          translateY: [36, 0],
          delay: window.anime.stagger(55),
          duration: 850,
        },
        "-=360"
      )
      .add(
        {
          targets: ".hero-summary, .hero-actions, .hero-metrics",
          opacity: [0, 1],
          translateY: [22, 0],
          delay: window.anime.stagger(90),
          duration: 700,
        },
        "-=420"
      )
      .add(
        {
          targets: ".flow-panel",
          opacity: [0, 1],
          translateY: [28, 0],
          scale: [0.98, 1],
          duration: 760,
        },
        "-=560"
      );
  }

  document.querySelectorAll(".flow-line").forEach((path) => {
    path.style.strokeDasharray = path.getTotalLength();
    path.style.strokeDashoffset = path.getTotalLength();
  });

  window.anime({
    targets: ".flow-line",
    strokeDashoffset: [window.anime.setDashoffset, 0],
    delay: window.anime.stagger(120, { start: 760 }),
    duration: 1300,
    easing: "easeInOutSine",
  });

  window.anime.set(".flow-node, .signal-dot, .flow-hub", { opacity: 0 });
  window.anime({
    targets: ".flow-node, .signal-dot, .flow-hub",
    opacity: [0, 1],
    delay: window.anime.stagger(110, { start: 960 }),
    duration: 740,
    easing: "easeOutCubic",
  });

  window.anime({
    targets: ".signal-dot",
    scale: [1, 1.28],
    opacity: [0.7, 1],
    direction: "alternate",
    loop: true,
    delay: window.anime.stagger(280),
    duration: 1100,
    easing: "easeInOutSine",
  });

  document.querySelectorAll("[data-count]").forEach((counter) => {
    const target = Number(counter.dataset.count);
    if (!Number.isFinite(target)) return;

    const state = { value: 0 };
    window.anime({
      targets: state,
      value: target,
      round: 1,
      duration: 1200,
      delay: 780,
      easing: "easeOutExpo",
      update: () => {
        counter.textContent = String(state.value);
      },
    });
  });

  // Note: .demo-card is intentionally excluded. Demo cards are carousel slides;
  // all but the active slide sit off-screen behind `overflow: hidden`, so the
  // IntersectionObserver below never fires for them and they would stay stuck at
  // opacity:0 — making the card (and its link) invisible/unclickable after "Next".
  const revealTargets = document.querySelectorAll(
    ".project-card, .standard-item, .contact-point, .contact-form, .catalog-meta span"
  );

  if ("IntersectionObserver" in window && revealTargets.length) {
    window.anime.set(revealTargets, { opacity: 0, translateY: 22 });

    const revealObserver = new IntersectionObserver(
      (entries) => {
        const visible = entries.filter((entry) => entry.isIntersecting).map((entry) => entry.target);
        if (!visible.length) return;

        window.anime({
          targets: visible,
          opacity: [0, 1],
          translateY: [22, 0],
          delay: window.anime.stagger(80),
          duration: 720,
          easing: "easeOutCubic",
        });

        visible.forEach((target) => revealObserver.unobserve(target));
      },
      { threshold: 0.16, rootMargin: "0px 0px -40px 0px" }
    );

    revealTargets.forEach((target) => revealObserver.observe(target));
  }
})();
