<?php
/**
 * Horizontal bar list (ranking / composition). Each row: label, bar, value; hover shows a tooltip.
 * @var list<array{label:string, value:float, display:string, sub?:string, color?:string, tip?:array}> $rows
 * @var string $empty  message when every value is 0
 */
$max = max(0.0, ...array_map(fn($r) => (float)$r['value'], $rows ?: [['value' => 0]]));
?>
<div class="crm-viz">
  <?php if ($max <= 0): ?>
  <div class="crm-viz-empty"><?= e($empty ?? 'No data yet.') ?></div>
  <?php endif; ?>
  <div class="crm-hbars">
    <?php foreach ($rows as $r):
      $pct = $max > 0 ? max(0, (float)$r['value'] / $max * 100) : 0;
      $color = $r['color'] ?? 'var(--viz-1)'; ?>
    <div class="crm-hbar" data-tip-title="<?= e($r['label']) ?>" data-tip='<?= e(json_encode($r['tip'] ?? [[$color, $r['label'], $r['display']]])) ?>'>
      <div class="crm-hbar-label">
        <span class="text-truncate"><?= e($r['label']) ?></span>
        <span class="crm-hbar-value"><?= e($r['display']) ?><?php if (!empty($r['sub'])): ?> <span class="text-secondary fw-normal"><?= e($r['sub']) ?></span><?php endif; ?></span>
      </div>
      <div class="crm-hbar-track"><div class="crm-hbar-fill" style="width:<?= number_format($pct, 2, '.', '') ?>%;background:<?= e($color) ?>"></div></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
