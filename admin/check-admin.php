<?php
require_once 'config/database.php';

echo "<h1>Admin Kullanıcıları</h1>";
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Kullanıcı Adı</th><th>Ad Soyad</th><th>Rol</th><th>Son Giriş</th><th>Aktif</th></tr>";

try {
    $stmt = $pdo->query("SELECT id, username, full_name, role, last_login, is_active FROM admins ORDER BY is_active DESC, last_login DESC");
    while ($row = $stmt->fetch()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['username'] . "</td>";
        echo "<td>" . $row['full_name'] . "</td>";
        echo "<td>" . $row['role'] . "</td>";
        echo "<td>" . ($row['last_login'] ?: 'Hiç giriş yapmamış') . "</td>";
        echo "<td>" . ($row['is_active'] ? 'Evet' : 'Hayır') . "</td>";
        echo "</tr>";
    }
} catch (PDOException $e) {
    echo "<tr><td colspan='6'>Hata: " . $e->getMessage() . "</td></tr>";
}

echo "</table>";

// Varsayılan admin kullanıcısı oluştur
echo "<h2>Varsayılan Admin Kullanıcısı Oluştur</h2>";
try {
    $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO admins (username, password, full_name, role, is_active) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE password = VALUES(password)");
    $stmt->execute(['admin', $hashedPassword, 'Sistem Yöneticisi', 'super_admin', 1]);
    echo "<p style='color: green;'>✅ admin kullanıcısı oluşturuldu/güncellendi. Şifre: admin123</p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>Hata: " . $e->getMessage() . "</p>";
}
?>