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

  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", (event) => {
      const selector = anchor.getAttribute("href");
      if (!selector || selector === "#") return;
      const target = document.querySelector(selector);
      if (!target) return;
      event.preventDefault();
      closeMenu();
      const top = target.getBoundingClientRect().top + window.scrollY - 70;
      window.scrollTo({ top, behavior: reduceMotion ? "auto" : "smooth" });
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

    function render() {
      track.style.transform = `translateX(-${current * 100}%)`;
      slides.forEach((slide, i) => slide.classList.toggle("is-active", i === current));
      dots.forEach((dot, i) => {
        const active = i === current;
        dot.classList.toggle("is-active", active);
        dot.setAttribute("aria-selected", String(active));
      });
    }

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

  const revealTargets = document.querySelectorAll(
    ".industry-panel, .project-card, .standard-item, .contact-point, .contact-form, .demo-card, .catalog-meta span"
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
