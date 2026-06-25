<?php
// AI 修改：提供 PHP 頁面穩定鍵值語言包，與既有前端即時切換並行。
function stafflessLanguage() {
    $language = $_COOKIE['staffless-language'] ?? 'en';
    return $language === 'zh-TW' ? 'zh-TW' : 'en';
}

function stafflessDictionary($language = null) {
    $language = $language ?: stafflessLanguage();
    $file = __DIR__ . '/../lang/' . ($language === 'zh-TW' ? 'zh-TW.php' : 'en.php');
    return is_file($file) ? require $file : [];
}

function stafflessText($key, $fallback = '') {
    $dictionary = stafflessDictionary();
    return $dictionary[$key] ?? $fallback ?: $key;
}
