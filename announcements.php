<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_login('Please sign in with your NSBM student account to view official university announcements and circulars.');

$page_title = "Campus Announcements & Circulars";
require_once __DIR__ . '/includes/header.php';

$priority_filter = $_GET['priority'] ?? '';

$sql = "SELECT * FROM announcements";
$params = [];

if (in_array($priority_filter, ['urgent', 'important', 'normal'])) {
    $sql .= " WHERE priority = ?";
    $params[] = $priority_filter;
}

$sql .= " ORDER BY FIELD(priority, 'urgent', 'important', 'normal'), created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$announcements = $stmt->fetchAll();
?>

<div class="container py-4">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-3 border-bottom gap-3">
        <div>
            <h2 class="fw-bold text-navy mb-1">
                <i class="bi bi-megaphone text-danger me-2"></i> Official University Announcements
            </h2>
            <p class="text-muted small mb-0">Official circulars, administrative directives, library notices, and academic deadlines.</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="announcements.php" class="btn btn-sm <?php echo empty($priority_filter) ? 'btn-navy' : 'btn-outline-secondary'; ?> rounded-pill px-3">
                All Notices
            </a>
            <a href="announcements.php?priority=urgent" class="btn btn-sm <?php echo ($priority_filter === 'urgent') ? 'btn-danger' : 'btn-outline-danger'; ?> rounded-pill px-3">
                <i class="bi bi-exclamation-circle-fill me-1"></i> Urgent
            </a>
            <a href="announcements.php?priority=important" class="btn btn-sm <?php echo ($priority_filter === 'important') ? 'btn-warning text-dark' : 'btn-outline-warning text-dark'; ?> rounded-pill px-3">
                <i class="bi bi-flag-fill me-1"></i> Important
            </a>
            <a href="announcements.php?priority=normal" class="btn btn-sm <?php echo ($priority_filter === 'normal') ? 'btn-success' : 'btn-outline-success'; ?> rounded-pill px-3">
                <i class="bi bi-info-circle-fill me-1"></i> Normal
            </a>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <?php if (!empty($announcements)): ?>
            <?php foreach ($announcements as $ann): ?>
                <?php
                    $border_color = ($ann['priority'] === 'urgent') ? 'border-danger' : (($ann['priority'] === 'important') ? 'border-warning' : 'border-success');
                    $badge_class = ($ann['priority'] === 'urgent') ? 'bg-danger' : (($ann['priority'] === 'important') ? 'bg-warning text-dark' : 'bg-success');
                    $icon_class = ($ann['priority'] === 'urgent') ? 'bi-exclamation-triangle-fill text-danger' : (($ann['priority'] === 'important') ? 'bi-shield-exclamation text-warning' : 'bi-info-circle-fill text-success');
                ?>
                <div class="col-lg-12">
                    <div class="card border-0 shadow-sm rounded-4 border-start border-4 <?php echo $border_color; ?> p-3 p-md-4">
                        <div class="card-body p-0">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge <?php echo $badge_class; ?> text-uppercase px-3 py-1">
                                        <?php echo htmlspecialchars($ann['priority']); ?>
                                    </span>
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi bi-people me-1"></i> Audience: <?php echo htmlspecialchars($ann['target_audience'] ?? 'All Students'); ?>
                                    </span>
                                </div>
                                <div class="text-muted small">
                                    <i class="bi bi-calendar-event me-1"></i> Published: <?php echo format_date($ann['created_at'], true); ?>
                                </div>
                            </div>

                            <h4 class="card-title fw-bold text-navy mb-2">
                                <?php echo htmlspecialchars($ann['title']); ?>
                            </h4>

                            <p class="card-text text-dark lh-base fs-6 mb-0">
                                <?php echo nl2br(htmlspecialchars($ann['content'])); ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-light border text-center py-5 rounded-4 shadow-sm">
                    <i class="bi bi-bell-slash display-4 text-muted d-block mb-3"></i>
                    <h5 class="fw-bold">No announcements found</h5>
                    <p class="text-muted small">There are currently no active announcements matching your selected criteria.</p>
                    <a href="announcements.php" class="btn btn-outline-secondary btn-sm rounded-pill px-4">View All</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

