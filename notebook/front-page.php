<?php
/**
 * 首页：笔记本封面
 */
if (!defined('ABSPATH')) {
    exit;
}

// 兼容 query 形式：/?nb_view=category
// 有 front-page.php 时，首页会优先走本模板；这里拦截后交给统一渲染入口。
$nb_view = get_query_var('nb_view');
if (empty($nb_view) && isset($_GET['nb_view'])) {
    $nb_view = sanitize_key(wp_unslash($_GET['nb_view']));
}
if (!empty($nb_view) && function_exists('notebook_render_nb_view')) {
    get_header();
    notebook_render_nb_view($nb_view);
    get_footer();
    return;
}

get_header();

// 目录页链接（优先生成 query 形式，避免 nginx rewrite 未生效时 404）
$directory_page = get_page_by_path('directory');
if ($directory_page && !empty($directory_page->ID)) {
    $directory_link = add_query_arg('page_id', (int) $directory_page->ID, home_url('/'));
} else {
    $template_pages = get_posts([
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'meta_key'       => '_wp_page_template',
        'meta_value'     => 'page-notebook-directory.php',
        'fields'         => 'ids',
    ]);
    if (!empty($template_pages[0])) {
        $directory_link = add_query_arg('page_id', (int) $template_pages[0], home_url('/'));
    } else {
        $directory_link = add_query_arg('pagename', 'directory', home_url('/'));
    }
}

$cfg = function_exists('notebook_theme_config') ? notebook_theme_config() : [];
$cover_center_image = isset($cfg['cover_center_image']) ? $cfg['cover_center_image'] : '';
$cover_logo_cfg = isset($cfg['cover_logo_url']) ? trim((string) $cfg['cover_logo_url']) : '';
$site_name = !empty($cfg['site_name']) ? $cfg['site_name'] : get_bloginfo('name');
$has_logo = ($cover_logo_cfg !== '') || (function_exists('has_custom_logo') && has_custom_logo());

$start_year = function_exists('notebook_get_first_post_year') ? notebook_get_first_post_year() : (int) date('Y');
$end_year = (int) date('Y');
$year_range = $start_year === $end_year ? (string) $start_year : ($start_year . '-' . $end_year);
$icp_html = isset($cfg['icp_html']) ? notebook_sanitize_footer_html($cfg['icp_html'], true) : '';
$wordpress_link_html = isset($cfg['wordpress_link_html']) ? notebook_sanitize_footer_html($cfg['wordpress_link_html']) : '';
$notebook_link_html = isset($cfg['notebook_link_html']) ? notebook_sanitize_footer_html($cfg['notebook_link_html']) : '';
?>

<style>
/* 首页：整页通栏，避免任何祖先 max-width / padding 造成两侧留白 */
body.notebook-is-front .site-wrapper,
body.notebook-is-front .site-main-wrapper,
body.notebook-is-front .site-main,
body.notebook-is-front .content-area {
    max-width: none !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    display: block !important;
}
body.notebook-is-front {
    overflow-x: hidden;
}
.site-header { display: none !important; }
</style>

<div class="content-area">
    <section class="notebook-cover notebook-cover--home">
        <div class="notebook-cover-logo-wrap">
            <?php if ($has_logo) : ?>
                <a class="notebook-cover-logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr($site_name); ?>">
                    <?php if ($cover_logo_cfg !== '') : ?>
                        <img src="<?php echo esc_url($cover_logo_cfg); ?>" alt="<?php echo esc_attr($site_name); ?>" width="120" height="120" loading="lazy" decoding="async">
                    <?php else : ?>
                        <?php
                        $logo_id = (int) get_theme_mod('custom_logo');
                        if ($logo_id) {
                            echo wp_get_attachment_image($logo_id, 'medium', false, [
                                'class'   => 'notebook-cover-logo-img',
                                'loading' => 'lazy',
                                'alt'     => $site_name,
                            ]);
                        }
                        ?>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
        </div>

        <div class="notebook-cover-inner">
            <div class="notebook-cover-title">
                <?php echo esc_html($site_name); ?>
            </div>
            <div class="notebook-cover-subtitle">
                <?php echo esc_html(!empty($cfg['site_desc']) ? $cfg['site_desc'] : get_bloginfo('description')); ?>
            </div>
            <?php
            $cover_author_line = isset($cfg['cover_author_line']) ? trim((string) $cfg['cover_author_line']) : '';
            if ($cover_author_line !== '') :
                ?>
                <div class="notebook-cover-author">
                    <?php echo esc_html($cover_author_line); ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($cover_center_image)) : ?>
            <div class="notebook-cover-center-art-wrap" aria-hidden="true">
                <img class="notebook-cover-center-art" src="<?php echo esc_url($cover_center_image); ?>" alt="">
            </div>
        <?php endif; ?>

        <?php
        $tabs = notebook_get_sidebar_tabs();
        ?>
        <div class="notebook-tabs-list notebook-tabs-list--cover">
            <?php foreach ($tabs as $tab) : ?>
                <div class="notebook-tab-item">
                    <?php if (!empty($tab['url'])) : ?>
                        <a href="<?php echo esc_url($tab['url']); ?>"><?php echo esc_html($tab['label']); ?></a>
                    <?php else : ?>
                        <span>&nbsp;</span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <a class="notebook-cover-next" href="<?php echo esc_url(add_query_arg('nb_view', 'next', home_url('/'))); ?>">
            <span class="notebook-cover-next-text"><?php esc_html_e('Next', 'notebook'); ?></span>
            <span class="notebook-cover-next-arrow" aria-hidden="true">→</span>
        </a>

        <div class="notebook-cover-footer" role="contentinfo">
            <div class="notebook-cover-footer-line notebook-cover-footer-line--copy">
                <?php echo '© ' . esc_html($year_range) . ' '; ?>
                <a href="<?php echo esc_url(home_url('/')); ?>"><?php echo esc_html($site_name); ?></a>
            </div>
            <div class="notebook-cover-footer-line notebook-cover-footer-line--tech">
                <?php
                echo $wordpress_link_html;
                echo ' & ';
                echo $notebook_link_html;
                ?>
            </div>
            <?php if ($icp_html !== '') : ?>
                <div class="notebook-cover-footer-line notebook-cover-footer-line--icp">
                    <?php echo $icp_html; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php
get_footer();
