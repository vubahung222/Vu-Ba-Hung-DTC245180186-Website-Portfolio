<section class="section" id="project-detail">
    <div class="container">
        <div style="margin-bottom:var(--space-6);">
            <a href="/du-an" class="btn btn-ghost btn-sm">← Tất cả dự án</a>
        </div>

        <div class="project-detail-layout">
            <!-- Main -->
            <div>
                <?php if (!empty($project['image'])): ?>
                <img src="<?= uploads_url($project['image']) ?>"
                     alt="Ảnh dự án <?= e($project['title']) ?>"
                     style="width:100%;border-radius:var(--radius-xl);border:1px solid var(--color-border);margin-bottom:var(--space-6);">
                <?php endif; ?>

                <h1 style="margin-bottom:var(--space-4);"><?= e($project['title']) ?></h1>

                <?php if ($project['is_featured']): ?>
                <span class="badge badge-success" style="margin-bottom:var(--space-4);">⭐ Dự án nổi bật</span>
                <?php endif; ?>

                <div style="color:var(--color-text-muted);line-height:1.8;font-size:1.0625rem;">
                    <?php foreach (explode("\n\n", $project['description']) as $para): ?>
                    <?php $para = trim($para); if ($para): ?>
                    <p style="margin-bottom:var(--space-4);"><?= nl2br(e($para)) ?></p>
                    <?php endif; endforeach; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div>
                <div class="card card-body" style="margin-bottom:var(--space-5);">
                    <h3 style="margin-bottom:var(--space-4);">🔗 Liên kết</h3>
                    <div style="display:flex;flex-direction:column;gap:var(--space-3);">
                        <?php if (!empty($project['github_url'])): ?>
                        <a href="<?= e($project['github_url']) ?>" target="_blank" rel="noopener"
                           class="btn btn-outline w-full" style="justify-content:center;">
                            🐙 Xem trên GitHub
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($project['demo_url'])): ?>
                        <a href="<?= e($project['demo_url']) ?>" target="_blank" rel="noopener"
                           class="btn btn-primary w-full" style="justify-content:center;">
                            🌐 Xem Demo
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($techs): ?>
                <div class="card card-body">
                    <h3 style="margin-bottom:var(--space-4);">⚙️ Công nghệ</h3>
                    <div style="display:flex;flex-wrap:wrap;gap:var(--space-2);">
                        <?php foreach ($techs as $t): ?>
                        <span class="tech-tag"><?= e($t) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="card card-body" style="margin-top:var(--space-5);">
                    <h3 style="margin-bottom:var(--space-3);">📅 Thông tin</h3>
                    <div style="font-size:.875rem;color:var(--color-text-muted);">
                        <div style="margin-bottom:var(--space-2);">
                            <span class="font-bold">Ngày tạo:</span>
                            <?= date('d/m/Y', strtotime($project['created_at'])) ?>
                        </div>
                        <div>
                            <span class="font-bold">Cập nhật:</span>
                            <?= date('d/m/Y', strtotime($project['updated_at'])) ?>
                        </div>
                    </div>
                </div>

                <div style="margin-top:var(--space-6);">
                    <a href="/lien-he" class="btn btn-primary w-full" style="justify-content:center;">
                        💬 Liên hệ về dự án này
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
