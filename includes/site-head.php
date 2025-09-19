<?php
/**
 * Front-end document head include for Prime EMS Studios.
 * Generates meta tags, canonical URLs and structured data from site settings.
 */

require_once __DIR__ . '/../config/database.php';

if (!isset($pageMeta) || !is_array($pageMeta)) {
    $pageMeta = [];
}

$siteName = getSetting('site_name', 'Prime EMS Studios İzmir');
$siteUrl = rtrim(getSetting('site_url', 'https://primeemsstudios.com'), '/');
$defaultImage = getSetting('social_share_image', $siteUrl . '/assets/images/logo.png');
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$currentPath = strtok($requestUri, '?') ?: '/';
$canonicalUrl = $pageMeta['canonical'] ?? ($siteUrl . ($currentPath === '/' ? '/' : $currentPath));

$meta = array_merge([
    'lang' => 'tr-TR',
    'title' => 'Prime EMS Studios İzmir — 20 Dakikada Maksimum Sonuç | Premium EMS',
    'description' => "Prime EMS Studios İzmir'de Almanya üretimi EMS cihazları ile 20 dakikalık bilimsel antrenman programları sunar.",
    'keywords' => 'EMS İzmir, elektrik kas stimülasyonu, i-motion, i-model, Balçova EMS, 20 dakika antrenman, yağ yakımı, kas geliştirme, rehabilitasyon',
    'author' => $siteName,
    'robots' => 'index, follow',
    'type' => 'website',
    'image' => $defaultImage,
    'twitter_card' => 'summary_large_image',
    'styles' => [],
    'meta' => [],
    'structured_data' => []
], $pageMeta);

$meta['canonical'] = $canonicalUrl;
$meta['styles'] = array_unique(array_merge(['assets/css/theme.css'], $meta['styles']));

$openGraph = [
    'og:title' => $meta['title'],
    'og:description' => $meta['description'],
    'og:image' => $meta['image'],
    'og:url' => $canonicalUrl,
    'og:site_name' => $siteName,
    'og:type' => $meta['type'],
    'og:locale' => 'tr_TR'
];

$twitter = [
    'twitter:card' => $meta['twitter_card'],
    'twitter:title' => $meta['title'],
    'twitter:description' => $meta['description'],
    'twitter:image' => $meta['image'],
    'twitter:url' => $canonicalUrl
];

$address = [
    '@type' => 'PostalAddress',
    'streetAddress' => getSetting('contact_address', 'Balçova, İzmir, Türkiye'),
    'addressLocality' => 'İzmir',
    'addressRegion' => 'İzmir',
    'postalCode' => getSetting('contact_postal_code', '35330'),
    'addressCountry' => 'TR'
];

$contactPoint = [
    '@type' => 'ContactPoint',
    'name' => 'Prime EMS Studios İletişim',
    'telephone' => getSetting('contact_phone', '+90 232 555 66 77'),
    'email' => getSetting('contact_email', 'info@primeems.com'),
    'contactType' => 'customer service',
    'areaServed' => 'TR',
    'availableLanguage' => ['Turkish', 'English'],
    'hoursAvailable' => [
        [
            '@type' => 'OpeningHoursSpecification',
            'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
            'opens' => '07:00',
            'closes' => '22:00'
        ],
        [
            '@type' => 'OpeningHoursSpecification',
            'dayOfWeek' => 'Sunday',
            'opens' => '09:00',
            'closes' => '20:00'
        ]
    ]
];

$defaultGraph = [
    [
        '@type' => 'Organization',
        '@id' => $siteUrl . '/#organization',
        'name' => $siteName,
        'url' => $siteUrl,
        'logo' => [
            '@type' => 'ImageObject',
            'url' => $defaultImage
        ],
        'description' => "Prime EMS Studios, İzmir Balçova'da Almanya üretimi i-motion® ve i-model® sistemleri ile bilimsel EMS antrenmanları sunan premium wellness merkezi.",
        'address' => $address,
        'contactPoint' => $contactPoint,
        'sameAs' => array_filter([
            getSetting('social_facebook', ''),
            getSetting('social_instagram', ''),
            getSetting('social_twitter', ''),
            getSetting('social_linkedin', ''),
            getSetting('social_youtube', '')
        ])
    ],
    [
        '@type' => 'HealthClub',
        '@id' => $siteUrl . '/#healthclub',
        'name' => 'Prime EMS Studios İzmir',
        'url' => $siteUrl,
        'description' => 'Premium EMS antrenmanları ile 20 dakikada maksimum sonuç elde edin. Almanya teknolojisi, bilimsel yöntemler.',
        'image' => $defaultImage,
        'priceRange' => '$$',
        'paymentAccepted' => ['Cash', 'Credit Card', 'Bank Transfer'],
        'currenciesAccepted' => 'TRY',
        'telephone' => getSetting('contact_phone', '+90 232 555 66 77'),
        'address' => $address,
        'openingHours' => ['Mo-Sa 07:00-22:00', 'Su 09:00-20:00'],
        'serviceType' => ['EMS Training', 'Body Shaping', 'Rehabilitation', 'Fitness']
    ],
    [
        '@type' => 'WebSite',
        '@id' => $siteUrl . '/#website',
        'name' => 'Prime EMS Studios İzmir',
        'url' => $siteUrl,
        'description' => $meta['description'],
        'publisher' => [
            '@type' => 'Organization',
            'name' => $siteName
        ],
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => $siteUrl . '/search?q={search_term_string}',
            'query-input' => 'required name=search_term_string'
        ]
    ]
];

$additionalGraph = [];
$structuredData = $meta['structured_data'];

if (!empty($structuredData)) {
    if (isset($structuredData['@graph'])) {
        $additionalGraph = array_merge($additionalGraph, (array) $structuredData['@graph']);
    } elseif (isset($structuredData['@type'])) {
        $additionalGraph[] = $structuredData;
    } elseif (is_array($structuredData)) {
        foreach ($structuredData as $item) {
            if (is_array($item)) {
                if (isset($item['@graph'])) {
                    $additionalGraph = array_merge($additionalGraph, (array) $item['@graph']);
                } elseif (isset($item['@type'])) {
                    $additionalGraph[] = $item;
                }
            }
        }
    }
}

$graph = array_values(array_filter(array_merge($defaultGraph, $additionalGraph)));
$structuredJson = json_encode([
    '@context' => 'https://schema.org',
    '@graph' => $graph
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($meta['lang']); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="content-language" content="<?php echo htmlspecialchars($meta['lang']); ?>">
    <title><?php echo htmlspecialchars($meta['title']); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($meta['description']); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($meta['keywords']); ?>">
    <meta name="author" content="<?php echo htmlspecialchars($meta['author']); ?>">
    <meta name="robots" content="<?php echo htmlspecialchars($meta['robots']); ?>">
    <link rel="canonical" href="<?php echo htmlspecialchars($meta['canonical']); ?>">

    <?php foreach ($meta['meta'] as $name => $content): ?>
        <meta name="<?php echo htmlspecialchars($name); ?>" content="<?php echo htmlspecialchars($content); ?>">
    <?php endforeach; ?>

    <?php foreach ($openGraph as $property => $content): ?>
        <meta property="<?php echo htmlspecialchars($property); ?>" content="<?php echo htmlspecialchars($content); ?>">
    <?php endforeach; ?>

    <?php foreach ($twitter as $name => $content): ?>
        <meta name="<?php echo htmlspecialchars($name); ?>" content="<?php echo htmlspecialchars($content); ?>">
    <?php endforeach; ?>

    <link rel="icon" type="image/x-icon" href="/assets/img/logo.png">
    <link rel="apple-touch-icon" href="/assets/img/logo.png">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <?php foreach ($meta['styles'] as $style): ?>
        <link rel="stylesheet" href="<?php echo htmlspecialchars($style); ?>">
    <?php endforeach; ?>

    <script type="application/ld+json"><?php echo $structuredJson; ?></script>
</head>
