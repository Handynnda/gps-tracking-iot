/**
 * Script Responsif Global untuk Sistem GPS Tracking
 * Otomatis menangani mobile drawer navigation, hamburger menu, dan touch overlay.
 */
document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.querySelector('.sidebar');
    if (!sidebar) return;

    // 1. Buat elemen Overlay Backdrop jika belum ada
    let overlay = document.getElementById('sidebar-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'sidebar-overlay';
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);
    }

    // 2. Tambahkan tombol Close (X) di dalam sidebar jika belum ada
    const logoEl = sidebar.querySelector('.logo');
    if (logoEl && !sidebar.querySelector('.sidebar-close-btn')) {
        const closeBtn = document.createElement('button');
        closeBtn.className = 'sidebar-close-btn';
        closeBtn.setAttribute('aria-label', 'Tutup Menu');
        closeBtn.innerHTML = '<i class="fas fa-times"></i>';
        closeBtn.addEventListener('click', closeSidebar);
        logoEl.appendChild(closeBtn);
    }

    // 3. Tambahkan Mobile Navbar Sticky di atas main-content jika belum ada
    const mainContent = document.querySelector('.main-content');
    if (mainContent && !document.querySelector('.mobile-navbar')) {
        const mobileNav = document.createElement('div');
        mobileNav.className = 'mobile-navbar';
        mobileNav.innerHTML = `
            <div class="mobile-brand">
                <i class="fas fa-car text-blue-600 mr-2"></i>
                <span class="font-bold text-gray-800 text-sm">GPS Tracking</span>
            </div>
            <button id="mobile-sidebar-toggle" class="mobile-toggle-btn" aria-label="Buka Menu">
                <i class="fas fa-bars"></i>
            </button>
        `;
        // Sisipkan mobile navbar di paling atas main-content
        mainContent.insertBefore(mobileNav, mainContent.firstChild);

        const toggleBtn = mobileNav.querySelector('#mobile-sidebar-toggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', toggleSidebar);
        }
    }

    function openSidebar() {
        sidebar.classList.add('open');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden'; // Kunci scroll latar belakang saat drawer terbuka
    }

    function closeSidebar() {
        sidebar.classList.remove('open');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    function toggleSidebar() {
        if (sidebar.classList.contains('open')) {
            closeSidebar();
        } else {
            openSidebar();
        }
    }

    // Klik overlay menutup sidebar
    overlay.addEventListener('click', closeSidebar);

    // Klik menu link di sidebar pada mobile otomatis menutup sidebar
    const menuLinks = sidebar.querySelectorAll('.menu a');
    menuLinks.forEach(link => {
        link.addEventListener('click', function () {
            if (window.innerWidth <= 1024) {
                closeSidebar();
            }
        });
    });

    // Reset overflow saat resize ke layar desktop
    window.addEventListener('resize', function () {
        if (window.innerWidth > 1024) {
            closeSidebar();
        }
    });
});
