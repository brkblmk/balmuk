<?php
require_once 'config/database.php';

$hashedPassword = '$2y$10$aTxVYkSEAw0iuLqKgQ0G.uVUt0tSV4dQGgzE5iaJHxgWUsL1FFI2S';

try {
    // Önce admin kullanıcısının var olup olmadığını kontrol et
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE username = 'admin'");
    $checkStmt->execute();
    $exists = $checkStmt->fetchColumn() > 0;

    if ($exists) {
        // Var ise güncelle
        $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE username = 'admin'");
        $result = $stmt->execute([$hashedPassword]);
        $action = "güncellendi";
    } else {
        // Yok ise oluştur
        $stmt = $pdo->prepare("INSERT INTO admins (username, password, full_name, role, is_active) VALUES (?, ?, ?, ?, ?)");
        $result = $stmt->execute(['admin', $hashedPassword, 'Sistem Yöneticisi', 'super_admin', 1]);
        $action = "oluşturuldu";
    }

    if ($result) {
        echo "Admin kullanıcısı başarıyla $action.\n";
    } else {
        echo "İşlem başarısız.\n";
    }
} catch (PDOException $e) {
    echo "Hata: " . $e->getMessage() . "\n";
}
?>