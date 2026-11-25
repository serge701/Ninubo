<?php
/**
 * Renderiza y recolecta los campos definidos en includes/brief-schema.php.
 * Usado por brief.php (formulario público), api/brief-guardar.php (guardado)
 * y admin/brief-ver.php (vista de solo lectura para el panel admin).
 */

function ninubo_brief_esc(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Genera un token aleatorio para identificar el link único de un cliente. */
function ninubo_brief_new_token(): string {
    return bin2hex(random_bytes(20));
}

/** Renderiza el control HTML de un campo para el formulario público (editable). */
function ninubo_brief_render_field(string $name, array $field, $value): string {
    $label = ninubo_brief_esc($field['label'] ?? '');
    $hint = isset($field['hint']) ? '<p class="brief-hint">' . ninubo_brief_esc($field['hint']) . '</p>' : '';
    $required = !empty($field['required']);
    $req_attr = $required ? ' required' : '';
    $req_mark = $required ? ' <span class="brief-required">*</span>' : '';

    switch ($field['type']) {
        case 'text':
        case 'tel':
        case 'email':
            $val = ninubo_brief_esc(is_string($value) ? $value : '');
            return "<div class=\"form-row\"><label for=\"f_{$name}\">{$label}{$req_mark}</label>{$hint}"
                . "<input type=\"{$field['type']}\" id=\"f_{$name}\" name=\"{$name}\" value=\"{$val}\"{$req_attr}></div>";

        case 'textarea':
            $val = ninubo_brief_esc(is_string($value) ? $value : '');
            return "<div class=\"form-row\"><label for=\"f_{$name}\">{$label}{$req_mark}</label>{$hint}"
                . "<textarea id=\"f_{$name}\" name=\"{$name}\"{$req_attr}>{$val}</textarea></div>";

        case 'checkboxes': {
            $selected = is_array($value) ? $value : [];
            $other_val = is_array($value) ? ($value['_otro'] ?? '') : '';
            $out = "<div class=\"form-row\"><label>{$label}{$req_mark}</label>{$hint}<div class=\"brief-check-grid\">";
            foreach ($field['options'] as $optKey => $optLabel) {
                $optKeyEsc = ninubo_brief_esc($optKey);
                $optLabelEsc = ninubo_brief_esc($optLabel);
                $checked = in_array($optKey, $selected, true) ? ' checked' : '';
                $id = 'f_' . $name . '_' . $optKey;
                $out .= "<label class=\"brief-check-item\" for=\"{$id}\"><input type=\"checkbox\" id=\"{$id}\" name=\"{$name}[]\" value=\"{$optKeyEsc}\"{$checked}{$req_attr}><span>{$optLabelEsc}</span></label>";
            }
            if (!empty($field['other'])) {
                $out .= "<label class=\"brief-check-item brief-check-other\">"
                    . "<span>Otro:</span> <input type=\"text\" name=\"{$name}_otro\" value=\"" . ninubo_brief_esc($other_val) . "\"></label>";
            }
            $out .= '</div></div>';
            return $out;
        }

        case 'radio': {
            $val = is_string($value) ? $value : '';
            $out = "<div class=\"form-row\"><label>{$label}{$req_mark}</label>{$hint}<div class=\"brief-check-grid\">";
            foreach ($field['options'] as $optKey => $optLabel) {
                $optKeyEsc = ninubo_brief_esc($optKey);
                $optLabelEsc = ninubo_brief_esc($optLabel);
                $checked = ($val === $optKey) ? ' checked' : '';
                $id = 'f_' . $name . '_' . $optKey;
                $out .= "<label class=\"brief-check-item\" for=\"{$id}\"><input type=\"radio\" id=\"{$id}\" name=\"{$name}\" value=\"{$optKeyEsc}\"{$checked}><span>{$optLabelEsc}</span></label>";
            }
            if (!empty($field['other'])) {
                $otherChecked = ($val !== '' && !array_key_exists($val, $field['options'])) ? $val : '';
                $out .= "<label class=\"brief-check-item brief-check-other\">"
                    . "<span>Otro:</span> <input type=\"text\" name=\"{$name}_otro\" value=\"" . ninubo_brief_esc($otherChecked) . "\"></label>";
            }
            $out .= '</div></div>';
            return $out;
        }

        case 'repeat': {
            $out = "<div class=\"brief-repeat\"><p class=\"brief-repeat-label\">{$label}</p>";
            for ($i = 1; $i <= $field['count']; $i++) {
                $item = is_array($value[$i] ?? null) ? $value[$i] : [];
                $out .= "<div class=\"brief-repeat-item\"><p class=\"brief-repeat-title\">" . ninubo_brief_esc($field['item_label']) . " {$i}</p>";
                foreach ($field['subfields'] as $subName => $subField) {
                    $subVal = $item[$subName] ?? '';
                    $out .= ninubo_brief_render_field("{$name}[{$i}][{$subName}]", $subField, $subVal);
                }
                $out .= '</div>';
            }
            $out .= '</div>';
            return $out;
        }

        case 'list': {
            $out = "<div class=\"brief-repeat\"><p class=\"brief-repeat-label\">{$label}</p><div class=\"brief-list-grid\">";
            for ($i = 1; $i <= $field['count']; $i++) {
                $val = ninubo_brief_esc(is_array($value) ? ($value[$i] ?? '') : '');
                $out .= "<div class=\"form-row\"><label for=\"f_{$name}_{$i}\">" . ninubo_brief_esc($field['item_label']) . " {$i}</label>"
                    . "<input type=\"text\" id=\"f_{$name}_{$i}\" name=\"{$name}[{$i}]\" value=\"{$val}\"></div>";
            }
            $out .= '</div></div>';
            return $out;
        }

        case 'schedule': {
            $out = "<div class=\"brief-repeat\"><p class=\"brief-repeat-label\">{$label}</p><div class=\"brief-list-grid\">";
            foreach ($field['days'] as $dayKey => $dayLabel) {
                $val = ninubo_brief_esc(is_array($value) ? ($value[$dayKey] ?? '') : '');
                $out .= "<div class=\"form-row\"><label for=\"f_{$name}_{$dayKey}\">" . ninubo_brief_esc($dayLabel) . "</label>"
                    . "<input type=\"text\" id=\"f_{$name}_{$dayKey}\" name=\"{$name}[{$dayKey}]\" value=\"{$val}\" placeholder=\"Ej. 9:00 am - 6:00 pm o Cerrado\"></div>";
            }
            $out .= '</div></div>';
            return $out;
        }
    }

    return '';
}

/** Recolecta y sanea $_POST según el esquema. Solo acepta llaves definidas en el esquema. */
function ninubo_brief_collect(array $schema): array {
    $data = [];
    foreach ($schema as $stepKey => $step) {
        foreach ($step['fields'] as $name => $field) {
            $data[$name] = ninubo_brief_collect_field($name, $field);
        }
    }
    return $data;
}

function ninubo_brief_collect_field(string $name, array $field) {
    switch ($field['type']) {
        case 'text':
        case 'tel':
        case 'email':
        case 'textarea':
            return trim((string)($_POST[$name] ?? ''));

        case 'checkboxes': {
            $raw = $_POST[$name] ?? [];
            $validKeys = array_keys($field['options']);
            $selected = is_array($raw) ? array_values(array_intersect($validKeys, $raw)) : [];
            $result = $selected;
            if (!empty($field['other'])) {
                $other = trim((string)($_POST["{$name}_otro"] ?? ''));
                if ($other !== '') {
                    $result[] = $other;
                }
            }
            return $result;
        }

        case 'radio': {
            $val = trim((string)($_POST[$name] ?? ''));
            if (array_key_exists($val, $field['options'])) {
                return $val;
            }
            if (!empty($field['other'])) {
                $other = trim((string)($_POST["{$name}_otro"] ?? ''));
                if ($other !== '') {
                    return $other;
                }
            }
            return '';
        }

        case 'repeat': {
            $out = [];
            $raw = $_POST[$name] ?? [];
            for ($i = 1; $i <= $field['count']; $i++) {
                $item = [];
                $hasValue = false;
                foreach ($field['subfields'] as $subName => $subField) {
                    $v = trim((string)($raw[$i][$subName] ?? ''));
                    $item[$subName] = $v;
                    if ($v !== '') {
                        $hasValue = true;
                    }
                }
                if ($hasValue) {
                    $out[$i] = $item;
                }
            }
            return $out;
        }

        case 'list': {
            $out = [];
            $raw = $_POST[$name] ?? [];
            for ($i = 1; $i <= $field['count']; $i++) {
                $v = trim((string)($raw[$i] ?? ''));
                if ($v !== '') {
                    $out[$i] = $v;
                }
            }
            return $out;
        }

        case 'schedule': {
            $out = [];
            $raw = $_POST[$name] ?? [];
            foreach ($field['days'] as $dayKey => $dayLabel) {
                $v = trim((string)($raw[$dayKey] ?? ''));
                if ($v !== '') {
                    $out[$dayKey] = $v;
                }
            }
            return $out;
        }
    }
    return null;
}

/** ¿Está vacío este valor de campo? (para mostrar "no proporcionado" en el panel admin) */
function ninubo_brief_is_empty($value): bool {
    if (is_array($value)) {
        return count($value) === 0;
    }
    return $value === null || trim((string)$value) === '';
}

/** Vista de solo lectura de un campo, para el panel admin. */
function ninubo_brief_render_view(string $name, array $field, $value): string {
    $label = ninubo_brief_esc($field['label'] ?? '');
    $empty = '<span class="brief-view-empty">— no proporcionado —</span>';

    switch ($field['type']) {
        case 'text':
        case 'tel':
        case 'email':
        case 'textarea':
            $out = ninubo_brief_is_empty($value) ? $empty : nl2br(ninubo_brief_esc((string)$value));
            return "<div class=\"brief-view-row\"><p class=\"brief-view-label\">{$label}</p><p class=\"brief-view-value\">{$out}</p></div>";

        case 'checkboxes':
        case 'radio': {
            if (ninubo_brief_is_empty($value)) {
                return "<div class=\"brief-view-row\"><p class=\"brief-view-label\">{$label}</p><p class=\"brief-view-value\">{$empty}</p></div>";
            }
            // El valor guardado es la clave estable de la opción (p.ej. "nuevos_clientes"), no el
            // texto mostrado — se resuelve aquí a su etiqueta en español. Un valor que no coincide
            // con ninguna clave es texto libre de "Otro" y se muestra tal cual.
            $items = is_array($value) ? $value : [$value];
            $labels = array_map(fn($v) => $field['options'][$v] ?? $v, $items);
            $out = ninubo_brief_esc(implode(', ', $labels));
            return "<div class=\"brief-view-row\"><p class=\"brief-view-label\">{$label}</p><p class=\"brief-view-value\">{$out}</p></div>";
        }

        case 'repeat': {
            if (ninubo_brief_is_empty($value)) {
                return "<div class=\"brief-view-row\"><p class=\"brief-view-label\">{$label}</p><p class=\"brief-view-value\">{$empty}</p></div>";
            }
            $out = "<div class=\"brief-view-row\"><p class=\"brief-view-label\">{$label}</p>";
            foreach ($value as $i => $item) {
                $out .= "<div class=\"brief-view-subitem\"><p class=\"brief-view-subtitle\">" . ninubo_brief_esc($field['item_label']) . " {$i}</p>";
                foreach ($field['subfields'] as $subName => $subField) {
                    $out .= ninubo_brief_render_view($subName, $subField, $item[$subName] ?? '');
                }
                $out .= '</div>';
            }
            $out .= '</div>';
            return $out;
        }

        case 'list': {
            if (ninubo_brief_is_empty($value)) {
                return "<div class=\"brief-view-row\"><p class=\"brief-view-label\">{$label}</p><p class=\"brief-view-value\">{$empty}</p></div>";
            }
            $items = [];
            foreach ($value as $i => $v) {
                $items[] = ninubo_brief_esc($field['item_label']) . " {$i}: " . ninubo_brief_esc($v);
            }
            return "<div class=\"brief-view-row\"><p class=\"brief-view-label\">{$label}</p><p class=\"brief-view-value\">" . implode('<br>', $items) . '</p></div>';
        }

        case 'schedule': {
            if (ninubo_brief_is_empty($value)) {
                return "<div class=\"brief-view-row\"><p class=\"brief-view-label\">{$label}</p><p class=\"brief-view-value\">{$empty}</p></div>";
            }
            $items = [];
            foreach ($field['days'] as $dayKey => $dayLabel) {
                if (!empty($value[$dayKey])) {
                    $items[] = ninubo_brief_esc($dayLabel) . ': ' . ninubo_brief_esc($value[$dayKey]);
                }
            }
            return "<div class=\"brief-view-row\"><p class=\"brief-view-label\">{$label}</p><p class=\"brief-view-value\">" . implode('<br>', $items) . '</p></div>';
        }
    }
    return '';
}
