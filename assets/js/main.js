document.addEventListener('DOMContentLoaded', function() {
    // Mobile Menu Toggle
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mobileCloseBtn = document.querySelector('.mobile-close-btn');
    const mobileNav = document.querySelector('.mobile-nav');
    const mobileOverlay = document.createElement('div');
    
    mobileOverlay.classList.add('mobile-overlay');
    document.body.appendChild(mobileOverlay);
    
    function openMobileMenu() {
        mobileNav.classList.add('active');
        mobileOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    function closeMobileMenu() {
        mobileNav.classList.remove('active');
        mobileOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    mobileMenuBtn.addEventListener('click', openMobileMenu);
    mobileCloseBtn.addEventListener('click', closeMobileMenu);
    mobileOverlay.addEventListener('click', closeMobileMenu);
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const userDropdown = document.querySelector('.user-dropdown');
        if (!userDropdown.contains(e.target)) {
            const dropdownContent = userDropdown.querySelector('.dropdown-content');
            dropdownContent.style.display = 'none';
        }
    });
    
    // Prevent dropdown from closing when clicking inside
    document.querySelectorAll('.dropdown-content a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
});