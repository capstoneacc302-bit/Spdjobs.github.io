// SPD Jobs Inc. — Main JavaScript

document.addEventListener('DOMContentLoaded', function () {

    // Auto-hide flash messages after 4 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function () { alert.remove(); }, 500);
        }, 4000);
    });

    // Salary display auto-fill
    const salMin = document.querySelector('[name="salary_min"]');
    const salMax = document.querySelector('[name="salary_max"]');
    const salDisplay = document.querySelector('[name="salary_display"]');
    if (salMin && salMax && salDisplay) {
        function updateSalDisplay() {
            const min = parseFloat(salMin.value);
            const max = parseFloat(salMax.value);
            if (!salDisplay.value || salDisplay.dataset.autoFilled) {
                if (min && max && min !== max) {
                    salDisplay.value = '₱' + min.toLocaleString() + '–₱' + max.toLocaleString() + '/day';
                } else if (min) {
                    salDisplay.value = '₱' + min.toLocaleString() + '/day';
                }
                salDisplay.dataset.autoFilled = 'true';
            }
        }
        salMin.addEventListener('blur', updateSalDisplay);
        salMax.addEventListener('blur', updateSalDisplay);
        salDisplay.addEventListener('input', function () {
            delete salDisplay.dataset.autoFilled;
        });
    }

    // File input: show selected filename
    document.querySelectorAll('input[type="file"]').forEach(function (input) {
        input.addEventListener('change', function () {
            const label = this.nextElementSibling;
            if (label && label.classList.contains('form-hint')) {
                if (this.files.length > 0) {
                    label.textContent = '✅ ' + this.files[0].name;
                    label.style.color = 'var(--green)';
                }
            }
        });
    });

    // Confirm delete buttons
    document.querySelectorAll('[data-confirm]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            if (!confirm(this.dataset.confirm || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // Job card hover: add cursor pointer visual feedback
    document.querySelectorAll('.job-card').forEach(function (card) {
        card.style.cursor = 'pointer';
    });

    // Mobile nav toggle (basic)
    const navToggle = document.getElementById('nav-toggle');
    const navMenu   = document.getElementById('nav-menu');
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function () {
            navMenu.classList.toggle('open');
        });
    }
});
