<?php
/**
 * 模板名称: 笔记本目录页
 */
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="content-area">
    <div class="notebook-page notebook-tabs notebook-page--directory">
        <div class="notebook-page-inner">
            <?php if (have_posts()) : the_post(); ?>
                <header class="entry-header">
                    <h1 class="entry-title"><?php the_title(); ?></h1>
                </header>
                <div class="entry-content">
                    <?php the_content(); ?>
                </div>

                <section class="directory-lists">
                    <h2><?php esc_html_e('文章目录', 'notebook'); ?></h2>
                    <ul>
                        <?php
                        $i = 1;
                        $posts = get_posts([
                            'numberposts' => -1,
                            'post_status' => 'publish',
                        ]);
                        foreach ($posts as $post_item) :
                            ?>
                            <li>
                                <a href="<?php echo esc_url(get_permalink($post_item)); ?>">
                                    <?php echo esc_html(get_the_title($post_item)); ?>
                                </a>
                                <span class="dir-date"><?php echo esc_html(get_the_date('', $post_item)); ?></span>
                                <span class="dir-page"><?php echo 'P' . str_pad((string) $i, 2, '0', STR_PAD_LEFT); ?></span>
                            </li>
                            <?php $i++; ?>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>
        </div>

        <div class="notebook-tabs-list notebook-tabs-list--directory">
            <?php
            // 插页标签文字从站点数据读取（同封面）
            $tabs = notebook_get_sidebar_tabs();
            foreach ($tabs as $tab) :
                ?>
                <div class="notebook-tab-item">
                    <?php if (!empty($tab['url'])) : ?>
                        <a href="<?php echo esc_url($tab['url']); ?>"><?php echo esc_html($tab['label']); ?></a>
                    <?php else : ?>
                        <span>&nbsp;</span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
get_footer();

