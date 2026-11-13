<?php
declare(strict_types=1);
date_default_timezone_set('America/Sao_Paulo');
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/LoanService.php';
require_once __DIR__ . '/Documents.php';
function e(mixed $value): string { return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function url(string $page, array $params = []): string { return 'index.php?' . http_build_query(['page'=>$page] + $params); }
function redirect(string $page, array $params = []): never { header('Location: '.url($page,$params)); exit; }
function csrf(): string { return '<input type="hidden" name="csrf" value="'.e($_SESSION['csrf']).'">'; }
function input(string $key, string $label, int $max, array $data = [], bool $required = true, string $type = 'text'): void {
    echo '<label>'.e($label).'<input type="'.e($type).'" name="'.e($key).'" maxlength="'.$max.'" value="'.e($data[$key] ?? '').'" '.($required?'required':'').'></label>';
}
function selectField(string $key, string $label, array $options, array $data = []): void {
    echo '<label>'.e($label).'<select required name="'.e($key).'">';
    foreach ($options as $value=>$text) echo '<option value="'.e($value).'" '.((string)($data[$key]??'')===(string)$value?'selected':'').'>'.e($text).'</option>';
    echo '</select></label>';
}
function dateBr(?string $s): string { return $s ? date('d/m/Y', strtotime($s)) : '—'; }
