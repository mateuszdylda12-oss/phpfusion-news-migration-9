<?php
/*-------------------------------------------------------+
| PHP Fusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: NewsSettingsAdmin.php
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

namespace PHPFusion\News\V7Migration;

class NewsSettingsAdmin {
    private static $instance = NULL;
    private $locale = [];

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->locale = fusion_get_locale('', NEWS_ADMIN_LOCALE);
    }

    /**
     * Display News Settings Admin
     */
    public function displayNewsAdmin() {
        pageaccess('S8');
        
        if (isset($_POST['savesettings'])) {
            $this->saveSettings();
        }
        
        $this->displayForm();
    }

    /**
     * Save Settings
     */
    private function saveSettings() {
        $settings_array = array(
            'news_image_link' => isnum($_POST['news_image_link']) ? $_POST['news_image_link'] : "0",
            'news_image_frontpage' => isnum($_POST['news_image_frontpage']) ? $_POST['news_image_frontpage'] : "0",
            'news_image_readmore' => isnum($_POST['news_image_readmore']) ? $_POST['news_image_readmore'] : "0",
            'news_thumb_ratio' => isnum($_POST['news_thumb_ratio']) ? $_POST['news_thumb_ratio'] : "0",
            'news_thumb_w' => isnum($_POST['news_thumb_w']) ? $_POST['news_thumb_w'] : "100",
            'news_thumb_h' => isnum($_POST['news_thumb_h']) ? $_POST['news_thumb_h'] : "100",
            'news_photo_w' => isnum($_POST['news_photo_w']) ? $_POST['news_photo_w'] : "400",
            'news_photo_h' => isnum($_POST['news_photo_h']) ? $_POST['news_photo_h'] : "300",
            'news_photo_max_w' => isnum($_POST['news_photo_max_w']) ? $_POST['news_photo_max_w'] : "1800",
            'news_photo_max_h' => isnum($_POST['news_photo_max_h']) ? $_POST['news_photo_max_h'] : "1600",
            'news_photo_max_b' => isnum($_POST['news_photo_max_b']) ? $_POST['news_photo_max_b'] : "150000"
        );
        
        foreach ($settings_array as $name => $value) {
            dbquery("UPDATE ".DB_SETTINGS." SET settings_value='".$value."' WHERE settings_name='".$name."'");
        }
        
        addnotice('success', $this->locale['900']);
        redirect(FUSION_REQUEST);
    }

    /**
     * Display Settings Form
     */
    private function displayForm() {
        $settings2 = array();
        $result = dbquery("SELECT * FROM ".DB_SETTINGS);
        while ($data = dbarray($result)) {
            $settings2[$data['settings_name']] = $data['settings_value'];
        }
        
        echo openform('settingsform', 'post', FUSION_SELF.fusion_get_aidlink());
        echo "<div class='row'>\n<div class='col-xs-12 col-sm-8'>\n";
        
        echo "<h4>".$this->locale['950']."</h4>\n";
        echo "<div class='row spacer-sm'>\n<div class='col-xs-12 col-sm-6'>\n";
        echo form_select('news_image_link', $this->locale['951'], $settings2['news_image_link'], ['options' => ['0' => $this->locale['952'], '1' => $this->locale['953']], 'width' => '100%']);
        echo "</div>\n<div class='col-xs-12 col-sm-6'>\n";
        echo form_select('news_image_frontpage', $this->locale['957'], $settings2['news_image_frontpage'], ['options' => ['0' => $this->locale['959'], '1' => $this->locale['960']], 'width' => '100%']);
        echo "</div>\n</div>\n";
        echo "<div class='row spacer-sm'>\n<div class='col-xs-12'>\n";
        echo form_select('news_image_readmore', $this->locale['958'], $settings2['news_image_readmore'], ['options' => ['0' => $this->locale['959'], '1' => $this->locale['960']], 'width' => '100%']);
        echo "</div>\n</div>\n";
        
        echo "<h4 class='spacer-md'>".$this->locale['950']." - Rozmiaru0</h4>\n";
        echo "<div class='row spacer-sm'>\n<div class='col-xs-12 col-sm-4'>\n";
        echo form_text('news_thumb_w', $this->locale['601'], $settings2['news_thumb_w'], ['width' => '100%', 'type' => 'number']);
        echo "</div>\n<div class='col-xs-12 col-sm-4'>\n";
        echo form_text('news_thumb_h', $this->locale['601'].' (H)', $settings2['news_thumb_h'], ['width' => '100%', 'type' => 'number']);
        echo "</div>\n<div class='col-xs-12 col-sm-4'>\n";
        echo form_select('news_thumb_ratio', $this->locale['954'], $settings2['news_thumb_ratio'], ['options' => ['0' => $this->locale['955'], '1' => $this->locale['956']], 'width' => '100%']);
        echo "</div>\n</div>\n";
        
        echo "<div class='row spacer-sm'>\n<div class='col-xs-12 col-sm-6'>\n";
        echo form_text('news_photo_w', $this->locale['602'], $settings2['news_photo_w'], ['width' => '100%', 'type' => 'number']);
        echo "</div>\n<div class='col-xs-12 col-sm-6'>\n";
        echo form_text('news_photo_h', $this->locale['602'].' (H)', $settings2['news_photo_h'], ['width' => '100%', 'type' => 'number']);
        echo "</div>\n</div>\n";
        
        echo "<div class='row spacer-sm'>\n<div class='col-xs-12 col-sm-6'>\n";
        echo form_text('news_photo_max_w', $this->locale['603'], $settings2['news_photo_max_w'], ['width' => '100%', 'type' => 'number']);
        echo "</div>\n<div class='col-xs-12 col-sm-6'>\n";
        echo form_text('news_photo_max_h', $this->locale['603'].' (H)', $settings2['news_photo_max_h'], ['width' => '100%', 'type' => 'number']);
        echo "</div>\n</div>\n";
        
        echo "<div class='row spacer-sm'>\n<div class='col-xs-12'>\n";
        echo form_text('news_photo_max_b', $this->locale['605'], $settings2['news_photo_max_b'], ['width' => '100%', 'type' => 'number']);
        echo "</div>\n</div>\n";
        
        echo "</div>\n<div class='col-xs-12 col-sm-4'>\n";
        openside();
        echo "<p class='text-muted'><small>".$this->locale['604']."</small></p>\n";
        closeside();
        echo "</div>\n</div>\n";
        
        echo form_button('savesettings', $this->locale['750'], $this->locale['750'], ['class' => 'btn-success', 'icon' => 'fa fa-save']);
        echo closeform();
    }
}
?>