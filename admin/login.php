<?php
session_start();
require_once '../config/security.php';
require_once '../config/database.php';

$error = '';
$message = '';

// CSRF Token oluştur
$csrf_token = \SecurityUtils::generateCSRFToken();

// Logout mesajı kontrolü
if (isset($_GET['logout'])) {
    $message = 'Başarıyla çıkış yaptınız.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Token doğrulama
    if (!isset($_POST['csrf_token']) || !\SecurityUtils::verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Geçersiz güvenlik tokenı. Lütfen sayfayı yenileyin ve tekrar deneyin.';
    } else {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if ($username && $password) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? AND is_active = 1");
                $stmt->execute([$username]);
                $admin = $stmt->fetch();

                if ($admin && password_verify($password, $admin['password'])) {
                    // Login başarılı
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_name'] = $admin['full_name'] ?? $admin['username'];
                    $_SESSION['admin_role'] = $admin['role'];

                    // Last login güncelle
                    $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?")->execute([$admin['id']]);

                    // Log kaydet
                    logActivity('login', 'auth', null, $admin['id']);

                    // Yeni CSRF token oluştur (güvenlik için)
                    \SecurityUtils::generateCSRFToken();

                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'Kullanıcı adı veya şifre hatalı!';
                }
            } catch (PDOException $e) {
                $error = 'Bir hata oluştu. Lütfen tekrar deneyin.';
            }
        } else {
            $error = 'Lütfen tüm alanları doldurun!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Giriş - Prime EMS Studios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/theme.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .login-header {
            background: var(--prime-gradient);
            padding: 30px;
            text-align: center;
        }
        
        .login-header h1 {
            color: var(--prime-dark);
            font-size: 1.8rem;
            margin: 0;
            font-weight: 700;
        }
        
        .login-body {
            padding: 40px 30px;
        }
        
        .form-floating label {
            color: #6c757d;
        }
        
        .form-control:focus {
            border-color: var(--prime-gold);
            box-shadow: 0 0 0 0.25rem rgba(255, 215, 0, 0.25);
        }
        
        .btn-login {
            background: var(--prime-gradient);
            border: none;
            color: var(--prime-dark);
            font-weight: 600;
            padding: 12px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);
            color: var(--prime-dark);
        }
        
        .login-footer {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
        }
        
        .remember-me {
            color: #6c757d;
        }
        
        .remember-me input:checked {
            background-color: var(--prime-gold);
            border-color: var(--prime-gold);
        }
        
        .alert {
            border-radius: 10px;
        }
        
        .logo-icon {
            font-size: 3rem;
            color: var(--prime-dark);
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="bi bi-lightning-charge-fill logo-icon"></i>
                <h1>Prime EMS Studios</h1>
                <p class="mb-0 text-dark-50">Admin Panel</p>
            </div>
            
            <div class="login-body">
                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="username" name="username" 
                               placeholder="Kullanıcı Adı" required autofocus>
                        <label for="username">
                            <i class="bi bi-person-fill me-2"></i>Kullanıcı Adı
                        </label>
                    </div>
                    
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Şifre" required>
                        <label for="password">
                            <i class="bi bi-lock-fill me-2"></i>Şifre
                        </label>
                    </div>

                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                    <div class="form-check mb-4 remember-me">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                        <label class="form-check-label" for="remember">
                            Beni hatırla
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-login w-100 mb-3">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        Giriş Yap
                    </button>
                    
                    <div class="text-center">
                        <a href="#" class="text-muted text-decoration-none">
                            <small>Şifremi unuttum</small>
                        </a>
                    </div>
                </form>
            </div>
            
            <div class="login-footer">
                <small class="text-muted">
                    &copy; 2024 Prime EMS Studios. Tüm hakları saklıdır.
                </small>
            </div>
        </div>
        
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>