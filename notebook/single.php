<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="content-area">
    <div class="notebook-page notebook-page--single">
        <div class="notebook-page-inner">
            <?php
            if (have_posts()) :
                while (have_posts()) :
                    the_post();
                    $post_id = get_the_ID();
                    $views = (int) get_post_meta($post_id, 'notebook_post_views', true);
                    if (!is_preview() && !is_admin()) {
                        $views++;
                        update_post_meta($post_id, 'notebook_post_views', $views);
                    }

                    $plain_text = wp_strip_all_tags(get_the_content());
                    $plain_text = preg_replace('/\s+/u', '', (string) $plain_text);
                    if (function_exists('mb_strlen')) {
                        $word_count = (int) mb_strlen($plain_text, 'UTF-8');
                    } else {
                        $word_count = (int) strlen($plain_text);
                    }

                    $directory_page = get_page_by_path('directory');
                    $directory_link = $directory_page ? get_permalink($directory_page) : home_url('/?nb_view=next');
                    $prev_post = get_previous_post();
                    $next_post = get_next_post();
                    $prev_link = $prev_post instanceof WP_Post ? get_permalink($prev_post) : $directory_link;
                    $next_link = $next_post instanceof WP_Post ? get_permalink($next_post) : home_url('/?nb_view=next');
                    ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                        <header class="entry-header">
                            <h1 class="entry-title"><?php the_title(); ?></h1>
                            <div class="entry-meta">
                                <span><?php the_author(); ?></span>
                                <span><?php echo esc_html(get_the_date('Y-m-d H:i')); ?></span>
                                <span><?php echo esc_html(sprintf(__('文章字数：%d', 'notebook'), $word_count)); ?></span>
                                <span><?php echo esc_html(sprintf(__('阅读数量：%d', 'notebook'), $views)); ?></span>
                            </div>
                        </header>

                        <div class="entry-content">
                            <?php the_content(); ?>
                        </div>
                    </article>

                    <?php comments_template(); ?>

                    <nav class="notebook-single-nav" aria-label="<?php esc_attr_e('文章导航', 'notebook'); ?>">
                        <div class="notebook-single-nav-item notebook-single-nav-item--prev">
                            <a href="<?php echo esc_url($prev_link); ?>" aria-label="上一篇">← 上一篇</a>
                        </div>
                        <div class="notebook-single-nav-item"><a href="<?php echo esc_url($directory_link); ?>">返回目录</a></div>
                        <div class="notebook-single-nav-item"><a href="<?php echo esc_url(home_url('/')); ?>">返回封面</a></div>
                        <div class="notebook-single-nav-item notebook-single-nav-item--next">
                            <a href="<?php echo esc_url($next_link); ?>" aria-label="下一篇">下一篇 →</a>
                        </div>
                    </nav>
                <?php
                endwhile;
            endif;
            ?>
        </div>
    </div>
</div>

<?php
get_footer();