<?php
require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$mysqli = new mysqli($_ENV['DB_HOST'], $_ENV['MYSQL_USER'], $_ENV['MYSQL_PASSWORD'], $_ENV['MYSQL_DATABASE']);

if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

$sql = "CREATE TABLE IF NOT EXISTS page_views (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(255) NOT NULL,
    user_agent TEXT NOT NULL,
    view_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    page_url TEXT NOT NULL,
    views_count INT DEFAULT 1,
    UNIQUE KEY ip_user_agent_unique (ip_address, user_agent)
)";


if ($mysqli->query($sql) === TRUE) {
    echo "Table page_views created successfully";
} else {
    echo "Error creating table: " . $mysqli->error;
}

$mysqli->close();
