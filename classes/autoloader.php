<?php
/*-------------------------------------------------------+
| PHP Fusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: autoloader.php
| Author: Migration from v7
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

spl_autoload_register(function($class) {
    if (strpos($class, 'PHPFusion\\News\\V7Migration\\') === 0) {
        $class = str_replace('PHPFusion\\News\\V7Migration\\', '', $class);
        $file = __DIR__ . '/' . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

// Load all classes
require_once __DIR__ . '/NewsAdmin.php';
require_once __DIR__ . '/NewsCategoryAdmin.php';
require_once __DIR__ . '/NewsSettingsAdmin.php';
?>