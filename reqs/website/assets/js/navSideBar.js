const showSidebar = (toggleId, sidebarId, headerId) => {
    const toggle = document.getElementById(toggleId),
          sidebar = document.getElementById(sidebarId),
          header = document.getElementById(headerId),
          root = document.documentElement; // Root element to update CSS variables

    // Function to update CSS variable for sidebar width
    const updateSidebarWidth = () => {
        if (sidebar.classList.contains('show-sidebar')) {
            root.style.setProperty('--sidebar-width', '90px'); // Collapsed width
        } else {
            root.style.setProperty('--sidebar-width', '275px'); // Expanded width
        }
    };

    const handlePadding = () => {
        if (sidebar.classList.contains('show-sidebar')) {
            if (window.innerWidth > 525 && window.innerWidth < 768) {
                header.classList.remove('left-pd');
            } else if (window.innerWidth > 768) {
                header.classList.add('left-pd');
            }
        } else {
            header.classList.remove('left-pd');
        }
    };

    const collapseSidebarOnSmallScreen = () => {
        if (window.innerWidth <= 525) {
                sidebar.classList.add('show-sidebar');
                header.classList.add('left-pd');
                updateSidebarWidth();
        }
    };

    if (toggle && sidebar && header) {
        collapseSidebarOnSmallScreen();
        updateSidebarWidth(); // Set initial width based on state

        toggle.addEventListener('click', () => {
            if (window.innerWidth > 525) {
                sidebar.classList.toggle('show-sidebar');
                handlePadding();
                updateSidebarWidth(); // Update width on toggle
            }
        });

        window.addEventListener('resize', () => {
            collapseSidebarOnSmallScreen();
            handlePadding();
            updateSidebarWidth(); // Update width on resize
        });
    }
};

showSidebar('header-toggle', 'sidebar', 'header');

/*=============== LINK ACTIVE ===============*/
const sidebarLinks = document.querySelectorAll('.sidebar__list a');

const linkColor = function() {
    sidebarLinks.forEach(link => link.classList.remove('active-link'));
    this.classList.add('active-link');
};

sidebarLinks.forEach(link => link.addEventListener('click', linkColor));

/*=============== DARK LIGHT THEME ===============*/ 
const themeButton = document.getElementById('theme-button');
const darkTheme = 'dark-theme';
const iconTheme = 'ri-sun-line';

// Previously selected topic (if user selected)
const selectedTheme = localStorage.getItem('selected-theme');
const selectedIcon = localStorage.getItem('selected-icon');

// We obtain the current theme that the interface has by validating the dark-theme class
const getCurrentTheme = () => document.body.classList.contains(darkTheme) ? 'dark' : 'light';
const getCurrentIcon = () => themeButton.classList.contains(iconTheme) ? 'ri-moon-clear-line' : 'ri-sun-line';

// We validate if the user previously chose a topic
if (selectedTheme) {
  // If the validation is fulfilled, we ask what the issue was to know if we activated or deactivated the dark
  document.body.classList[selectedTheme === 'dark' ? 'add' : 'remove'](darkTheme);
  themeButton.classList[selectedIcon === 'ri-moon-clear-line' ? 'add' : 'remove'](iconTheme);
}

// Activate / deactivate the theme manually with the button
themeButton.addEventListener('click', () => {
    // Add or remove the dark / icon theme
    document.body.classList.toggle(darkTheme);
    themeButton.classList.toggle(iconTheme);
    // We save the theme and the current icon that the user chose
    localStorage.setItem('selected-theme', getCurrentTheme());
    localStorage.setItem('selected-icon', getCurrentIcon());
});
