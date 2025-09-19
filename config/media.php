<?php
// Media Configuration and File Management
define('MEDIA_BASE_PATH', '/assets/');
define('IMAGES_PATH', MEDIA_BASE_PATH . 'images/');
define('IMG_PATH', MEDIA_BASE_PATH . 'img/');
define('VIDEOS_PATH', MEDIA_BASE_PATH . 'videos/');

// Certificate paths
define('CERTIFICATES_PATH', IMG_PATH . 'certifications/');
$certificates = [
    'anvisa' => CERTIFICATES_PATH . 'anvisa.svg',
    'ce-marking' => CERTIFICATES_PATH . 'ce-marking.svg',
    'eu-mdr' => CERTIFICATES_PATH . 'eu-mdr.svg',
    'fda-approved' => CERTIFICATES_PATH . 'fda-approved.svg',
    'iec-60601' => CERTIFICATES_PATH . 'iec-60601.svg',
    'iecee' => CERTIFICATES_PATH . 'iecee.svg',
    'invima' => CERTIFICATES_PATH . 'invima.svg',
    'iso-9001-2015' => CERTIFICATES_PATH . 'iso-9001-2015.svg',
    'iso-13485' => CERTIFICATES_PATH . 'iso-13485.svg',
    'iso-14001-2015' => CERTIFICATES_PATH . 'iso-14001-2015.svg',
    'ktc' => CERTIFICATES_PATH . 'ktc.svg',
    'sgs' => CERTIFICATES_PATH . 'sgs.svg',
    'tuv-certified' => CERTIFICATES_PATH . 'tuv-certified.svg'
];

// Device images
$device_images = [
    'i-motion' => IMAGES_PATH . 'devices/device1.jpg',
    'i-model' => IMAGES_PATH . 'devices/device2.jpg'
];

// Trainer images
$trainer_images = [
    1 => IMAGES_PATH . 'trainers/trainer1.jpg',
    2 => IMAGES_PATH . 'trainers/trainer2.jpg',
    3 => IMAGES_PATH . 'trainers/trainer3.jpg'
];

// Gallery images
$gallery_images = [
    1 => IMAGES_PATH . 'gallery/gallery1.jpg',
    2 => IMAGES_PATH . 'gallery/gallery2.jpg',
    3 => IMAGES_PATH . 'gallery/gallery3.jpg'
];

// Hero media
define('HERO_VIDEO_PATH', VIDEOS_PATH . 'hero.mp4');
define('HERO_IMAGE_PATH', IMG_PATH . 'hero-bg.jpg');

// Helper function to check if file exists
function mediaFileExists($path) {
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . $path;
    return file_exists($fullPath);
}

// Helper function to get file info
function getMediaInfo($path) {
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . $path;
    if (file_exists($fullPath)) {
        return [
            'exists' => true,
            'size' => filesize($fullPath),
            'modified' => filemtime($fullPath)
        ];
    }
    return ['exists' => false];
}
?>