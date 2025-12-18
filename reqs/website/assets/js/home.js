document.addEventListener('DOMContentLoaded', function () {
    let menu = document.querySelector('#menu-icon');
    let navbar = document.querySelector('.navbar');
    let sections = document.querySelectorAll('section');

    // Add click event listener to navbar links
    document.querySelectorAll('.navbar a:not(.auth-link)').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();

            // Remove the 'active' class from all links
            document.querySelectorAll('.navbar a:not(.auth-link)').forEach(link => {
                link.classList.remove('active');
            });

            // Add the 'active' class to the clicked link
            link.classList.add('active');

            // Get the target section ID from the link's href
            let targetId = link.getAttribute('href').substring(1);
            let targetSection = document.getElementById(targetId);

            // Scroll to the target section smoothly
            if (targetSection) {
                let headerOffset = 80; // Account for fixed header
                let elementPosition = targetSection.offsetTop;
                let offsetPosition = elementPosition - headerOffset;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }

            // Close the navbar after a link is clicked
            menu.classList.remove('bx-x');
            navbar.classList.remove('open');
        });
    });

    // Close navbar when clicking auth links on mobile
    document.querySelectorAll('.navbar .auth-link').forEach(link => {
        link.addEventListener('click', () => {
            menu.classList.remove('bx-x');
            navbar.classList.remove('open');
        });
    });

    // Add scroll event listener to update the active link
    window.addEventListener('scroll', () => {
        let scrollPosition = window.scrollY + 100; // Offset for fixed header

        sections.forEach(section => {
            let sectionTop = section.offsetTop;
            let sectionHeight = section.offsetHeight;
            let sectionId = section.getAttribute('id');

            if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                updateActiveLink(sectionId);
            }
        });
    });

    // Check the initially visible section on page load
    let initialScrollPosition = window.scrollY + 100;
    sections.forEach(section => {
        let sectionTop = section.offsetTop;
        let sectionHeight = section.offsetHeight;
        let sectionId = section.getAttribute('id');

        if (initialScrollPosition >= sectionTop && initialScrollPosition < sectionTop + sectionHeight) {
            updateActiveLink(sectionId);
        }
    });

    // Helper function to update the active link
    function updateActiveLink(sectionId) {
        document.querySelectorAll('.navbar a:not(.auth-link)').forEach(link => {
            link.classList.remove('active');
        });

        let activeLink = document.querySelector(`.navbar a[href="#${sectionId}"]`);
        if (activeLink) {
            activeLink.classList.add('active');
        }
    }

    // Toggle menu icon and navbar
    menu.onclick = () => {
        menu.classList.toggle('bx-x');
        navbar.classList.toggle('open');
    };

    // Close navbar when clicking outside
    document.addEventListener('click', (e) => {
        if (!navbar.contains(e.target) && !menu.contains(e.target) && navbar.classList.contains('open')) {
            menu.classList.remove('bx-x');
            navbar.classList.remove('open');
        }
    });

    // Infinite Scroll for Stats - Clone items for seamless loop
    const statsTrack = document.querySelector('.stats-track');
    if (statsTrack) {
        const statsItems = Array.from(statsTrack.children);

        // Clone items and append to create seamless loop
        statsItems.forEach(item => {
            const clone = item.cloneNode(true);
            statsTrack.appendChild(clone);
        });
    }

    // Scroll to Top Button
    let scrollToTopBtn = document.getElementById('scroll-to-top');

    // Show/hide scroll-to-top button based on scroll position
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            scrollToTopBtn.classList.add('show');
        } else {
            scrollToTopBtn.classList.remove('show');
        }
    });

    // Smooth scroll to top when clicking the button
    scrollToTopBtn.addEventListener('click', (e) => {
        e.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
});
