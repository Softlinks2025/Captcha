import './bootstrap';

// Toggle the profile dropdown menu
document.addEventListener('DOMContentLoaded', function() {
    const profileButton = document.getElementById('user-menu');
    const profileDropdown = document.querySelector('[aria-labelledby="user-menu"]');

    if (profileButton && profileDropdown) {
        profileButton.addEventListener('click', function() {
            const expanded = this.getAttribute('aria-expanded') === 'true' || false;
            this.setAttribute('aria-expanded', !expanded);
            profileDropdown.classList.toggle('hidden');
        });

        // Close the dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!profileButton.contains(event.target) && !profileDropdown.contains(event.target)) {
                profileButton.setAttribute('aria-expanded', 'false');
                profileDropdown.classList.add('hidden');
            }
        });
    }

    // Auto-dismiss flash messages after 5 seconds
    const flashMessages = document.querySelectorAll('[role="alert"]');
    flashMessages.forEach(message => {
        setTimeout(() => {
            message.style.transition = 'opacity 0.5s';
            message.style.opacity = '0';
            setTimeout(() => message.remove(), 500);
        }, 5000);
    });
});
