<?php
// Hero Section Variables
$media_type = $hero['media_type'] ?? 'video';
$media_path = $hero['media_path'] ?? '';
?>

<!-- Hero Section -->
<section id="home" class="hero-section">
    <?php if ($media_type === 'video' && !empty($media_path)): ?>
        <video class="hero-video" autoplay muted loop playsinline controls preload="metadata">
            <source src="<?php echo htmlspecialchars($media_path); ?>" type="video/mp4">
            Tarayıcınız video etiketini desteklemiyor.
        </video>
    <?php elseif ($media_type === 'image' && !empty($media_path)): ?>
        <img class="hero-image" src="<?php echo htmlspecialchars($media_path); ?>" alt="Prime EMS Studios">
    <?php else: ?>
        <img class="hero-image" src="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80" alt="Prime EMS Studios - Profesyonel EMS Antrenman Salonu">
    <?php endif; ?>

    <div class="hero-overlay"></div>

    <div class="container">
        <div class="hero-content" data-aos="fade-up">
                <h1 class="display-3 fw-bold mb-4">
                    <?php echo !empty($hero['title']) ? htmlspecialchars($hero['title']) : 'Prime EMS Studios'; ?><br>
                    <span style="color: var(--prime-gold);"><?php echo !empty($hero['subtitle']) ? htmlspecialchars($hero['subtitle']) : "İzmir'in EMS Uzmanı"; ?></span>
                </h1>
                <p class="lead mb-5">
                    <?php echo !empty($hero['description']) ? htmlspecialchars($hero['description']) : 'Profesyonel EMS antrenmanları ile <strong>20 dakikada</strong> maksimum sonuç!<br>Haftada sadece 2 seans ile vücudunuzu dönüştürün.'; ?>
                </p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="#reservation" class="btn btn-prime btn-lg pulse-effect" role="button" aria-label="Ücretsiz keşif seansı için hemen rezervasyon yapın">
                        <i class="bi bi-lightning-charge"></i> Hemen Başlayın
                    </a>
                    <a href="#campaigns" class="btn btn-prime-outline btn-lg" role="button" aria-label="Mevcut kampanyaları görüntüleyin">
                        <i class="bi bi-star-fill"></i> Özel Kampanyalar
                    </a>
                </div>
            </div>
    </div>
</section>