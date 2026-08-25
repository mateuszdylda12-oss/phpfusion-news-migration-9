<?php
/*-------------------------------------------------------+
| PHP Fusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: NewsCategoryAdmin.php
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

class NewsCategoryAdmin {
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
     * Display News Category Admin Interface
     */
    public function displayNewsAdmin() {
        pageaccess('NC');
        
        if ((isset($_GET['action']) && $_GET['action'] == "delete") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
            $this->deleteCategory($_GET['cat_id']);
        } elseif (isset($_POST['save_cat'])) {
            $this->saveCategory();
        } elseif ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
            $this->editCategory($_GET['cat_id']);
        }
        
        $this->displayForm();
    }

    /**
     * Save Category
     */
    private function saveCategory() {
        $cat_name = stripinput($_POST['cat_name']);
        $cat_image = stripinput($_POST['cat_image']);
        
        if ($cat_name && $cat_image) {
            if ((isset($_GET['action']) && $_GET['action'] == "edit") && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
                dbquery("UPDATE ".DB_NEWS_CATS." SET news_cat_name='".$cat_name."', news_cat_image='".$cat_image."' WHERE news_cat_id='".$_GET['cat_id']."'");
                addnotice('success', $this->locale['421']);
            } else {
                $checkCat = dbcount("(news_cat_id)", DB_NEWS_CATS, "news_cat_name='".$cat_name."'");
                if ($checkCat == 0) {
                    $data = array('news_cat_name' => $cat_name, 'news_cat_image' => $cat_image);
                    dbquery_insert(DB_NEWS_CATS, $data, 'insert');
                    addnotice('success', $this->locale['420']);
                } else {
                    addnotice('warning', $this->locale['461']);
                }
            }
        } else {
            addnotice('danger', $this->locale['460']);
        }
        
        redirect(FUSION_REQUEST);
    }

    /**
     * Delete Category
     */
    private function deleteCategory($cat_id) {
        $result = dbcount("(news_cat_id)", DB_NEWS, "news_cat='".$cat_id."'");
        if (!empty($result)) {
            addnotice('warning', $this->locale['422']);
        } else {
            dbquery("DELETE FROM ".DB_NEWS_CATS." WHERE news_cat_id='".$cat_id."'");
            addnotice('success', $this->locale['424']);
        }
        
        redirect(FUSION_REQUEST);
    }

    /**
     * Edit Category
     */
    private function editCategory($cat_id) {
        $result = dbquery("SELECT news_cat_id, news_cat_name, news_cat_image FROM ".DB_NEWS_CATS." WHERE news_cat_id='".$cat_id."'");
        if (dbrows($result)) {
            $data = dbarray($result);
            return [
                'cat_name' => $data['news_cat_name'],
                'cat_image' => $data['news_cat_image'],
                'cat_id' => $data['news_cat_id']
            ];
        }
        return false;
    }

    /**
     * Display Category Form
     */
    private function displayForm() {
        $cat_name = "";
        $cat_image = "";
        $formaction = FUSION_SELF.fusion_get_aidlink();
        
        echo openform('addcat', 'post', $formaction);
        echo "<div class='row'>\n<div class='col-xs-12 col-sm-6'>\n";
        echo form_text('cat_name', $this->locale['430'], $cat_name, ['required' => true, 'width' => '100%']);
        echo form_select('cat_image', $this->locale['431'], $cat_image, ['width' => '100%']);
        echo "</div>\n</div>\n";
        echo form_button('save_cat', $this->locale['432'], $this->locale['432'], ['class' => 'btn-success', 'icon' => 'fa fa-save']);
        echo closeform();
        
        // List categories
        $result = dbquery("SELECT news_cat_id, news_cat_name FROM ".DB_NEWS_CATS." ORDER BY news_cat_name");
        $rows = dbrows($result);
        
        if ($rows != 0) {
            echo "<div class='row'>\n<div class='col-xs-12'>\n";
            echo "<table class='table table-striped'>\n<thead>\n<tr>\n";
            echo "<th>".$this->locale['430']."</th>\n";
            echo "<th class='text-right'>".$this->locale['actions']."</th>\n";
            echo "</tr>\n</thead>\n<tbody>\n";
            
            while ($data = dbarray($result)) {
                echo "<tr>\n";
                echo "<td>".$data['news_cat_name']."</td>\n";
                echo "<td class='text-right'>\n";
                echo "<a href='\"'.FUSION_SELF.fusion_get_aidlink()."&action=edit&cat_id=".$data['news_cat_id']."\" class='btn btn-xs btn-primary'><i class='fa fa-pencil'></i></a> ";
                echo "<a href='\"'.FUSION_SELF.fusion_get_aidlink()."&action=delete&cat_id=".$data['news_cat_id']."\" onclick=\"return confirm('\"'.$this->locale['confirm_delete'].'\");\" class='btn btn-xs btn-danger'><i class='fa fa-trash'></i></a>\n";
                echo "</td>\n</tr>\n";
            }
            
            echo "</tbody>\n</table>\n";
            echo "</div>\n</div>\n";
        } else {
            echo "<div class='alert alert-info'>".$this->locale['435']."</div>\n";
        }
    }
}
?>