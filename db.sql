
-- Create Roles Table
CREATE TABLE Roles (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    roleName VARCHAR(50) NOT NULL UNIQUE
);

-- Insert roles with specific roleId values
INSERT INTO Roles (role_id, roleName) VALUES
(1, 'user'),
(2, 'super admin'),
(3, 'notification admin'),
(4, 'user admin');

-- Create Users Table
CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    role_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES Roles(role_id)
);

CREATE TABLE Permissions (
    permission_id INT AUTO_INCREMENT PRIMARY KEY,
    permission_name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE RolePermissions (
    role_permission_id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT,
    permission_id INT,
    FOREIGN KEY (role_id) REFERENCES Roles(role_id),
    FOREIGN KEY (permission_id) REFERENCES Permissions(permission_id)
);

DELIMITER //

CREATE PROCEDURE hash_passwords()
BEGIN
    DECLARE done INT DEFAULT 0;
    DECLARE user_id INT;
    DECLARE pwd VARCHAR(255);
    DECLARE cursor1 CURSOR FOR SELECT user_id, password_hash FROM Users;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN cursor1;

    read_loop: LOOP
        FETCH cursor1 INTO user_id, pwd;
        IF done THEN
            LEAVE read_loop;
        END IF;

        UPDATE Users
        SET password_hash = SHA2(pwd, 256)
        WHERE user_id = user_id;
    END LOOP;

    CLOSE cursor1;
END //

DELIMITER ;

DELIMITER //

CREATE TRIGGER before_user_insert
BEFORE INSERT ON Users
FOR EACH ROW
BEGIN
    SET NEW.password_hash = SHA2(NEW.password_hash, 256);
END //

DELIMITER ;




-- Inserting sample users
INSERT INTO Users (username, password_hash, email, fullname, role_id)
VALUES
('john_doe', 'password123', 'john@example.com', 'John Doe', 1),



CREATE TABLE ToDoList (
    todo_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    task VARCHAR(255) NOT NULL,
    is_completed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
);





CREATE TABLE Categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

2
CREATE TABLE Budgets (
    budget_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    amount_limit DECIMAL(10, 2) NOT NULL,
    month INT NOT NULL, -- Representing month as integer 1 to 12
    year INT NOT NULL, -- Representing year as integer
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (category_id) REFERENCES Categories(category_id),
    UNIQUE (user_id, category_id, month, year) -- Ensuring unique limit per category per user per month
);


CREATE TABLE Expenses (
    expense_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    description VARCHAR(255),
    date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (category_id) REFERENCES Categories(category_id),
    UNIQUE (user_id, category_id, date)
);

CREATE TABLE Goals (
    goal_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    goal_name VARCHAR(100) NOT NULL,
    target_amount DECIMAL(10, 2) NOT NULL,
    current_amount DECIMAL(10, 2) DEFAULT 0.00,
    target_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
);

CREATE TABLE Bills (
    bill_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    bill_name VARCHAR(100) NOT NULL,
    amount_due DECIMAL(10, 2) NOT NULL,
    due_date DATE NOT NULL,
    reminder_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reminders TINYINT(1) DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
);



INSERT INTO Categories (category_name) VALUES
('Food'),
('Clothing'),
('Stationary'),
('Transport'),
('Healthcare'),
('Entertainment'),
('Utilities'),
('Education'),
('Rent'),
('Savings');
('others');

-- Insert sample data into Budgets table
INSERT INTO Budgets (user_id, category_id, amount_limit, month, year) VALUES
(2, 1, 1000.00, 6, 2024),
(2, 2, 500.00, 6, 2024),
(2, 3, 200.00, 6, 2024),
(2, 4, 300.00, 6, 2024),
(2, 5, 400.00, 6, 2024),
(2, 6, 600.00, 6, 2024),
(2, 7, 700.00, 6, 2024),
(2, 8, 800.00, 6, 2024),
(2, 9, 900.00, 6, 2024),
(2, 10, 1000.00, 6, 2024);

-- Insert sample data into Expenses table
INSERT INTO Expenses (user_id, category_id, amount, description, date) VALUES
(2, 1, 150.00, 'Groceries', '2024-06-05'),
(2, 2, 75.00, 'New Shoes', '2024-06-10'),
(2, 3, 20.00, 'Pens and Notebooks', '2024-06-15'),
(2, 4, 50.00, 'Bus Fare', '2024-06-20'),
(2, 5, 100.00, 'Doctor Visit', '2024-06-25'),
(2, 6, 120.00, 'Movie Tickets', '2024-06-10'),
(2, 7, 150.00, 'Electricity Bill', '2024-06-18'),
(2, 8, 200.00, 'Course Materials', '2024-06-22'),
(2, 9, 850.00, 'June Rent', '2024-06-01'),
(2, 10, 50.00, 'Savings Deposit', '2024-06-28');

-- Insert sample data into Goals table
INSERT INTO Goals (user_id, goal_name, target_amount, current_amount, target_date) VALUES
(2, 'Vacation Trip', 2000.00, 500.00, '2024-12-31'),
(2, 'New Laptop', 1500.00, 300.00, '2024-09-30'),
(2, 'Emergency Fund', 10000.00, 2000.00, '2025-06-30'),
(2, 'Car Down Payment', 5000.00, 1000.00, '2024-11-30'),
(2, 'Home Renovation', 8000.00, 1500.00, '2025-01-31');

-- Insert sample data into Bills table
INSERT INTO Bills (user_id, bill_name, amount_due, due_date, reminder_date) VALUES
(2, 'Internet Bill', 50.00, '2024-06-10', '2024-06-05'),
(2, 'Water Bill', 30.00, '2024-06-15', '2024-06-10'),
(2, 'Credit Card Payment', 200.00, '2024-06-20', '2024-06-15'),
(2, 'Phone Bill', 45.00, '2024-06-25', '2024-06-20'),
(2, 'Car Insurance', 150.00, '2024-06-30', '2024-06-25');


INSERT INTO Permissions (permission_name) VALUES
('view_dashboard'),
('manage_users'),
('manage_notifications'),
('view_reports'),
('add_admin');

INSERT INTO RolePermissions (role_id, permission_id) VALUES
(2, 1), -- super admin can view_dashboard
(2, 2), -- super admin can manage_users
(2, 3), -- super admin can manage_notifications
(2, 5), -- super admin can manage_admins
(2, 4), -- super admin can view_reports
(3, 1), -- notification admin can view_dashboard
(3, 3), -- notification admin can manage_notifications
(4, 1), -- user admin can view_dashboard
(4, 2); -- user admin can manage_users


-- Inserting sample users
INSERT INTO Users (username, password_hash, email, fullname, role_id)
VALUES
('Emily', '12356', 'emily@gmail.com', 'Emily', 3),
('Bhuvi','bhuvi@2004','bhuvi@gmail.com','Bhuvaneshwari',2);

CREATE TABLE NotificationTemplates (
    noti_template_id INT AUTO_INCREMENT PRIMARY KEY,
    template_creator_id INT NOT NULL,
    noti_name varchar(255) UNIQUE NOT NULL,
    noti_template VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    FOREIGN KEY (template_creator_id) REFERENCES Users(user_id)
);

CREATE TABLE PushNotifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    noti_template_id INT,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (noti_template_id) REFERENCES NotificationTemplates(noti_template_id),
    FOREIGN KEY (sender_id) REFERENCES Users(user_id),
    FOREIGN KEY (receiver_id) REFERENCES Users(user_id),
    INDEX (receiver_id),  -- Added index for receiver_id to optimize read status queries
    INDEX (is_read)       -- Added index for is_read to optimize status update queries
);



INSERT INTO NotificationTemplates (template_creator_id, noti_name, noti_template) VALUES
(3, 'Budget Exceeded', 'Alert: You have exceeded your budget limit for this category.'),
(3, 'Bill Reminder', 'Reminder: You have an upcoming bill due soon.'),
(3, 'Monthly Budget Summary', 'Summary: Here is your monthly budget summary.'),
(3, 'Goal Achievement', 'Congratulations: You have achieved your financial goal!'),
(3, 'Weekly Expense Report', 'Report: Here is your weekly expense report.'),
(3,'Close to exceeding the budget','Alert: You are very to close to exceeding your budget.');

INSERT INTO PushNotifications (noti_template_id, sender_id, receiver_id, is_read) VALUES
(1, 3, 1, FALSE),
(2, 3, 1, FALSE),
(3, 3, 1, FALSE),
(4, 3, 1, FALSE),
(5, 3, 1, FALSE),
(6, 3, 1, FALSE);



DELIMITER //

CREATE TRIGGER BillReminderTrigger
AFTER INSERT ON Bills
FOR EACH ROW
BEGIN
    IF NEW.reminder_date = CURDATE() THEN
        INSERT INTO PushNotifications (noti_template_id, sender_id, receiver_id, is_read)
        VALUES (1, 2, NEW.user_id, FALSE);
    END IF;
END //

DELIMITER ;


DELIMITER //

CREATE TRIGGER GoalReminderTrigger
AFTER INSERT ON Goals
FOR EACH ROW
BEGIN
    IF NEW.target_date = CURDATE() THEN
        INSERT INTO PushNotifications (noti_template_id, sender_id, receiver_id, is_read)
        VALUES (4, 2, NEW.user_id, FALSE);  -- noti_template_id 4 for 'Goal Reminder', sender_id 2 by default
    END IF;
END //

DELIMITER ;














INSERT INTO Permissions (permission_name) VALUES
('view_users'),
('delete_users'),
('add_admins'),
('view_templates'),
('add_templates'),
('delete_admins'),
('grant_revoke_permissions');

-- Super Admin Permissions
INSERT INTO RolePermissions (role_id, permission_id)
VALUES
(2, 1), -- view_users
(2, 2), -- delete_users
(2, 3), -- add_admins
(2, 4), -- view_templates
(2, 5), -- add_templates
(2, 6), -- delete_admins
(2, 7); -- grant_revoke_permissions

-- Notification Admin Permissions
INSERT INTO RolePermissions (role_id, permission_id)
VALUES
(3, 1), -- view_users
(3, 4), -- view_templates
(3, 5); -- add_templates

-- User Admin Permissions
INSERT INTO RolePermissions (role_id, permission_id)
VALUES
(4, 1), -- view_users
(4, 2); -- delete_users



--user permissions
CREATE TABLE UserPermissions (
    user_permission_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    permission_id INT,
    is_granted BOOLEAN,
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (permission_id) REFERENCES Permissions(permission_id)
);

DELIMITER //

CREATE TRIGGER after_user_insert
AFTER INSERT ON Users
FOR EACH ROW
BEGIN
    -- Insert role-based permissions into UserPermissions
    INSERT INTO UserPermissions (user_id, permission_id, is_granted)
    SELECT NEW.user_id, rp.permission_id, TRUE
    FROM RolePermissions rp
    WHERE rp.role_id = NEW.role_id;
END;
//

DELIMITER ;

--already inserted users
DELIMITER //

CREATE PROCEDURE InsertPermissionsForExistingUsers()
BEGIN
    DECLARE done INT DEFAULT 0;
    DECLARE current_user_id INT;
    DECLARE current_role_id INT;
    DECLARE cur CURSOR FOR SELECT user_id, role_id FROM Users;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN cur;

    read_loop: LOOP
        FETCH cur INTO current_user_id, current_role_id;
        IF done THEN
            LEAVE read_loop;
        END IF;

        INSERT INTO UserPermissions (user_id, permission_id, is_granted)
        SELECT current_user_id, rp.permission_id, TRUE
        FROM RolePermissions rp
        WHERE rp.role_id = current_role_id;
    END LOOP;

    CLOSE cur;
END;
//

DELIMITER ;

CALL InsertPermissionsForExistingUsers();


INSERT INTO Expenses (user_id, category_id, amount, description, date)
VALUES
 
    (1, 1, 300.00, 'Dinner with friends', '2024-06-15'),
    (1, 1, 250.00, '', '2024-06-16'),
    (1, 1, 120.00, '', '2024-06-17'),
    (1, 1, 300.00, '', '2024-06-18'),
    (1, 1, 250.00, '', '2024-06-19'),
    (1, 1, 120.00, '', '2024-06-20'),
    (1, 1, 300.00, '', '2024-06-21'),
    (1, 1, 250.00, '', '2024-06-22'),
    (1, 1, 120.00, '', '2024-06-23'),
    (1, 1, 300.00, '', '2024-06-24'),
    (1, 1, 250.00, '', '2024-06-25'),
    (1, 1, 120.00, '', '2024-06-26'),
    (1, 1, 100.00, '', '2024-06-27');



    ALTER TABLE Categories
ADD COLUMN user_id INT;

UPDATE Categories
SET user_id = 3
WHERE user_id IS NOT NULL AND user_id NOT IN (SELECT user_id FROM Users);

ALTER TABLE Categories
ADD CONSTRAINT fk_user_id
FOREIGN KEY (user_id) REFERENCES Users(user_id);




CREATE TABLE adminbuffer (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    adder_id int not null,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    role_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (adder_id) REFERENCES Users(user_id)
);

    ALTER TABLE adminbuffer
ADD adminbuffer user_id INT;




CREATE TABLE BillReminders (
    reminder_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reminder_message VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
);
DELIMITER //
CREATE PROCEDURE InsertBillReminders()
BEGIN
    DECLARE today DATE;
    SET today = CURDATE();

    -- Insert reminders for bills with reminder_date = today
    INSERT INTO BillReminders (user_id, reminder_message)
    SELECT user_id, CONCAT('Reminder! Pay your bill (', bill_name, ')')
    FROM Bills
    WHERE reminder_date = today;

    -- Insert reminders for bills with due_date = today
    INSERT INTO BillReminders (user_id, reminder_message)
    SELECT user_id, CONCAT('Today is the due date to pay your bill (', bill_name, ')')
    FROM Bills
    WHERE due_date = today;
END //
DELIMITER ;

DELIMITER //

CREATE EVENT IF NOT EXISTS daily_reminder_check
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP + INTERVAL 1 DAY
DO
BEGIN
    CALL InsertBillReminders();
END //

DELIMITER ;

SHOW VARIABLES LIKE 'event_scheduler';
SET GLOBAL event_scheduler = ON;

ALTER EVENT daily_reminder_check
ON SCHEDULE EVERY 1 MINUTE;


ALTER EVENT daily_reminder_check
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP + INTERVAL 1 DAY;

--restart after 
SHOW EVENTS;
SET GLOBAL event_scheduler = OFF;
SET GLOBAL event_scheduler = ON;




CREATE TABLE colorcode (
    user_id INT PRIMARY KEY,
    startpercentage INT,
    endpercentage INT,
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
);