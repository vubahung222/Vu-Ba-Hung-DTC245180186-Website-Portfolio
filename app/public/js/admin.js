/**
 * Admin Panel JavaScript
 */

'use strict';

// ── Mobile Sidebar Toggle ─────────────────────
(function() {
  const toggle  = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('adminSidebar');
  const overlay = document.getElementById('sidebarOverlay');
  if (!toggle || !sidebar) return;

  function openSidebar() {
    sidebar.classList.add('open');
    overlay?.classList.add('visible');
    toggle.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
  }

  function closeSidebar() {
    sidebar.classList.remove('open');
    overlay?.classList.remove('visible');
    toggle.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
  }

  toggle.addEventListener('click', () => {
    sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
  });

  overlay?.addEventListener('click', closeSidebar);
})();

// ── Auto-dismiss alerts ───────────────────────
document.querySelectorAll('.alert').forEach(alert => {
  setTimeout(() => {
    alert.style.transition = 'opacity 0.4s ease';
    alert.style.opacity = '0';
    setTimeout(() => alert.remove(), 400);
  }, 4000);
});

// ── Confirm Delete Modal ──────────────────────
(function() {
  const overlay = document.getElementById('confirmModal');
  if (!overlay) return;

  let pendingForm = null;

  document.querySelectorAll('[data-confirm]').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const message = btn.dataset.confirm || 'Bạn có chắc muốn xóa?';
      const form    = btn.closest('form') || document.getElementById(btn.dataset.formId);
      if (!form) return;

      pendingForm = form;
      document.getElementById('confirmMessage').textContent = message;
      overlay.classList.add('active');
    });
  });

  document.getElementById('confirmYes')?.addEventListener('click', () => {
    if (pendingForm) pendingForm.submit();
    overlay.classList.remove('active');
  });

  document.getElementById('confirmNo')?.addEventListener('click', () => {
    overlay.classList.remove('active');
    pendingForm = null;
  });

  overlay.addEventListener('click', (e) => {
    if (e.target === overlay) {
      overlay.classList.remove('active');
      pendingForm = null;
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      overlay.classList.remove('active');
      pendingForm = null;
    }
  });
})();

// ── Skill level range display ─────────────────
document.querySelectorAll('input[type="range"][name="level"]').forEach(range => {
  const display = document.getElementById(range.id + '_display');
  if (display) {
    display.textContent = range.value + '%';
    range.addEventListener('input', () => {
      display.textContent = range.value + '%';
    });
  }
});

// ── Avatar preview before upload ──────────────
const avatarInput = document.getElementById('avatarInput');
if (avatarInput) {
  avatarInput.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file) return;
    const preview = document.getElementById('avatarPreview');
    if (preview) {
      const url = URL.createObjectURL(file);
      if (preview.tagName === 'IMG') {
        preview.src = url;
      } else {
        // Replace placeholder with img
        const img = document.createElement('img');
        img.src = url; img.id = 'avatarPreview';
        img.className = 'avatar-preview';
        img.alt = 'Ảnh đại diện mới';
        preview.replaceWith(img);
      }
    }
  });
}

// ── Message modal ─────────────────────────────
document.querySelectorAll('[data-msg-id]').forEach(btn => {
  btn.addEventListener('click', () => {
    const msgId   = btn.dataset.msgId;
    const name    = btn.dataset.name;
    const email   = btn.dataset.email;
    const subject = btn.dataset.subject;
    const message = btn.dataset.message;
    const date    = btn.dataset.date;

    document.getElementById('msgModal-name').textContent    = name ?? '';
    document.getElementById('msgModal-email').textContent   = email ?? '';
    document.getElementById('msgModal-subject').textContent = subject ?? '';
    document.getElementById('msgModal-message').textContent = message ?? '';
    document.getElementById('msgModal-date').textContent    = date ?? '';

    const readForm = document.getElementById('msgReadForm');
    if (readForm) readForm.action = `/admin/messages/${msgId}/read`;

    document.getElementById('msgModal')?.classList.add('active');
  });
});

document.getElementById('msgModalClose')?.addEventListener('click', () => {
  document.getElementById('msgModal')?.classList.remove('active');
});

// ── Form loading state ────────────────────────
document.querySelectorAll('form[data-loading]').forEach(form => {
  form.addEventListener('submit', () => {
    const btn = form.querySelector('[type="submit"]');
    if (btn) {
      btn.disabled = true;
      btn.textContent = '⏳ Đang xử lý...';
    }
  });
});
