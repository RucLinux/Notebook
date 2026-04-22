<?php
if (!defined('ABSPATH')) {
    exit;
}

// notebook 信纸页视图路由
$nb_view = get_query_var('nb_view');
if (!empty($nb_view) && function_exists('notebook_render_nb_view')) {
    get_header();
    notebook_render_nb_view($nb_view);
    get_footer();
    return;
}

// 「设置 → 阅读」为「最新文章」时首页走 index.php，仍使用笔记本封面模板
if (is_front_page() && !is_paged()) {
    require get_template_directory() . '/front-page.php';
    return;
}

get_header();
?>

<div class="content-area">
    <div class="notebook-page notebook-tabs notebook-page--content">
        <div class="notebook-page-inner">
            <?php if (have_posts()) : ?>
                <?php while (have_posts()) : the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                        <header class="entry-header">
                            <?php the_title('<h2 class="entry-title"><a href="' . esc_url(get_permalink()) . '">', '</a></h2>'); ?>
                            <div class="entry-meta">
                                <?php echo get_the_date(); ?> · <?php the_author(); ?>
                            </div>
                        </header>
                        <div class="entry-content">
                            <?php the_excerpt(); ?>
                        </div>
                    </article>
                <?php endwhile; ?>

                <div class="pagination">
                    <?php the_posts_pagination(); ?>
                </div>
            <?php else : ?>
                <p><?php esc_html_e('暂无文章。', 'notebook'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php get_sidebar(); ?>

<?php
get_footer();

