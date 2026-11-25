<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/mockups.php';

function ninubo_img(string $file, string $alt, string $class = '', string $ratio = '16/9'): void {
    $path = ninubo_url('assets/img/' . $file);
    $full = __DIR__ . '/../assets/img/' . $file;
    $classAttr = $class ? ' ' . htmlspecialchars($class) : '';
    $map = ninubo_mockup_map();
    $mockupType = $map[$file] ?? 'default';

    echo '<figure class="media-frame' . $classAttr . '" style="--ratio:' . htmlspecialchars($ratio) . '">';
    if (file_exists($full)) {
        echo '<img src="' . htmlspecialchars($path) . '" alt="' . htmlspecialchars($alt) . '" loading="lazy">';
    } else {
        echo '<div class="visual-scene" role="img" aria-label="' . htmlspecialchars($alt) . '">';
        ninubo_render_mockup($mockupType);
        echo '</div>';
    }
    echo '</figure>';
}
