<?php
/*-------------------------------------------------------+
| PHP Fusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: NewsAdmin.php
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

class NewsAdmin {
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
     * Display News Admin Interface
     */
    public function displayNewsAdmin() {
        pageaccess('N');
        
        if (isset($_POST['save'])) {
            $this->saveNews();
        } elseif (isset($_POST['delete']) && isset($_POST['news_id'])) {
            $this->deleteNews($_POST['news_id']);
        } elseif (isset($_POST['preview'])) {
            $this->previewNews();
        }
        
        $this->displayForm();
    }

    /**
     * Save News (Create/Update)
     */
    private function saveNews() {
        $error = 0;
        $news_subject = stripinput($_POST['news_subject']);
        $news_cat = isnum($_POST['news_cat']) ? $_POST['news_cat'] : "0";
        $body = addslash($_POST['body']);
        $body2 = isset($_POST['body2']) ? addslash(preg_replace("(^<p>\s</p>$)", "", $_POST['body2'])) : "";
        
        // Handle image upload
        $news_image = "";
        $news_image_t1 = "";
        $news_image_t2 = "";
        
        if (isset($_FILES['news_image']) && is_uploaded_file($_FILES['news_image']['tmp_name'])) {
            $result = $this->handleImageUpload($_FILES['news_image']);
            if ($result['success']) {
                $news_image = $result['image'];
                $news_image_t1 = $result['image_t1'];
                $news_image_t2 = $result['image_t2'];
            } else {
                $error = $result['error'];
            }
        }
        
        // Handle dates
        $news_start_date = 0;
        $news_end_date = 0;
        
        if ($_POST['news_start']['mday']!="--" && $_POST['news_start']['mon']!="--" && $_POST['news_start']['year']!="----") {
            $news_start_date = mktime($_POST['news_start']['hours'], $_POST['news_start']['minutes'], 0, $_POST['news_start']['mon'], $_POST['news_start']['mday'], $_POST['news_start']['year']);
        }
        
        if ($_POST['news_end']['mday']!="--" && $_POST['news_end']['mon']!="--" && $_POST['news_end']['year']!="----") {
            $news_end_date = mktime($_POST['news_end']['hours'], $_POST['news_end']['minutes'], 0, $_POST['news_end']['mon'], $_POST['news_end']['mday'], $_POST['news_end']['year']);
        }
        
        $news_visibility = isnum($_POST['news_visibility']) ? $_POST['news_visibility'] : "0";
        $news_draft = isset($_POST['news_draft']) ? "1" : "0";
        $news_sticky = isset($_POST['news_sticky']) ? "1" : "0";
        $news_breaks = isset($_POST['line_breaks']) ? "y" : "n";
        $news_comments = isset($_POST['news_comments']) ? "1" : "0";
        $news_ratings = isset($_POST['news_ratings']) ? "1" : "0";
        
        if (isset($_POST['news_id']) && isnum($_POST['news_id'])) {
            // Update
            if ($news_sticky == "1") {
                dbquery("UPDATE ".DB_NEWS." SET news_sticky='0' WHERE news_sticky='1'");
            }
            
            $data = array(
                'news_subject' => $news_subject,
                'news_cat' => $news_cat,
                'news_news' => $body,
                'news_extended' => $body2,
                'news_breaks' => $news_breaks,
                'news_visibility' => $news_visibility,
                'news_draft' => $news_draft,
                'news_sticky' => $news_sticky,
                'news_allow_comments' => $news_comments,
                'news_allow_ratings' => $news_ratings,
                'news_start' => $news_start_date,
                'news_end' => $news_end_date
            );
            
            if (!empty($news_image)) {
                $data['news_image'] = $news_image;
                $data['news_image_t1'] = $news_image_t1;
                $data['news_image_t2'] = $news_image_t2;
            }
            
            dbquery_insert(DB_NEWS, $data, 'update', ['primary_key' => 'news_id', 'where' => 'news_id = '.$_POST['news_id']);
            addnotice('success', $this->locale['411']);
        } else {
            // Insert
            if ($news_sticky == "1") {
                dbquery("UPDATE ".DB_NEWS." SET news_sticky='0' WHERE news_sticky='1'");
            }
            
            $data = array(
                'news_subject' => $news_subject,
                'news_cat' => $news_cat,
                'news_news' => $body,
                'news_extended' => $body2,
                'news_breaks' => $news_breaks,
                'news_name' => userid(),
                'news_datestamp' => time(),
                'news_start' => $news_start_date,
                'news_end' => $news_end_date,
                'news_image' => $news_image,
                'news_image_t1' => $news_image_t1,
                'news_image_t2' => $news_image_t2,
                'news_visibility' => $news_visibility,
                'news_draft' => $news_draft,
                'news_sticky' => $news_sticky,
                'news_allow_comments' => $news_comments,
                'news_allow_ratings' => $news_ratings
            );
            
            dbquery_insert(DB_NEWS, $data, 'insert');
            addnotice('success', $this->locale['410']);
        }
        
        redirect(FUSION_REQUEST);
    }

    /**
     * Delete News
     */
    private function deleteNews($news_id) {
        $result = dbquery("SELECT news_image, news_image_t1, news_image_t2 FROM ".DB_NEWS." WHERE news_id='".$news_id."' LIMIT 1");
        
        if (dbrows($result)) {
            $data = dbarray($result);
            
            // Delete images
            if (!empty($data['news_image']) && file_exists(IMAGES_N.$data['news_image'])) {
                unlink(IMAGES_N.$data['news_image']);
            }
            if (!empty($data['news_image_t1']) && file_exists(IMAGES_N_T.$data['news_image_t1'])) {
                unlink(IMAGES_N_T.$data['news_image_t1']);
            }
            if (!empty($data['news_image_t2']) && file_exists(IMAGES_N_T.$data['news_image_t2'])) {
                unlink(IMAGES_N_T.$data['news_image_t2']);
            }
            
            // Delete news and related data
            dbquery("DELETE FROM ".DB_NEWS." WHERE news_id='".$news_id."'");
            dbquery("DELETE FROM ".DB_COMMENTS." WHERE comment_item_id='".$news_id."' AND comment_type='N'");
            dbquery("DELETE FROM ".DB_RATINGS." WHERE rating_item_id='".$news_id."' AND rating_type='N'");
            
            addnotice('success', $this->locale['412']);
        }
        
        redirect(FUSION_REQUEST);
    }

    /**
     * Handle Image Upload
     */
    private function handleImageUpload($image) {
        require_once INCLUDES."photo_functions_include.php";
        
        $image_name = stripfilename(str_replace(" ", "_", strtolower(substr($image['name'], 0, strrpos($image['name'], ".")))));
        $image_ext = strtolower(strrchr($image['name'],"."));
        
        if ($image_ext == ".gif") { $filetype = 1;
        } elseif ($image_ext == ".jpg" || $image_ext == ".jpeg") { $filetype = 2;
        } elseif ($image_ext == ".png") { $filetype = 3;
        } else { $filetype = false; }
        
        if (!preg_match("/^[-0-9A-Z_\.\[\]]+$/i", $image_name)) {
            return ['success' => false, 'error' => 1];
        } elseif ($image['size'] > 2097152) {
            return ['success' => false, 'error' => 2];
        } elseif (!$filetype) {
            return ['success' => false, 'error' => 3];
        }
        
        $image_full = image_exists(IMAGES_N, $image_name.$image_ext);
        move_uploaded_file($image['tmp_name'], IMAGES_N.$image_full);
        
        $imagefile = @getimagesize(IMAGES_N.$image_full);
        if ($imagefile[0] > 1800 || $imagefile[1] > 1600) {
            unlink(IMAGES_N.$image_full);
            return ['success' => false, 'error' => 4];
        }
        
        $image_t1 = image_exists(IMAGES_N_T, $image_name."_t1".$image_ext);
        $image_t2 = image_exists(IMAGES_N_T, $image_name."_t2".$image_ext);
        
        createthumbnail($filetype, IMAGES_N.$image_full, IMAGES_N_T.$image_t1, 800, 640);
        createsquarethumbnail($filetype, IMAGES_N.$image_full, IMAGES_N_T.$image_t2, 300);
        
        return [
            'success' => true,
            'image' => $image_full,
            'image_t1' => $image_t1,
            'image_t2' => $image_t2
        ];
    }

    /**
     * Display News Form
     */
    private function displayForm() {
        $news_subject = "";
        $news_cat = "0";
        $body = "";
        $body2 = "";
        $news_image = "";
        $news_image_t1 = "";
        $news_image_t2 = "";
        $news_visibility = 0;
        $news_draft = "";
        $news_sticky = "";
        $news_breaks = " checked='checked'";
        $news_comments = " checked='checked'";
        $news_ratings = " checked='checked'";
        $news_id = "";
        
        // Get news list
        $result = dbquery("SELECT news_id, news_subject, news_draft FROM ".DB_NEWS." ORDER BY news_draft DESC, news_datestamp DESC");
        
        if (dbrows($result) != 0) {
            $editlist = "";
            $sel = "";
            while ($data = dbarray($result)) {
                if ((isset($_POST['news_id']) && isnum($_POST['news_id'])) || (isset($_GET['news_id']) && isnum($_GET['news_id']))) {
                    $news_id = isset($_POST['news_id']) ? $_POST['news_id'] : $_GET['news_id'];
                    $sel = ($news_id == $data['news_id'] ? " selected='selected'" : "");
                }
                $editlist .= "<option value='".$data['news_id']."'".$sel.">".($data['news_draft'] ? "[DRAFT] " : "").$data['news_subject']."</option>\n";
            }
            
            echo openform('selectform', 'post', FUSION_SELF.fusion_get_aidlink());
            echo "<div class='row'>\n<div class='col-xs-12'>\n";
            echo form_select('news_id', $this->locale['400'], '', ['options' => $editlist, 'width' => '100%']);
            echo "</div>\n</div>\n";
            echo form_button('edit', $this->locale['420'], $this->locale['420'], ['class' => 'btn-primary', 'icon' => 'fa fa-pencil']);
            echo form_button('delete', $this->locale['421'], $this->locale['421'], ['class' => 'btn-danger', 'icon' => 'fa fa-trash', 'onclick' => "return confirm('\"'.$this->locale['451'].'\");"]);
            echo closeform();
        }
        
        // Get categories
        $result = dbquery("SELECT news_cat_id, news_cat_name FROM ".DB_NEWS_CATS." ORDER BY news_cat_name");
        $news_cat_opts = "";
        if (dbrows($result)) {
            while ($data = dbarray($result)) {
                $sel = ($news_cat == $data['news_cat_id'] ? " selected='selected'" : "");
                $news_cat_opts .= "<option value='".$data['news_cat_id']."'".$sel.">".stripslash($data['news_cat_name'])."</option>\n";
            }
        }
        
        echo openform('inputform', 'post', FUSION_SELF.fusion_get_aidlink(), ['class' => 'spacer-sm', 'enctype' => 'multipart/form-data']);
        echo "<div class='row'>\n<div class='col-xs-12 col-sm-9'>\n";
        echo form_text('news_subject', $this->locale['422'], $news_subject, ['required' => true, 'width' => '100%']);
        echo form_select('news_cat', $this->locale['423'], $news_cat, ['options' => $news_cat_opts, 'width' => '100%']);
        echo form_textarea('body', $this->locale['425'], $body, ['required' => true, 'width' => '100%', 'rows' => 10]);
        echo form_textarea('body2', $this->locale['426'], $body2, ['width' => '100%', 'rows' => 10]);
        echo "</div>\n<div class='col-xs-12 col-sm-3'>\n";
        echo form_file('news_image', $this->locale['439'], '', ['width' => '100%']);
        echo "</div>\n</div>\n";
        echo form_button('preview', $this->locale['436'], $this->locale['436'], ['class' => 'btn-info', 'icon' => 'fa fa-eye']);
        echo form_button('save', $this->locale['437'], $this->locale['437'], ['class' => 'btn-success', 'icon' => 'fa fa-save']);
        echo closeform();
    }

    /**
     * Preview News
     */
    private function previewNews() {
        echo "<div class='alert alert-info'>\n".$this->locale['436']." ".$_POST['news_subject']."\n</div>\n";
        echo "<div class='row'>\n<div class='col-xs-12'>\n";
        echo stripslash($_POST['body']);
        echo "</div>\n</div>\n";
    }
}
?>