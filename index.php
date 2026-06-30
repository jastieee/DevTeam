<?php
$form_success = isset($_GET['sent']) && $_GET['sent'] === '1';
$form_error   = isset($_GET['sent']) && $_GET['sent'] === '0';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name    = htmlspecialchars(strip_tags(trim($_POST['name'] ?? '')));
    $email   = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $company = htmlspecialchars(strip_tags(trim($_POST['company'] ?? '')));
    $topic   = htmlspecialchars(strip_tags(trim($_POST['topic'] ?? 'Demo Request')));
    $message = htmlspecialchars(strip_tags(trim($_POST['message'] ?? '')));

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $message === '') {
        header('Location: index.php?sent=0#contact');
        exit;
    }

    $to      = 'program@newton.com.ph, irish@newton.com.ph';
    $subject = '[Newton Enterprise Demos] ' . $topic;
    $body    = "Name: $name\nEmail: $email\nCompany: $company\nTopic: $topic\n\nMessage:\n$message";
    $headers = "From: noreply@newtonscanning.com.ph\r\n"
             . "Reply-To: $email\r\n"
             . "X-Mailer: PHP/" . phpversion();

    if (mail($to, $subject, $body, $headers)) {
        header('Location: index.php?sent=1#contact');
    } else {
        header('Location: index.php?sent=0#contact');
    }
    exit;
}

$industryPanels = [
    [
        'title' => 'Manufacturing',
        'summary' => 'Barcode, labeling, and traceability workflows for production and warehouse teams.',
        'meta' => 'Plant operations',
        'image' => 'images/1.jpg',
        'position' => '68% center',
    ],
    [
        'title' => 'Logistics',
        'summary' => 'Receiving, dispatch, stock movement, and mobile scanning for high-volume operations.',
        'meta' => 'Distribution flow',
        'image' => 'images/2.jpg',
        'position' => '82% center',
    ],
    [
        'title' => 'Retail',
        'summary' => 'Inventory accuracy, product lookup, labeling, and replenishment tools for store networks.',
        'meta' => 'Store systems',
        'image' => 'images/3.jpg',
        'position' => '55% center',
    ],
    [
        'title' => 'Custom Software',
        'summary' => 'Customized screens, approvals, reports, and integrations aligned to your business process.',
        'meta' => 'Process alignment',
        'image' => 'images/4.jpg',
        'position' => '34% center',
    ],
];

$projects = [
    [
        'title' => 'Inventory Control Suite',
        'summary' => 'Products, assets, stock movement, and reporting in one browser-based workflow.',
        'status' => 'live',
        'status_label' => 'Live demo',
        'impact' => 'Stock visibility',
        'workflow' => 'Inventory and assets',
        'tags' => ['Products', 'Assets', 'Reports'],
        'url' => 'inventory/index.php',
        'cta' => 'Open demo',
        'icon' => 'fi fi-rr-box-open',
    ],
    [
        'title' => 'WMS Demo Platform',
        'summary' => 'Receiving, putaway, picking, packing, and dispatch in one connected warehouse workflow.',
        'status' => 'live',
        'status_label' => 'Live demo',
        'impact' => 'Warehouse flow',
        'workflow' => 'Receiving to dispatch',
        'tags' => ['Receiving', 'Picking', 'Dispatching'],
        'url' => 'WMS/index.php',
        'cta' => 'Open demo',
        'icon' => 'fi fi-rr-warehouse-alt',
    ],
    [
        'title' => 'CRM Demo Platform',
        'summary' => 'Contacts, sales pipeline, interactions, and activity tracking on one centralized platform.',
        'status' => 'live',
        'status_label' => 'Live demo',
        'impact' => 'Sales visibility',
        'workflow' => 'Contacts and pipeline',
        'tags' => ['Contacts', 'Pipeline', 'Deals'],
        'url' => 'CRM/index.php',
        'cta' => 'Open demo',
        'icon' => 'fi fi-rr-users-alt',
    ],
    [
        'title' => 'RFID Asset Tracking',
        'summary' => 'Traceable asset movement, audit events, and location checks for controlled operations.',
        'status' => 'preview',
        'status_label' => 'Preview',
        'impact' => 'Asset traceability',
        'workflow' => 'RFID audit trail',
        'tags' => ['RFID', 'Audit', 'Assets'],
        'url' => 'index.php#contact',
        'cta' => 'Request access',
        'icon' => 'fi fi-rr-rss',
    ],
];

$standards = [
    [
        'icon' => 'fi fi-rr-shield-check',
        'title' => 'Role-aware access',
        'copy' => 'Separate views for admins, operators, reviewers, and client teams.',
    ],
    [
        'icon' => 'fi fi-rr-chart-histogram',
        'title' => 'Operational reporting',
        'copy' => 'Dashboards, exports, and activity history for management review.',
    ],
    [
        'icon' => 'fi fi-rr-barcode-read',
        'title' => 'Device integration',
        'copy' => 'Designed around barcode scanners, RFID workflows, and mobile computers.',
    ],
    [
        'icon' => 'fi fi-rr-settings-sliders',
        'title' => 'Configurable rollout',
        'copy' => 'Demo flows can be adapted to your site, process, and approval structure.',
    ],
    [
        'icon' => 'fi fi-rr-edit',
        'title' => 'Process-first customization',
        'copy' => 'We customize software to align with your business process, roles, approvals, and reporting needs.',
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Enterprise-grade demo applications and project showcases from Newton Scanning System Inc.">
    <title>Newton Enterprise Demos</title>
    <link rel="preload" as="image" href="images/enterprise-operations-hero.png">
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/2.6.0/uicons-regular-rounded/css/uicons-regular-rounded.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700&display=swap">
    <link rel="stylesheet" href="output.css">
</head>
<body>
<header class="site-header">
    <nav class="nav container" aria-label="Primary">
        <a href="index.php" class="brand" aria-label="Newton Enterprise Demos">
      
            <img class="brand-icon" src="images/icon.png" alt="" aria-hidden="true">
            <span class="brand-name">
                <span class="brand-primary">Newton</span>
                <span class="brand-subname">Scanning Systems, Inc.</span>
            </span>
        </a>

        <ul class="nav-links">
            <li><a href="#hero" class="active" data-nav-link>Home</a></li>
            <li><a href="#standards" data-nav-link>Standards</a></li>
            <li><a href="#projects" data-nav-link>Projects</a></li>
            <li><a href="#contact" data-nav-link>Contact</a></li>
        </ul>

        <div class="nav-actions">
            <a href="demos.php" class="btn btn-nav">View demos</a>
            <button class="menu-toggle" type="button" aria-label="Open menu" aria-expanded="false" data-menu-toggle>
                <span></span>
            </button>
        </div>
    </nav>
</header>

<div class="mobile-panel" data-mobile-panel>
    <a href="#hero">Home</a>
    <a href="#standards">Standards</a>
    <a href="#projects">Projects</a>
    <a href="#contact">Contact</a>
    <a href="demos.php">View demos</a>
</div>

<main>
    <section class="hero" id="hero" data-nav-section>
        <div class="hero-media" aria-hidden="true">
            <img src="images/enterprise-operations-hero.png" alt="">
        </div>

        <div class="hero-content container">
            <div class="hero-copy">
                
                <h1 class="hero-title">
                    <span class="word">Software</span>
                    <span class="word">demos</span><br>
                    <span class="word">for</span>
                    <span class="word accent">operational</span>
                    <span class="word accent">teams.</span>
                </h1>
                <p class="hero-summary">
                    Barcode, RFID, inventory, mobile data, and custom software workflows aligned to real business processes.
                </p>

                <div class="hero-actions">
                    <a href="demos.php" class="btn btn-primary">
                        View demo apps
                        <i class="fi fi-rr-arrow-right" aria-hidden="true"></i>
                    </a>
                    <a href="#contact" class="btn btn-secondary">Request walkthrough</a>
                </div>

                <div class="hero-metrics" aria-label="Newton demo portfolio facts">
                    <div class="metric">
                        <strong><span data-count="30">30</span>+</strong>
                        <span>Years in data capture</span>
                    </div>
                    <div class="metric">
                        <strong><span data-count="4">4</span></strong>
                        <span>Solution lines</span>
                    </div>
                    <div class="metric">
                        <strong>PH</strong>
                        <span>Enterprise support</span>
                    </div>
                </div>
            </div>

            <aside class="flow-panel" aria-label="Custom development illustration">
                <!-- Lottie animation "Developer" created by Chase Gee. -->
                <lottie-player
                    class="hero-lottie"
                    src="images/developer.json"
                    background="transparent"
                    speed="1"
                    loop
                    autoplay
                ></lottie-player>
            </aside>
        </div>
    </section>

    <section class="trust-strip" aria-label="Industries served">
        <div class="trust-inner container">
            <!-- <div class="trust-copy">
      
                <h2>Built around the way your teams work.</h2>
            </div> -->

            <div class="industry-carousel" data-image-carousel>
                <?php foreach ($industryPanels as $index => $panel):
                    $isActive = $index === 0;
                ?>
                <button
                    class="industry-panel <?= $isActive ? 'is-active' : '' ?>"
                    type="button"
                    data-carousel-panel
                    aria-expanded="<?= $isActive ? 'true' : 'false' ?>"
                    aria-pressed="<?= $isActive ? 'true' : 'false' ?>"
                    style="--panel-position: <?= htmlspecialchars($panel['position']) ?>;"
                >
                    <img
                        src="<?= htmlspecialchars($panel['image']) ?>"
                        alt="<?= htmlspecialchars($panel['title']) ?> operations"
                        class="industry-panel-img"
                        loading="<?= $isActive ? 'eager' : 'lazy' ?>"
                    >
                    <span class="industry-panel-shade" aria-hidden="true"></span>
                    <span class="industry-panel-copy">
                        <span class="industry-panel-kicker"><?= htmlspecialchars($panel['meta']) ?></span>
                        <strong><?= htmlspecialchars($panel['title']) ?></strong>
                        <span class="industry-panel-summary"><?= htmlspecialchars($panel['summary']) ?></span>
                    </span>
                </button>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section section-muted" id="standards" data-nav-section>
        <div class="container standards-layout">
            <div>
                <p class="section-kicker">Enterprise fit</p>
                <h2 class="section-title">Less demo theater. More operational proof.</h2>
                <p class="section-copy">
                    Each showcase focuses on workflows, controls, and device realities that matter during implementation.
                </p>
            </div>

            <div class="standard-list">
                <?php foreach ($standards as $standard): ?>
                <div class="standard-item">
                    <i class="<?= htmlspecialchars($standard['icon']) ?>" aria-hidden="true"></i>
                    <div>
                        <strong><?= htmlspecialchars($standard['title']) ?></strong>
                        <span><?= htmlspecialchars($standard['copy']) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section section-demos" id="projects" data-nav-section>
        <div class="demos-bg" aria-hidden="true">
            <img src="images/2.jpg" alt="">
            <span class="demos-bg-shade"></span>
            <canvas class="tech-bg" data-tech-bg></canvas>
        </div>

        <div class="container demos-inner">
            <div class="section-head section-head-light">
                <div>
                    
                    <h2 class="section-title">Demo apps with enterprise use cases.</h2>
                </div>
            </div>

            <div
                class="demos-carousel<?= count($projects) <= 1 ? ' is-single' : '' ?>"
                data-projects-carousel
                aria-roledescription="carousel"
                aria-label="Demo applications"
            >
                <button
                    class="demos-nav demos-nav-prev"
                    type="button"
                    data-projects-prev
                    aria-label="Previous demo"
                >
                    <i class="fi fi-rr-angle-left" aria-hidden="true"></i>
                </button>

                <div class="demos-viewport">
                    <div class="demos-track" data-projects-track>
                        <?php foreach ($projects as $index => $project): ?>
                        <?php $cardImage = $project['image'] ?? ('images/' . (($index % 4) + 1) . '.jpg'); ?>
                        <article
                            class="project-card demos-slide<?= $index === 0 ? ' is-active' : '' ?>"
                            data-projects-slide
                            aria-roledescription="slide"
                            aria-label="<?= ($index + 1) . ' of ' . count($projects) ?>"
                            style="--card-image: url('<?= htmlspecialchars($cardImage) ?>')"
                        >
                            <div class="card-top">
                                <span class="project-index"><i class="<?= htmlspecialchars($project['icon']) ?>" aria-hidden="true"></i></span>
                                <span class="status status-<?= htmlspecialchars($project['status']) ?>">
                                    <?= htmlspecialchars($project['status_label']) ?>
                                </span>
                            </div>
                            <h3><?= htmlspecialchars($project['title']) ?></h3>
                            <p><?= htmlspecialchars($project['summary']) ?></p>

                            <dl class="project-meta">
                                <div>
                                    <dt>Impact</dt>
                                    <dd><?= htmlspecialchars($project['impact']) ?></dd>
                                </div>
                                <div>
                                    <dt>Workflow</dt>
                                    <dd><?= htmlspecialchars($project['workflow']) ?></dd>
                                </div>
                            </dl>

                            <div class="tag-row" aria-label="Project tags">
                                <?php foreach ($project['tags'] as $tag): ?>
                                <span class="tag"><?= htmlspecialchars($tag) ?></span>
                                <?php endforeach; ?>
                            </div>

                            <a href="<?= htmlspecialchars($project['url']) ?>" class="card-link">
                                <?= htmlspecialchars($project['cta']) ?>
                                <i class="fi fi-rr-arrow-right" aria-hidden="true"></i>
                            </a>
                        </article>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button
                    class="demos-nav demos-nav-next"
                    type="button"
                    data-projects-next
                    aria-label="Next demo"
                >
                    <i class="fi fi-rr-angle-right" aria-hidden="true"></i>
                </button>

                <div class="demos-dots" data-projects-dots role="tablist" aria-label="Choose a demo">
                    <?php foreach ($projects as $index => $project): ?>
                    <button
                        class="demos-dot<?= $index === 0 ? ' is-active' : '' ?>"
                        type="button"
                        role="tab"
                        data-projects-dot="<?= $index ?>"
                        aria-label="<?= htmlspecialchars($project['title']) ?>"
                        aria-selected="<?= $index === 0 ? 'true' : 'false' ?>"
                    ></button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="section contact-section" id="contact" data-nav-section>
        <div class="container contact-layout">
            <div>
                <p class="section-kicker">Request access</p>
                <h2 class="section-title">Schedule a focused walkthrough.</h2>
                <p class="section-copy">
                    Tell us which workflow you want to evaluate. We will route the right demo and technical context.
                </p>

                <div class="contact-points">
                    <div class="contact-point">
                        <i class="fi fi-rr-envelope" aria-hidden="true"></i>
                        <div>
                            <strong>Dev team</strong>
                            <span>program@newton.com.ph</span>
                        </div>
                    </div>
                    <div class="contact-point">
                        <i class="fi fi-rr-marker" aria-hidden="true"></i>
                        <div>
                            <strong>Newton Scanning System Inc.</strong>
                            <span>Makati City, Philippines</span>
                        </div>
                    </div>
                </div>
            </div>

            <form class="contact-form" method="POST" action="index.php">
                <?php if ($form_success): ?>
                <div class="alert alert-success">Message sent. We will get back to you shortly.</div>
                <?php elseif ($form_error): ?>
                <div class="alert alert-error">Something went wrong. Please check your details and try again.</div>
                <?php endif; ?>

                <input type="hidden" name="topic" value="Demo Request">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" placeholder="Your name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Work email</label>
                        <input type="email" id="email" name="email" placeholder="name@company.com" required>
                    </div>
                    <div class="form-group full">
                        <label for="company">Company</label>
                        <input type="text" id="company" name="company" placeholder="Company name">
                    </div>
                    <div class="form-group full">
                        <label for="message">Workflow</label>
                        <textarea id="message" name="message" placeholder="Inventory, RFID, mobile data collection, label printing, custom software..." required></textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    Send request
                    <i class="fi fi-rr-arrow-right" aria-hidden="true"></i>
                </button>
            </form>
        </div>
    </section>
</main>

<footer class="site-footer">
    <div class="container footer-inner">
        <span>&copy; <?= date('Y') ?> Newton Scanning System Inc.</span>
        <span>Enterprise demo portfolio</span>
    </div>
</footer>

<script src="https://unpkg.com/@lottiefiles/lottie-player@2.0.8/dist/lottie-player.js"></script>
<script src="vendor/anime.min.js"></script>
<script src="site.js"></script>
</body>
</html>
