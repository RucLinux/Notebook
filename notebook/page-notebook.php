<?php
/* Template Name: Notebook Directory */
if (!defined('ABSPATH')) {
    exit;
}

// 复用最新目录页模板，避免旧版布局与右侧贴纸/信纸页逻辑冲突。
require get_template_directory() . '/page-notebook-directory.php';