<?php
// ============================================================
// index.php — Public Landing Page
// ============================================================
session_start();
require_once 'config/db.php';

// Redirect logged-in users straight to their dashboard
if (!empty($_SESSION['user_id'])) {
    $dest = ($_SESSION['role'] === 'admin') ? 'admin/dashboard.php' : 'user/dashboard.php';
    header('Location: ' . $dest);
    exit;
}

// Fetch 3 most recently approved papers for homepage
$stmt = mysqli_prepare($conn,
    "SELECT p.title, p.category, p.abstract, u.name AS author_name
     FROM papers p
     JOIN users u ON p.user_id = u.id
     WHERE p.status = 'approved'
     ORDER BY p.created_at DESC
     LIMIT 3"
);
mysqli_stmt_execute($stmt);
$result         = mysqli_stmt_get_result($stmt);
$featured_papers = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Fetch site-wide counts for stat section
$total_papers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM papers WHERE status='approved'"))['c'];
$total_users  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM users WHERE role='user'"))['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Academic Archive | Advancing Knowledge</title>
    <meta name="description" content="Academic Archive — A premium management ecosystem for researchers to submit, track, and publish research papers."/>
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,200..800;1,6..72,200..800&family=Inter:wght@100..900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
        tailwind.config = {
            theme: { extend: {
                colors: {
                    "primary":"#00236f","primary-container":"#1e3a8a",
                    "secondary":"#505f76","surface":"#f7f9fb",
                    "surface-container":"#eceef0","surface-container-low":"#f2f4f6",
                    "surface-container-high":"#e6e8ea","surface-container-lowest":"#ffffff",
                    "on-surface":"#191c1e","on-surface-variant":"#444651",
                    "secondary-container":"#d0e1fb","on-secondary-container":"#54647a",
                    "error":"#ba1a1a","outline-variant":"#c5c5d3","outline":"#757682"
                },
                fontFamily: { "headline":["Newsreader","serif"], "body":["Inter","sans-serif"], "label":["Inter","sans-serif"] },
                borderRadius: { "DEFAULT":"0.125rem","lg":"0.25rem","xl":"0.5rem","full":"0.75rem" }
            }}
        }
    </script>
    <link rel="stylesheet" href="assets/css/app.css"/>
    <style>
        .text-gradient { background: linear-gradient(135deg,#00236f 0%,#1e3a8a 100%); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
        .bg-gradient-primary { background: linear-gradient(135deg,#00236f 0%,#1e3a8a 100%); }
    </style>
</head>
<body class="bg-surface text-on-surface font-body">

<!-- ── NAV ── -->
<nav class="fixed top-0 w-full flex justify-between items-center px-8 h-20 bg-white/80 backdrop-blur-md z-50 shadow-sm">
    <div class="flex items-center gap-12">
        <span class="text-2xl font-headline italic font-bold text-primary">Academic Archive</span>
        <div class="hidden md:flex gap-8 items-center">
            <a href="#featured"  class="font-headline text-lg font-semibold tracking-tight text-slate-600 hover:text-primary transition-colors">Explore</a>
            <a href="#search-section" class="font-headline text-lg font-semibold tracking-tight text-slate-600 hover:text-primary transition-colors">Search</a>
        </div>
    </div>
    <div class="flex items-center gap-4">
        <a href="auth/login.php"    class="px-6 py-2 text-slate-600 font-semibold hover:text-primary transition-colors">Login</a>
        <a href="auth/register.php" class="bg-gradient-primary text-white px-6 py-2 rounded-md font-semibold hover:opacity-90 transition-opacity">Register</a>
    </div>
</nav>

<main class="pt-20">
    <!-- ── HERO ── -->
    <section class="relative min-h-[820px] flex items-center px-8 md:px-24 overflow-hidden bg-surface">
        <div class="z-10 max-w-4xl">
            <span class="font-label text-sm uppercase tracking-[0.2em] text-secondary mb-6 block">The Digital Curator</span>
            <h1 class="font-headline text-6xl md:text-8xl font-bold leading-[1.1] text-primary mb-8">
                Advancing Knowledge <br/>
                <span class="italic font-normal">through</span> Peer-Reviewed <br/>
                Excellence
            </h1>
            <p class="text-xl text-on-surface-variant max-w-xl mb-10 leading-relaxed">
                A premium management ecosystem for researchers and university presses to curate humanity's collective intellect.
            </p>
            <div class="flex flex-wrap gap-6">
                <a href="auth/register.php" class="bg-gradient-primary text-white px-8 py-4 rounded-md text-lg font-semibold shadow-xl hover:shadow-2xl transition-all hover:scale-105">
                    Submit Your Research
                </a>
                <a href="#featured" class="flex items-center gap-3 px-8 py-4 text-primary font-semibold hover:bg-surface-container transition-colors rounded-md">
                    Browse Publications
                    <span class="material-symbols-outlined">arrow_forward</span>
                </a>
            </div>
        </div>

        <!-- Decorative right image block -->
        <div class="absolute right-[-5%] top-[10%] w-[45%] h-[80%] hidden lg:block">
            <div class="relative w-full h-full rounded-xl overflow-hidden editorial-shadow">
                <img
                    src="assets/images/hero-library.png"
                    alt="University library interior"
                    class="w-full h-full object-cover"
                    onerror="this.style.background='linear-gradient(135deg,#00236f,#1e3a8a)';this.style.display='block'"
                />
                <div class="absolute inset-0 bg-primary/10 mix-blend-multiply"></div>
            </div>
            <!-- Overlapping quote card -->
            <div class="absolute -bottom-10 -left-14 bg-white p-7 rounded-xl editorial-shadow max-w-xs border border-outline-variant/20 z-10">
                <div class="flex items-center gap-4 mb-3">
                    <div class="w-10 h-10 rounded-full bg-secondary-container flex items-center justify-center text-primary shrink-0">
                        <span class="material-symbols-outlined">menu_book</span>
                    </div>
                    <h4 class="font-headline text-base font-bold text-primary">Journal of Modern Ethics</h4>
                </div>
                <p class="text-sm text-on-surface-variant italic">"Architecture of the mind is the foundation of digital sovereignty in the age of curated data."</p>
            </div>
        </div>
    </section>

    <!-- ── STATS ── -->
    <section class="py-12 px-8 bg-surface-container-low">
        <div class="max-w-7xl mx-auto flex flex-wrap justify-around gap-10 items-center">
            <div class="flex flex-col items-center">
                <span class="text-4xl font-headline font-bold text-primary"><?= number_format($total_papers + 12000) ?>+</span>
                <span class="font-label text-xs uppercase tracking-widest text-secondary mt-1">Papers Published</span>
            </div>
            <div class="flex flex-col items-center">
                <span class="text-4xl font-headline font-bold text-primary"><?= number_format($total_users + 450) ?></span>
                <span class="font-label text-xs uppercase tracking-widest text-secondary mt-1">Registered Researchers</span>
            </div>
            <div class="flex flex-col items-center">
                <span class="text-4xl font-headline font-bold text-primary">98%</span>
                <span class="font-label text-xs uppercase tracking-widest text-secondary mt-1">Review Speed Index</span>
            </div>
            <div class="flex flex-col items-center">
                <span class="text-4xl font-headline font-bold text-primary">2.4M</span>
                <span class="font-label text-xs uppercase tracking-widest text-secondary mt-1">Citations Yearly</span>
            </div>
        </div>
    </section>

    <!-- ── FEATURED PUBLICATIONS ── -->
    <section id="featured" class="py-24 px-8 max-w-7xl mx-auto">
        <div class="flex justify-between items-end mb-16">
            <div>
                <h2 class="font-headline text-5xl font-bold text-primary mb-4">Featured Publications</h2>
                <p class="text-on-surface-variant max-w-md">Highlighting the most impactful research from recent submissions.</p>
            </div>
            <a href="auth/login.php" class="font-label text-sm uppercase font-bold tracking-widest text-primary border-b border-primary/20 pb-1 hover:border-primary transition-all">
                View All
            </a>
        </div>

        <?php if (!empty($featured_papers)): ?>
        <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
            <!-- Large featured card (first paper) -->
            <?php $fp = $featured_papers[0]; ?>
            <div class="md:col-span-8 bg-white rounded-xl p-10 editorial-shadow flex flex-col md:flex-row gap-8 items-center border border-outline-variant/10">
                <div class="w-full md:w-2/5 aspect-[3/4] rounded-lg bg-gradient-to-br from-primary to-primary-container flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-white" style="font-size:5rem;">article</span>
                </div>
                <div class="w-full">
                    <?php if ($fp['category']): ?>
                    <span class="bg-secondary-container text-on-secondary-container px-3 py-1 rounded text-xs font-bold uppercase tracking-wide mb-5 inline-block">
                        <?= htmlspecialchars($fp['category']) ?>
                    </span>
                    <?php endif; ?>
                    <h3 class="font-headline text-3xl font-bold text-primary mb-4 leading-snug">
                        <?= htmlspecialchars($fp['title']) ?>
                    </h3>
                    <p class="text-on-surface-variant mb-6 text-sm leading-relaxed italic line-clamp-3">
                        "<?= htmlspecialchars(substr($fp['abstract'], 0, 200)) ?>…"
                    </p>
                    <div class="flex items-center gap-3 pt-5 border-t border-surface-container">
                        <div class="w-9 h-9 rounded-full bg-primary flex items-center justify-center text-white font-bold text-sm shrink-0">
                            <?= strtoupper(substr($fp['author_name'], 0, 1)) ?>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-primary"><?= htmlspecialchars($fp['author_name']) ?></p>
                            <p class="text-xs text-secondary">Researcher</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Small cards (papers 2 & 3) -->
            <?php for ($i = 1; $i < count($featured_papers); $i++):
                $p = $featured_papers[$i]; ?>
            <div class="md:col-span-4 bg-surface-container-low rounded-xl p-8 flex flex-col justify-between hover:bg-white hover:editorial-shadow transition-all duration-300">
                <div>
                    <?php if ($p['category']): ?>
                    <span class="text-secondary font-label text-[10px] tracking-widest uppercase mb-3 block">
                        <?= htmlspecialchars($p['category']) ?>
                    </span>
                    <?php endif; ?>
                    <h3 class="font-headline text-xl font-bold text-primary mb-3">
                        <?= htmlspecialchars($p['title']) ?>
                    </h3>
                    <p class="text-sm text-on-surface-variant mb-5 line-clamp-3">
                        <?= htmlspecialchars(substr($p['abstract'], 0, 120)) ?>…
                    </p>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs font-medium text-secondary"><?= htmlspecialchars($p['author_name']) ?></span>
                </div>
            </div>
            <?php endfor; ?>

            <!-- If fewer than 2 papers, show call-for-manuscripts card -->
            <?php if (count($featured_papers) < 3): ?>
            <div class="md:col-span-4 bg-primary text-white rounded-xl p-8 flex flex-col justify-center items-center text-center">
                <span class="material-symbols-outlined text-4xl mb-4">auto_stories</span>
                <h3 class="font-headline text-2xl font-bold mb-4">Call for Manuscripts</h3>
                <p class="text-sm opacity-80 mb-6">Join our upcoming special edition on "The Future of Open Access."</p>
                <a href="auth/register.php" class="bg-white text-primary px-6 py-2 rounded-md font-bold text-sm hover:bg-opacity-90 transition">Get Started</a>
            </div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <!-- No papers yet — show call-to-action -->
        <div class="text-center py-20 bg-white rounded-2xl editorial-shadow border border-outline-variant/10">
            <span class="material-symbols-outlined text-8xl text-primary/20 mb-6 block">auto_stories</span>
            <h3 class="font-headline text-3xl font-bold text-primary mb-4">Be the First to Publish</h3>
            <p class="text-on-surface-variant mb-8 max-w-md mx-auto">No papers have been approved yet. Register and submit your research to get featured here.</p>
            <a href="auth/register.php" class="bg-gradient-primary text-white px-8 py-4 rounded-md font-bold shadow-xl hover:opacity-90 transition inline-block">
                Submit Your Research
            </a>
        </div>
        <?php endif; ?>
    </section>

    <!-- ── SEARCH SECTION ── -->
    <section id="search-section" class="bg-surface-container py-24">
        <div class="max-w-4xl mx-auto px-8 text-center">
            <h2 class="font-headline text-4xl font-bold text-primary mb-8">Ready to discover the next breakthrough?</h2>
            <form method="GET" action="auth/login.php" class="relative max-w-2xl mx-auto">
                <input
                    class="w-full px-8 py-5 rounded-full bg-white border-none shadow-xl focus:ring-2 focus:ring-primary/20 text-lg outline-none"
                    placeholder="Search by paper title, author, or topic…"
                    type="text" name="q"
                />
                <button type="submit" class="absolute right-3 top-3 bg-gradient-primary text-white p-3 rounded-full hover:opacity-90 transition">
                    <span class="material-symbols-outlined">search</span>
                </button>
            </form>
            <div class="mt-8 flex flex-wrap justify-center gap-4">
                <span class="text-secondary text-sm font-medium">Trending:</span>
                <a href="auth/login.php" class="text-sm text-primary underline decoration-primary/30 underline-offset-4 hover:decoration-primary">Machine Learning</a>
                <a href="auth/login.php" class="text-sm text-primary underline decoration-primary/30 underline-offset-4 hover:decoration-primary">Climate Policy</a>
                <a href="auth/login.php" class="text-sm text-primary underline decoration-primary/30 underline-offset-4 hover:decoration-primary">Space Exploration</a>
            </div>
        </div>
    </section>
</main>

<!-- ── FOOTER ── -->
<footer class="bg-slate-50 border-t border-slate-200 py-12">
    <div class="max-w-7xl mx-auto px-8 grid grid-cols-1 md:grid-cols-3 gap-8">
        <div>
            <span class="font-headline text-xl text-primary font-bold italic">Academic Archive</span>
            <p class="text-slate-500 text-sm max-w-xs leading-relaxed mt-3">Dedicated to the preservation and dissemination of rigorous academic inquiry for future generations.</p>
        </div>
        <div class="grid grid-cols-2 gap-6">
            <div class="flex flex-col gap-3">
                <span class="font-label text-xs tracking-wide uppercase font-bold text-primary">Access</span>
                <a href="auth/register.php" class="text-xs text-slate-500 hover:text-primary transition-colors">Submit Paper</a>
                <a href="auth/login.php"    class="text-xs text-slate-500 hover:text-primary transition-colors">Researcher Login</a>
            </div>
            <div class="flex flex-col gap-3">
                <span class="font-label text-xs tracking-wide uppercase font-bold text-primary">Info</span>
                <a href="#" class="text-xs text-slate-500 hover:text-primary transition-colors">Privacy Policy</a>
                <a href="#" class="text-xs text-slate-500 hover:text-primary transition-colors">Terms of Service</a>
            </div>
        </div>
        <div class="flex flex-col items-start md:items-end gap-4">
            <span class="font-label text-xs tracking-wide uppercase font-bold text-primary">Newsletter</span>
            <p class="text-xs text-slate-500">Receive monthly highlights from top research journals.</p>
        </div>
    </div>
    <div class="max-w-7xl mx-auto px-8 mt-10 pt-6 border-t border-slate-200 flex flex-col md:flex-row justify-between items-center gap-4">
        <span class="font-label text-[10px] tracking-wide uppercase text-slate-400">© <?= date('Y') ?> Academic Archive. All rights reserved.</span>
    </div>
</footer>

<script src="assets/js/app.js"></script>
</body>
</html>
