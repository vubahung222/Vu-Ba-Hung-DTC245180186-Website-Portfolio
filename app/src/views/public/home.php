<?php
/**
 * Trang chủ Portfolio
 * @var array $profile
 * @var array $skills
 * @var array $projects
 * @var array $education
 * @var array $experiences
 */
$initials = '';
if (!empty($profile['full_name'])) {
    $parts = explode(' ', $profile['full_name']);
    $initials = mb_strtoupper(mb_substr(end($parts), 0, 1, 'UTF-8'), 'UTF-8');
    if (count($parts) > 1) $initials = mb_strtoupper(mb_substr($parts[0], 0, 1, 'UTF-8'), 'UTF-8') . $initials;
}
?>

<!-- ── HERO ──────────────────────────────── -->
<section class="hero" aria-labelledby="hero-heading">
    <div class="container">
        <div class="hero-inner">
            <div class="hero-content animate-fadeInUp">
                <div class="hero-greeting">Xin chào, tôi là 👋</div>
                <h1 class="hero-name" id="hero-heading"><?= e($profile['full_name'] ?? '[HỌ TÊN]') ?></h1>
                <p class="hero-title"><?= e($profile['title'] ?? 'Sinh viên Công nghệ Thông tin') ?></p>
                <p class="hero-bio"><?= nl2br(e($profile['bio_short'] ?? '')) ?></p>

                <div class="hero-cta">
                    <a href="#projects" class="btn btn-primary btn-lg" id="hero-cta-projects">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7"/></svg>
                        Xem dự án
                    </a>
                    <a href="/lien-he" class="btn btn-outline btn-lg" id="hero-cta-contact">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Liên hệ
                    </a>
                </div>

                <?php if (!empty($profile['github_url']) && $profile['github_url'] !== '[GITHUB_URL]'): ?>
                <div class="hero-social">
                    <a href="<?= e($profile['github_url']) ?>" target="_blank" rel="noopener" class="social-chip" aria-label="GitHub của tôi">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.477 2 2 6.477 2 12c0 4.42 2.865 8.17 6.839 9.49.5.092.682-.217.682-.482 0-.237-.008-.866-.013-1.7-2.782.604-3.369-1.34-3.369-1.34-.454-1.156-1.11-1.464-1.11-1.464-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.578 9.578 0 0112 6.836c.85.004 1.705.115 2.504.337 1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.203 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.579.688.481C19.138 20.167 22 16.418 22 12c0-5.523-4.477-10-10-10z"/></svg>
                        GitHub
                    </a>
                    <?php if (!empty($profile['email'])): ?>
                    <a href="mailto:<?= e($profile['email']) ?>" class="social-chip">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <?= e($profile['email']) ?>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="hero-avatar animate-fadeInUp delay-200">
                <?php if (!empty($profile['avatar'])): ?>
                <img src="<?= uploads_url($profile['avatar']) ?>"
                     alt="Ảnh đại diện của <?= e($profile['full_name'] ?? '') ?>"
                     class="hero-img">
                <?php else: ?>
                <div class="hero-avatar-placeholder">
                    <?= e($initials ?: '?') ?>
                </div>
                <?php endif; ?>
                <?php if ($profile['is_available'] ?? false): ?>
                <div class="available-badge">
                    <span class="available-dot"></span>
                    Sẵn sàng nhận việc
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="hero-scroll" aria-hidden="true">
        <a href="#about" class="scroll-indicator">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>
        </a>
    </div>
</section>

<!-- ── ABOUT ──────────────────────────────── -->
<section class="section section-alt" id="about" aria-labelledby="about-heading">
    <div class="container">
        <div class="section-header">
            <span class="section-label">Về tôi</span>
            <h2 class="section-title" id="about-heading">Giới thiệu bản thân</h2>
        </div>
        <div class="about-layout">
            <div class="about-text">
                <?php
                $bioFull = $profile['bio_full'] ?? $profile['bio_short'] ?? 'Thông tin giới thiệu sẽ được cập nhật sớm.';
                foreach (explode("\n\n", $bioFull) as $para):
                    $para = trim($para);
                    if ($para):
                ?>
                <p><?= nl2br(e($para)) ?></p>
                <?php endif; endforeach; ?>
            </div>
            <div class="about-meta">
                <div class="about-info-grid">
                    <?php if (!empty($profile['location'])): ?>
                    <div class="about-info-item">
                        <span class="about-info-icon">📍</span>
                        <div>
                            <span class="about-info-label">Địa điểm</span>
                            <span class="about-info-value"><?= e($profile['location']) ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($profile['email'])): ?>
                    <div class="about-info-item">
                        <span class="about-info-icon">✉️</span>
                        <div>
                            <span class="about-info-label">Email</span>
                            <a href="mailto:<?= e($profile['email']) ?>" class="about-info-value"><?= e($profile['email']) ?></a>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($profile['phone'])): ?>
                    <div class="about-info-item">
                        <span class="about-info-icon">📞</span>
                        <div>
                            <span class="about-info-label">Điện thoại</span>
                            <a href="tel:<?= e($profile['phone']) ?>" class="about-info-value"><?= e($profile['phone']) ?></a>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($profile['github_url']) && $profile['github_url'] !== '[GITHUB_URL]'): ?>
                    <div class="about-info-item">
                        <span class="about-info-icon">🐙</span>
                        <div>
                            <span class="about-info-label">GitHub</span>
                            <a href="<?= e($profile['github_url']) ?>" target="_blank" rel="noopener" class="about-info-value">Xem profile</a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($profile['cv_url'])): ?>
                <a href="<?= e($profile['cv_url']) ?>" class="btn btn-primary mt-4" target="_blank" rel="noopener" id="download-cv">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Tải CV
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- ── SKILLS ─────────────────────────────── -->
<section class="section" id="skills" aria-labelledby="skills-heading">
    <div class="container">
        <div class="section-header">
            <span class="section-label">Kỹ năng</span>
            <h2 class="section-title" id="skills-heading">Năng lực chuyên môn</h2>
            <p class="section-desc">Các công nghệ và công cụ tôi sử dụng trong công việc hàng ngày</p>
        </div>

        <?php if (empty($skills)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">⚡</div>
            <p>Chưa có kỹ năng nào. Hãy thêm kỹ năng trong trang admin.</p>
        </div>
        <?php else: ?>
        <div class="skills-layout">
            <?php
            $categoryLabels = [
                'frontend' => '🎨 Frontend',
                'backend'  => '⚙️ Backend',
                'devops'   => '🐳 DevOps',
                'tool'     => '🔧 Công cụ',
                'language' => '💻 Ngôn ngữ',
                'other'    => '✨ Khác',
            ];
            foreach ($skills as $category => $catSkills):
                $label = $categoryLabels[$category] ?? ucfirst($category);
            ?>
            <div class="skill-category">
                <h3 class="skill-category-title"><?= e($label) ?></h3>
                <div class="skills-grid">
                    <?php foreach ($catSkills as $skill): ?>
                    <div class="skill-item">
                        <div class="skill-header">
                            <span class="skill-name"><?= e($skill['name']) ?></span>
                            <span class="skill-level"><?= (int)$skill['level'] ?>%</span>
                        </div>
                        <div class="skill-bar" role="progressbar"
                             aria-valuenow="<?= (int)$skill['level'] ?>"
                             aria-valuemin="0" aria-valuemax="100"
                             aria-label="<?= e($skill['name']) ?>: <?= (int)$skill['level'] ?>%">
                            <div class="skill-bar-fill" style="width: <?= (int)$skill['level'] ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ── PROJECTS ───────────────────────────── -->
<section class="section section-alt" id="projects" aria-labelledby="projects-heading">
    <div class="container">
        <div class="section-header">
            <span class="section-label">Dự án</span>
            <h2 class="section-title" id="projects-heading">Dự án nổi bật</h2>
            <p class="section-desc">Những dự án tôi đã thực hiện và đóng góp</p>
        </div>

        <?php if (empty($projects)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">🚀</div>
            <p>Chưa có dự án nào. Hãy thêm dự án trong trang admin.</p>
        </div>
        <?php else: ?>
        <div class="projects-grid">
            <?php foreach ($projects as $proj): ?>
            <?php $techs = Project::getTechStack($proj); ?>
            <article class="project-card card" aria-labelledby="proj-<?= $proj['id'] ?>-title">
                <div class="project-img-wrap">
                    <?php if (!empty($proj['image'])): ?>
                    <img src="<?= uploads_url($proj['image']) ?>"
                         alt="Ảnh dự án <?= e($proj['title']) ?>"
                         class="project-img" loading="lazy">
                    <?php else: ?>
                    <div class="project-img-placeholder">
                        <span aria-hidden="true">🚀</span>
                    </div>
                    <?php endif; ?>
                    <?php if ($proj['is_featured']): ?>
                    <span class="project-featured-badge">⭐ Nổi bật</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <h3 class="project-title" id="proj-<?= $proj['id'] ?>-title">
                        <?= e($proj['title']) ?>
                    </h3>
                    <p class="project-desc"><?= e(mb_substr($proj['description'], 0, 130, 'UTF-8')) ?><?= mb_strlen($proj['description'], 'UTF-8') > 130 ? '...' : '' ?></p>

                    <?php if ($techs): ?>
                    <div class="project-techs" aria-label="Công nghệ sử dụng">
                        <?php foreach (array_slice($techs, 0, 4) as $tech): ?>
                        <span class="tech-tag"><?= e($tech) ?></span>
                        <?php endforeach; ?>
                        <?php if (count($techs) > 4): ?>
                        <span class="tech-tag" aria-label="và <?= count($techs) - 4 ?> công nghệ khác">+<?= count($techs) - 4 ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <div class="project-links">
                        <a href="/du-an/<?= $proj['id'] ?>" class="btn btn-outline btn-sm" id="project-detail-<?= $proj['id'] ?>">
                            Chi tiết
                        </a>
                        <?php if (!empty($proj['github_url'])): ?>
                        <a href="<?= e($proj['github_url']) ?>" target="_blank" rel="noopener"
                           class="btn btn-ghost btn-sm" aria-label="Xem code trên GitHub">
                            <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.477 2 2 6.477 2 12c0 4.42 2.865 8.17 6.839 9.49.5.092.682-.217.682-.482 0-.237-.008-.866-.013-1.7-2.782.604-3.369-1.34-3.369-1.34-.454-1.156-1.11-1.464-1.11-1.464-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.578 9.578 0 0112 6.836c.85.004 1.705.115 2.504.337 1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.203 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.579.688.481C19.138 20.167 22 16.418 22 12c0-5.523-4.477-10-10-10z"/></svg>
                            GitHub
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($proj['demo_url'])): ?>
                        <a href="<?= e($proj['demo_url']) ?>" target="_blank" rel="noopener"
                           class="btn btn-ghost btn-sm" aria-label="Xem demo trực tuyến">
                            🔗 Demo
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-6">
            <a href="/du-an" class="btn btn-outline" id="view-all-projects">Xem tất cả dự án →</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ── TIMELINE (Education + Experience) ───── -->
<section class="section" id="timeline" aria-labelledby="timeline-heading">
    <div class="container">
        <div class="section-header">
            <span class="section-label">Hành trình</span>
            <h2 class="section-title" id="timeline-heading">Học vấn &amp; Kinh nghiệm</h2>
        </div>

        <div class="timeline-layout">
            <!-- Education -->
            <div class="timeline-col">
                <h3 class="timeline-col-title">
                    <span aria-hidden="true">🎓</span> Học vấn
                </h3>
                <?php if (empty($education)): ?>
                <p class="text-muted text-sm">Chưa có thông tin học vấn.</p>
                <?php else: ?>
                <div class="timeline">
                    <?php foreach ($education as $edu): ?>
                    <div class="timeline-item">
                        <div class="timeline-dot" aria-hidden="true"></div>
                        <div class="timeline-content">
                            <div class="timeline-period">
                                <?= e($edu['start_year']) ?> – <?= $edu['end_year'] ? e($edu['end_year']) : 'Hiện tại' ?>
                            </div>
                            <h4 class="timeline-title"><?= e($edu['degree']) ?></h4>
                            <p class="timeline-subtitle"><?= e($edu['institution']) ?></p>
                            <?php if (!empty($edu['field_of_study'])): ?>
                            <p class="timeline-desc"><?= e($edu['field_of_study']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($edu['gpa'])): ?>
                            <span class="badge badge-primary">GPA: <?= e($edu['gpa']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($edu['description'])): ?>
                            <p class="timeline-desc"><?= nl2br(e($edu['description'])) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Experience -->
            <div class="timeline-col">
                <h3 class="timeline-col-title">
                    <span aria-hidden="true">💼</span> Kinh nghiệm
                </h3>
                <?php if (empty($experiences)): ?>
                <p class="text-muted text-sm">Chưa có thông tin kinh nghiệm.</p>
                <?php else: ?>
                <div class="timeline">
                    <?php foreach ($experiences as $exp): ?>
                    <div class="timeline-item">
                        <div class="timeline-dot" aria-hidden="true"></div>
                        <div class="timeline-content">
                            <div class="timeline-period"><?= Experience::formatPeriod($exp) ?></div>
                            <h4 class="timeline-title"><?= e($exp['position']) ?></h4>
                            <p class="timeline-subtitle"><?= e($exp['company']) ?></p>
                            <?php if (!empty($exp['location'])): ?>
                            <p class="text-xs text-muted">📍 <?= e($exp['location']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($exp['description'])): ?>
                            <p class="timeline-desc"><?= nl2br(e($exp['description'])) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- ── CONTACT CTA ────────────────────────── -->
<section class="section section-alt" id="contact-cta" aria-labelledby="contact-cta-heading">
    <div class="container">
        <div class="cta-box">
            <h2 id="contact-cta-heading">Hãy cùng hợp tác! 🤝</h2>
            <p>Bạn có dự án thú vị hay cơ hội tốt? Tôi luôn sẵn sàng lắng nghe và trao đổi.</p>
            <div class="flex-center gap-4 mt-6">
                <a href="/lien-he" class="btn btn-primary btn-lg" id="cta-contact-btn">
                    Gửi tin nhắn cho tôi
                </a>
                <?php if (!empty($profile['github_url']) && $profile['github_url'] !== '[GITHUB_URL]'): ?>
                <a href="<?= e($profile['github_url']) ?>" target="_blank" rel="noopener" class="btn btn-outline btn-lg">
                    Xem GitHub
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
