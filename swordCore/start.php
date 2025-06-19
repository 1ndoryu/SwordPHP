#!/usr/bin/env php
<?php
chdir(__DIR__);

if (!defined('SWORD_CORE_PATH')) {
    define('SWORD_CORE_PATH', __DIR__);
}
if (!defined('SWORD_CONTENT_PATH')) {
    define('SWORD_CONTENT_PATH', realpath(__DIR__ . '/../swordContent'));
}
if (!defined('SWORD_THEMES_PATH')) {
    define('SWORD_THEMES_PATH', SWORD_CONTENT_PATH . '/themes');
}
if (!defined('SWORD_PLUGINS_PATH')) {
    define('SWORD_PLUGINS_PATH', SWORD_CONTENT_PATH . '/plugins');
}

require_once __DIR__ . '/vendor/autoload.php';
support\App::run();
