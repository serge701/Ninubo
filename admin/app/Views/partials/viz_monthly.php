<?php
/**
 * "Invoiced vs collected" — collected as bars (series 1), invoiced as a line (series 2), one shared $ axis.
 * Inline SVG with a hover band + tooltip per month (assets/app/viz.js) and a table view.
 * @var array $months from Stats::monthly()
 */
$W = 720; $H = 320;
$pad = ['t' => 14, 'r' => 12, 'b' => 30, 'l' => 52];
$plotW = $W - $pad['l'] - $pad['r'];
$plotH = $H - $pad['t'] - $pad['b'];
$n = count($months);
$max = max(1.0, ...array_map(fn($m) => max($m['invoiced'], $m['collected']), $months));
$scale = nice_scale($max);
$y = fn(float $v) => $pad['t'] + $plotH - ($v / $scale['max']) * $plotH;
$band = $plotW / max(1, $n);
$barW = min(26.0, $band * 0.46);
$cx = fn(int $i) => $pad['l'] + $band * ($i + .5);
$hasData = array_sum(array_map(fn($m) => $m['invoiced'] + $m['collected'], $months)) > 0;

/** Bar path with 4px rounded top (data end) and a square base on the baseline. */
$bar = function (float $x, float $top, float $w, float $base): string {
    $h = $base - $top;
    if ($h <= 0.5) {
        return '';
    }
    $r = min(4.0, $w / 2, $h);
    return sprintf('M%.1f %.1fV%.1fQ%.1f %.1f %.1f %.1fH%.1fQ%.1f %.1f %.1f %.1fV%.1fZ',
        $x, $base, $top + $r, $x, $top, $x + $r, $top, $x + $w - $r, $x + $w, $top, $x + $w, $top + $r, $base);
};
$linePts = [];
foreach ($months as $i => $m) {
    $linePts[] = sprintf('%.1f,%.1f', $cx($i), $y($m['invoiced']));
}
$base = $pad['t'] + $plotH;
?>
<div class="crm-viz">
  <div class="crm-legend mb-2">
    <span class="crm-legend-item"><span class="crm-swatch" style="background:var(--viz-1)"></span>Collected</span>
    <span class="crm-legend-item"><span class="crm-swatch crm-swatch-line" style="--c:var(--viz-2)"></span>Invoiced</span>
  </div>
  <?php if (!$hasData): ?>
  <div class="crm-viz-empty">No invoices or payments in the last 12 months yet.</div>
  <?php else: ?>
  <svg viewBox="0 0 <?= $W ?> <?= $H ?>" class="crm-chart" role="img" aria-label="Invoiced and collected per month, last 12 months">
    <?php for ($v = 0; $v <= $scale['max'] + 0.01; $v += $scale['step']): ?>
      <line x1="<?= $pad['l'] ?>" x2="<?= $W - $pad['r'] ?>" y1="<?= round($y($v), 1) ?>" y2="<?= round($y($v), 1) ?>" class="<?= $v == 0 ? 'crm-axis' : 'crm-grid' ?>"/>
      <text x="<?= $pad['l'] - 8 ?>" y="<?= round($y($v), 1) + 4 ?>" text-anchor="end" class="crm-tick"><?= e(money_short($v)) ?></text>
    <?php endfor; ?>

    <?php foreach ($months as $i => $m): ?>
      <g class="crm-col" data-tip-title="<?= e($m['label']) ?>"
         data-tip='<?= e(json_encode([["var(--viz-1)", 'Collected', money($m['collected'])], ["var(--viz-2)", 'Invoiced', money($m['invoiced'])]])) ?>'>
        <rect x="<?= round($pad['l'] + $band * $i, 1) ?>" y="<?= $pad['t'] ?>" width="<?= round($band, 1) ?>" height="<?= $plotH ?>" class="crm-hit"/>
        <path d="<?= $bar($cx($i) - $barW / 2, $y($m['collected']), $barW, $base) ?>" fill="var(--viz-1)"/>
        <text x="<?= round($cx($i), 1) ?>" y="<?= $H - 10 ?>" text-anchor="middle" class="crm-tick<?= $i === $n - 1 ? ' crm-tick-strong' : '' ?>"><?= e($m['short']) ?></text>
      </g>
    <?php endforeach; ?>

    <polyline points="<?= implode(' ', $linePts) ?>" fill="none" stroke="var(--viz-2)" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" pointer-events="none"/>
    <?php foreach ($months as $i => $m): if ($m['invoiced'] <= 0) continue; ?>
      <circle cx="<?= round($cx($i), 1) ?>" cy="<?= round($y($m['invoiced']), 1) ?>" r="4" fill="var(--viz-2)" stroke="var(--viz-surface)" stroke-width="2" pointer-events="none"/>
    <?php endforeach; ?>
  </svg>
  <?php endif; ?>
  <details class="crm-viz-table">
    <summary>View as table</summary>
    <table class="table table-sm mb-0">
      <thead><tr><th>Month</th><th class="text-end">Invoiced</th><th class="text-end">Collected</th></tr></thead>
      <tbody>
      <?php foreach (array_reverse($months) as $m): ?>
        <tr><td><?= e($m['label']) ?></td><td class="text-end"><?= e(money($m['invoiced'])) ?></td><td class="text-end"><?= e(money($m['collected'])) ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </details>
</div>
