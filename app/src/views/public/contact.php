<!-- Contact Page -->
<section class="section" id="contact-page" aria-labelledby="contact-heading">
    <div class="container">
        <div class="section-header">
            <span class="section-label">Liên hệ</span>
            <h1 class="section-title" id="contact-heading">Hãy kết nối với tôi</h1>
            <p class="section-desc">Có dự án thú vị hay câu hỏi nào? Tôi sẵn sàng lắng nghe và phản hồi sớm nhất.</p>
        </div>

        <?php if ($success = \Core\Session::getFlash('contact_success')): ?>
        <div class="alert alert-success animate-fadeIn" role="alert" style="max-width:600px;margin:0 auto var(--space-6);">
            ✅ <?= e($success) ?>
        </div>
        <?php endif; ?>

        <?php if ($error = \Core\Session::getFlash('contact_error')): ?>
        <div class="alert alert-error animate-fadeIn" role="alert" style="max-width:600px;margin:0 auto var(--space-6);">
            ❌ <?= e($error) ?>
        </div>
        <?php endif; ?>

        <div class="contact-layout">
            <!-- Info Column -->
            <div class="contact-info">
                <?php if (!empty($profile['email'])): ?>
                <div class="contact-info-card">
                    <div class="contact-info-icon">✉️</div>
                    <div>
                        <h3 style="font-size:1rem;margin-bottom:4px;">Email</h3>
                        <a href="mailto:<?= e($profile['email']) ?>" class="text-primary"><?= e($profile['email']) ?></a>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($profile['phone'])): ?>
                <div class="contact-info-card">
                    <div class="contact-info-icon">📞</div>
                    <div>
                        <h3 style="font-size:1rem;margin-bottom:4px;">Điện thoại</h3>
                        <a href="tel:<?= e($profile['phone']) ?>" class="text-primary"><?= e($profile['phone']) ?></a>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($profile['location'])): ?>
                <div class="contact-info-card">
                    <div class="contact-info-icon">📍</div>
                    <div>
                        <h3 style="font-size:1rem;margin-bottom:4px;">Địa điểm</h3>
                        <p class="text-muted" style="margin:0"><?= e($profile['location']) ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($profile['github_url']) && $profile['github_url'] !== '[GITHUB_URL]'): ?>
                <div class="contact-info-card">
                    <div class="contact-info-icon">🐙</div>
                    <div>
                        <h3 style="font-size:1rem;margin-bottom:4px;">GitHub</h3>
                        <a href="<?= e($profile['github_url']) ?>" target="_blank" rel="noopener" class="text-primary">Xem profile GitHub →</a>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($profile['is_available'] ?? false): ?>
                <div class="contact-info-card" style="background:var(--color-success-bg);border-color:#A7F3D0;">
                    <div class="contact-info-icon" style="background:rgba(5,150,105,.1);">✅</div>
                    <div>
                        <h3 style="font-size:1rem;margin-bottom:4px;color:var(--color-success)">Sẵn sàng nhận việc</h3>
                        <p style="margin:0;font-size:.875rem;color:var(--color-text-muted)">Đang tìm kiếm cơ hội phù hợp</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Form Column -->
            <div class="contact-form-card">
                <h2 style="font-size:1.25rem;margin-bottom:var(--space-6);">📝 Gửi tin nhắn</h2>
                <form action="/lien-he/gui" method="POST" id="contactForm" novalidate>
                    <?= csrf_field() ?>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="sender_name" class="form-label">Họ và tên <span class="required">*</span></label>
                            <input type="text" id="sender_name" name="sender_name"
                                   class="form-control" required
                                   value="<?= e(\Core\Session::getFlash('old_sender_name', '')) ?>"
                                   placeholder="Nguyễn Văn A"
                                   aria-required="true">
                        </div>
                        <div class="form-group">
                            <label for="sender_email" class="form-label">Email <span class="required">*</span></label>
                            <input type="email" id="sender_email" name="sender_email"
                                   class="form-control" required
                                   value="<?= e(\Core\Session::getFlash('old_sender_email', '')) ?>"
                                   placeholder="email@example.com"
                                   aria-required="true">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="subject" class="form-label">Tiêu đề <span class="required">*</span></label>
                        <input type="text" id="subject" name="subject"
                               class="form-control" required
                               value="<?= e(\Core\Session::getFlash('old_subject', '')) ?>"
                               placeholder="Chủ đề tin nhắn..."
                               aria-required="true">
                    </div>

                    <div class="form-group">
                        <label for="message" class="form-label">Nội dung <span class="required">*</span></label>
                        <textarea id="message" name="message"
                                  class="form-control" required
                                  rows="6"
                                  placeholder="Nội dung tin nhắn của bạn..."
                                  aria-required="true"><?= e(\Core\Session::getFlash('old_message', '')) ?></textarea>
                        <span class="form-hint">Tối thiểu 10 ký tự, tối đa 3000 ký tự.</span>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-full" id="sendBtn">
                        🚀 Gửi tin nhắn
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
