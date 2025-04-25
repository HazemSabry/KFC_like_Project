-- Database for KFC Egypt
/**
 * This script creates a database named 'kfc_egypt_db' if it does not already exist.
 * After creating or verifying the existence of the database, it sets the active database context to 'kfc_egypt_db'.
 */
CREATE DATABASE IF NOT EXISTS kfc_egypt_db;
USE kfc_egypt_db;
-- Menu Items table
/**
 * Table: menu_items
 * 
 * Description:
 * This table stores information about menu items for a restaurant or food service application.
 * 
 * Columns:
 * - id: Primary key, unique identifier for each menu item. Auto-incremented integer.
 * - item_name: Name of the menu item. Non-nullable string with a maximum length of 100 characters.
 * - description: Detailed description of the menu item. Optional text field.
 * - price: Price of the menu item. Non-nullable decimal value with up to 10 digits and 2 decimal places.
 * - category: Category or type of the menu item (e.g., appetizer, main course, dessert). Optional string with a maximum length of 50 characters.
 * - image_url: URL of the image representing the menu item. Optional string with a maximum length of 255 characters.
 * - calories: Caloric content of the menu item. Optional integer.
 * - available: Availability status of the menu item. Boolean value, defaults to 1 (available).
 * - created_at: Timestamp indicating when the menu item was created. Defaults to the current timestamp.
 * - updated_at: Timestamp indicating the last update time of the menu item. Automatically updated on modification.
 * 
 * Constraints:
 * - Primary Key: id
 * - Default Values: available (1), created_at (CURRENT_TIMESTAMP), updated_at (CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)
 */
CREATE TABLE menu_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    item_name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    category VARCHAR(50),
    image_url VARCHAR(255),
    calories INT,
    available BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
-- Deals table
/**
 * Table: deals
 * 
 * Description:
 * This table stores information about various deals offered by the business.
 * 
 * Columns:
 * - id: Primary key, unique identifier for each deal. Auto-incremented integer.
 * - deal_name: Name of the deal. A non-null string with a maximum length of 100 characters.
 * - description: Detailed description of the deal. Optional text field.
 * - price: Price of the deal. A non-null decimal value with up to 10 digits, including 2 decimal places.
 * - image_url: URL of the image representing the deal. Optional string with a maximum length of 255 characters.
 * - active: Indicates whether the deal is active. Boolean value, defaults to 1 (true).
 * - priority: Priority level of the deal. Integer value, defaults to 0.
 * - created_at: Timestamp indicating when the deal was created. Defaults to the current timestamp.
 * - updated_at: Timestamp indicating the last update to the deal. Automatically updated to the current timestamp on modification.
 */
CREATE TABLE deals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    deal_name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(255),
    active BOOLEAN DEFAULT 1,
    priority INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
-- Users table
/**
 * Table: users
 * 
 * This table stores information about the users of the system.
 * 
 * Columns:
 * - id: Primary key, auto-incrementing integer that uniquely identifies each user.
 * - username: A unique, non-nullable string (up to 50 characters) representing the user's username.
 * - password: A non-nullable string (up to 255 characters) storing the user's hashed password.
 * - email: A unique, non-nullable string (up to 100 characters) representing the user's email address.
 * - phone: An optional string (up to 20 characters) for the user's phone number.
 * - address: An optional text field for the user's address.
 * - favorite_item: An optional integer referencing the `id` column in the `menu_items` table, representing the user's favorite menu item.
 * - delivery_notes: An optional text field for any special delivery instructions provided by the user.
 * - newsletter_subscription: A boolean indicating whether the user is subscribed to the newsletter (default is 0, meaning not subscribed).
 * - registration_date: A timestamp indicating when the user registered, defaulting to the current timestamp.
 * - last_login: A nullable timestamp indicating the last time the user logged in.
 * 
 * Foreign Keys:
 * - favorite_item: References the `id` column in the `menu_items` table.
 * 
 * Notes:
 * - The `username` and `email` fields must be unique to ensure no duplicates.
 */
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    favorite_item INT,
    delivery_notes TEXT,
    newsletter_subscription BOOLEAN DEFAULT 0,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    FOREIGN KEY (favorite_item) REFERENCES menu_items(id)
);
-- Orders table
/**
 * This script creates the 'orders' table, which stores information about customer orders.
 * 
 * Columns:
 * - id: Primary key, auto-incremented integer to uniquely identify each order.
 * - customer_id: Foreign key referencing the 'id' column in the 'users' table, representing the customer who placed the order.
 * - order_date: Timestamp indicating when the order was placed, defaults to the current timestamp.
 * - delivery_address: Text field to store the delivery address for the order, cannot be null.
 * - phone: String (up to 20 characters) to store the customer's phone number, cannot be null.
 * - email: String (up to 100 characters) to store the customer's email address, cannot be null.
 * - total_amount: Decimal value (up to 10 digits, 2 decimal places) representing the total cost of the order, cannot be null.
 * - payment_method: String (up to 50 characters) indicating the payment method used for the order, cannot be null.
 * - status: String (up to 20 characters) representing the current status of the order, defaults to 'Pending'.
 * - created_at: Timestamp indicating when the order record was created, defaults to the current timestamp.
 * - updated_at: Timestamp indicating the last time the order record was updated, automatically updated on modification.
 * 
 * Constraints:
 * - The 'customer_id' column is a foreign key referencing the 'id' column in the 'users' table.
 * - The 'delivery_address', 'phone', 'email', 'total_amount', and 'payment_method' columns are mandatory (NOT NULL).
 */
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    delivery_address TEXT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    status VARCHAR(20) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id)
);
-- Order Items table
/**
 * This script defines the `order_items` table, which represents the items included in a specific order.
 * 
 * Table: order_items
 * - id: Primary key, unique identifier for each order item (auto-incremented).
 * - order_id: Foreign key referencing the `id` column in the `orders` table, indicating the associated order.
 * - item_id: Foreign key referencing the `id` column in the `menu_items` table, indicating the specific menu item.
 * - quantity: The number of units of the menu item in the order.
 * - price: The price of the menu item at the time of the order, stored as a decimal with two decimal places.
 * - created_at: Timestamp indicating when the record was created, with a default value of the current timestamp.
 * 
 * Constraints:
 * - Primary Key: `id`.
 * - Foreign Key: `order_id` references `orders(id)`.
 * - Foreign Key: `item_id` references `menu_items(id)`.
 */
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (item_id) REFERENCES menu_items(id)
);
-- Locations table
/**
 * Table: locations
 * 
 * Description:
 * This table stores information about various locations, including their name, address, city, contact details, geographical coordinates, and operational hours.
 * 
 * Columns:
 * - id (INT, Primary Key, Auto Increment): Unique identifier for each location.
 * - name (VARCHAR(100), NOT NULL): The name of the location.
 * - address (TEXT, NOT NULL): The full address of the location.
 * - city (VARCHAR(50), NOT NULL): The city where the location is situated.
 * - phone (VARCHAR(20), NULL): The contact phone number for the location.
 * - opening_hours (VARCHAR(100), NULL): The operational hours of the location.
 * - latitude (DECIMAL(10, 8), NULL): The latitude coordinate of the location.
 * - longitude (DECIMAL(11, 8), NULL): The longitude coordinate of the location.
 * - created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP): The timestamp when the record was created.
 * - updated_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP): The timestamp when the record was last updated.
 * 
 * Constraints:
 * - Primary Key: id
 */
CREATE TABLE locations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    opening_hours VARCHAR(100),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    image_url VARCHAR(255) DEFAULT '/images/default-location.jpg'
);
-- Special Offers table
/**
 * Table: special_offers
 * 
 * Description:
 * This table stores information about special offers available in the system.
 * 
 * Columns:
 * - id (INT, Primary Key, Auto Increment): Unique identifier for each special offer.
 * - offer_name (VARCHAR(100), NOT NULL): The name of the special offer.
 * - description (TEXT): A detailed description of the special offer.
 * - discount_type (VARCHAR(20)): The type of discount applied ('percentage' or 'fixed').
 * - discount_value (DECIMAL(10, 2)): The value of the discount, either as a percentage or a fixed amount.
 * - start_date (DATETIME): The start date and time of the special offer.
 * - end_date (DATETIME): The end date and time of the special offer.
 * - active (BOOLEAN, Default: 1): Indicates whether the special offer is active (1 for active, 0 for inactive).
 * - priority (INT, Default: 0): The priority of the special offer, used for ordering or precedence.
 * - created_at (TIMESTAMP, Default: CURRENT_TIMESTAMP): The timestamp when the record was created.
 * - updated_at (TIMESTAMP, Default: CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP): The timestamp when the record was last updated.
 * 
 * Notes:
 * - The `discount_type` column determines how the `discount_value` should be interpreted.
 * - The `priority` column can be used to manage overlapping offers, with higher values taking precedence.
 */
CREATE TABLE special_offers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    offer_name VARCHAR(100) NOT NULL,
    description TEXT,
    discount_type VARCHAR(20),
    -- 'percentage' or 'fixed'
    discount_value DECIMAL(10, 2),
    start_date DATETIME,
    end_date DATETIME,
    active BOOLEAN DEFAULT 1,
    priority INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
-- Feedback table
/**
 * This script creates a table named 'feedback' to store customer feedback data.
 * 
 * Columns:
 * - id: Primary key, an auto-incrementing integer to uniquely identify each feedback entry.
 * - order_id: Foreign key referencing the 'id' column in the 'orders' table, linking feedback to a specific order.
 *  - rating: Integer column with a constraint to ensure values are between 1 and 5 (inclusive), representing the customer's rating.
 * - comments: Text column to store optional customer comments about their experience.
 * - submission_date: Timestamp column with a default value of the current timestamp, indicating when the feedback was submitted.
 * 
 * Constraints:
 * - Primary key on 'id' ensures each feedback entry is unique.
 * - Foreign key on 'order_id' enforces referential integrity with the 'orders' table.
 * - Check constraint on 'rating' ensures valid rating values between 1 and 5.
 */
CREATE TABLE feedback (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    rating INT CHECK (
        rating >= 1
        AND rating <= 5
    ),
    comments TEXT,
    submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id)
);
-- Promo Codes table
/**
 * Table: promo_codes
 * 
 * Description:
 * This table stores information about promotional codes that can be applied to orders. 
 * Each promo code has a unique identifier, a code string, discount details, and validity period.
 * 
 * Columns:
 * - id (INT, Primary Key, Auto Increment): Unique identifier for each promo code.
 * - code (VARCHAR(20), Unique, Not Null): The promotional code string.
 * - discount_type (VARCHAR(20)): Type of discount ('percentage' or 'fixed').
 * - discount_value (DECIMAL(10, 2)): The value of the discount.
 * - minimum_order (DECIMAL(10, 2), Default 0): Minimum order amount required to apply the promo code.
 * - valid_from (DATETIME): The start date and time from which the promo code is valid.
 * - valid_until (DATETIME): The end date and time until which the promo code is valid.
 * - active (BOOLEAN, Default 1): Indicates whether the promo code is active (1) or inactive (0).
 * - created_at (TIMESTAMP, Default CURRENT_TIMESTAMP): Timestamp when the promo code was created.
 * - updated_at (TIMESTAMP, Default CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP): Timestamp when the promo code was last updated.
 * 
 * Constraints:
 * - The 'code' column must be unique to ensure no duplicate promo codes.
 */
CREATE TABLE promo_codes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(20) UNIQUE NOT NULL,
    discount_type VARCHAR(20),
    -- 'percentage' or 'fixed'
    discount_value DECIMAL(10, 2),
    minimum_order DECIMAL(10, 2) DEFAULT 0,
    valid_from DATETIME,
    valid_until DATETIME,
    active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
-- Notifications table
/**
 * Table: notifications
 * 
 * Description:
 * This table is used to store notification data, including the title, message, 
 * optional image URL, and status of the notification. It also tracks creation 
 * and update timestamps.
 * 
 * Columns:
 * - id (INT, Primary Key, Auto Increment): Unique identifier for each notification.
 * - title (VARCHAR(100), NOT NULL): The title of the notification.
 * - message (TEXT, NOT NULL): The content or body of the notification.
 * - image_url (VARCHAR(255), NULL): Optional URL for an image associated with the notification.
 * - active (BOOLEAN, DEFAULT 1): Indicates whether the notification is active (1) or inactive (0).
 * - created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP): The timestamp when the notification was created.
 * - updated_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP): The timestamp when the notification was last updated.
 */
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    image_url VARCHAR(255),
    active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
-- Newsletter Subscribers table
/**
 * This script creates the `newsletter_subscribers` table, which is used to store information about subscribers to a newsletter.
 * 
 * Table Structure:
 * - `id` (INT): The primary key for the table. It is an auto-incrementing integer.
 * - `email` (VARCHAR(100)): The email address of the subscriber. This field is unique and cannot be null.
 * - `subscription_date` (TIMESTAMP): The date and time when the subscription was created. Defaults to the current timestamp.
 * - `active` (BOOLEAN): Indicates whether the subscription is active. Defaults to 1 (active).
 * 
 * Constraints:
 * - Primary Key: `id`
 * - Unique Constraint: `email`
 */
CREATE TABLE newsletter_subscribers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) UNIQUE NOT NULL,
    subscription_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    active BOOLEAN DEFAULT 1
);
-- Sample data
/**
 * This SQL script inserts multiple rows into the `menu_items` table.
 * Each row represents a menu item with the following fields:
 * 
 * - `item_name`: The name of the menu item.
 * - `description`: A brief description of the menu item.
 * - `price`: The price of the menu item in the local currency.
 * - `category`: The category to which the menu item belongs (e.g., Chicken, Sandwiches, Wraps, Meals, Snacks, Sides, Drinks).
 * - `image_url`: The URL of the image representing the menu item.
 * - `calories`: The calorie count of the menu item.
 * 
 * Example menu items included in this script:
 * - Original Recipe Chicken
 * - Zinger Sandwich
 * - Twister Wrap
 * - Family Feast
 * - Dinner Box
 * - Hot Wings
 * - Potato Wedges
 * - Pepsi 1L
 * - Rizo Rice
 */
INSERT INTO menu_items (
        item_name,
        description,
        price,
        category,
        image_url,
        calories
    )
VALUES (
        'Original Recipe Chicken',
        'Classic KFC fried chicken made with the secret recipe of 11 herbs and spices',
        25.00,
        'Chicken',
        '/images/original-recipe.jpg',
        300
    ),
    (
        'Zinger Sandwich',
        'Extra crispy and spicy chicken fillet with mayo and lettuce',
        35.00,
        'Sandwiches',
        '/images/zinger.jpg',
        450
    ),
    (
        'Twister Wrap',
        'Tender chicken strips with fresh veggies wrapped in tortilla',
        40.00,
        'Wraps',
        '/images/twister.jpg',
        520
    ),
    (
        'Family Feast',
        '10pc chicken, 4 portions of fries, coleslaw, and 2L drink',
        199.00,
        'Meals',
        '/images/family-feast.jpg',
        2400
    ),
    (
        'Dinner Box',
        '4pc chicken, 2 portions of fries, and a drink',
        85.00,
        'Meals',
        '/images/dinner-box.jpg',
        1200
    ),
    (
        'Hot Wings',
        'Spicy chicken wings served with ranch sauce',
        30.00,
        'Snacks',
        '/images/hot-wings.jpg',
        350
    ),
    (
        'Potato Wedges',
        'Crispy potato wedges with a hint of spices',
        20.00,
        'Sides',
        '/images/potato-wedges.jpg',
        250
    ),
    (
        'Pepsi 1L',
        'Refreshing Pepsi drink in a 1.5L bottle',
        15.00,
        'Drinks',
        '/images/pepsi-1L.jpg',
        150
    ),
    (
        'Rizo Rice',
        'Fluffy rice with a hint of spices, perfect as a side dish',
        10.00,
        'Sides',
        '/images/rizo-rice.jpg',
        200
    );
/**
 * This SQL script inserts multiple rows into the `locations` table. 
 * Each row represents a KFC branch with the following details:
 * 
 * - `name`: The name of the branch.
 * - `address`: The full address of the branch.
 * - `city`: The city where the branch is located.
 * - `phone`: The contact phone number of the branch.
 * - `opening_hours`: The operating hours of the branch.
 * - `latitude`: The geographical latitude of the branch's location.
 * - `longitude`: The geographical longitude of the branch's location.
 * 
 * The script includes data for three branches:
 * 1. KFC Nasr City in Cairo.
 * 2. KFC Downtown in Cairo.
 * 3. KFC Alexandria Corniche in Alexandria.
 */
INSERT INTO locations (
        name,
        address,
        city,
        phone,
        opening_hours,
        latitude,
        longitude,
        image_url
    )
VALUES (
        'KFC Nasr City',
        '123 Abbas El Akkad St., Nasr City',
        'Cairo',
        '02-24000000',
        '10:00 AM - 12:00 AM',
        30.055166,
        31.341267,
        '/images/giza-pyramids-mall.jpg'
    ),
    (
        'KFC Downtown',
        '45 Talaat Harb St., Downtown',
        'Cairo',
        '02-25000000',
        '10:00 AM - 2:00 AM',
        30.046511,
        31.241234,
        '/images/cairo-downtown.jpg'
    ),
    (
        'KFC Alexandria Corniche',
        '22 Corniche Road, San Stefano',
        'Alexandria',
        '03-5000000',
        '10:00 AM - 1:00 AM',
        31.245165,
        29.976543,
        '/images/alexandria-mall.jpg'
    );
/**
 * This script inserts promotional codes into the `promo_codes` table.
 * Each promo code includes the following details:
 * - `code`: The unique identifier for the promo code.
 * - `discount_type`: The type of discount offered (e.g., 'percentage' or 'fixed').
 * - `discount_value`: The value of the discount (percentage or fixed amount).
 * - `minimum_order`: The minimum order amount required to apply the promo code.
 * - `valid_from`: The start date and time when the promo code becomes valid.
 * - `valid_until`: The end date and time when the promo code expires.
 * Example promo codes:
 * - 'WELCOME10': Offers a 10% discount on orders of at least 50.00, valid throughout 2025.
 * - 'FLAT15': Offers a fixed discount of 15.00 on orders of at least 100.00, valid throughout 2025.
 */
INSERT INTO promo_codes (
        code,
        discount_type,
        discount_value,
        minimum_order,
        valid_from,
        valid_until
    )
VALUES (
        'WELCOME10',
        'percentage',
        10.00,
        50.00,
        '2025-01-01 00:00:00',
        '2025-12-31 23:59:59'
    ),
    (
        'FLAT15',
        'fixed',
        15.00,
        100.00,
        '2025-01-01 00:00:00',
        '2025-12-31 23:59:59'
    );
/**
 * This SQL script inserts multiple rows into the `deals` table. Each row represents a specific deal offered by the business, with the following columns:
 * 
 * - `deal_name`: The name of the deal (e.g., "Family Feast").
 * - `description`: A brief description of the deal, including its contents.
 * - `price`: The price of the deal in the local currency.
 * - `image_url`: The relative path to the image representing the deal.
 * - `active`: A flag indicating whether the deal is currently active (1 for active, 0 for inactive).
 * - `priority`: A numerical value representing the priority or ranking of the deal (higher priority deals may appear first in listings).
 * 
 * The script includes four sample deals with varying details, prices, and priorities.
 */
INSERT INTO deals (
        deal_name,
        description,
        price,
        image_url,
        active,
        priority
    )
VALUES (
        'Family Feast',
        '10pc chicken, 4 portions of fries, coleslaw, and 2L drink',
        599.00,
        '/images/family-feast.jpg',
        1,
        3
    ),
    (
        'Dinner Box',
        '4pc chicken, 2 portions of fries, and a drink',
        125.00,
        '/images/dinner-box.jpg',
        1,
        2
    ),
    (
        'Zinger Combo',
        'Zinger sandwich with fries and a drink',
        150.00,
        '/images/zinger.jpg',
        1,
        1
    ),
    (
        'Potato Wedges',
        'Crispy potato wedges with a hint of spices',
        20.00,
        '/images/potato-wedges.jpg',
        1,
        0
    );