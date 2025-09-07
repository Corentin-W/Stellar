import './bootstrap';
import '../css/app.css';

// Alpine.js for interactive components
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

// Smooth scrolling for anchor links
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scrolling
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Generate stars
    function createStars() {
        const starField = document.getElementById('starField');
        if (!starField) return;

        const numStars = window.innerWidth < 768 ? 50 : 100;

        for (let i = 0; i < numStars; i++) {
            const star = document.createElement('div');
            star.className = 'star';
            star.style.left = Math.random() * 100 + '%';
            star.style.top = Math.random() * 100 + '%';
            star.style.width = Math.random() * 3 + 1 + 'px';
            star.style.height = star.style.width;
            star.style.animationDelay = Math.random() * 2 + 's';
            starField.appendChild(star);
        }
    }

    createStars();

    // Parallax effect for floating elements
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const rate = scrolled * -0.5;

        document.querySelectorAll('.floating').forEach((element, index) => {
            const speed = (index + 1) * 0.3;
            element.style.transform = `translateY(${rate * speed}px)`;
        });
    });

    // Form enhancements
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
                submitBtn.disabled = true;

                // Re-enable after 3 seconds to prevent permanent disable on validation errors
                setTimeout(() => {
                    submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                    submitBtn.disabled = false;
                }, 3000);
            }
        });
    });

    // Add loading states to buttons
    document.querySelectorAll('.btn-cosmic').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!this.disabled) {
                this.style.position = 'relative';
                this.style.overflow = 'hidden';
            }
        });
    });
});
