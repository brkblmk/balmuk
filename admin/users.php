<?php
require_once '../config/database.php';
require_once '../config/security.php';
require_once '../config/performance.php';

// Admin kontrolü
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Security check for admin session
SecurityUtils::secureSession();

// Check if session is expired (30 minutes)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    SecurityUtils::logSecurityEvent('ADMIN_SESSION_EXPIRED', ['admin_id' => $_SESSION['admin_id'] ?? null]);
    session_destroy();
    header('Location: login.php?expired=1');
    exit;
}
$_SESSION['last_activity'] = time();

$message = '';
$message_type = '';

// CRUD Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!SecurityUtils::verifyCSRFToken($csrf_token)) {
        SecurityUtils::logSecurityEvent('CSRF_TOKEN_INVALID', ['ip' => SecurityUtils::getClientIP(), 'action' => 'admin_users']);
        $message = 'Güvenlik hatası. Lütfen sayfayı yenileyip tekrar deneyin.';
        $message_type = 'danger';
    } else {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'create':
                $username = SecurityUtils::sanitizeInput($_POST['username'] ?? '', 'string');
                $email = SecurityUtils::sanitizeInput($_POST['email'] ?? '', 'email');
                $password = $_POST['password'] ?? '';
                $role = SecurityUtils::sanitizeInput($_POST['role'] ?? 'admin', 'string');
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                
                // Validation
                $errors = [];
                if (!SecurityUtils::validateInput($username, 'string', ['min_length' => 3, 'max_length' => 50])) {
                    $errors[] = 'Kullanıcı adı 3-50 karakter arası olmalıdır.';
                }
                if (!SecurityUtils::validateInput($email, 'email')) {
                    $errors[] = 'Geçerli bir e-posta adresi giriniz.';
                }
                if (!empty($errors)) {
                    $message = implode('<br>', $errors);
                    $message_type = 'danger';
                } else {
                    // Check if username or email already exists
                    $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE username = ? OR email = ?");
                    $check_stmt->execute([$username, $email]);
                    
                    if ($check_stmt->fetchColumn() > 0) {
                        $message = 'Bu kullanıcı adı veya e-posta zaten kullanılıyor.';
                        $message_type = 'warning';
                    } else {
                        // Password validation
                        $password_check = SecurityUtils::validatePasswordStrength($password);
                        if (!$password_check['valid']) {
                            $message = $password_check['error'];
                            $message_type = 'danger';
                        } else {
                            $hashed_password = SecurityUtils::hashPassword($password);
                            
                            $stmt = $pdo->prepare("INSERT INTO admins (username, email, password, role, is_active, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                            
                            if ($stmt->execute([$username, $email, $hashed_password, $role, $is_active])) {
                                SecurityUtils::logSecurityEvent('ADMIN_USER_CREATED', [
                                    'new_user_id' => $pdo->lastInsertId(),
                                    'new_username' => $username,
                                    'created_by' => $_SESSION['admin_id']
                                ]);
                                $message = 'Kullanıcı başarıyla oluşturuldu.';
                                $message_type = 'success';
                            } else {
                                $message = 'Kullanıcı oluşturulurken hata oluştu.';
                                $message_type = 'danger';
                            }
                        }
                    }
                }
                break;
                
            case 'update':
                $id = (int)$_POST['id'];
                $username = SecurityUtils::sanitizeInput($_POST['username'] ?? '', 'string');
                $email = SecurityUtils::sanitizeInput($_POST['email'] ?? '', 'email');
                $role = SecurityUtils::sanitizeInput($_POST['role'] ?? 'admin', 'string');
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                
                // Don't allow admin to deactivate themselves
                if ($id == $_SESSION['admin_id'] && !$is_active) {
                    $message = 'Kendi hesabınızı deaktive edemezsiniz.';
                    $message_type = 'warning';
                } else {
                    $stmt = $pdo->prepare("UPDATE admins SET username = ?, email = ?, role = ?, is_active = ? WHERE id = ?");
                    
                    if ($stmt->execute([$username, $email, $role, $is_active, $id])) {
                        SecurityUtils::logSecurityEvent('ADMIN_USER_UPDATED', [
                            'updated_user_id' => $id,
                            'updated_by' => $_SESSION['admin_id']
                        ]);
                        $message = 'Kullanıcı başarıyla güncellendi.';
                        $message_type = 'success';
                    } else {
                        $message = 'Kullanıcı güncellenirken hata oluştu.';
                        $message_type = 'danger';
                    }
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                
                // Don't allow admin to delete themselves
                if ($id == $_SESSION['admin_id']) {
                    $message = 'Kendi hesabınızı silemezsiniz.';
                    $message_type = 'warning';
                } else {
                    $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
                    
                    if ($stmt->execute([$id])) {
                        SecurityUtils::logSecurityEvent('ADMIN_USER_DELETED', [
                            'deleted_user_id' => $id,
                            'deleted_by' => $_SESSION['admin_id']
                        ]);
                        $message = 'Kullanıcı başarıyla silindi.';
                        $message_type = 'success';
                    } else {
                        $message = 'Kullanıcı silinirken hata oluştu.';
                        $message_type = 'danger';
                    }
                }
                break;
                
            case 'change_password':
                $id = (int)$_POST['id'];
                $new_password = $_POST['new_password'] ?? '';
                
                $password_check = SecurityUtils::validatePasswordStrength($new_password);
                if (!$password_check['valid']) {
                    $message = $password_check['error'];
                    $message_type = 'danger';
                } else {
                    $hashed_password = SecurityUtils::hashPassword($new_password);
                    $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
                    
                    if ($stmt->execute([$hashed_password, $id])) {
                        SecurityUtils::logSecurityEvent('ADMIN_PASSWORD_CHANGED', [
                            'user_id' => $id,
                            'changed_by' => $_SESSION['admin_id']
                        ]);
                        $message = 'Şifre başarıyla değiştirildi.';
                        $message_type = 'success';
                    } else {
                        $message = 'Şifre değiştirilirken hata oluştu.';
                        $message_type = 'danger';
                    }
                }
                break;
        }
    }
}

// Pagination
$page = (int)($_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;

// Search
$search = SecurityUtils::sanitizeInput($_GET['search'] ?? '', 'string');
$search_query = '';
$search_params = [];

if ($search) {
    $search_query = " WHERE username LIKE ? OR email LIKE ?";
    $search_params = ["%$search%", "%$search%"];
}

// Get total count
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM admins" . $search_query);
$count_stmt->execute($search_params);
$total_users = $count_stmt->fetchColumn();
$total_pages = ceil($total_users / $limit);

// Get users
$stmt = $pdo->prepare("SELECT * FROM admins" . $search_query . " ORDER BY created_at DESC LIMIT ? OFFSET ?");
$params = array_merge($search_params, [$limit, $offset]);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcı Yönetimi - Prime EMS Admin</title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="admin-content">
            <!-- Header -->
            <?php include 'includes/header.php'; ?>
            
            <!-- Page Content -->
            <div class="container-fluid p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">Kullanıcı Yönetimi</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                        <i class="bi bi-person-plus"></i> Yeni Kullanıcı
                    </button>
                </div>

                <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Search and Filters -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="Kullanıcı adı veya e-posta ile ara..." 
                                           value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary me-2">Ara</button>
                                <a href="users.php" class="btn btn-outline-secondary">Temizle</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Kullanıcı Adı</th>
                                        <th>E-posta</th>
                                        <th>Rol</th>
                                        <th>Durum</th>
                                        <th>Son Giriş</th>
                                        <th>Oluşturma</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                            <?php if ($user['id'] == $_SESSION['admin_id']): ?>
                                                <span class="badge bg-info ms-1">Siz</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <?php
                                            $role_labels = [
                                                'super_admin' => '<span class="badge bg-danger">Süper Admin</span>',
                                                'admin' => '<span class="badge bg-primary">Admin</span>',
                                                'moderator' => '<span class="badge bg-warning">Moderatör</span>'
                                            ];
                                            echo $role_labels[$user['role']] ?? '<span class="badge bg-secondary">' . ucfirst($user['role']) . '</span>';
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($user['is_active']): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Pasif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            echo $user['last_login'] 
                                                ? date('d.m.Y H:i', strtotime($user['last_login'])) 
                                                : '<span class="text-muted">Hiç giriş yapmamış</span>'; 
                                            ?>
                                        </td>
                                        <td><?php echo date('d.m.Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-warning"
                                                        onclick="changePassword(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                                    <i class="bi bi-key"></i>
                                                </button>
                                                <?php if ($user['id'] != $_SESSION['admin_id']): ?>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                        onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if (empty($users)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-people display-1 text-muted"></i>
                            <p class="mt-3 text-muted">Kullanıcı bulunamadı.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Create User Modal -->
    <div class="modal fade" id="createUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo SecurityUtils::generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="modal-header">
                        <h5 class="modal-title">Yeni Kullanıcı Oluştur</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="username" class="form-label">Kullanıcı Adı</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">E-posta</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Şifre</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="form-text">En az 8 karakter, büyük/küçük harf, sayı ve özel karakter içermelidir.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="role" class="form-label">Rol</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="admin">Admin</option>
                                <option value="moderator">Moderatör</option>
                                <option value="super_admin">Süper Admin</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                <label class="form-check-label" for="is_active">
                                    Aktif kullanıcı
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Oluştur</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="editUserForm">
                    <input type="hidden" name="csrf_token" value="<?php echo SecurityUtils::generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="modal-header">
                        <h5 class="modal-title">Kullanıcı Düzenle</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_username" class="form-label">Kullanıcı Adı</label>
                            <input type="text" class="form-control" id="edit_username" name="username" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">E-posta</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_role" class="form-label">Rol</label>
                            <select class="form-select" id="edit_role" name="role" required>
                                <option value="admin">Admin</option>
                                <option value="moderator">Moderatör</option>
                                <option value="super_admin">Süper Admin</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active">
                                <label class="form-check-label" for="edit_is_active">
                                    Aktif kullanıcı
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Güncelle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="changePasswordForm">
                    <input type="hidden" name="csrf_token" value="<?php echo SecurityUtils::generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="change_password">
                    <input type="hidden" name="id" id="password_user_id">
                    
                    <div class="modal-header">
                        <h5 class="modal-title">Şifre Değiştir</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    
                    <div class="modal-body">
                        <p>Kullanıcı: <strong id="password_username"></strong></p>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Yeni Şifre</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <div class="form-text">En az 8 karakter, büyük/küçük harf, sayı ve özel karakter içermelidir.</div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-warning">Şifreyi Değiştir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="deleteUserForm">
                    <input type="hidden" name="csrf_token" value="<?php echo SecurityUtils::generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete_user_id">
                    
                    <div class="modal-header">
                        <h5 class="modal-title">Kullanıcıyı Sil</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    
                    <div class="modal-body">
                        <p>Bu kullanıcıyı silmek istediğinizden emin misiniz?</p>
                        <p><strong id="delete_username"></strong></p>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i>
                            Bu işlem geri alınamaz!
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-danger">Sil</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/mobile-nav.js"></script>
    
    <script>
        function editUser(user) {
            document.getElementById('edit_id').value = user.id;
            document.getElementById('edit_username').value = user.username;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_role').value = user.role;
            document.getElementById('edit_is_active').checked = user.is_active == 1;
            
            const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
            modal.show();
        }
        
        function changePassword(id, username) {
            document.getElementById('password_user_id').value = id;
            document.getElementById('password_username').textContent = username;
            
            const modal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
            modal.show();
        }
        
        function deleteUser(id, username) {
            document.getElementById('delete_user_id').value = id;
            document.getElementById('delete_username').textContent = username;
            
            const modal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
            modal.show();
        }
    </script>
</body>
</html>