document.addEventListener('DOMContentLoaded', function () {
    const isAdmin = document.body.getAttribute('data-is-admin') === 'true';
    if (isAdmin) {
        const desktopNav = document.querySelector('header nav ul');
        if (desktopNav) {
            const adminLi = document.createElement('li');
            const adminLink = document.createElement('a');
            adminLink.href = window.baseUrl + 'admin/dashboard';
            adminLink.textContent = 'Admin Dashboard';
            if (window.location.pathname.startsWith(window.baseUrl + 'admin/dashboard')) {
                adminLink.classList.add('active');
            }
            adminLi.appendChild(adminLink);
            const contactLi = desktopNav.querySelector('li a[href="' + window.baseUrl + 'contact"]');
            if (contactLi && contactLi.parentElement) {
                desktopNav.insertBefore(adminLi, contactLi.parentElement);
            } else {
                desktopNav.appendChild(adminLi);
            }
        }
        const mobileNav = document.querySelector('.mobile-menu nav ul');
        if (mobileNav) {
            const adminLi = document.createElement('li');
            const adminLink = document.createElement('a');
            adminLink.href = window.baseUrl + 'admin/dashboard';
            adminLink.textContent = 'Admin Dashboard';
            if (window.location.pathname.startsWith(window.baseUrl + 'admin/dashboard')) {
                adminLink.classList.add('active');
            }
            adminLi.appendChild(adminLink);
            const contactLi = mobileNav.querySelector('li a[href="' + window.baseUrl + 'contact"]');
            if (contactLi && contactLi.parentElement) {
                mobileNav.insertBefore(adminLi, contactLi.parentElement);
            } else {
                mobileNav.appendChild(adminLi);
            }
        }
    }
});
