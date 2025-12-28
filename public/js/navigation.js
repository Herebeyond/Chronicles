// Chronicles - Enhanced Navigation Dropdown Functionality
document.addEventListener('DOMContentLoaded', function() {
    const navTrigger = document.querySelector('.nav-trigger');
    const dropdown = document.querySelector('.dropdown');
    const menuItems = document.querySelectorAll('.liIntro:not(.disabled)');
    
    // User dropdown functionality
    const userMenu = document.querySelector('.user-menu');
    const userDropdown = document.querySelector('.user-dropdown-menu');
    
    if (userMenu && userDropdown) {
        userMenu.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !isExpanded);
            userDropdown.style.display = !isExpanded ? 'block' : 'none';
        });
        
        // Close user dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!userMenu.contains(e.target) && !userDropdown.contains(e.target)) {
                userMenu.setAttribute('aria-expanded', 'false');
                userDropdown.style.display = 'none';
            }
        });
    }
    
    // Keyboard navigation
    if (navTrigger) {
        navTrigger.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.focus();
                if (menuItems.length > 0) {
                    menuItems[0].focus();
                }
            }
        });
        
        // Arrow key navigation in dropdown
        menuItems.forEach((item, index) => {
            item.addEventListener('keydown', function(e) {
                switch(e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        const nextIndex = (index + 1) % menuItems.length;
                        menuItems[nextIndex].focus();
                        break;
                    case 'ArrowUp':
                        e.preventDefault();
                        const prevIndex = (index - 1 + menuItems.length) % menuItems.length;
                        menuItems[prevIndex].focus();
                        break;
                    case 'Escape':
                        navTrigger.focus();
                        break;
                    case 'Enter':
                    case ' ':
                        e.preventDefault();
                        item.click();
                        break;
                }
            });
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!navTrigger.contains(e.target) && !dropdown.contains(e.target)) {
                navTrigger.setAttribute('aria-expanded', 'false');
            }
        });
        
        // Update aria-expanded on hover
        const menuItem = navTrigger.closest('.menu-item');
        if (menuItem) {
            menuItem.addEventListener('mouseenter', function() {
                navTrigger.setAttribute('aria-expanded', 'true');
            });
            
            menuItem.addEventListener('mouseleave', function() {
                navTrigger.setAttribute('aria-expanded', 'false');
            });
        }
    }
    
    console.log('Chronicles navigation loaded');
});
