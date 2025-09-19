<header class="admin-header">
    <div class="d-flex justify-content-between align-items-center w-100">
        <div class="d-flex align-items-center">
            <button class="sidebar-toggle d-lg-none me-3">
                <i class="bi bi-list"></i>
            </button>
                
                <div class="search-box d-none d-md-block">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" placeholder="Ara...">
                    </div>
                </div>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                <!-- Notifications -->
                <div class="dropdown">
                    <button class="btn btn-link text-dark position-relative" data-bs-toggle="dropdown">
                        <i class="bi bi-bell fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            3
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <h6 class="dropdown-header">Bildirimler</h6>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-calendar-check text-success"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <small class="text-muted">5 dk önce</small>
                                    <p class="mb-0">Yeni randevu talebi</p>
                                </div>
                            </div>
                        </a>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-person-plus text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <small class="text-muted">1 saat önce</small>
                                    <p class="mb-0">Yeni üye kaydı</p>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-center" href="#">Tümünü Gör</a>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="dropdown">
                    <button class="btn btn-warning btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-plus"></i> Hızlı Ekle
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="appointments.php?action=new">
                            <i class="bi bi-calendar-plus me-2"></i> Randevu
                        </a></li>
                        <li><a class="dropdown-item" href="members.php?action=new">
                            <i class="bi bi-person-plus me-2"></i> Üye
                        </a></li>
                        <li><a class="dropdown-item" href="campaigns.php?action=new">
                            <i class="bi bi-megaphone me-2"></i> Kampanya
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="services.php?action=new">
                            <i class="bi bi-plus-circle me-2"></i> Hizmet
                        </a></li>
                    </ul>
                </div>
                
                <!-- User Menu -->
                <div class="dropdown">
                    <button class="btn btn-link text-dark d-flex align-items-center" data-bs-toggle="dropdown">
                        <div class="avatar-sm bg-warning text-dark rounded-circle me-2">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <span class="d-none d-sm-inline">
                            <?php echo $_SESSION['admin_name'] ?? 'Admin'; ?>
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php">
                            <i class="bi bi-person me-2"></i> Profilim
                        </a></li>
                        <li><a class="dropdown-item" href="settings.php">
                            <i class="bi bi-gear me-2"></i> Ayarlar
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i> Çıkış Yap
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
</header>
