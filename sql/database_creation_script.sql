USE y1d13;
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'customer',
    account_status ENUM('active', 'inactive', 'locked') DEFAULT 'active',
    failed_login_attempts INT DEFAULT 0,
    last_login_date DATETIME,
    password_changed_date DATETIME,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    verification_token VARCHAR(64),
    reset_token VARCHAR(64),
    reset_token_expires DATETIME,
    INDEX idx_email (email),
    INDEX idx_role (role) 
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(50) NOT NULL,
    parent_id INT NULL,
    FOREIGN KEY (parent_id) REFERENCES categories(category_id) ON DELETE CASCADE 
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
CREATE TABLE IF NOT EXISTS products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    description TEXT,
    stock_quantity INT NOT NULL DEFAULT 100,
    low_stock_threshold INT NOT NULL DEFAULT 10,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE 
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM(
        'pending',
        'processing',
        'completed',
        'cancelled'
    ) DEFAULT 'pending',
    shipping_address TEXT,
    payment_method VARCHAR(50),
    notes TEXT,
    last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_order_date (order_date) 
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
CREATE TABLE IF NOT EXISTS order_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE RESTRICT,
    INDEX idx_order_id (order_id),
    INDEX idx_product_id (product_id) 
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
CREATE TABLE IF NOT EXISTS login_attempts (
    attempt_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100),
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(255),
    attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    success BOOLEAN DEFAULT FALSE,
    INDEX idx_email (email),
    INDEX idx_ip_address (ip_address),
    INDEX idx_attempt_time (attempt_time) 
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
CREATE TABLE IF NOT EXISTS security_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    event_type VARCHAR(50) NOT NULL,
    event_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    description TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE
    SET
        NULL 
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
CREATE TABLE IF NOT EXISTS user_sessions (
    session_id VARCHAR(128) PRIMARY KEY,
    user_id INT NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at) 
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
CREATE TABLE IF NOT EXISTS password_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    change_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id) 
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
CREATE TABLE IF NOT EXISTS inventory_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    event_type ENUM('order', 'restock', 'adjustment', 'low_stock') NOT NULL,
    quantity INT NOT NULL,
    before_quantity INT,
    after_quantity INT,
    order_id INT,
    user_id INT,
    low_stock_threshold INT,
    description TEXT,
    log_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE
    SET
        NULL,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE
    SET
        NULL,
        INDEX idx_product_id (product_id),
        INDEX idx_event_type (event_type),
        INDEX idx_log_date (log_date) 
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
CREATE TABLE IF NOT EXISTS order_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    status VARCHAR(20) NOT NULL,
    user_id INT,
    notes TEXT,
    change_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE
    SET
        NULL,
        INDEX idx_order_id (order_id),
        INDEX idx_change_date (change_date) 
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
DELIMITER 
CREATE TRIGGER password_history_trigger
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    IF OLD.password != NEW.password THEN
INSERT INTO
    password_history (user_id, password_hash)
VALUES
    (NEW.user_id, NEW.password);
END IF;
END 
DELIMITER ;
DELIMITER 
CREATE TRIGGER order_status_change_trigger
AFTER UPDATE ON orders
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
INSERT INTO
    order_history (order_id, status, user_id, notes)
VALUES
    (
        NEW.order_id,
        NEW.status,
        NULL,
        CONCAT(
            'Status changed from ',
            OLD.status,
            ' to ',
            NEW.status
        )
    );
END IF;
END 
DELIMITER ;
DELIMITER 
CREATE TRIGGER inventory_order_trigger
AFTER INSERT ON order_items
FOR EACH ROW
BEGIN
    DECLARE current_stock INT;
DECLARE product_name VARCHAR(100);
SELECT
    stock_quantity,
    name INTO current_stock,
    product_name
FROM
    products
WHERE
    product_id = NEW.product_id;
SET
    current_stock = current_stock - NEW.quantity;
UPDATE
    products
SET
    stock_quantity = current_stock
WHERE
    product_id = NEW.product_id;
INSERT INTO
    inventory_logs (
        product_id,
        event_type,
        quantity,
        before_quantity,
        after_quantity,
        order_id,
        description
    )
VALUES
    (
        NEW.product_id,
        'order',
        NEW.quantity,
        current_stock + NEW.quantity,
        current_stock,
        NEW.order_id,
        CONCAT(
            'Order #',
            NEW.order_id,
            ': ',
            NEW.quantity,
            ' units of "',
            product_name,
            '" purchased'
        )
    );
END 
DELIMITER ;
DELIMITER 
CREATE PROCEDURE record_login_attempt(
    IN p_email VARCHAR(100),
    IN p_ip_address VARCHAR(45),
    IN p_user_agent VARCHAR(255),
    IN p_success BOOLEAN
) BEGIN 
INSERT INTO
    login_attempts (email, ip_address, user_agent, success)
VALUES
    (p_email, p_ip_address, p_user_agent, p_success);
IF p_success
AND p_email IS NOT NULL THEN
UPDATE
    users
SET
    failed_login_attempts = 0,
    last_login_date = NOW()
WHERE
    email = p_email;
ELSEIF NOT p_success
AND p_email IS NOT NULL THEN
UPDATE
    users
SET
    failed_login_attempts = failed_login_attempts + 1
WHERE
    email = p_email;
UPDATE
    users
SET
    account_status = 'locked'
WHERE
    email = p_email
    AND failed_login_attempts >= 5;
END IF;
END 
DELIMITER ;
DELIMITER 
CREATE PROCEDURE log_security_event(
    IN p_user_id INT,
    IN p_event_type VARCHAR(50),
    IN p_ip_address VARCHAR(45),
    IN p_description TEXT
) BEGIN
INSERT INTO
    security_logs (user_id, event_type, ip_address, description)
VALUES
    (
        p_user_id,
        p_event_type,
        p_ip_address,
        p_description
    );
END 
DELIMITER ;
DELIMITER 
CREATE PROCEDURE clean_expired_sessions()
BEGIN
DELETE FROM
    user_sessions
WHERE
    expires_at < NOW();
END 
DELIMITER ;
CREATE EVENT clean_sessions_event ON SCHEDULE EVERY 1 HOUR DO CALL clean_expired_sessions();
DELIMITER 
CREATE PROCEDURE create_order(
    IN p_user_id INT,
    IN p_items JSON,
    IN p_shipping_address TEXT,
    IN p_payment_method VARCHAR(50),
    IN p_notes TEXT,
    OUT p_order_id INT,
    OUT p_success BOOLEAN,
    OUT p_message VARCHAR(255) 
) proc: BEGIN DECLARE v_total DECIMAL(10, 2) DEFAULT 0;
DECLARE v_item_count INT DEFAULT 0;
DECLARE v_product_id INT;
DECLARE v_quantity INT;
DECLARE v_price DECIMAL(10, 2);
DECLARE v_stock INT;
DECLARE v_product_name VARCHAR(100);
DECLARE i INT DEFAULT 0;
DECLARE v_items_length INT;
DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK;
SET
    p_success = FALSE;
SET
    p_message = 'An error occurred while processing the order.';
END;
START TRANSACTION;
IF NOT EXISTS (
    SELECT
        1
    FROM
        users
    WHERE
        user_id = p_user_id
) THEN
SET
    p_success = FALSE;
SET
    p_message = 'User not found.';
ROLLBACK;
LEAVE proc;
END IF;
SET
    v_items_length = JSON_LENGTH(p_items);
IF v_items_length = 0 THEN
SET
    p_success = FALSE;
SET
    p_message = 'No items in order.';
ROLLBACK;
LEAVE proc;
END IF;
WHILE i < v_items_length DO 
SET
    v_product_id = JSON_EXTRACT(p_items, CONCAT('$[', i, '].product_id'));
SET
    v_quantity = JSON_EXTRACT(p_items, CONCAT('$[', i, '].quantity'));
SELECT
    stock_quantity,
    price,
    name,
    is_active INTO v_stock,
    v_price,
    v_product_name,
    @is_active
FROM
    products
WHERE
    product_id = v_product_id;
IF v_price IS NULL THEN
SET
    p_success = FALSE;
SET
    p_message = CONCAT('Product ID ', v_product_id, ' not found.');
ROLLBACK;
LEAVE proc;
END IF;
IF @is_active = 0 THEN
SET
    p_success = FALSE;
SET
    p_message = CONCAT(
        'Product "',
        v_product_name,
        '" is not available for purchase.'
    );
ROLLBACK;
LEAVE proc;
END IF;
IF v_stock < v_quantity THEN
SET
    p_success = FALSE;
SET
    p_message = CONCAT(
        'Not enough stock for "',
        v_product_name,
        '". Available: ',
        v_stock
    );
ROLLBACK;
LEAVE proc;
END IF;
SET
    v_total = v_total + (v_price * v_quantity);
SET
    v_item_count = v_item_count + 1;
SET
    i = i + 1;
END WHILE;
INSERT INTO
    orders (
        user_id,
        total_amount,
        shipping_address,
        payment_method,
        notes
    )
VALUES
    (
        p_user_id,
        v_total,
        p_shipping_address,
        p_payment_method,
        p_notes
    );
SET
    p_order_id = LAST_INSERT_ID();
SET
    i = 0;
WHILE i < v_items_length DO 
SET
    v_product_id = JSON_EXTRACT(p_items, CONCAT('$[', i, '].product_id'));
SET
    v_quantity = JSON_EXTRACT(p_items, CONCAT('$[', i, '].quantity'));
SELECT
    price INTO v_price
FROM
    products
WHERE
    product_id = v_product_id;
INSERT INTO
    order_items (order_id, product_id, quantity, price)
VALUES
    (p_order_id, v_product_id, v_quantity, v_price);
SET
    i = i + 1;
END WHILE;
COMMIT;
SET
    p_success = TRUE;
SET
    p_message = CONCAT(
        'Order created successfully with ',
        v_item_count,
        ' items.'
    );
END 
DELIMITER ;
INSERT INTO
    categories (category_id, category_name, parent_id)
VALUES
    (1, 'Baked Goods', NULL),
    (2, 'Dairy Products', NULL),
    (3, 'Fruits & Veggies', NULL),
    (4, 'Meat', NULL),
    (5, 'Spices & Seasonings', NULL);
INSERT INTO
    categories (category_id, category_name, parent_id)
VALUES
    (6, 'Bread', 1),
    (7, 'Pastries & Sweets', 1),
    (8, 'Milk & Cream', 2),
    (9, 'Cheese & Yogurt', 2),
    (10, 'Butter', 2),
    (11, 'Fruits', 3),
    (12, 'Vegetables', 3),
    (13, 'Poultry', 4),
    (14, 'Beef & Pork', 4),
    (15, 'Fish', 4),
    (16, 'Ground Spices', 5),
    (17, 'Whole Spices & Herbs', 5);
INSERT INTO
    products (
        product_id,
        category_id,
        name,
        price,
        image_path,
        description,
        stock_quantity
    )
VALUES
    (
        1,
        6,
        'Whole Wheat Bread',
        3.49,
        'assets/images/products/whole_wheat_bread.png',
        'Freshly baked whole wheat bread, perfect for sandwiches.',
        150 
    ),
    (
        2,
        6,
        'French Baguette',
        2.99,
        'assets/images/products/french_baguette.png',
        'Authentic French baguette with crispy crust and soft interior.',
        120 
    ),
    (
        3,
        7,
        'Chocolate Chip Cookies (Pack of 12)',
        4.99,
        'assets/images/products/chocolate_chip_cookies.png',
        'Homemade style chocolate chip cookies, soft and chewy.',
        100 
    ),
    (
        4,
        7,
        'Blueberry Muffins (Pack of 6)',
        5.99,
        'assets/images/products/blueberry_muffins.png',
        'Fluffy muffins loaded with juicy blueberries.',
        80 
    ),
    (
        5,
        7,
        'Croissants (Pack of 4)',
        6.49,
        'assets/images/products/croissants.png',
        'Buttery, flaky croissants baked to golden perfection.',
        90 
    );
INSERT INTO
    products (
        product_id,
        category_id,
        name,
        price,
        image_path,
        description,
        stock_quantity
    )
VALUES
    (
        6,
        8,
        'Whole Milk (1L)',
        2.49,
        'assets/images/products/whole_milk.png',
        'Fresh whole milk from local farms.',
        200 
    ),
    (
        7,
        9,
        'Cheddar Cheese (250g)',
        4.99,
        'assets/images/products/cheddar_cheese.png',
        'Sharp cheddar cheese, perfect for sandwiches and cooking.',
        150 
    ),
    (
        8,
        9,
        'Greek Yogurt (500g)',
        3.99,
        'assets/images/products/greek_yogurt.png',
        'Creamy Greek yogurt, high in protein.',
        120 
    ),
    (
        9,
        10,
        'Butter (250g)',
        3.49,
        'assets/images/products/butter.png',
        'Pure butter made from pasteurized cream.',
        180 
    ),
    (
        10,
        8,
        'Sour Cream (300g)',
        2.79,
        'assets/images/products/sour_cream.png',
        'Rich and tangy sour cream, perfect for dips and toppings.',
        100 
    );
INSERT INTO
    products (
        product_id,
        category_id,
        name,
        price,
        image_path,
        description,
        stock_quantity
    )
VALUES
    (
        11,
        11,
        'Bananas (1kg)',
        1.99,
        'assets/images/products/bananas.png',
        'Fresh yellow bananas, rich in potassium.',
        250 
    ),
    (
        12,
        12,
        'Broccoli (each)',
        2.29,
        'assets/images/products/broccoli.png',
        'Crisp, fresh broccoli heads, locally sourced.',
        150 
    ),
    (
        13,
        11,
        'Avocado (each)',
        1.79,
        'assets/images/products/avocado.png',
        'Ripe avocados, perfect for guacamole or toast.',
        120 
    ),
    (
        14,
        12,
        'Spinach (300g)',
        2.99,
        'assets/images/products/spinach.png',
        'Fresh spinach leaves, washed and ready to use.',
        100 
    ),
    (
        15,
        11,
        'Red Apples (1kg)',
        3.49,
        'assets/images/products/red_apples.png',
        'Crisp and sweet red apples, perfect for snacking.',
        180 
    );
INSERT INTO
    products (
        product_id,
        category_id,
        name,
        price,
        image_path,
        description,
        stock_quantity
    )
VALUES
    (
        16,
        13,
        'Chicken Breast (500g)',
        7.99,
        'assets/images/products/chicken_breast.png',
        'Boneless, skinless chicken breast, high in protein.',
        150 
    ),
    (
        17,
        14,
        'Ground Beef (500g)',
        6.99,
        'assets/images/products/ground_beef.png',
        'Lean ground beef, ideal for burgers and pasta dishes.',
        130 
    ),
    (
        18,
        15,
        'Salmon Fillet (300g)',
        9.99,
        'assets/images/products/salmon_fillet.png',
        'Fresh Atlantic salmon fillet, rich in omega-3.',
        80 
    ),
    (
        19,
        14,
        'Pork Chops (500g)',
        8.49,
        'assets/images/products/pork_chops.png',
        'Tender pork chops, perfect for grilling or roasting.',
        100 
    ),
    (
        20,
        13,
        'Turkey Breast (400g)',
        7.49,
        'assets/images/products/turkey_breast.png',
        'Lean turkey breast, low in fat and high in protein.',
        90 
    );
INSERT INTO
    products (
        product_id,
        category_id,
        name,
        price,
        image_path,
        description,
        stock_quantity
    )
VALUES
    (
        21,
        16,
        'Ground Cinnamon (50g)',
        2.99,
        'assets/images/products/ground_cinnamon.png',
        'Aromatic ground cinnamon, perfect for baking and beverages.',
        150 
    ),
    (
        22,
        17,
        'Black Peppercorns (100g)',
        3.49,
        'assets/images/products/black_peppercorns.png',
        'Whole black peppercorns for fresh grinding.',
        120 
    ),
    (
        23,
        16,
        'Paprika (40g)',
        2.49,
        'assets/images/products/paprika.png',
        'Sweet Hungarian paprika, adds color and flavor to dishes.',
        100 
    ),
    (
        24,
        17,
        'Italian Herb Mix (30g)',
        3.99,
        'assets/images/products/italian_herbs.png',
        'Blend of oregano, basil, thyme, and other Mediterranean herbs.',
        90 
    ),
    (
        25,
        16,
        'Garlic Powder (60g)',
        2.79,
        'assets/images/products/garlic_powder.png',
        'Fine garlic powder, great for seasoning any dish.',
        110 
    );
