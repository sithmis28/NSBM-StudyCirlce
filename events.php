<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_login('Please sign in with your NSBM student account to explore campus events and conferences.');

$page_title = "Campus Events & Conferences";
require_once __DIR__ . '/includes/header.php';

try {
    $events_stmt = $pdo->query("SELECT * FROM events ORDER BY event_date ASC");
    $events = $events_stmt->fetchAll();
} catch (Exception $e) {
    $events = [];
}
?>

<div class="container py-4">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-3 border-bottom gap-3">
        <div>
            <h2 class="fw-bold text-navy mb-1">
                <i class="bi bi-calendar-event text-success me-2"></i> Campus Events & Research Conferences
            </h2>
            <p class="text-muted small mb-0">Upcoming international research symposia, student competitions, and green campus cultural fests.</p>
        </div>
        <div>
            <a href="user/submit_article.php" class="btn btn-outline-success btn-sm rounded-pill px-3">
                <i class="bi bi-plus-lg me-1"></i> Submit Club Event
            </a>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <?php if (!empty($events)): ?>
            

<?php foreach ($events as $ev): ?>
<?php
$date = $ev['event_date'];
$today = date('Y-m-d');
$isToday = ($date === $today);
$isPast = ($date < $today);

$status = $isToday ? 'Today' : ($isPast ? 'Completed' : 'Upcoming');
?>

<div class="col-12 timeline-item" data-status="<?php echo strtolower($status); ?>">
    <div class="d-flex gap-3 gap-md-4">

        <div class="timeline-date-column">
            <div class="timeline-date <?php echo $isToday ? 'today' : ''; ?>">
                <span class="date-day"><?php echo date('d', strtotime($date)); ?></span>
                <span class="date-month"><?php echo date('M', strtotime($date)); ?></span>
                <span class="date-year"><?php echo date('Y', strtotime($date)); ?></span>
            </div>
            <div class="timeline-line"></div>
        </div>

        <div class="timeline-card flex-grow-1 mb-4">

            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="event-badge">
                    <i class="bi bi-clock me-1"></i>
                    <?php echo date('h:i A', strtotime($ev['start_time'] ?? '09:00:00')); ?>
                </span>

                <span class="event-badge">
                    <i class="bi bi-geo-alt me-1"></i>
                    <?php echo htmlspecialchars($ev['location'] ?? 'NSBM Green University'); ?>
                </span>

                <span class="event-status status-<?php echo strtolower($status); ?>">
                    <?php echo $status; ?>
                </span>
            </div>

            <h4 class="event-title">
                <?php echo htmlspecialchars($ev['title']); ?>
            </h4>

            <p class="event-description">
                <?php echo nl2br(htmlspecialchars($ev['description'] ?? '')); ?>
            </p>

            <?php if (!$isPast && !$isToday): ?>
                <?php
                $daysLeft = (int) floor(
                    (strtotime($date) - strtotime($today)) / 86400
                );
                ?>
                <div class="event-countdown">
                    <i class="bi bi-hourglass-split me-1"></i>
                    <?php echo $daysLeft === 1 ? 'Tomorrow' : $daysLeft . ' days to go'; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php endforeach; ?>


        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-light border text-center py-5 rounded-4 shadow-sm">
                    <i class="bi bi-calendar-x display-4 text-muted d-block mb-3"></i>
                    <h5 class="fw-bold">No upcoming events scheduled</h5>
                    <p class="text-muted small">Stay tuned for new event announcements from NSBM societies.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

