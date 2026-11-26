<?php
/**
 * Tiny trend bars for a KPI tile (last N months, current month emphasized).
 * @var list<float> $values @var list<string> $labels @var string $color @var string $name
 */
$W = 120; $H = 36; $n = count($values);
$max = max(1.0, ...$values);
$gap = 3;
$w = ($W - $gap * ($n - 1)) / max(1, $n);
?>
<svg viewBox="0 0 <?= $W ?> <?= $H ?>" class="crm-spark" role="img" aria-label="<?= e($name) ?>, last <?= $n ?> months">
  <?php foreach ($values as $i => $v):
    $h = max(2.0, $v / $max * ($H - 2));
    $x = $i * ($w + $gap); ?>
    <rect x="<?= round($x, 1) ?>" y="<?= round($H - $h, 1) ?>" width="<?= round($w, 1) ?>" height="<?= round($h, 1) ?>" rx="2"
          fill="<?= e($color) ?>" opacity="<?= $i === $n - 1 ? '1' : '.38' ?>"
          data-tip-title="<?= e($labels[$i]) ?>" data-tip='<?= e(json_encode([[$color, $name, money($v)]])) ?>'/>
  <?php endforeach; ?>
</svg>
