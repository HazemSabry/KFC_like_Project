<?php
// Enable CORS
header("Access-Control-Allow-Origin: *"); // Allow requests from any origin
header("Access-Control-Allow-Methods: GET, POST, OPTIONS"); // Allow specific HTTP methods
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Allow specific headers

// Handle preflight requests
/**
 * Handles preflight requests for CORS (Cross-Origin Resource Sharing).
 * 
 * If the HTTP request method is 'OPTIONS', this block sends a 200 OK 
 * response and terminates the script execution. This is typically used 
 * to handle preflight requests sent by browsers to check permissions 
 * before making the actual request.
 */
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection
/**
 * Establishes a connection to the MySQL database.
 *
 * This function creates a new connection to the database using the provided
 * server name, username, password, and database name. If the connection fails,
 * the script will terminate and output an error message.
 *
 * @return mysqli Returns a MySQLi connection object on success.
 * @throws Exception If the connection to the database fails.
 */
function connectDB() {
    $servername = "db";
    $username = "kfc_user";
    $password = "kfc_password";
    $dbname = "kfc_egypt_db";
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Handle menu items retrieval
/**
 * Retrieves menu items from the database, optionally filtered by category.
 *
 * @param string $category (Optional) The category to filter menu items by. 
 *                         If empty, all menu items will be retrieved.
 * 
 * @return array An array of associative arrays, where each associative array 
 *               represents a menu item with its details.
 * 
 * @throws mysqli_sql_exception If a database error occurs during query execution.
 */
function getMenuItems($category = '') {
    $conn = connectDB();
    
    $sql = "SELECT * FROM menu_items";
    if (!empty($category)) {
        $sql .= " WHERE category = ?";
    }
    $sql .= " ORDER BY item_name";
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($category)) {
        $stmt->bind_param("s", $category);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $menuItems = [];
    while ($row = $result->fetch_assoc()) {
        $menuItems[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    return $menuItems;
}

// Handle deals retrieval
/**
 * Retrieves a list of active deals from the database, ordered by priority in descending order.
 *
 * @return array An array of associative arrays, where each associative array represents a deal.
 *               Each deal contains the columns fetched from the "deals" table.
 *
 * @throws Exception If there is an issue connecting to the database or executing the query.
 */
function getDeals() {
    $conn = connectDB();
    
    $sql = "SELECT * FROM deals WHERE active = 1 ORDER BY priority DESC";
    $result = $conn->query($sql);
    
    $deals = [];
    while ($row = $result->fetch_assoc()) {
        $deals[] = $row;
    }
    
    $conn->close();
    
    return $deals;
}

// Handle order processing
/**
 * Processes a customer order by inserting order details into the database, 
 * including order header and items, and sends a confirmation email.
 *
 * @param array $order An associative array containing order details:
 *                     - 'customer_id' (int): The ID of the customer placing the order.
 *                     - 'delivery_address' (string): The delivery address for the order.
 *                     - 'phone' (string): The customer's phone number.
 *                     - 'email' (string): The customer's email address.
 *                     - 'total_amount' (float): The total amount of the order.
 *                     - 'payment_method' (string): The payment method used for the order.
 *                     - 'items' (array): An array of items in the order, where each item is an associative array:
 *                         - 'item_id' (int): The ID of the item.
 *                         - 'quantity' (int): The quantity of the item.
 *                         - 'price' (float): The price of the item.
 *
 * @return array An associative array containing the result of the operation:
 *               - 'success' (bool): Whether the order was processed successfully.
 *               - 'order_id' (int|null): The ID of the created order (if successful).
 *               - 'message' (string): A message indicating the result of the operation.
 *
 * @throws Exception If an error occurs during the database operations or email sending.
 */
function processOrder($order) {
    $conn = connectDB();
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Insert order header
        $sql = "INSERT INTO orders (customer_id, order_date, delivery_address, phone, email, total_amount, payment_method, status) 
                VALUES (?, NOW(), ?, ?, ?, ?, ?, 'Pending')";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssds", 
            $order['customer_id'], 
            $order['delivery_address'], 
            $order['phone'], 
            $order['email'], 
            $order['total_amount'], 
            $order['payment_method']
        );
        
        $stmt->execute();
        $orderId = $stmt->insert_id;
        $stmt->close();
        
        // Insert order items
        foreach ($order['items'] as $item) {
            $sql = "INSERT INTO order_items (order_id, item_id, quantity, price) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiid", $orderId, $item['item_id'], $item['quantity'], $item['price']);
            $stmt->execute();
            $stmt->close();
        }
        
        // Commit transaction
        $conn->commit();
        
        // Send confirmation email
        sendOrderConfirmation($orderId, $order);
        
        $conn->close();

        return [
            'success' => true,
            'order_id' => $orderId,
            'message' => 'Order placed successfully!'
        ];
    } catch (Exception $e) {
        // Rollback in case of error
        $conn->rollback();
        
        $conn->close();
        return [
            'success' => false,
            'message' => 'Error processing order: ' . $e->getMessage()
        ];
    }
    
}

// Send order confirmation email
/**
 * Sends an order confirmation email to the customer.
 *
 * This function generates an HTML email containing the order details
 * and sends it to the customer's email address. The email includes
 * information such as the order ID, delivery address, phone number,
 * payment method, ordered items, and the total amount.
 *
 * @param int $orderId The unique identifier of the order.
 * @param array $order An associative array containing order details:
 *                     - 'email' (string): The customer's email address.
 *                     - 'delivery_address' (string): The delivery address for the order.
 *                     - 'phone' (string): The customer's phone number.
 *                     - 'payment_method' (string): The payment method used for the order.
 *                     - 'items' (array): A list of items in the order, where each item is an associative array:
 *                         - 'name' (string): The name of the item.
 *                         - 'quantity' (int): The quantity of the item.
 *                         - 'price' (float): The price of the item.
 *                     - 'total_amount' (float): The total amount for the order.
 *
 * @return void
 */
function sendOrderConfirmation($orderId, $order) {
    $to = $order['email'];
    $subject = "KFC Egypt - Order Confirmation #" . $orderId;
    
    $message = "
    <html>
    <head>
        <title>KFC Egypt - Order Confirmation</title>
    </head>
    <body>
        <div style='max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif;'>
            <div style='background-color: #e4002b; padding: 20px; text-align: center;'>
                <img src='https://www.example.com/kfc-logo.png' alt='KFC Logo' style='height: 50px;'>
            </div>
            <div style='padding: 20px; background-color: #f5f5f5;'>
                <h2>Thank you for your order!</h2>
                <p>Dear Customer,</p>
                <p>Your order #" . $orderId . " has been received and is being processed.</p>
                <p><strong>Delivery Address:</strong> " . $order['delivery_address'] . "</p>
                <p><strong>Phone:</strong> " . $order['phone'] . "</p>
                <p><strong>Payment Method:</strong> " . $order['payment_method'] . "</p>
                
                <table style='width: 100%; border-collapse: collapse; margin-top: 20px;'>
                    <thead>
                        <tr style='background-color: #f8c12c;'>
                            <th style='padding: 10px; text-align: left;'>Item</th>
                            <th style='padding: 10px; text-align: center;'>Quantity</th>
                            <th style='padding: 10px; text-align: right;'>Price</th>
                        </tr>
                    </thead>
                    <tbody>";
    
    foreach ($order['items'] as $item) {
        $message .= "
                        <tr>
                            <td style='padding: 10px; border-bottom: 1px solid #ddd;'>" . $item['name'] . "</td>
                            <td style='padding: 10px; border-bottom: 1px solid #ddd; text-align: center;'>" . $item['quantity'] . "</td>
                            <td style='padding: 10px; border-bottom: 1px solid #ddd; text-align: right;'>EGP " . number_format($item['price'], 2) . "</td>
                        </tr>";
    }
    
    $message .= "
                        <tr style='font-weight: bold;'>
                            <td colspan='2' style='padding: 10px; text-align: right;'>Total:</td>
                            <td style='padding: 10px; text-align: right;'>EGP " . number_format($order['total_amount'], 2) . "</td>
                        </tr>
                    </tbody>
                </table>
                
                <p style='margin-top: 20px;'>Expected delivery time: 30-45 minutes</p>
                <p>If you have any questions about your order, please contact our customer service at 16006.</p>
            </div>
            <div style='background-color: #333; color: white; padding: 20px; text-align: center;'>
                <p>&copy; 2025 KFC Egypt. All Rights Reserved.</p>
            </div>
        </div>
    </body>
    </html>";
    
    // To send HTML mail, the Content-type header must be set
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: KFC Egypt <noreply@kfc.com.eg>" . "\r\n";
    
    mail($to, $subject, $message, $headers);
}

// Get restaurant locations
/**
 * Retrieves a list of locations from the database, ordered by city and name.
 *
 * This function connects to the database, executes a query to fetch all
 * records from the `locations` table, and returns the results as an array.
 *
 * @return array An array of associative arrays, where each associative array
 *               represents a location record from the database.
 *
 * @throws Exception If there is an issue with the database connection or query execution.
 */
function getLocations() {
    $conn = connectDB();
    
    $sql = "SELECT * FROM locations ORDER BY city, name";
    $result = $conn->query($sql);
    
    $locations = [];
    while ($row = $result->fetch_assoc()) {
        $locations[] = $row;
    }
    
    $conn->close();
    
    return $locations;

}

// User authentication functions
/**
 * Registers a new user in the database.
 *
 * @param string $username The username of the user.
 * @param string $password The plaintext password of the user, which will be hashed before storing.
 * @param string $email The email address of the user.
 * @param string $phone The phone number of the user.
 * @param string $address The physical address of the user.
 *
 * @return array An associative array containing:
 *               - 'success' (bool): Whether the registration was successful.
 *               - 'user_id' (int|null): The ID of the newly registered user (if successful).
 *               - 'message' (string): A message indicating the result of the registration.
 */
function registerUser($username, $password, $email, $phone, $address) {
    $conn = connectDB();
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO users (username, password, email, phone, address, registration_date) 
            VALUES (?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $username, $hashedPassword, $email, $phone, $address);
    
    if ($stmt->execute()) {
        $userId = $stmt->insert_id;
        $stmt->close();
        $conn->close();
        return [
            'success' => true,
            'user_id' => $userId,
            'message' => 'Registration successful!'
        ];
    } else {
        $stmt->close();
        $conn->close();
        return [
            'success' => false,
            'message' => 'Registration failed: ' . $conn->error
        ];
    }
}

/**
 * Logs in a user by verifying their username and password.
 *
 * This function connects to the database, retrieves the user's details
 * based on the provided username, and verifies the password using
 * password hashing. If the credentials are valid, user session data
 * is initialized and a success response is returned. Otherwise, an
 * error response is returned.
 *
 * @param string $username The username of the user attempting to log in.
 * @param string $password The password of the user attempting to log in.
 * 
 * @return array An associative array containing:
 *               - 'success' (bool): Whether the login was successful.
 *               - 'message' (string): A message indicating the result of the login attempt.
 */
function loginUser($username, $password) {
    $conn = connectDB();
    
    $sql = "SELECT id, password, email, phone, address FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $user['email'];
            $_SESSION['phone'] = $user['phone'];
            $_SESSION['address'] = $user['address'];
            
            $stmt->close();
            $conn->close();
            
            return [
                'success' => true,
                'message' => 'Login successful!'
            ];
        }
    }
    
    $stmt->close();
    $conn->close();
    
    return [
        'success' => false,
        'message' => 'Invalid username or password'
    ];
}

/**
 * Logs out the currently authenticated user by destroying the session.
 *
 * @return array An associative array containing:
 *               - 'success' (bool): Indicates whether the logout was successful.
 *               - 'message' (string): A message confirming the logout.
 */
function logoutUser() {
    session_destroy();
    return [
        'success' => true,
        'message' => 'Logout successful!'
    ];
}

// Order tracking functions
/**
 * Retrieves the status and details of an order by its ID.
 *
 * This function connects to the database to fetch the order's status, 
 * order date, total amount, and payment method. Additionally, it retrieves 
 * the items associated with the order, including their quantity, price, 
 * name, and description.
 *
 * @param int $orderId The ID of the order to retrieve.
 * 
 * @return array An associative array containing:
 *               - 'success' (bool): Indicates whether the operation was successful.
 *               - 'order' (array|null): The order details (status, order_date, total_amount, payment_method) 
 *                 if the order is found, or null otherwise.
 *               - 'items' (array|null): An array of order items (quantity, price, item_name, description) 
 *                 if the order is found, or null otherwise.
 *               - 'message' (string|null): An error message if the order is not found.
 */
function getOrderStatus($orderId) {
    $conn = connectDB();
    
    $sql = "SELECT status, order_date, total_amount, payment_method FROM orders WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $orderId);
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $order = $result->fetch_assoc();
        
        // Get order items
        $sql = "SELECT oi.quantity, oi.price, mi.item_name, mi.description 
                FROM order_items oi 
                JOIN menu_items mi ON oi.item_id = mi.id 
                WHERE oi.order_id = ?";
        $stmtItems = $conn->prepare($sql);
        $stmtItems->bind_param("i", $orderId);
        
        $stmtItems->execute();
        $itemsResult = $stmtItems->get_result();
        
        $orderItems = [];
        while ($row = $itemsResult->fetch_assoc()) {
            $orderItems[] = $row;
        }
        
        $stmtItems->close();
        $stmt->close();
        $conn->close();
        
        return [
            'success' => true,
            'order' => $order,
            'items' => $orderItems
        ];
    }
    
    $stmt->close();
    $conn->close();
    
    return [
        'success' => false,
        'message' => 'Order not found'
    ];
}

// Update order status (admin function)
/**
 * Updates the status of an order in the database.
 *
 * @param int $orderId The ID of the order to update.
 * @param string $newStatus The new status to set for the order.
 * @return array An associative array containing:
 *               - 'success' (bool): Whether the update was successful.
 *               - 'message' (string): A message describing the result.
 */
function updateOrderStatus($orderId, $newStatus) {
    $conn = connectDB();
    
    $sql = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $newStatus, $orderId);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        return [
            'success' => true,
            'message' => 'Order status updated successfully!'
        ];
    } else {
        $stmt->close();
        $conn->close();
        return [
            'success' => false,
            'message' => 'Error updating order status'
        ];
    }
}

// Get user's previous orders

/**
 * Retrieves a list of orders for a specific user.
 *
 * @param int $userId The ID of the user whose orders are to be retrieved.
 * @return array An array of orders, where each order is represented as an associative array
 *               containing the following keys:
 *               - 'id': The ID of the order.
 *               - 'order_date': The date the order was placed.
 *               - 'total_amount': The total amount of the order.
 *               - 'status': The status of the order.
 */
function getUserOrders($userId) {
    $conn = connectDB();
    
    $sql = "SELECT id, order_date, total_amount, status FROM orders WHERE customer_id = ? ORDER BY order_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    return $orders;
}

// Add/Update user preferences

/**
 * Updates the preferences of a user in the database.
 *
 * @param int $userId The ID of the user whose preferences are being updated.
 * @param array $preferences An associative array containing the user's preferences:
 *                           - 'favorite_item' (string): The user's favorite item.
 *                           - 'delivery_notes' (string): Notes for delivery.
 *                           - 'newsletter_subscription' (int): Subscription status (1 for subscribed, 0 for unsubscribed).
 *
 * @return array An associative array with the following keys:
 *               - 'success' (bool): True if the update was successful, false otherwise.
 *               - 'message' (string): A message indicating the result of the operation.
 */
function updateUserPreferences($userId, $preferences) {
    $conn = connectDB();
    
    $sql = "UPDATE users SET 
            favorite_item = ?, 
            delivery_notes = ?, 
            newsletter_subscription = ? 
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", 
        $preferences['favorite_item'], 
        $preferences['delivery_notes'], 
        $preferences['newsletter_subscription'], 
        $userId
    );
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        return [
            'success' => true,
            'message' => 'Preferences updated successfully!'
        ];
    } else {
        $stmt->close();
        $conn->close();
        return [
            'success' => false,
            'message' => 'Error updating preferences'
        ];
    }
}

// Get menu categories
/**
 * Retrieves a list of distinct menu categories from the database.
 *
 * This function connects to the database, executes a query to fetch
 * all unique categories from the `menu_items` table, and returns them
 * as an array. The categories are ordered alphabetically.
 *
 * @return array An array of distinct menu categories.
 *
 * @throws Exception If there is an issue connecting to the database
 *                   or executing the query.
 */
function getMenuCategories() {
    $conn = connectDB();
    
    $sql = "SELECT DISTINCT category FROM menu_items ORDER BY category";
    $result = $conn->query($sql);
    
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['category'];
    }
    
    $conn->close();
    
    return $categories;
}

// Get special offers
/**
 * Retrieves the list of active special offers from the database.
 *
 * This function connects to the database, fetches all special offers
 * that are currently active (based on the `active` flag and the date range),
 * and orders them by their priority in descending order.
 *
 * @return array An array of associative arrays, where each associative array
 *               represents a special offer with its details.
 *
 * @throws Exception If there is an issue with the database connection or query execution.
 */
function getSpecialOffers() {
    $conn = connectDB();
    
    $sql = "SELECT * FROM special_offers WHERE active = 1 AND start_date <= NOW() AND end_date >= NOW() ORDER BY priority DESC";
    $result = $conn->query($sql);
    
    $offers = [];
    while ($row = $result->fetch_assoc()) {
        $offers[] = $row;
    }
    
    $conn->close();
    
    return $offers;
}

// Search menu items
/**
 * Searches for menu items in the database based on a search term.
 *
 * This function connects to the database, performs a search query on the
 * `menu_items` table, and retrieves items where the `item_name` or `description`
 * matches the provided search term (case-insensitive, partial match).
 *
 * @param string $searchTerm The term to search for in the menu items.
 * @return array An array of associative arrays, where each associative array
 *               represents a menu item that matches the search criteria.
 *
 * @throws mysqli_sql_exception If there is an error during the database query execution.
 */
function searchMenuItems($searchTerm) {
    $conn = connectDB();
    
    $sql = "SELECT * FROM menu_items WHERE item_name LIKE ? OR description LIKE ?";
    $searchPattern = "%{$searchTerm}%";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $searchPattern, $searchPattern);
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $menuItems = [];
    while ($row = $result->fetch_assoc()) {
        $menuItems[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    return $menuItems;
}

// Feedback and rating functions
/**
 * Submits feedback for a specific order.
 *
 * @param int $orderId The ID of the order for which feedback is being submitted.
 * @param int $rating The rating provided by the user (e.g., 1-5 scale).
 * @param string $comments Additional comments provided by the user.
 * 
 * @return array An associative array containing:
 *               - 'success' (bool): Indicates whether the feedback submission was successful.
 *               - 'message' (string): A message describing the result of the operation.
 */
function submitFeedback($orderId, $rating, $comments) {
    $conn = connectDB();
    
    $sql = "INSERT INTO feedback (order_id, rating, comments, submission_date) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $orderId, $rating, $comments);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        return [
            'success' => true,
            'message' => 'Thank you for your feedback!'
        ];
    } else {
        $stmt->close();
        $conn->close();
        return [
            'success' => false,
            'message' => 'Error submitting feedback'
        ];
    }
}

// Apply promo code
/**
 * Applies a promo code to a given total amount and calculates the discount.
 *
 * @param string $promoCode The promo code to be applied.
 * @param float $totalAmount The total amount of the order before applying the promo code.
 * 
 * @return array An associative array containing:
 *               - 'success' (bool): Whether the promo code was successfully applied.
 *               - 'discount_amount' (float, optional): The discount amount applied (if successful).
 *               - 'final_amount' (float, optional): The final amount after applying the discount (if successful).
 *               - 'message' (string): A message indicating the result of the operation.
 * 
 * The function checks the validity of the promo code, including:
 * - Whether the promo code is active.
 * - Whether the promo code is within its valid date range.
 * - Whether the total amount meets the minimum order requirement for the promo code.
 * 
 * If the promo code is valid, the discount is calculated based on the discount type
 * (percentage or fixed amount) and applied to the total amount.
 * 
 * Database connection and prepared statements are used to prevent SQL injection.
 */
function applyPromoCode($promoCode, $totalAmount) {
    $conn = connectDB();
    
    $sql = "SELECT discount_type, discount_value, minimum_order, valid_from, valid_until 
            FROM promo_codes 
            WHERE code = ? AND active = 1 AND valid_from <= NOW() AND valid_until >= NOW()";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $promoCode);
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $promo = $result->fetch_assoc();
        
        if ($totalAmount < $promo['minimum_order']) {
            $stmt->close();
            $conn->close();
            return [
                'success' => false,
                'message' => 'Minimum order amount not met for this promo code'
            ];
        }
        
        $discountAmount = 0;
        if ($promo['discount_type'] === 'percentage') {
            $discountAmount = $totalAmount * ($promo['discount_value'] / 100);
        } else {
            $discountAmount = $promo['discount_value'];
        }
        
        $finalAmount = $totalAmount - $discountAmount;
        
        $stmt->close();
        $conn->close();
        
        return [
            'success' => true,
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount,
            'message' => 'Promo code applied successfully!'
        ];
    } else {
        $stmt->close();
        $conn->close();
        return [
            'success' => false,
            'message' => 'Invalid or expired promo code'
        ];
    }
}

// Get recent notifications
/**
 * Retrieves a list of active notifications from the database.
 *
 * This function connects to the database, fetches notifications
 * that are marked as active, and returns them in descending order
 * of their creation date. The number of notifications retrieved
 * can be limited by the `$limit` parameter.
 *
 * @param int $limit The maximum number of notifications to retrieve. Default is 5.
 * @return array An array of associative arrays, where each associative array
 *               represents a notification record from the database.
 *
 * Example usage:
 * ```php
 * $notifications = getNotifications(10);
 * foreach ($notifications as $notification) {
 *     echo $notification['title'];
 * }
 * ```
 */
function getNotifications($limit = 5) {
    $conn = connectDB();
    
    $sql = "SELECT * FROM notifications WHERE active = 1 ORDER BY created_at DESC LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    return $notifications;
}

// Store subscription
/**
 * Subscribes a user to the newsletter by adding their email to the database.
 *
 * @param string $email The email address of the user to subscribe.
 * 
 * @return array An associative array containing:
 *               - 'success' (bool): True if the subscription was successful, false otherwise.
 *               - 'message' (string): A message indicating the result of the operation.
 */
function subscribeNewsletter($email) {
    $conn = connectDB();
    
    $sql = "INSERT INTO newsletter_subscribers (email, subscription_date) VALUES (?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        return [
            'success' => true,
            'message' => 'Successfully subscribed to newsletter!'
        ];
    } else {
        $stmt->close();
        $conn->close();
        return [
            'success' => false,
            'message' => 'Error subscribing to newsletter'
        ];
    }
}

// API endpoints
/**
 * Handles various actions based on the 'action' parameter in the GET request.
 * Supported actions include fetching menu items, deals, locations, categories, 
 * special offers, searching the menu, retrieving order status, and notifications.
 *
 * Actions:
 * - 'get_menu': Retrieves menu items, optionally filtered by category.
 *   Parameters:
 *     - category (optional): The category to filter menu items by.
 *
 * - 'get_deals': Retrieves available deals.
 *
 * - 'get_locations': Retrieves store locations.
 *
 * - 'get_categories': Retrieves menu categories.
 *
 * - 'get_offers': Retrieves special offers.
 *
 * - 'search_menu': Searches menu items based on a search term.
 *   Parameters:
 *     - search (optional): The search term to filter menu items.
 *
 * - 'get_order_status': Retrieves the status of a specific order.
 *   Parameters:
 *     - order_id (optional): The ID of the order to retrieve the status for.
 *
 * - 'get_notifications': Retrieves notifications, with an optional limit.
 *   Parameters:
 *     - limit (optional): The maximum number of notifications to retrieve (default: 5).
 *
 * Response:
 * - Returns a JSON-encoded response based on the action performed.
 *
 * Headers:
 * - Sets the 'Content-Type' header to 'application/json'.
 */
if (isset($_GET['action']) && !empty($_GET['action'])) {
    $action = $_GET['action'];
    $response = [];
    
    switch ($action) {
        case 'get_menu':
            $category = isset($_GET['category']) ? $_GET['category'] : '';
            $response = getMenuItems($category);
            break;
            
        case 'get_deals':
            $response = getDeals();
            break;
            
        case 'get_locations':
            $response = getLocations();
            break;
            
        case 'get_categories':
            $response = getMenuCategories();
            break;
            
        case 'get_offers':
            $response = getSpecialOffers();
            break;
            
        case 'search_menu':
            $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
            $response = searchMenuItems($searchTerm);
            break;
            
        case 'get_order_status':
            $orderId = isset($_GET['order_id']) ? $_GET['order_id'] : '';
            $response = getOrderStatus($orderId);
            break;
            
        case 'get_notifications':
            $limit = isset($_GET['limit']) ? $_GET['limit'] : 5;
            $response = getNotifications($limit);
            break;
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// POST request handlers
/**
 * Handles various POST actions for the KFC Egypt backend.
 *
 * Supported actions:
 * - `process_order`: Processes an order based on the provided JSON-encoded order data.
 * - `register`: Registers a new user with the provided username, password, email, phone, and address.
 * - `login`: Authenticates a user with the provided username and password.
 * - `logout`: Logs out the currently authenticated user.
 * - `update_order_status`: Updates the status of an order using the provided order ID and status.
 * - `update_preferences`: Updates user preferences such as favorite item, delivery notes, and newsletter subscription.
 * - `submit_feedback`: Submits feedback for an order using the provided order ID, rating, and comments.
 * - `apply_promo`: Applies a promo code to a total amount and returns the updated total.
 * - `subscribe_newsletter`: Subscribes a user to the newsletter using the provided email address.
 *
 * The response is returned as a JSON object.
 *
 * @global array $_POST The HTTP POST variables.
 * @global array $_SESSION The session variables (used for user ID in `update_preferences`).
 * @global array $_SERVER The server and execution environment information.
 *
 * @return void Outputs a JSON-encoded response and terminates the script.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $response = [];
    
    switch ($action) {
        case 'process_order':
            $order = json_decode($_POST['order'], true);
            $response = processOrder($order);
            break;
            
        case 'register':
            $response = registerUser($_POST['username'], $_POST['password'], $_POST['email'], $_POST['phone'], $_POST['address']);
            break;
            
        case 'login':
            $response = loginUser($_POST['username'], $_POST['password']);
            break;
            
        case 'logout':
            $response = logoutUser();
            break;
            
        case 'update_order_status':
            $response = updateOrderStatus($_POST['order_id'], $_POST['status']);
            break;
            
        case 'update_preferences':
            $preferences = [
                'favorite_item' => $_POST['favorite_item'],
                'delivery_notes' => $_POST['delivery_notes'],
                'newsletter_subscription' => $_POST['newsletter_subscription']
            ];
            $response = updateUserPreferences($_SESSION['user_id'], $preferences);
            break;
            
        case 'submit_feedback':
            $response = submitFeedback($_POST['order_id'], $_POST['rating'], $_POST['comments']);
            break;
            
        case 'apply_promo':
            $response = applyPromoCode($_POST['promo_code'], $_POST['total_amount']);
            break;
            
        case 'subscribe_newsletter':
            $response = subscribeNewsletter($_POST['email']);
            break;
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>