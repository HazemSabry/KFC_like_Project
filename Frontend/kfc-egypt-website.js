
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

// Function to fetch data from the backend
async function fetchData(endpoint) {
    try {
        const response = await fetch(`http://localhost:8081?action=${endpoint}`);
        if (!response.ok) {
            throw new Error(`Failed to fetch ${endpoint}: ${response.statusText}`);
        }
        return await response.json();
    } catch (error) {
        console.error(error);
        return [];
    }
}

// Function to populate the menu section
function populateMenu(menuItems) {
    const menuGrid = document.querySelector('.menu-grid');
    menuGrid.innerHTML = ''; // Clear existing content

    menuItems.forEach(item => {
        const menuItem = document.createElement('div');
        menuItem.classList.add('menu-item');
        menuItem.innerHTML = `
            <img src="${item.image_url}" alt="${item.item_name}">
            <div class="item-info">
                <h3 class="item-title">${item.item_name}</h3>
                <p class="item-description">${item.description}</p>
                <p class="item-price">EGP ${item.price}</p>
                <button class="add-to-cart">Add to Cart</button>
            </div>
        `;
        menuGrid.appendChild(menuItem);
    });
}

// Function to populate the deals section
function populateDeals(deals) {
    const dealsSlider = document.querySelector('.deals-slider');
    dealsSlider.innerHTML = ''; // Clear existing content

    deals.forEach(deal => {
        const dealCard = document.createElement('div');
        dealCard.classList.add('deal-card');
        dealCard.innerHTML = `
            <img src="${deal.image_url}" alt="${deal.title}">
            <div class="deal-info">
                <span class="deal-tag">${deal.tag}</span>
                <h3 class="deal-title">${deal.title}</h3>
                <p class="deal-price">EGP ${deal.price}</p>
                <button class="add-to-cart">Order Now</button>
            </div>
        `;
        dealsSlider.appendChild(dealCard);
    });
}

// Function to populate the locations section
function populateLocations(locations) {
    const locationsGrid = document.querySelector('.locations-grid');
    locationsGrid.innerHTML = ''; // Clear existing content

    locations.forEach(location => {
        const locationCard = document.createElement('div');
        locationCard.classList.add('location-card');
        locationCard.innerHTML = `
            <img src="${location.image_url}" alt="${location.name}">
            <div class="location-info">
                <h3 class="location-title">${location.name}</h3>
                <p class="location-address">${location.address}</p>
                <p class="location-hours">${location.hours}</p>
                <a href="${location.map_url}" class="view-on-map">View on Map →</a>
            </div>
        `;
        locationsGrid.appendChild(locationCard);
    });
}

// Function to load data on page load
async function loadData() {
    const menuItems = await fetchData('get_menu');
    populateMenu(menuItems);

    const deals = await fetchData('get_deals');
    populateDeals(deals);

    const locations = await fetchData('get_locations');
    populateLocations(locations);
}

// Run the loadData function when the page loads
document.addEventListener('DOMContentLoaded', loadData);