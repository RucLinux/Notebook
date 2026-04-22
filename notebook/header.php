<?php
if (!defined('ABSPATH')) {
    exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<div class="site-wrapper">
    <?php
    // 首页/目录页不输出顶部站点头部，避免出现你说的顶部两行“站点标题链接”
    $hide_header = is_front_page() || is_page_template('page-notebook-directory.php') || is_page_template('page-notebook.php');
    ?>
    <?php if (!$hide_header) : ?>
    <header class="site-header">
        <div class="header-inner">
            <div class="site-branding">
                <?php if (has_custom_logo()) : ?>
                    <?php the_custom_logo(); ?>
                <?php endif; ?>
                <div class="site-title-desc">
                    <div class="site-title">
                        <a href="<?php echo esc_url(home_url('/')); ?>">
                            <?php bloginfo('name'); ?>
                        </a>
                    </div>
                    <?php if (get_bloginfo('description')) : ?>
                        <div class="site-description">
                            <?php bloginfo('description'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <nav class="main-nav" aria-label="<?php esc_attr_e('主菜单', 'notebook'); ?>">
                <?php
                wp_nav_menu([
                    'theme_location' => 'primary',
                    'container'      => false,
                    'menu_class'     => '',
                    'fallback_cb'    => false,
                ]);
                ?>
            </nav>
        </div>
    </header>
    <?php endif; ?>

    <div class="site-main-wrapper">
        <main class="site-main">