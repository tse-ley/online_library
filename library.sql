-- Step 1: Create the database
CREATE DATABASE IF NOT EXISTS library;

-- Step 2: Use the library database
USE library;

-- Step 3: Create the users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin', 'superadmin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Step 4: Create the authors table
CREATE TABLE IF NOT EXISTS authors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Step 5: Create the categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Step 6: Create the ebooks table
CREATE TABLE IF NOT EXISTS ebooks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author_id INT,
    category_id INT,
    isbn VARCHAR(13) UNIQUE,
    available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES authors(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Step 7: Create the checkouts table
CREATE TABLE IF NOT EXISTS checkouts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    ebook_id INT,
    issue_date DATE NOT NULL,
    return_date DATE NOT NULL,
    returned BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (ebook_id) REFERENCES ebooks(id) ON DELETE SET NULL
);

-- Step 8: Create indexes for performance optimization
CREATE INDEX idx_ebook_title ON ebooks(title);
CREATE INDEX idx_author_name ON authors(name);
CREATE INDEX idx_category_name ON categories(name);
CREATE INDEX idx_checkout_dates ON checkouts(issue_date, return_date);

-- Step 9: Insert a super admin user
INSERT INTO users (username, email, password, role)
VALUES ('superadmin', 'superadmin@example.com', 'hashed_password_here', 'superadmin');