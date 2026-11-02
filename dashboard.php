
<?php
$admin_page_title = "Overview Dashboard";
require_once __DIR__ . '/includes/admin_header.php';

$total_news = $pdo->query("SELECT COUNT(*) FROM news WHERE status = 'published'")->fetchColumn();
$total_views = $pdo->query("SELECT COALESCE(SUM(views), 0) FROM news")->fetchColumn();
$total_students = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$total_comments = $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();
$pending_submissions = $pdo->query("SELECT COUNT(*) FROM article_submissions WHERE status = 'pending'")->fetchColumn();
$pending_comments = $pdo->query("SELECT COUNT(*) FROM comments WHERE status = 'pending'")->fetchColumn();

$cat_chart_stmt = $pdo->query("
    SELECT c.name, COUNT(n.id) AS article_count, COALESCE(SUM(n.views), 0) AS total_views
    FROM categories c
    LEFT JOIN news n ON n.category_id = c.id AND n.status = 'published'
    GROUP BY c.id, c.name
");
$cat_data = $cat_chart_stmt->fetchAll();
$chart_labels = [];
$chart_views = [];
$chart_articles = [];
foreach ($cat_data as $row) {
    $chart_labels[] = $row['name'];
    $chart_views[] = (int)$row['total_views'];
    $chart_articles[] = (int)$row['article_count'];
}

$pending_list_stmt = $pdo->query("
    SELECT a.*, c.name AS category_name, u.name AS student_name, u.student_id
    FROM article_submissions a
    JOIN categories c ON a.category_id = c.id
    JOIN users u ON a.user_id = u.id
    WHERE a.status = 'pending'
    ORDER BY a.submitted_at DESC
    LIMIT 5
");
$pending_list = $pending_list_stmt->fetchAll();

$top_news_stmt = $pdo->query("
    SELECT n.*, c.name AS category_name
    FROM news n
    LEFT JOIN categories c ON n.category_id = c.id
    WHERE n.status = 'published'
    ORDER BY n.views DESC
    LIMIT 5
");
$top_news = $top_news_stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold text-navy mb-1">Administrative Overview</h2>
        <p class="text-muted small mb-0">System health, readership traffic, student submissions, and moderation queue.</p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <a href="news/create.php" class="btn btn-nsbm rounded-pill px-4 shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> New Article
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success admin-card-stat">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Published News</span>
                    <h3 class="fw-bold text-navy mb-0" data-dashboard-stat="publishedNews"><?php echo number_format($total_news); ?></h3>
                </div>
                <div class="bg-success-subtle text-success p-3 rounded-circle">
                    <i class="bi bi-newspaper fs-4"></i>
                </div>
            </div>
            <small class="text-muted mt-2 d-block"><i class="bi bi-check2-circle text-success me-1"></i> Live on Campus Desk</small>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary admin-card-stat">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Total Views</span>
                    <h3 class="fw-bold text-navy mb-0" data-dashboard-stat="totalViews"><?php echo number_format($total_views); ?></h3>
                </div>
                <div class="bg-primary-subtle text-primary p-3 rounded-circle">
                    <i class="bi bi-eye-fill fs-4"></i>
                </div>
            </div>
            <small class="text-muted mt-2 d-block"><i class="bi bi-graph-up text-primary me-1"></i> Live Views Count</small>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-warning admin-card-stat">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Pending Submissions</span>
                    <h3 class="fw-bold text-navy mb-0" data-dashboard-stat="pendingSubmissions"><?php echo number_format($pending_submissions); ?></h3>
                </div>
                <div class="bg-warning-subtle text-warning p-3 rounded-circle">
                    <i class="bi bi-hourglass-split fs-4"></i>
                </div>
            </div>
            <small class="text-muted mt-2 d-block">
                <a href="submissions/index.php" class="text-warning-emphasis text-decoration-none fw-semibold">Review submissions &rarr;</a>
            </small>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-info admin-card-stat">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Registered Students</span>
                    <h3 class="fw-bold text-navy mb-0" data-dashboard-stat="registeredStudents"><?php echo number_format($total_students); ?></h3>
                </div>
                <div class="bg-info-subtle text-info p-3 rounded-circle">
                    <i class="bi bi-people-fill fs-4"></i>
                </div>
            </div>
            <small class="text-muted mt-2 d-block"><i class="bi bi-person-badge text-info me-1"></i> Across All 4 Faculties</small>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <h5 class="fw-bold text-navy mb-0">
                    <i class="bi bi-bar-chart-line text-success me-2"></i> Category Engagement & Traffic
                </h5>
                <a href="reports/index.php" class="small text-muted text-decoration-none">Detailed Report &rarr;</a>
            </div>
            <div style="height: 280px; position: relative;">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <h5 class="fw-bold text-navy mb-3 pb-2 border-bottom">
                <i class="bi bi-trophy-fill text-warning me-2"></i> Top Read Stories
            </h5>
            <div class="d-flex flex-column gap-3">
                <?php foreach ($top_news as $idx => $tn): ?>
                    <div class="d-flex align-items-center gap-3">
                        <span class="badge rounded-circle bg-light text-navy border fs-6 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <?php echo $idx + 1; ?>
                        </span>
                        <div class="flex-grow-1 overflow-hidden">
                            <h6 class="mb-0 text-truncate" style="font-size: 0.88rem;">
                                <a href="news/edit.php?id=<?php echo $tn['id']; ?>" class="text-navy text-decoration-none fw-semibold">
                                    <?php echo htmlspecialchars($tn['title']); ?>
                                </a>
                            </h6>
                            <small class="text-muted"><i class="bi bi-eye"></i> <?php echo number_format($tn['views']); ?> views</small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
        <h5 class="fw-bold text-navy mb-0">
            <i class="bi bi-file-earmark-diff text-warning me-2"></i> Student Submissions Awaiting Approval
        </h5>
        <a href="submissions/index.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">View Queue</a>
    </div>

    <?php if (!empty($pending_list)): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Headline</th>
                        <th>Student Author</th>
                        <th>Category</th>
                        <th>Submitted At</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_list as $item): ?>
                        <tr>
                            <td>
                                <span class="fw-semibold text-navy"><?php echo htmlspecialchars($item['title']); ?></span>
                            </td>
                            <td>
                                <div><?php echo htmlspecialchars($item['student_name']); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($item['student_id'] ?? 'Student'); ?></small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($item['category_name']); ?></span>
                            </td>
                            <td>
                                <small class="text-muted"><?php echo format_date($item['submitted_at'], true); ?></small>
                            </td>
                            <td class="text-end">
                                <a href="submissions/index.php?action=approve&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-success rounded-pill px-3 me-1">
                                    <i class="bi bi-check2"></i> Approve
                                </a>
                                <a href="submissions/index.php?action=reject&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                    <i class="bi bi-x"></i> Reject
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center py-4 text-muted">
            <i class="bi bi-check-circle-fill text-success fs-3 d-block mb-2"></i>
            <p class="mb-0 small">Great job! The student submission queue is completely clear.</p>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const statElements = {
        publishedNews: document.querySelector('[data-dashboard-stat="publishedNews"]'),
        totalViews: document.querySelector('[data-dashboard-stat="totalViews"]'),
        pendingSubmissions: document.querySelector('[data-dashboard-stat="pendingSubmissions"]'),
        registeredStudents: document.querySelector('[data-dashboard-stat="registeredStudents"]')
    };
    const numberFormatter = new Intl.NumberFormat();

    async function refreshDashboardStats() {
        try {
            const response = await fetch('dashboard_stats.php', {
                headers: { Accept: 'application/json' },
                cache: 'no-store',
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error('Dashboard stats request failed with status ' + response.status);
            }

            const stats = await response.json();
            Object.keys(statElements).forEach(function (key) {
                if (!Number.isSafeInteger(stats[key]) || stats[key] < 0) {
                    throw new Error('Invalid dashboard stat received: ' + key);
                }
            });

            Object.keys(statElements).forEach(function (key) {
                if (statElements[key]) {
                    statElements[key].textContent = numberFormatter.format(stats[key]);
                }
            });
        } catch (error) {
            console.error('Unable to refresh admin dashboard stats:', error);
        } finally {
            window.setTimeout(refreshDashboardStats, 15000);
        }
    }

    refreshDashboardStats();
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('categoryChart');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    function isDarkTheme() {
        return document.documentElement.getAttribute('data-bs-theme') === 'dark';
    }

    function getChartThemeColors() {
        const isDark = isDarkTheme();
        return {
            textColor: isDark ? '#e2e8f0' : '#1e293b',
            gridColor: isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.06)',
            viewsBg: isDark ? 'rgba(16, 185, 129, 0.85)' : 'rgba(0, 135, 81, 0.85)',
            viewsBorder: isDark ? '#34d399' : '#005f38',
            articlesBg: isDark ? 'rgba(56, 189, 248, 0.85)' : 'rgba(30, 58, 138, 0.85)',
            articlesBorder: isDark ? '#38bdf8' : '#1e3a8a'
        };
    }

    let colors = getChartThemeColors();

    const chartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($chart_labels); ?>,
            datasets: [{
                label: 'Readership Views',
                data: <?php echo json_encode($chart_views); ?>,
                backgroundColor: colors.viewsBg,
                borderColor: colors.viewsBorder,
                borderWidth: 1.5,
                borderRadius: 6
            }, {
                label: 'Published Articles',
                data: <?php echo json_encode($chart_articles); ?>,
                backgroundColor: colors.articlesBg,
                borderColor: colors.articlesBorder,
                borderWidth: 1.5,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        color: colors.textColor,
                        font: { weight: '600', size: 12 },
                        padding: 16
                    }
                },
                tooltip: {
                    backgroundColor: isDarkTheme() ? '#0f172a' : '#1e293b',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    borderColor: colors.gridColor,
                    borderWidth: 1
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: colors.textColor,
                        font: { weight: '600', size: 11 }
                    },
                    grid: {
                        color: colors.gridColor
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: colors.textColor,
                        font: { weight: '600', size: 11 },
                        precision: 0
                    },
                    grid: {
                        color: colors.gridColor
                    }
                }
            }
        }
    });

    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            if (mutation.attributeName === 'data-bs-theme') {
                const newColors = getChartThemeColors();
                chartInstance.data.datasets[0].backgroundColor = newColors.viewsBg;
                chartInstance.data.datasets[0].borderColor = newColors.viewsBorder;
                chartInstance.data.datasets[1].backgroundColor = newColors.articlesBg;
                chartInstance.data.datasets[1].borderColor = newColors.articlesBorder;
                chartInstance.options.plugins.legend.labels.color = newColors.textColor;
                chartInstance.options.scales.x.ticks.color = newColors.textColor;
                chartInstance.options.scales.x.grid.color = newColors.gridColor;
                chartInstance.options.scales.y.ticks.color = newColors.textColor;
                chartInstance.options.scales.y.grid.color = newColors.gridColor;
                chartInstance.update('none');
            }
        });
    });
    observer.observe(document.documentElement, { attributes: true });
});
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
