<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 自定义评论回调：显示 IP、位置、系统和浏览器以及评论图片
 */
if (!function_exists('notebook_comment_callback')) {
    function notebook_comment_callback($comment, $args, $depth) {
        $GLOBALS['comment'] = $comment;
        $comment_id = $comment->comment_ID;
        $ip = get_comment_meta($comment_id, 'nb_ip', true);
        $os = get_comment_meta($comment_id, 'nb_os', true);
        $browser = get_comment_meta($comment_id, 'nb_browser', true);
        $location = get_comment_meta($comment_id, 'nb_location', true);
        $img = get_comment_meta($comment_id, 'nb_comment_image', true);
        ?>
        <li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
            <article class="comment-body">
                <header class="comment-meta">
                    <span class="comment-author-avatar">
                        <?php echo get_avatar($comment, $args['avatar_size']); ?>
                    </span>
                    <span class="comment-author-name">
                        <?php echo wp_kses_post(get_comment_author_link()); ?>
                    </span>
                    <span class="comment-meta-time">
                        <a href="<?php echo esc_url(get_comment_link($comment_id)); ?>">
                            <time datetime="<?php comment_time('c'); ?>">
                                <?php printf('%1$s %2$s', get_comment_date(), get_comment_time()); ?>
                            </time>
                        </a>
                    </span>
                </header>

                <div class="comment-meta-extra">
                    <?php if ($ip) : ?><span><?php echo 'IP：' . esc_html($ip); ?></span><?php endif; ?>
                    <?php if ($location) : ?><span><?php echo ' · 位置：' . esc_html($location); ?></span><?php endif; ?>
                    <?php if ($os) : ?><span><?php echo ' · 系统：' . esc_html($os); ?></span><?php endif; ?>
                    <?php if ($browser) : ?><span><?php echo ' · 浏览器：' . esc_html($browser); ?></span><?php endif; ?>
                </div>

                <div class="comment-content">
                    <?php comment_text(); ?>
                </div>

                <?php if ($img) : ?>
                    <div class="comment-image">
                        <img src="<?php echo esc_url($img); ?>" alt="">
                    </div>
                <?php endif; ?>

                <div class="reply">
                    <?php
                    comment_reply_link(array_merge($args, [
                        'reply_text' => __('回复', 'notebook'),
                        'depth'      => $depth,
                        'max_depth'  => $args['max_depth'],
                    ]));
                    ?>
                </div>
            </article>
        </li>
        <?php
    }
}

if (post_password_required()) {
    return;
}
?>

<div id="comments" class="comments-area">
    <?php if (have_comments()) : ?>
        <h2 class="comments-title">
            <?php
            printf(
                esc_html(_nx('一条评论', '%1$s 条评论', get_comments_number(), 'comments title', 'notebook')),
                number_format_i18n(get_comments_number())
            );
            ?>
        </h2>

        <ol class="comment-list">
            <?php
            wp_list_comments([
                'style'       => 'ol',
                'short_ping'  => true,
                'avatar_size' => 40,
                'callback'    => 'notebook_comment_callback',
            ]);
            ?>
        </ol>

        <?php the_comments_pagination(); ?>
    <?php endif; ?>

    <?php if (!comments_open() && get_comments_number() && post_type_supports(get_post_type(), 'comments')) : ?>
        <p class="no-comments"><?php esc_html_e('评论已关闭。', 'notebook'); ?></p>
    <?php endif; ?>

    <?php
    comment_form([
        'comment_field' => '<p class="comment-form-comment"><label for="comment">评论</label><textarea id="comment" name="comment" cols="45" rows="5" required></textarea></p>',
        'title_reply'   => __('发表回复', 'notebook'),
    ]);
    ?>
</div>