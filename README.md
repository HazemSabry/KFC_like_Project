# KFC Egypt Website

This project is a web application for KFC Egypt, providing users with an interactive platform to browse the menu, view deals, locate branches, and place orders. It includes a backend for managing data and a frontend for user interaction.

## Table of Contents

- [Features](#features)
- [Technologies Used](#technologies-used)
- [Project Structure](#project-structure)
- [Setup Instructions](#setup-instructions)
- [Database Schema](#database-schema)
- [API Endpoints](#api-endpoints)
- [License](#license)

---

## Features

- **Frontend**:
  - Responsive design for desktop and mobile.
  - Browse menu items and deals.
  - Locate nearby branches with operational hours.
  - Add items to the cart and place orders.
  - Subscribe to newsletters.

- **Backend**:
  - API for fetching menu items, deals, locations, and special offers.
  - User authentication (register, login, logout).
  - Order processing and tracking.
  - Feedback submission and promo code application.

- **Database**:
  - Stores menu items, deals, orders, users, feedback, and more.

---

## Technologies Used

- **Frontend**:
  - HTML5, CSS3, JavaScript.

- **Backend**:
  - PHP (MySQLi for database interaction).

- **Database**:
  - MySQL.

---

## Project Structure

.
├── image/                     # Placeholder for images
├──

kfc-egypt-backend.php

      # Backend logic and API endpoints
├──

kfc-egypt-db-schema.sql

    # Database schema and sample data
├──

kfc-egypt-website.css

      # Stylesheet for the frontend
├──

kfc-egypt-website.html

     # Frontend HTML file

```

---

## Setup Instructions

1. **Clone the Repository**:
   ```bash
   git clone <repository-url>
   cd <repository-folder>
   ```

2. **Set Up the Database**:
   - Import the

kfc-egypt-db-schema.sql

 file into your MySQL database.

- Update the database credentials in

kfc-egypt-backend.php

:
     ```php
     $servername = "localhost";
     $username = "your_username";
     $password = "your_password";
     $dbname = "kfc_egypt_db";
     ```

3. **Run the Application**:
   - Place the project files in your web server's root directory (e.g., `htdocs` for XAMPP).
   - Start your web server and navigate to `http://localhost/kfc-egypt-website.html`.

---

## Database Schema

The database schema is defined in

kfc-egypt-db-schema.sql

. Key tables include:

- `menu_items`: Stores menu items with details like name, price, category, and availability.
- `deals`: Stores active deals with priority and pricing.
- `users`: Stores user information for authentication and preferences.
- `orders`: Tracks customer orders and their statuses.
- `feedback`: Stores customer feedback and ratings.

---

## API Endpoints

The backend provides the following API endpoints (defined in kfc-egypt-backend.php):

### GET Endpoints

- `/kfc-egypt-backend.php?action=get_menu&category=<category>`: Fetch menu items (optional category filter).
- `/kfc-egypt-backend.php?action=get_deals`: Fetch active deals.
- `/kfc-egypt-backend.php?action=get_locations`: Fetch branch locations.
- `/kfc-egypt-backend.php?action=get_categories`: Fetch menu categories.
- `/kfc-egypt-backend.php?action=get_offers`: Fetch special offers.
- `/kfc-egypt-backend.php?action=search_menu&search=<term>`: Search menu items.
- `/kfc-egypt-backend.php?action=get_order_status&order_id=<id>`: Fetch order status.
- `/kfc-egypt-backend.php?action=get_notifications&limit=<limit>`: Fetch recent notifications.

### POST Endpoints

-

kfc-egypt-backend.php

with `action=process_order`: Process a customer order
-

kfc-egypt-backend.php

with `action=register`: Register a new user
-

kfc-egypt-backend.php

with `action=login`: Log in a user
-

kfc-egypt-backend.php

with `action=logout`: Log out the current user
-

kfc-egypt-backend.php

with `action=update_preferences`: Update user preferences
-

kfc-egypt-backend.php

with `action=submit_feedback`: Submit feedback for an order
-

kfc-egypt-backend.php

with `action=apply_promo`: Apply a promo code
-

kfc-egypt-backend.php

 with `action=subscribe_newsletter`: Subscribe to the newsletter.

---

## License

This project is licensed under the MIT License. See the LICENSE file for details.

```

This `README.md` provides a comprehensive overview of the project, including its features, setup instructions, and API documentation.
This `README.md` provides a comprehensive overview of the project, including its features, setup instructions, and API documentation.
