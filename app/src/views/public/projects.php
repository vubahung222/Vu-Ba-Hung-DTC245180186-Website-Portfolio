<section class="section" id="projects-page">
    <div class="container">
        <div class="section-header">
            <span class="section-label">Dự án</span>
            <h1 class="section-title">Tất cả dự án</h1>
            <p class="section-desc">Danh sách đầy đủ các dự án tôi đã thực hiện</p>
        </div>

        <?php if (empty($projects)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">🚀</div>
            <p>Chưa có dự án nào được thêm vào.</p>
        </div>
        <?php else: ?>

        <!-- Filter buttons -->
        <div class="projects-filter" id="filterBar" role="toolbar" aria-label="Lọc theo công nghệ">
            <button class="filter-btn active" data-filter="all">Tất cả (<?= count($projects) ?>)</button>
            <?php
            $allTechs = [];
            foreach ($projects as $p) {
                foreach (Project::getTechStack($p) as $t) {
                    $key = strtolower($t);
                    $allTechs[$key] = ($allTechs[$key] ?? 0) + 1;
                }
            }
            arsort($allTechs);
            foreach (array_slice($allTechs, 0, 8, true) as $tech => $count):
            ?>
            <button class="filter-btn" data-filter="<?= e($tech) ?>"><?= e(ucfirst($tech)) ?> (<?= $count ?>)</button>
            <?php endforeach; ?>
        </div>

        <div class="projects-grid" id="projectsGrid">
            <?php foreach ($projects as $proj): ?>
            <?php $techs = Project::getTechStack($proj); $techStr = implode(' ', array_map('strtolower', $techs)); ?>
            <article class="project-card card" data-techs="<?= e($techStr) ?>" aria-labelledby="p<?= $proj['id'] ?>">
                <div class="project-img-wrap">
                    <?php if (!empty($proj['image'])): ?>
                    <img src="<?= uploads_url($proj['image']) ?>"
                         alt="Ảnh dự án <?= e($proj['title']) ?>"
                         class="project-img" loading="lazy">
                    <?php else: ?>
                    <div class="project-img-placeholder"><span>🚀</span></div>
                    <?php endif; ?>
                    <?php if ($proj['is_featured']): ?>
                    <span class="project-featured-badge">⭐ Nổi bật</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <h2 class="project-title" id="p<?= $proj['id'] ?>"><?= e($proj['title']) ?></h2>
                    <p class="project-desc"><?= e(mb_substr($proj['description'], 0, 150, 'UTF-8')) ?><?= mb_strlen($proj['description'], 'UTF-8') > 150 ? '...' : '' ?></p>
                    <?php if ($techs): ?>
                    <div class="project-techs">
                        <?php foreach (array_slice($techs, 0, 5) as $t): ?>
                        <span class="tech-tag"><?= e($t) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <div class="project-links">
                        <a href="/du-an/<?= $proj['id'] ?>" class="btn btn-outline btn-sm">Chi tiết</a>
                        <?php if (!empty($proj['github_url'])): ?>
                        <a href="<?= e($proj['github_url']) ?>" target="_blank" rel="noopener" class="btn btn-ghost btn-sm">🐙 GitHub</a>
                        <?php endif; ?>
                        <?php if (!empty($proj['demo_url'])): ?>
                        <a href="<?= e($proj['demo_url']) ?>" target="_blank" rel="noopener" class="btn btn-ghost btn-sm">🔗 Demo</a>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
// Filter logic
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const filter = btn.dataset.filter;
        document.querySelectorAll('#projectsGrid .project-card').forEach(card => {
            if (filter === 'all' || card.dataset.techs.includes(filter)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    });
});
</script>
