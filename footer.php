<?php
$root = get_root_prefix();
$user_prefix = (strpos($_SERVER['PHP_SELF'] ?? '', '/user/') !== false) ? '' : 'user/';
?>

    <footer class="mt-auto">
        <div class="footer-top">
            <div class="container">
                <div class="row g-4">

                    <div class="col-lg-4 col-md-6">
                        <div class="d-flex align-items-center mb-3">
                            <img src="<?php echo $root; ?>assets/images/logo_nsbm.png" alt="NSBM Logo" style="height: 44px; filter: brightness(0) invert(1);">
                            <div class="ms-2 ps-2 border-start border-light-subtle">
                                <h5 class="text-white mb-0 fw-bold">NewsDesk</h5>
                                <small class="text-success fw-semibold">Campus News Portal</small>
                            </div>
                        </div>
                        <p class="text-light-subtle small mb-3">
                            The central digital news and broadcast platform for <strong>NSBM Green University</strong>. Delivering timely campus headlines, academic milestones, student research innovations, and university event updates.
                        </p>
                        <div class="d-flex gap-2">
                            <a href="https://www.facebook.com/nsbm.lk" target="_blank" class="btn btn-outline-light btn-sm rounded-circle" style="width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center;"><i class="bi bi-facebook"></i></a>
                            <a href="https://www.instagram.com/nsbmgreenuniversity/" target="_blank" class="btn btn-outline-light btn-sm rounded-circle" style="width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center;"><i class="bi bi-instagram"></i></a>
                            <a href="https://www.linkedin.com/company/nsbm-green-university-town/" target="_blank" class="btn btn-outline-light btn-sm rounded-circle" style="width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center;"><i class="bi bi-linkedin"></i></a>
                            <a href="https://www.youtube.com/channel/UCHsodhRyiuri2jD7H7nfsRg" target="_blank" class="btn btn-outline-light btn-sm rounded-circle" style="width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center;"><i class="bi bi-youtube"></i></a>
                        </div>
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <h6 class="footer-title">Faculties</h6>
                        <ul class="footer-links">
                            <li><a href="<?php echo $root; ?>category.php?slug=computing-tech"><i class="bi bi-chevron-right me-1 small"></i> Computing</a></li>
                            <li><a href="<?php echo $root; ?>category.php?slug=academic"><i class="bi bi-chevron-right me-1 small"></i> Business</a></li>
                            <li><a href="<?php echo $root; ?>category.php?slug=academic"><i class="bi bi-chevron-right me-1 small"></i> Engineering</a></li>
                            <li><a href="<?php echo $root; ?>category.php?slug=academic"><i class="bi bi-chevron-right me-1 small"></i> Science</a></li>
                            <li><a href="<?php echo $root; ?>category.php?slug=academic"><i class="bi bi-chevron-right me-1 small"></i> Postgraduate</a></li>
                        </ul>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <h6 class="footer-title">Quick Links</h6>
                        <ul class="footer-links">
                            <?php if (!is_logged_in()): ?>
                                <li><a href="<?php echo $root; ?>index.php"><i class="bi bi-chevron-right me-1 small"></i> Welcome & Overview</a></li>
                                <li><a href="<?php echo $root; ?>login.php"><i class="bi bi-chevron-right me-1 small"></i> Student & Staff Sign In</a></li>
                                <li><a href="<?php echo $root; ?>register.php"><i class="bi bi-chevron-right me-1 small"></i> Student Registration</a></li>
                                <li><a href="<?php echo $root; ?>login.php?redirect=index.php"><i class="bi bi-chevron-right me-1 small"></i> Access News Feed</a></li>
                                <li><a href="<?php echo $root; ?>login.php?redirect=announcements.php"><i class="bi bi-chevron-right me-1 small"></i> University Announcements</a></li>
                            <?php else: ?>
                                <li><a href="<?php echo $root; ?>landing.php"><i class="bi bi-chevron-right me-1 small"></i> Welcome / Overview</a></li>
                                <li><a href="<?php echo $root; ?>index.php"><i class="bi bi-chevron-right me-1 small"></i> Live News Feed</a></li>
                                <li><a href="<?php echo $root; ?>news.php"><i class="bi bi-chevron-right me-1 small"></i> All Campus Stories</a></li>
                                <li><a href="<?php echo $root; ?>announcements.php"><i class="bi bi-chevron-right me-1 small"></i> University Announcements</a></li>
                                <li><a href="<?php echo $root; ?>events.php"><i class="bi bi-chevron-right me-1 small"></i> Upcoming Events</a></li>
                                <li><a href="<?php echo $user_prefix; ?>submit_article.php"><i class="bi bi-chevron-right me-1 small"></i> Student Story Submission</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <h6 class="footer-title">Contact University</h6>
                        <ul class="footer-links small">
                            <li class="text-light-subtle d-flex align-items-start mb-2">
                                <i class="bi bi-geo-alt-fill text-success me-2 fs-6"></i>
                                <span>NSBM Green University Town, Mahenwaththa, Pitipana, Homagama, Sri Lanka.</span>
                            </li>
                            <li class="text-light-subtle d-flex align-items-center mb-2">
                                <i class="bi bi-telephone-fill text-success me-2 fs-6"></i>
                                <span>+94 11 544 5000 / +94 71 244 5000</span>
                            </li>
                            <li class="text-light-subtle d-flex align-items-center mb-2">
                                <i class="bi bi-envelope-fill text-success me-2 fs-6"></i>
                                <span>inquiry@gmail.com</span>
                            </li>
                            <li class="text-light-subtle d-flex align-items-center">
                                <i class="bi bi-globe text-success me-2 fs-6"></i>
                                <span>www.nsbm.ac.lk</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                <div>
                    &copy; <?php echo date('Y'); ?> <strong>NSBM Green University</strong>. All Rights Reserved.
                </div>
                <div class="text-muted small">
                    NSBM Green University &mdash; Official Campus News & Media Portal
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="<?php echo $root; ?>assets/js/main.js?v=<?php echo @filemtime(__DIR__ . '/../assets/js/main.js') ?: time(); ?>"></script>
</body>
</html>

