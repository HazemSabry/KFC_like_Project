
// Mobile menu toggle
document.querySelector('.mobile-menu-btn').addEventListener('click', function () {
    document.getElementById('main-nav').classList.toggle('active');
});

// Smooth scrolling for navigation links
document.querySelectorAll('nav a, .cta-button').forEach(link => {
    link.addEventListener('click', function (e) {
        const href = this.getAttribute('href');

        if (href.startsWith('#') && document.querySelector(href)) {
            e.preventDefault();
            document.querySelector(href).scrollIntoView({
                behavior: 'smooth'
            });

            // Close mobile menu if open
            document.getElementById('main-nav').classList.remove('active');
        }
    });
});

// Add to cart functionality
document.querySelectorAll('.add-to-cart').forEach(button => {
    button.addEventListener('click', function () {
        const itemName = this.closest('.menu-item, .deal-card').querySelector('.item-title, .deal-title').textContent;
        alert(`Added ${itemName} to cart!`);
        // In a real implementation, this would add to cart logic
    });
});