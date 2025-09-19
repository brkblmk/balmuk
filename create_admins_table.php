<?php
require_once 'config/database.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS admins (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100),
        email VARCHAR(100),
        role VARCHAR(50) DEFAULT 'admin',
        is_active TINYINT(1) DEFAULT 1,
        last_login DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    $pdo->exec($sql);
    echo "Admins tablosu başarıyla oluşturuldu.\n";
} catch (PDOException $e) {
    echo "Hata: " . $e->getMessage() . "\n";
}
?>