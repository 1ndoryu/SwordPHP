#!/usr/bin/env php
<?php
chdir(__DIR__);

if (!defined('SWORD_CORE_PATH')) {
    define('SWORD_CORE_PATH', __DIR__);
}
if (!defined('SWORD_CONTENT_PATH')) {
    // Se elimina la llamada a realpath() que está fallando en Windows.
    define('SWORD_CONTENT_PATH', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'swordContent');
}
if (!defined('SWORD_THEMES_PATH')) {
    define('SWORD_THEMES_PATH', SWORD_CONTENT_PATH . DIRECTORY_SEPARATOR . 'themes');
}
if (!defined('SWORD_PLUGINS_PATH')) {
    define('SWORD_PLUGINS_PATH', SWORD_CONTENT_PATH . DIRECTORY_SEPARATOR . 'plugins');
}

require_once __DIR__ . '/vendor/autoload.php';
support\App::run();
