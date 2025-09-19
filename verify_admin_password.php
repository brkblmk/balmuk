<?php
require_once 'config/database.php';

$expectedHash = '$2y$10$aTxVYkSEAw0iuLqKgQ0G.uVUt0tSV4dQGgzE5iaJHxgWUsL1FFI2S';

try {
    $stmt = $pdo->prepare("SELECT id, username, password, full_name, role, is_active FROM admins WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        echo "Admin kullanıcısı bulundu:\n";
        echo "- ID: " . $admin['id'] . "\n";
        echo "- Kullanıcı adı: " . $admin['username'] . "\n";
        echo "- Ad Soyad: " . $admin['full_name'] . "\n";
        echo "- Rol: " . $admin['role'] . "\n";
        echo "- Aktif: " . ($admin['is_active'] ? 'Evet' : 'Hayır') . "\n";
        echo "- Şifre hash: " . $admin['password'] . "\n";

        if ($admin['password'] === $expectedHash) {
            echo "\n✅ Şifre hash doğrulaması başarılı! 'admin123' şifresi doğru şekilde ayarlandı.\n";
        } else {
            echo "\n❌ Şifre hash uyumsuzluğu!\n";
            echo "Beklenen: " . $expectedHash . "\n";
            echo "Mevcut: " . $admin['password'] . "\n";
        }

        // Şifre doğrulama testi
        if (password_verify('admin123', $admin['password'])) {
            echo "\n✅ Şifre doğrulama testi başarılı! 'admin123' ile giriş yapılabilir.\n";
        } else {
            echo "\n❌ Şifre doğrulama testi başarısız!\n";
        }
    } else {
        echo "Admin kullanıcısı bulunamadı.\n";
    }
} catch (PDOException $e) {
    echo "Hata: " . $e->getMessage() . "\n";
}
?>