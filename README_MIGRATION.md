# PHP Fusion 7 to 9 News System Migration

## Overview
This migration brings the classic PHP Fusion 7 news system to PHP Fusion 9 with the same look and functionality.

## Features Migrated
- ✅ News administration panel
- ✅ News categories management
- ✅ News settings (image sizes, limits)
- ✅ Image upload with thumbnails
- ✅ Draft and sticky news
- ✅ Publish date scheduling
- ✅ Comments and ratings support
- ✅ News visibility levels
- ✅ HTML/text editor support

## Directory Structure
```
infusions/news/
├── classes/
│   ├── NewsAdmin.php              # Main news CRUD operations
│   ├── NewsCategoryAdmin.php      # Category management
│   ├── NewsSettingsAdmin.php      # Settings management
│   └── autoloader.php             # Class autoloader
├── news_admin.php                 # Admin entry point
└── README_MIGRATION.md            # This file
```

## Installation
1. Copy files to `infusions/news/classes/`
2. Ensure database tables exist:
   - `news` table with all required columns
   - `news_cats` table for categories
   - `settings` table for news settings
3. Access admin panel at: `admin/news_admin.php`

## Usage
### Creating a News Item
1. Go to Admin Panel > News Management
2. Fill in the form:
   - Title (required)
   - Category (optional)
   - Content (required)
   - Extended content (optional)
   - Image upload (optional)
   - Scheduling dates (optional)
3. Set options:
   - Draft status
   - Sticky (featured)
   - Allow comments
   - Allow ratings
   - Visibility level
4. Save or preview

### Managing Categories
1. Go to Admin Panel > News Categories
2. Add, edit, or delete categories
3. Cannot delete categories with associated news

### Settings
1. Go to Admin Panel > News Settings
2. Configure:
   - Image display options
   - Thumbnail dimensions
   - Photo display dimensions
   - Maximum file size
   - File types

## Database Requirements
### news table
```sql
CREATE TABLE IF NOT EXISTS `news` (
  `news_id` int(11) NOT NULL AUTO_INCREMENT,
  `news_subject` varchar(255) NOT NULL,
  `news_cat` int(11) NOT NULL DEFAULT '0',
  `news_news` longtext NOT NULL,
  `news_extended` longtext,
  `news_image` varchar(255),
  `news_image_t1` varchar(255),
  `news_image_t2` varchar(255),
  `news_breaks` char(1) DEFAULT 'n',
  `news_name` int(11) NOT NULL,
  `news_datestamp` int(11) NOT NULL,
  `news_start` int(11) DEFAULT '0',
  `news_end` int(11) DEFAULT '0',
  `news_draft` tinyint(1) DEFAULT '0',
  `news_sticky` tinyint(1) DEFAULT '0',
  `news_visibility` int(11) DEFAULT '0',
  `news_allow_comments` tinyint(1) DEFAULT '1',
  `news_allow_ratings` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`news_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### news_cats table
```sql
CREATE TABLE IF NOT EXISTS `news_cats` (
  `news_cat_id` int(11) NOT NULL AUTO_INCREMENT,
  `news_cat_name` varchar(100) NOT NULL,
  `news_cat_image` varchar(255),
  PRIMARY KEY (`news_cat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## API Functions

### NewsAdmin::getInstance()->displayNewsAdmin()
Main admin display for news management

### NewsCategoryAdmin::getInstance()->displayNewsAdmin()
Category management display

### NewsSettingsAdmin::getInstance()->displayNewsAdmin()
Settings management display

## Compatibility
- PHP Fusion 9.x
- PHP 7.2+
- MySQL 5.7+

## License
Affero GPL

## Notes
- Images are stored in `images/news/` and `images/news_t/`
- Thumbnails are auto-generated on upload
- Soft delete is not implemented - deletes are permanent
- News visibility follows PHP Fusion user group system
