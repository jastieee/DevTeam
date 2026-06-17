<?php
$demos = [
    [
        'title' => 'Inventory Control Suite',
        'summary' => 'Products, assets, stock in/out, and reporting for operations teams.',
        'status' => 'live',
        'status_label' => 'Live demo',
        'area' => 'Inventory',
        'tags' => ['Products', 'Assets', 'Reports'],
        'url' => 'inventory/index.php',
        'cta' => 'Open demo',
        'icon' => 'fi fi-rr-box-open',
    ],
    [
        'title' => 'RFID Asset Tracking',
        'summary' => 'Traceable asset movement, audit events, and location checks.',
        'status' => 'preview',
        'status_label' => 'Preview',
        'area' => 'RFID',
        'tags' => ['RFID', 'Audit', 'Assets'],
        'url' => 'index.php#contact',
        'cta' => 'Request access',
        'icon' => 'fi fi-rr-rss',
    ],
    [
        'title' => 'Mobile Data Collection',
        'summary' => 'Handheld workflows for receiving, counting, picking, and field capture.',
        'status' => 'pilot',
        'status_label' => 'Pilot',
        'area' => 'Mobile',
        'tags' => ['Handhelds', 'Sync', 'Scanning'],
        'url' => 'index.php#contact',
        'cta' => 'Request access',
        'icon' => 'fi fi-rr-mobile-notch',
    ],
    [
        'title' => 'Label Print Operations',
        'summary' => 'Batch printing, barcode checks, approval flows, and print job control.',
        'status' => 'preview',
        'status_label' => 'Preview',
        'area' => 'Barcode',
        'tags' => ['Labels', 'Barcode', 'QA'],
        'url' => 'index.php#contact',
        'cta' => 'Request access',
        'icon' => 'fi fi-rr-barcode',
    ],
    [
        'title' => 'Executive Reporting',
        'summary' => 'Management dashboards for stock activity, asset status, and exception review.',
        'status' => 'preview',
        'status_label' => 'Preview',
        'area' => 'Analytics',
        'tags' => ['Dashboards', 'Exports', 'Review'],
        'url' => 'index.php#contact',
        'cta' => 'Request access',
        'icon' => 'fi fi-rr-chart-histogram',
    ],
    [
        'title' => 'Service Workflow Tracker',
        'summary' => 'Request intake, assignment, service status, and resolution history.',
        'status' => 'preview',
        'status_label' => 'Preview',
        'area' => 'Service',
        'tags' => ['Requests', 'SLA', 'History'],
        'url' => 'index.php#contact',
        'cta' => 'Request access',
        'icon' => 'fi fi-rr-settings-sliders',
    ],
    [
        'title' => 'Custom Software Alignment',
        'summary' => 'Customized software workflows shaped around your actual business process, roles, approvals, and reports.',
        'status' => 'preview',
        'status_label' => 'Consultation',
        'area' => 'Customization',
        'tags' => ['Custom Software', 'Workflow', 'Integration'],
        'url' => 'index.php#contact',
        'cta' => 'Request consultation',
        'icon' => 'fi fi-rr-settings-sliders',
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Enterprise demo application catalog from Newton Scanning System Inc.">
    <title>Demo Catalog - Newton Enterprise Demos</title>
    <link rel="preload" as="image" href="images/enterprise-operations-hero.png">
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/2.6.0/uicons-regular-rounded/css/uicons-regular-rounded.css">
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
            <li><a href="index.php#hero">Home</a></li>
            <li><a href="index.php#projects">Projects</a></li>
            <li><a href="index.php#standards">Standards</a></li>
            <li><a href="index.php#contact">Contact</a></li>
            <li><a href="demos.php" class="active">Demos</a></li>
        </ul>

        <div class="nav-actions">
            <a href="index.php#contact" class="btn btn-nav">Request access</a>
            <button class="menu-toggle" type="button" aria-label="Open menu" aria-expanded="false" data-menu-toggle>
                <span></span>
            </button>
        </div>
    </nav>
</header>

<div class="mobile-panel" data-mobile-panel>
    <a href="index.php#hero">Home</a>
    <a href="index.php#projects">Projects</a>
    <a href="index.php#standards">Standards</a>
    <a href="index.php#contact">Contact</a>
    <a href="demos.php">Demos</a>
</div>

<main>
    <section class="catalog-hero">
        <div class="container">
         
            <h1 class="catalog-title">Enterprise project previews, ready to evaluate.</h1>
            <p class="catalog-summary">
                A focused catalog for barcode, RFID, inventory, mobility, reporting, and custom software workflows.
            </p>
            <div class="catalog-meta">
                <span><?= count($demos) ?> demos listed</span>
                <span>1 live environment</span>
                <span>Private walkthroughs available</span>
            </div>
        </div>
    </section>

    <section class="section section-demos" aria-labelledby="demo-list-title">
        <div class="demos-bg" aria-hidden="true">
            <img src="images/4.jpg" alt="">
            <span class="demos-bg-shade"></span>
        </div>

        <div class="container demos-inner">
            <div class="section-head section-head-light">
                <div>
                    <p class="section-kicker">Portfolio</p>
                    <h2 class="section-title" id="demo-list-title">Select a demo.</h2>
                </div>
            </div>

            <div
                class="demos-carousel<?= count($demos) <= 1 ? ' is-single' : '' ?>"
                data-projects-carousel
                aria-roledescription="carousel"
                aria-label="Demo catalog"
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
                        <?php foreach ($demos as $index => $demo): ?>
                        <article
                            class="demo-card demos-slide<?= $index === 0 ? ' is-active' : '' ?>"
                            data-projects-slide
                            aria-roledescription="slide"
                            aria-label="<?= ($index + 1) . ' of ' . count($demos) ?>"
                        >
                            <div class="card-top">
                                <span class="demo-icon"><i class="<?= htmlspecialchars($demo['icon']) ?>" aria-hidden="true"></i></span>
                                <span class="status status-<?= htmlspecialchars($demo['status']) ?>">
                                    <?= htmlspecialchars($demo['status_label']) ?>
                                </span>
                            </div>

                            <h2><?= htmlspecialchars($demo['title']) ?></h2>
                            <p><?= htmlspecialchars($demo['summary']) ?></p>

                            <div class="tag-row" aria-label="Demo tags">
                                <?php foreach ($demo['tags'] as $tag): ?>
                                <span class="tag"><?= htmlspecialchars($tag) ?></span>
                                <?php endforeach; ?>
                            </div>

                            <div class="demo-foot">
                                <small><?= htmlspecialchars($demo['area']) ?></small>
                                <a href="<?= htmlspecialchars($demo['url']) ?>" class="card-link">
                                    <?= htmlspecialchars($demo['cta']) ?>
                                    <i class="fi fi-rr-arrow-right" aria-hidden="true"></i>
                                </a>
                            </div>
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
                    <?php foreach ($demos as $index => $demo): ?>
                    <button
                        class="demos-dot<?= $index === 0 ? ' is-active' : '' ?>"
                        type="button"
                        role="tab"
                        data-projects-dot="<?= $index ?>"
                        aria-label="<?= htmlspecialchars($demo['title']) ?>"
                        aria-selected="<?= $index === 0 ? 'true' : 'false' ?>"
                    ></button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
</main>

<footer class="site-footer">
    <div class="container footer-inner">
        <span>&copy; <?= date('Y') ?> Newton Scanning System Inc.</span>
        <span>Demo catalog</span>
    </div>
</footer>

<script src="vendor/anime.min.js"></script>
<script src="site.js"></script>
</body>
</html>
