<?php
declare(strict_types=1);

namespace Crm\Core;

/**
 * Draws, collects and displays website-brief fields defined in includes/brief-schema.php.
 * Used by the public form (/brief.php) and the CRM's brief page. Markup is Tabler.
 */
final class BriefForm
{
    /** Editable control for the public form. $sub: a sub-field of a repeat (no data-field wrapper). */
    public static function field(string $name, array $field, mixed $value, string $lang, bool $sub = false): string
    {
        $id = self::id($name);
        $required = !empty($field['required']);
        $wrap = $sub ? '<div class="mb-3">' : '<div class="mb-4 brief-field" data-field="' . e($name) . '"' . ($required ? ' data-required' : '') . '>';
        $label = e($field['label'] ?? '');
        $reqClass = $required ? ' required' : '';
        $hint = isset($field['hint']) && $field['hint'] !== '' ? '<div class="form-hint mt-n1 mb-2">' . e($field['hint']) . '</div>' : '';
        $placeholder = isset($field['placeholder']) ? ' placeholder="' . e($field['placeholder']) . '"' : '';

        switch ($field['type']) {
            case 'text':
            case 'tel':
            case 'email':
                $extra = match ($field['type']) {
                    'tel' => ' inputmode="tel" autocomplete="tel"' . ($placeholder ?: ' placeholder="(956) 555-0123"'),
                    'email' => ' autocomplete="email"' . $placeholder,
                    default => $placeholder,
                };
                return $wrap . "<label class=\"form-label{$reqClass}\" for=\"{$id}\">{$label}</label>{$hint}"
                    . '<input type="' . $field['type'] . "\" class=\"form-control\" id=\"{$id}\" name=\"" . e($name) . '" value="' . e(is_string($value) ? $value : '') . '"'
                    . ($required ? ' required' : '') . $extra . '></div>';

            case 'textarea':
                return $wrap . "<label class=\"form-label{$reqClass}\" for=\"{$id}\">{$label}</label>{$hint}"
                    . "<textarea class=\"form-control\" id=\"{$id}\" name=\"" . e($name) . '" rows="3"' . ($required ? ' required' : '') . $placeholder . '>'
                    . e(is_string($value) ? $value : '') . '</textarea></div>';

            case 'checkboxes':
            case 'radio': {
                $isRadio = $field['type'] === 'radio';
                $all = $field['option_labels'] ?? $field['options'];
                $picked = $isRadio ? (is_string($value) && $value !== '' ? [$value] : []) : (is_array($value) ? $value : []);
                $otherText = implode(', ', array_filter($picked, fn($v) => !array_key_exists($v, $all)));
                $out = $wrap . "<div class=\"form-label{$reqClass}\">{$label}</div>{$hint}";

                // A single long option (the final confirmation) reads better as a plain checkbox.
                if (!$isRadio && count($field['options']) === 1 && empty($field['other'])) {
                    $key = (string)array_key_first($field['options']);
                    return $out . '<label class="form-check"><input class="form-check-input" type="checkbox" name="' . e($name) . '[]" value="' . e($key) . '"'
                        . (in_array($key, $picked, true) ? ' checked' : '') . '><span class="form-check-label">' . e($field['options'][$key]) . '</span></label></div>';
                }

                $out .= '<div class="form-selectgroup">';
                foreach ($field['options'] as $key => $optLabel) {
                    $key = (string)$key;
                    $out .= '<label class="form-selectgroup-item"><input type="' . ($isRadio ? 'radio' : 'checkbox') . '" name="' . e($name) . ($isRadio ? '' : '[]')
                        . '" value="' . e($key) . '" class="form-selectgroup-input"' . (in_array($key, $picked, true) ? ' checked' : '')
                        . '><span class="form-selectgroup-label">' . e($optLabel) . '</span></label>';
                }
                $out .= '</div>';
                if (!empty($field['other'])) {
                    $out .= '<input type="text" class="form-control form-control-sm mt-2 brief-other" name="' . e($name) . '_otro" value="' . e($otherText) . '" placeholder="'
                        . ($lang === 'en' ? 'Other: …' : 'Otro: …') . '" aria-label="' . ($lang === 'en' ? 'Other' : 'Otro') . '"' . ($isRadio ? ' data-radio-other' : '') . '>';
                }
                return $out . '</div>';
            }

            case 'repeat': {
                $out = $wrap . "<div class=\"form-label\">{$label}</div>{$hint}";
                for ($i = 1; $i <= (int)$field['count']; $i++) {
                    $item = is_array($value[$i] ?? null) ? $value[$i] : [];
                    $out .= '<fieldset class="border rounded p-3 mb-2"><legend class="float-none w-auto px-2 mb-0 fs-5 fw-medium">' . e($field['item_label']) . " {$i}</legend>";
                    foreach ($field['subfields'] as $subName => $subField) {
                        $out .= self::field("{$name}[{$i}][{$subName}]", $subField, $item[$subName] ?? '', $lang, true);
                    }
                    $out .= '</fieldset>';
                }
                return $out . '</div>';
            }

            case 'list': {
                $out = $wrap . "<div class=\"form-label\">{$label}</div>{$hint}<div class=\"row g-2\">";
                for ($i = 1; $i <= (int)$field['count']; $i++) {
                    $v = is_array($value) ? (string)($value[$i] ?? '') : '';
                    $out .= '<div class="col-sm-6"><input type="text" class="form-control" name="' . e($name) . "[{$i}]\" value=\"" . e($v) . '" placeholder="'
                        . e($field['item_label']) . " {$i}\" aria-label=\"" . e($field['item_label']) . " {$i}\"></div>";
                }
                return $out . '</div></div>';
            }

            case 'schedule': {
                $out = $wrap . "<div class=\"form-label\">{$label}</div>{$hint}";
                foreach ($field['days'] as $day => $dayLabel) {
                    $v = is_array($value) ? (string)($value[$day] ?? '') : '';
                    $dayId = self::id("{$name}_{$day}");
                    $out .= '<div class="row g-2 align-items-center mb-2"><label class="col-4 col-sm-3 col-form-label py-0" for="' . $dayId . '">' . e($dayLabel) . '</label>'
                        . '<div class="col"><input type="text" class="form-control" id="' . $dayId . '" name="' . e($name) . "[{$day}]\" value=\"" . e($v) . '"' . $placeholder . '></div></div>';
                }
                return $out . '</div>';
            }
        }
        return '';
    }

    /** Reads and cleans $post following the schema; only keys defined there are accepted. */
    public static function collect(array $schema, array $post): array
    {
        $data = [];
        foreach ($schema as $step) {
            foreach ($step['fields'] as $name => $field) {
                $data[$name] = self::collectField($name, $field, $post);
            }
        }
        return $data;
    }

    private static function collectField(string $name, array $field, array $post): mixed
    {
        $str = fn($v): string => mb_substr(trim(is_scalar($v) ? (string)$v : ''), 0, 5000);
        switch ($field['type']) {
            case 'text':
            case 'tel':
            case 'email':
            case 'textarea':
                return $str($post[$name] ?? '');

            case 'checkboxes': {
                $raw = $post[$name] ?? [];
                $result = is_array($raw) ? array_values(array_intersect(array_map('strval', array_keys($field['options'])), array_map('strval', $raw))) : [];
                $other = !empty($field['other']) ? $str($post["{$name}_otro"] ?? '') : '';
                if ($other !== '') {
                    $result[] = $other;
                }
                return $result;
            }

            case 'radio': {
                $v = $str($post[$name] ?? '');
                if (array_key_exists($v, $field['options'])) {
                    return $v;
                }
                return !empty($field['other']) ? $str($post["{$name}_otro"] ?? '') : '';
            }

            case 'repeat': {
                $out = [];
                $raw = is_array($post[$name] ?? null) ? $post[$name] : [];
                for ($i = 1; $i <= (int)$field['count']; $i++) {
                    $item = [];
                    foreach ($field['subfields'] as $subName => $subField) {
                        $item[$subName] = $str($raw[$i][$subName] ?? '');
                    }
                    if (implode('', $item) !== '') {
                        $out[$i] = $item;
                    }
                }
                return $out;
            }

            case 'list': {
                $out = [];
                $raw = is_array($post[$name] ?? null) ? $post[$name] : [];
                for ($i = 1; $i <= (int)$field['count']; $i++) {
                    $v = $str($raw[$i] ?? '');
                    if ($v !== '') {
                        $out[$i] = $v;
                    }
                }
                return $out;
            }

            case 'schedule': {
                $out = [];
                $raw = is_array($post[$name] ?? null) ? $post[$name] : [];
                foreach (array_keys($field['days']) as $day) {
                    $v = $str($raw[$day] ?? '');
                    if ($v !== '') {
                        $out[$day] = $v;
                    }
                }
                return $out;
            }
        }
        return null;
    }

    public static function isEmpty(mixed $value): bool
    {
        if (is_array($value)) {
            return count($value) === 0;
        }
        return $value === null || trim((string)$value) === '';
    }

    /** @return string[] labels of required fields left empty */
    public static function missingRequired(array $schema, array $data): array
    {
        $missing = [];
        foreach ($schema as $step) {
            foreach ($step['fields'] as $name => $field) {
                if (!empty($field['required']) && self::isEmpty($data[$name] ?? null)) {
                    $missing[] = $field['label'];
                }
            }
        }
        return $missing;
    }

    /** Read-only answer for the CRM (schema resolved in English). */
    public static function view(string $name, array $field, mixed $value): string
    {
        $label = e($field['label'] ?? '');
        $row = fn(string $content): string => "<div class=\"brief-view-row\"><p class=\"brief-view-label\">{$label}</p><div class=\"brief-view-value\">{$content}</div></div>";
        if (self::isEmpty($value)) {
            return $row('<span class="brief-view-empty">— not provided —</span>');
        }

        switch ($field['type']) {
            case 'checkboxes':
            case 'radio': {
                // Stored values are option keys; anything else is the client's "Other" text.
                $all = $field['option_labels'] ?? $field['options'];
                $labels = array_map(fn($v) => isset($all[$v]) ? $all[$v] : 'Other: ' . $v, is_array($value) ? $value : [$value]);
                return $row(e(implode(', ', $labels)));
            }

            case 'repeat': {
                $out = '';
                foreach ((array)$value as $i => $item) {
                    $out .= '<div class="brief-view-subitem"><p class="brief-view-subtitle">' . e($field['item_label']) . " {$i}</p>";
                    foreach ($field['subfields'] as $subName => $subField) {
                        $out .= self::view($subName, $subField, $item[$subName] ?? '');
                    }
                    $out .= '</div>';
                }
                return $row($out);
            }

            case 'list':
                return $row(implode('<br>', array_map(fn($i, $v) => e($field['item_label']) . " {$i}: " . e($v), array_keys((array)$value), (array)$value)));

            case 'schedule': {
                $lines = [];
                foreach ($field['days'] as $day => $dayLabel) {
                    if (!empty($value[$day])) {
                        $lines[] = e($dayLabel) . ': ' . e($value[$day]);
                    }
                }
                return $row(implode('<br>', $lines));
            }
        }
        return $row(nl2br(e((string)$value)));
    }

    /**
     * Answers saved under keys this brief's form no longer asks (older versions of the questions).
     * @return array<string,string> label => plain-text answer
     */
    public static function legacyAnswers(array $schema, array $data): array
    {
        $known = [];
        foreach ($schema as $step) {
            $known += $step['fields'];
        }
        $labels = ninubo_brief_legacy_labels();
        $out = [];
        foreach ($data as $key => $value) {
            if (isset($known[$key]) || self::isEmpty($value)) {
                continue;
            }
            $label = $labels[$key] ?? ucfirst(str_replace('_', ' ', (string)$key));
            $out[$label] = is_array($value) ? self::flatten($value) : (string)$value;
        }
        return $out;
    }

    private static function flatten(array $value): string
    {
        $parts = [];
        foreach ($value as $k => $v) {
            $text = is_array($v) ? self::flatten($v) : (string)$v;
            $parts[] = is_int($k) ? $text : "{$k}: {$text}";
        }
        return implode('; ', $parts);
    }

    private static function id(string $name): string
    {
        return 'f_' . trim((string)preg_replace('/[^a-z0-9_]+/i', '_', $name), '_');
    }
}
