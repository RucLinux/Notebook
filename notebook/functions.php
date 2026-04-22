<?php
/**
 * Theme Name: Notebook
 * 分发包：请在本文件 notebook_theme_config() 中填写站点展示文案；默认已留空以便新用户自行配置。
 *
 * Notebook Theme functions
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme setup
 */
function notebook_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('custom-logo', [
        'height'      => 80,
        'width'       => 80,
        'flex-width'  => true,
        'flex-height' => true,
    ]);

    register_nav_menus([
        'primary' => __('主菜单', 'notebook'),
    ]);
}
add_action('after_setup_theme', 'notebook_theme_setup');

/**
 * 封面首页：静态首页时 body 不一定带 WordPress 默认的 .home，补稳定类名供 CSS 使用
 */
function notebook_body_class_front($classes) {
    if (is_front_page()) {
        $classes[] = 'notebook-is-front';
    }
    return $classes;
}
add_filter('body_class', 'notebook_body_class_front');

/**
 * Enqueue styles
 */
function notebook_enqueue_assets() {
    $style_path = get_stylesheet_directory() . '/style.css';
    $style_ver = file_exists($style_path) ? (string) filemtime($style_path) : '1.0.0';
    wp_enqueue_style('notebook-style', get_stylesheet_uri(), [], $style_ver);

    // 代码高亮（前台）
    if (!is_admin()) {
        wp_enqueue_style(
            'notebook-hljs-style',
            'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css',
            [],
            '11.9.0'
        );
        wp_enqueue_script(
            'notebook-hljs',
            'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js',
            [],
            '11.9.0',
            true
        );
        wp_add_inline_script(
            'notebook-hljs',
            'document.addEventListener("DOMContentLoaded",function(){if(window.hljs){document.querySelectorAll("pre code").forEach(function(el){window.hljs.highlightElement(el);});}});'
        );
    }
}
add_action('wp_enqueue_scripts', 'notebook_enqueue_assets');

/**
 * Sidebar widgets
 */
function notebook_register_sidebars() {
    register_sidebar([
        'name'          => __('主侧边栏', 'notebook'),
        'id'            => 'sidebar-1',
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ]);
}
add_action('widgets_init', 'notebook_register_sidebars');

/**
 * 主题配置（供封面/目录/信纸页/页脚等调用）
 * 注意：其中“备案号”等信息目前给了占位值，你需要按实际修改。
 */
function notebook_theme_config() {
    // 优先读取 WordPress 数据库里的站点名称/描述（旧站已写入的数据会在这里生效）
    $db_site_name = trim((string) get_bloginfo('name'));
    $db_site_desc = trim((string) get_bloginfo('description'));

    return [
        // 站点名称：用于封面/底部等位置展示
        'site_name' => $db_site_name !== '' ? $db_site_name : '',
        // 站点描述：用于封面标题下方展示的说明文字
        'site_desc' => $db_site_desc !== '' ? $db_site_desc : '',
        // 封面第三行：显示在「网站标题、网站描述」下方（例如：作者：某某）。留空则不显示该行
        'cover_author_line' => '',
        // 封面背景图（若你仍使用 style.css 背景图，可不影响；这里提供给模板做扩展/替换用）
        'cover_image' => get_theme_file_uri('images/cover-1.png'),
        // 封面中间“主题图案”（例如太极图）
        // 需要把图片放到：themes/Notebook/images/taiji.png
        'cover_center_image' => get_theme_file_uri('images/taiji.png'),
        // 封面左上角 Logo：留空则优先使用「外观 → 自定义 → 站点身份 → 标志」
        'cover_logo_url' => '',
        // 备案信息（第三行底部显示）
        // 请把占位“粤ICP备XXXX号-X”替换成你的真实备案号与链接
        'icp_html' => '备案号：<a href="https://beian.miit.gov.cn/" target="_blank" rel="noopener">琼ICP备XXXX号-X</a>',
        // 底部“WordPress & Notebook”第二行中的 WordPress 链接（带文字与跳转）
        'wordpress_link_html' => '<a href="https://wordpress.org" target="_blank" rel="noopener">WordPress</a>',
        // 底部“WordPress & Notebook”第二行中的 Notebook 链接（带文字与跳转）
        'notebook_link_html'  => '<a href="https://notebook.myzhenai.com.cn/" target="_blank" rel="noopener">Notebook</a>',
    ];
}

/**
 * 对配置里的 HTML（页脚链接/备案）做最小白名单过滤，避免注入。
 */
function notebook_sanitize_footer_html($html, $allow_br = false) {
    $allowed = [
        'a' => [
            'href'   => true,
            'target' => true,
            'rel'    => true,
            'title'  => true,
        ],
    ];
    if ($allow_br) {
        $allowed['br'] = [];
    }
    return wp_kses((string) $html, $allowed);
}

/**
 * 获取第一个文章（最早发布）的年份
 * 用于底部版权年份范围：例如 2019-2026
 */
function notebook_get_first_post_year() {
    $q = new WP_Query([
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'orderby'        => 'date',
        'order'          => 'ASC',
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ]);

    if (!empty($q->posts[0])) {
        $ts = get_post_time('U', true, $q->posts[0]);
        if ($ts) {
            return (int) date('Y', $ts);
        }
    }
    return (int) date('Y');
}

/**
 * 插页标签数据：从站点实际数据读取文字（页面标题、文章页标题等）
 * 返回 [ ['label' => '...', 'url' => '...'], ... ]
 */
function notebook_get_sidebar_tabs() {
    // 7 个贴纸位（按你图片从上到下的排列）
    // 1. 分类（显示分类下文章标题，分类下不评论）
    // 2. 归档（近 12 个月：点击树状展示该月文章标题）
    // 3. Link（WordPress 链接管理器 Links/书签）
    // 4. 关于（about 页面内容）
    // 5. 登陆（WordPress 登录）
    // 6. 搜索（站内搜索入口）
    // 7. 自定义（列出所有自定义页面名称和链接）
    $home = home_url('/');
    $view_url = function ($view) use ($home) {
        // 兼容 nginx 未启用 rewrite：统一走 query 形式，避免 /nb/{view}/ 404
        return add_query_arg('nb_view', $view, $home);
    };

    $tabs = [
        ['label' => __('分类', 'notebook'), 'url' => $view_url('category')],
        ['label' => __('归档', 'notebook'), 'url' => $view_url('archive')],
        ['label' => __('Link', 'notebook'), 'url' => $view_url('links')],
        ['label' => __('关于', 'notebook'), 'url' => $view_url('about')],
        ['label' => __('登陆', 'notebook'), 'url' => wp_login_url(home_url('/'))],
        ['label' => __('搜索', 'notebook'), 'url' => $view_url('search')],
        ['label' => __('自定义', 'notebook'), 'url' => $view_url('custom')],
    ];

    return $tabs;
}

/**
 * 分类目录内链（信纸页），不跳转到 WordPress 默认分类归档。
 *
 * @param string $slug 分类 slug；空字符串表示根「分类目录」。
 */
function notebook_get_nb_category_directory_url($slug = '') {
    $slug = sanitize_title((string) $slug);
    $base = home_url('/');
    if ($slug === '') {
        return add_query_arg('nb_view', 'category', $base);
    }
    return add_query_arg(
        [
            'nb_view' => 'category',
            'nb_cat'  => $slug,
        ],
        $base
    );
}

/**
 * 默认「分类归档」URL 仍走 WordPress 主循环（侧栏 + 摘要）。重定向到信纸内的分类目录流。
 */
function notebook_redirect_category_archive_to_nb_view() {
    if (is_admin() || wp_doing_ajax() || is_feed() || is_embed()) {
        return;
    }
    if (function_exists('wp_is_json_request') && wp_is_json_request()) {
        return;
    }
    if (!is_category()) {
        return;
    }
    $term = get_queried_object();
    if (!$term instanceof WP_Term || $term->taxonomy !== 'category') {
        return;
    }
    $slug = $term->slug;
    if ($slug === '') {
        return;
    }
    $target = notebook_get_nb_category_directory_url($slug);
    if ($target === '') {
        return;
    }
    wp_safe_redirect($target, 301);
    exit;
}
add_action('template_redirect', 'notebook_redirect_category_archive_to_nb_view', 5);

/**
 * ====== Notebook 信纸页视图路由（干净路径 /nb/{view}/） ======
 * 说明：通过 rewrite 把 /nb/category/ 之类映射到 index.php，再由 index.php 调用渲染函数。
 */
function notebook_add_nb_rewrite_rules() {
    add_rewrite_rule('^nb/category/([^/]+)/?$', 'index.php?nb_view=category&nb_cat=$matches[1]', 'top');
    add_rewrite_rule('^nb/([^/]+)/?$', 'index.php?nb_view=$matches[1]', 'top');
}
add_action('init', 'notebook_add_nb_rewrite_rules');

function notebook_nb_query_vars($vars) {
    $vars[] = 'nb_view';
    $vars[] = 'nb_cat';
    return $vars;
}
add_filter('query_vars', 'notebook_nb_query_vars');

function notebook_flush_nb_rewrites() {
    notebook_add_nb_rewrite_rules();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'notebook_flush_nb_rewrites');

/**
 * 信纸页渲染入口（给 index.php 用）
 * @param string $view
 */
function notebook_render_nb_view($view) {
    $view = sanitize_key((string) $view);

    $directory_page = get_page_by_path('directory');
    $directory_link = $directory_page ? get_permalink($directory_page) : home_url('/');
    $cover_link = home_url('/');
    $cfg = function_exists('notebook_theme_config') ? notebook_theme_config() : [];
    $site_name = !empty($cfg['site_name']) ? $cfg['site_name'] : get_bloginfo('name');
    $icp_html = isset($cfg['icp_html']) ? notebook_sanitize_footer_html($cfg['icp_html'], true) : '';
    $wordpress_link_html = isset($cfg['wordpress_link_html']) ? notebook_sanitize_footer_html($cfg['wordpress_link_html']) : '';
    $notebook_link_html = isset($cfg['notebook_link_html']) ? notebook_sanitize_footer_html($cfg['notebook_link_html']) : '';
    $start_year = function_exists('notebook_get_first_post_year') ? notebook_get_first_post_year() : (int) date('Y');
    $end_year = (int) date('Y');
    $year_range = $start_year === $end_year ? (string) $start_year : ($start_year . '-' . $end_year);

    $title_map = [
        'category' => '分类目录',
        'archive'  => '文章归档',
        'links'    => '友情链接',
        'about'    => '关于我们',
        'next'     => '文章目录',
        'search'   => '搜索',
        'custom'   => '所有页面',
    ];

    $nb_cat_slug = '';
    $current_cat_term = null;
    $category_leaf_as_next = false;

    if ($view === 'category') {
        $nb_cat_slug = (string) get_query_var('nb_cat');
        if ($nb_cat_slug === '' && isset($_GET['nb_cat'])) {
            $nb_cat_slug = sanitize_title(wp_unslash($_GET['nb_cat']));
        }
        if ($nb_cat_slug !== '') {
            $current_cat_term = get_term_by('slug', $nb_cat_slug, 'category');
            if ($current_cat_term && !is_wp_error($current_cat_term)) {
                $child_cats = get_categories([
                    'taxonomy'   => 'category',
                    'parent'     => (int) $current_cat_term->term_id,
                    'hide_empty' => false,
                ]);
                $category_leaf_as_next = empty($child_cats);
            }
        }
    }

    $layout_is_next = ($view === 'next') || ($view === 'category' && $category_leaf_as_next);

    $directory_views = ['category', 'archive', 'links', 'search', 'custom'];
    $is_directory_view = in_array($view, $directory_views, true) && !$layout_is_next;
    $page_class = $is_directory_view ? 'notebook-page--directory' : 'notebook-page--content';
    if ($layout_is_next) {
        $page_class = 'notebook-page--next';
    }

    $top_title = isset($title_map[$view]) ? $title_map[$view] : '';
    if ($layout_is_next) {
        $top_title = $title_map['next'];
    }

    echo '<div class="content-area">';
    echo '<div class="notebook-page notebook-tabs notebook-page--view ' . esc_attr($page_class) . '">';
    echo '<div class="notebook-page-inner">';

    if ($top_title) {
        if ($is_directory_view) {
            echo '<section class="directory-lists nb-directory-lists">';
            echo '<h2 class="nb-directory-title">' . esc_html($top_title) . '</h2>';
        } else {
            echo '<header class="entry-header">';
            echo '<h1 class="entry-title" style="text-align:center;font-size:1.9rem;margin:0 0 0.6rem;">' . esc_html($top_title) . '</h1>';
            echo '</header>';
        }
    }

    if ($is_directory_view) {
        echo '<div class="nb-directory-body">';
    } else {
        echo '<div class="entry-content">';
    }

    switch ($view) {
        case 'category': {
            if ($nb_cat_slug !== '' && (!$current_cat_term || is_wp_error($current_cat_term))) {
                echo '<p>' . esc_html__('未找到该分类。', 'notebook') . '</p>';
                echo '<p><a href="' . esc_url(notebook_get_nb_category_directory_url()) . '">' . esc_html__('返回分类目录', 'notebook') . '</a></p>';
                break;
            }

            if ($category_leaf_as_next && $current_cat_term) {
                $posts = get_posts([
                    'post_type'      => 'post',
                    'post_status'    => 'publish',
                    'posts_per_page' => -1,
                    'orderby'        => 'date',
                    'order'          => 'DESC',
                    'category__in'   => [(int) $current_cat_term->term_id],
                ]);
                if (empty($posts)) {
                    echo '<p>' . esc_html__('该分类下暂无文章。', 'notebook') . '</p>';
                } else {
                    echo '<ul class="nb-list nb-list--lined">';
                    foreach ($posts as $p) {
                        echo '<li><a href="' . esc_url(get_permalink($p)) . '">' . esc_html(get_the_title($p)) . '</a></li>';
                    }
                    echo '</ul>';
                }
                break;
            }

            $current = $current_cat_term;

            if ($current) {
                echo '<nav class="nb-directory-nav" aria-label="' . esc_attr__('分类位置', 'notebook') . '">';
                echo '<a href="' . esc_url(notebook_get_nb_category_directory_url()) . '">' . esc_html__('分类目录', 'notebook') . '</a>';
                $ancestors = get_ancestors($current->term_id, 'category');
                $ancestors = array_reverse($ancestors);
                foreach ($ancestors as $aid) {
                    $t = get_term($aid, 'category');
                    if ($t && !is_wp_error($t)) {
                        $n = $t->name !== '' ? $t->name : $t->slug;
                        echo ' <span class="nb-directory-sep">/</span> ';
                        echo '<a href="' . esc_url(notebook_get_nb_category_directory_url($t->slug)) . '">' . esc_html($n) . '</a>';
                    }
                }
                $cn = $current->name !== '' ? $current->name : $current->slug;
                echo ' <span class="nb-directory-sep">/</span> ';
                echo '<span class="nb-directory-current">' . esc_html($cn) . '</span>';
                echo '</nav>';
            }

            $categories = [];
            if ($current) {
                $categories = get_categories([
                    'taxonomy'   => 'category',
                    'parent'     => (int) $current->term_id,
                    'hide_empty' => false,
                    'orderby'    => 'name',
                    'order'      => 'ASC',
                ]);
            } else {
                $categories = get_categories([
                    'hide_empty' => false,
                    'orderby'    => 'name',
                    'order'      => 'ASC',
                ]);
            }

            if (!empty($categories)) {
                echo '<ul class="nb-list nb-list--lined">';
                foreach ($categories as $cat) {
                    $name = $cat->name !== '' ? $cat->name : $cat->slug;
                    echo '<li><a href="' . esc_url(notebook_get_nb_category_directory_url($cat->slug)) . '">' . esc_html($name) . '</a></li>';
                }
                echo '</ul>';
                break;
            }

            echo '<p>' . esc_html__('暂无分类。', 'notebook') . '</p>';
            break;
        }

        case 'archive': {
            global $wpdb;
            $months = $wpdb->get_results("
                SELECT DISTINCT YEAR(post_date) AS y, MONTH(post_date) AS m
                FROM {$wpdb->posts}
                WHERE post_type = 'post' AND post_status = 'publish'
                ORDER BY y DESC, m DESC
            ");
            if (empty($months)) {
                echo '<p>' . esc_html__('暂无归档文章。', 'notebook') . '</p>';
                break;
            }
            echo '<div class="nb-tree nb-archive">';
            $current_year = 0;
            foreach ($months as $row) {
                $y = (int) $row->y;
                $m = (int) $row->m;
                if ($y !== $current_year) {
                    if ($current_year !== 0) {
                        echo '</div>';
                        echo '</details>';
                    }
                    $current_year = $y;
                    echo '<details class="nb-tree-item nb-tree-item--year" open="true">';
                    echo '<summary>' . esc_html($y . ' 年') . '</summary>';
                    echo '<div class="nb-tree nb-tree--year-months">';
                }

                $start = sprintf('%04d-%02d-01 00:00:00', $y, $m);
                $end = date('Y-m-t 23:59:59', strtotime($start));
                $posts = get_posts([
                    'posts_per_page' => -1,
                    'post_status'    => 'publish',
                    'orderby'        => 'date',
                    'order'          => 'DESC',
                    'date_query'     => [
                        ['after' => $start, 'before' => $end, 'inclusive' => true],
                    ],
                ]);

                echo '<details class="nb-tree-item" open="false">';
                echo '<summary>' . esc_html($y . ' 年 ' . $m . ' 月') . '</summary>';
                echo '<ul class="nb-list nb-list--lined">';
                if (empty($posts)) {
                    echo '<li>' . esc_html__('暂无文章', 'notebook') . '</li>';
                } else {
                    foreach ($posts as $p) {
                        $date_txt = get_the_date('m-d', $p);
                        echo '<li><span class="nb-date">' . esc_html($date_txt) . '</span> <a href="' . esc_url(get_permalink($p)) . '">' . esc_html(get_the_title($p)) . '</a></li>';
                    }
                }
                echo '</ul>';
                echo '</details>';
            }
            if ($current_year !== 0) {
                echo '</div>';
                echo '</details>';
            }
            echo '</div>';
            break;
        }

        case 'links': {
            if (!function_exists('get_bookmarks')) {
                echo '<p>' . esc_html__('友情链接功能不可用：当前环境没有链接管理器。', 'notebook') . '</p>';
                break;
            }
            $links = get_bookmarks(['orderby' => 'name', 'order' => 'ASC']);
            echo '<ul class="nb-list nb-list--lined nb-links-list">';
            if (empty($links)) {
                echo '<li>' . esc_html__('暂无友情链接。', 'notebook') . '</li>';
                break;
            }
            foreach ($links as $b) {
                $name = isset($b->link_name) ? $b->link_name : '';
                $url = isset($b->link_url) ? $b->link_url : '';
                $desc = isset($b->link_description) ? trim((string) $b->link_description) : '';
                $img = isset($b->link_image) ? trim((string) $b->link_image) : '';
                echo '<li>';
                if ($img !== '') {
                    echo '<img class="nb-link-logo" src="' . esc_url($img) . '" alt="' . esc_attr($name) . '">';
                }
                echo '<a href="' . esc_url($url) . '" target="_blank" rel="noopener">' . esc_html($name) . '</a>';
                if ($desc !== '') {
                    echo '<div class="nb-link-desc">' . esc_html($desc) . '</div>';
                }
                echo '<div class="nb-link-url">' . esc_html($url) . '</div>';
                echo '</li>';
            }
            echo '</ul>';
            break;
        }

        case 'about': {
            $about = get_page_by_path('about');
            if (!$about) {
                $about = get_page_by_path('guanyu');
            }
            if (!$about) {
                $about_page = get_page_by_title('关于');
                if ($about_page instanceof WP_Post) {
                    $about = $about_page;
                }
            }
            if (!$about) {
                echo '<p>' . esc_html__('暂无关于内容。', 'notebook') . '</p>';
                break;
            }
            $content = apply_filters('the_content', $about->post_content);
            echo '<div class="nb-html">' . $content . '</div>';
            break;
        }

        case 'search': {
            $keyword = isset($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : '';
            echo '<form class="nb-search-form" action="' . esc_url(home_url('/')) . '" method="get">';
            echo '<input type="hidden" name="nb_view" value="search">';
            echo '<input type="text" name="q" value="' . esc_attr($keyword) . '" placeholder="' . esc_attr__('输入关键词', 'notebook') . '">';
            echo '<button type="submit">' . esc_html__('搜索', 'notebook') . '</button>';
            echo '</form>';

            if ($keyword === '') {
                echo '<p>' . esc_html__('请输入关键词后搜索。', 'notebook') . '</p>';
                break;
            }

            $q = new WP_Query([
                'post_type'      => ['post', 'page'],
                'post_status'    => 'publish',
                's'              => $keyword,
                'posts_per_page' => 30,
            ]);

            echo '<ul class="nb-list nb-list--lined">';
            if (!$q->have_posts()) {
                echo '<li>' . esc_html__('没有找到匹配内容。', 'notebook') . '</li>';
            } else {
                while ($q->have_posts()) {
                    $q->the_post();
                    echo '<li><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></li>';
                }
            }
            echo '</ul>';
            wp_reset_postdata();
            break;
        }

        case 'next': {
            $posts = get_posts([
                'post_type'      => 'post',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'orderby'        => 'date',
                'order'          => 'DESC',
            ]);
            echo '<ul class="nb-list nb-list--lined">';
            if (empty($posts)) {
                echo '<li>' . esc_html__('暂无文章。', 'notebook') . '</li>';
            } else {
                foreach ($posts as $p) {
                    echo '<li><a href="' . esc_url(get_permalink($p)) . '">' . esc_html(get_the_title($p)) . '</a></li>';
                }
            }
            echo '</ul>';
            break;
        }

        case 'custom': {
            $exclude = ['about', 'directory'];
            $pages = get_pages(['post_status' => 'publish']);
            echo '<ul class="nb-list nb-list--lined">';
            if (empty($pages)) {
                echo '<li>' . esc_html__('暂无自定义页面。', 'notebook') . '</li>';
                break;
            }
            foreach ($pages as $p) {
                $slug = $p->post_name;
                if (in_array($slug, $exclude, true)) {
                    continue;
                }
                echo '<li><a href="' . esc_url(get_permalink($p)) . '">' . esc_html($p->post_title) . '</a></li>';
            }
            echo '</ul>';
            break;
        }

        default:
            echo '<p>' . esc_html__('内容不存在。', 'notebook') . '</p>';
            break;
    }

    if ($is_directory_view) {
        echo '</div>'; // nb-directory-body
        echo '</section>'; // directory-lists
    } else {
        echo '</div>'; // entry-content
    }

    // 底部导航：仅保留返回封面
    // 放在内容容器之外，避免被内容区宽度/位移规则影响位置
    $nav_class = 'nb-view-nav nb-view-nav--next';
    echo '<div class="' . esc_attr($nav_class) . '">';
    echo '<a href="' . esc_url($cover_link) . '">' . esc_html__('返回封面', 'notebook') . '</a>';
    echo '</div>';

    echo '<div class="notebook-view-footer">';
    echo '<div class="notebook-view-footer-line">';
    echo '© ' . esc_html($year_range) . ' <a href="' . esc_url(home_url('/')) . '">' . esc_html($site_name) . '</a>';
    echo '</div>';
    echo '<div class="notebook-view-footer-line">';
    echo $wordpress_link_html . ' & ' . $notebook_link_html;
    echo '</div>';
    if ($icp_html !== '') {
        echo '<div class="notebook-view-footer-line notebook-view-footer-icp">' . $icp_html . '</div>';
    }
    echo '</div>';

    echo '</div>'; // notebook-page-inner

    if (isset($title_map[$view])) {
        echo '<div class="notebook-tabs-list notebook-tabs-list--directory">';
        $tabs = notebook_get_sidebar_tabs();
        foreach ($tabs as $tab) {
            echo '<div class="notebook-tab-item">';
            if (!empty($tab['url'])) {
                echo '<a href="' . esc_url($tab['url']) . '">' . esc_html($tab['label']) . '</a>';
            } else {
                echo '<span>&nbsp;</span>';
            }
            echo '</div>';
        }
        echo '</div>';
    }

    echo '</div>'; // notebook-page
    echo '</div>'; // content-area
}

/**
 * 帖子里图片列表（扫描正文中的 <img>）
 */
function notebook_extract_media_from_content($content) {
    $media = [
        'images' => [],
    ];

    if (preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches)) {
        $media['images'] = array_unique($matches[1]);
    }

    return $media;
}

// 自定义管理员与游客头像（来自 twentyten4.3，使用自定义器上传）
function notebook_custom_avatar($avatar, $id_or_email, $size, $default, $alt) {
    $admin_avatar_url = get_theme_mod('notebook_admin_avatar');
    $guest_avatar_url = get_theme_mod('notebook_guest_avatar');

    $is_admin = false;
    $email = '';
    if (is_object($id_or_email)) {
        if (!empty($id_or_email->comment_author_email)) {
            $email = $id_or_email->comment_author_email;
        }
    } elseif (is_numeric($id_or_email)) {
        $user = get_user_by('id', (int)$id_or_email);
        if ($user) {
            $email = $user->user_email;
        }
    } elseif (is_string($id_or_email)) {
        $email = $id_or_email;
    }

    if ($email && is_email($email)) {
        $user = get_user_by('email', $email);
        if ($user && user_can($user, 'manage_options')) {
            $is_admin = true;
        }
    }

    if ($is_admin && $admin_avatar_url) {
        $avatar = "<img alt='" . esc_attr($alt) . "' src='" . esc_url($admin_avatar_url) . "' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
    } elseif (!$is_admin && $guest_avatar_url) {
        $avatar = "<img alt='" . esc_attr($alt) . "' src='" . esc_url($guest_avatar_url) . "' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
    }

    return $avatar;
}
add_filter('get_avatar', 'notebook_custom_avatar', 10, 5);

// 自定义后台选项（管理员/游客头像）
function notebook_customize_register($wp_customize) {
    $wp_customize->add_section('notebook_avatars', array(
        'title' => __('Notebook Avatars', 'notebook'),
        'priority' => 30,
    ));

    $wp_customize->add_setting('notebook_admin_avatar', array('default' => ''));
    $wp_customize->add_setting('notebook_guest_avatar', array('default' => ''));

    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'notebook_admin_avatar', array(
        'label' => __('Admin Avatar', 'notebook'),
        'section' => 'notebook_avatars',
        'settings' => 'notebook_admin_avatar',
    )));

    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'notebook_guest_avatar', array(
        'label' => __('Guest Avatar', 'notebook'),
        'section' => 'notebook_avatars',
        'settings' => 'notebook_guest_avatar',
    )));
}
add_action('customize_register', 'notebook_customize_register');

// 获取访客IP（来自 twentyten4.3，兼容 PHP 5.3+）
function notebook_get_ip() {
    $keys = array('HTTP_CLIENT_IP','HTTP_X_FORWARDED_FOR','REMOTE_ADDR');
    foreach ($keys as $k) {
        if (!empty($_SERVER[$k])) {
            $parts = explode(',', $_SERVER[$k]);
            return trim($parts[0]);
        }
    }
    return '0.0.0.0';
}

// 简单解析 User-Agent 获取系统和浏览器
function notebook_parse_user_agent($ua) {
    $os = 'Unknown OS';
    $browser = 'Unknown Browser';

    if (preg_match('/Windows NT/i', $ua)) $os = 'Windows';
    elseif (preg_match('/Android/i', $ua)) $os = 'Android';
    elseif (preg_match('/iPhone|iPad/i', $ua)) $os = 'iOS';
    elseif (preg_match('/Mac OS X/i', $ua)) $os = 'macOS';
    elseif (preg_match('/Linux/i', $ua)) $os = 'Linux';

    if (preg_match('/Edge\/|Edg\//i', $ua)) $browser = 'Edge';
    elseif (preg_match('/OPR\//i', $ua)) $browser = 'Opera';
    elseif (preg_match('/Chrome\//i', $ua)) $browser = 'Chrome';
    elseif (preg_match('/Safari\//i', $ua)) $browser = 'Safari';
    elseif (preg_match('/Firefox\//i', $ua)) $browser = 'Firefox';
    elseif (preg_match('/MSIE|Trident\//i', $ua)) $browser = 'IE';

    return array($os, $browser);
}

/**
 * 评论图片上传检测：限制大小 + 仅允许白名单图片格式 + 检查是否为“真实图片”
 * - 防止伪装成图片的非图片文件上传
 * - 也避免 SVG 等可被脚本利用的格式（这里不允许 svg）
 *
 * @param array $file $_FILES['comment_image']
 * @return true|WP_Error
 */
function notebook_comment_image_max_bytes() {
    return (int) apply_filters('notebook_comment_image_max_bytes', 500 * 1024); // 默认 500KB
}

function notebook_validate_comment_image_upload($file) {
    if (!is_array($file) || empty($file['tmp_name'])) {
        return new WP_Error('invalid_file', '无效上传文件。');
    }

    if (!isset($file['error']) || (int) $file['error'] !== UPLOAD_ERR_OK) {
        return new WP_Error('upload_error', '上传过程中发生错误。');
    }

    $max_bytes = notebook_comment_image_max_bytes();
    $max_width = (int) apply_filters('notebook_comment_image_max_width', 2000);
    $max_height = (int) apply_filters('notebook_comment_image_max_height', 2000);

    $size = isset($file['size']) ? (int) $file['size'] : 0;
    if ($size <= 0 || $size > $max_bytes) {
        return new WP_Error('size_limit', '图片大小超过限制。');
    }

    // 白名单：不包含 image/svg+xml
    $allowed_mimes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    // 通过 WordPress 检测扩展名/类型（通常会检查 MIME 与扩展）
    if (function_exists('wp_check_filetype_and_ext')) {
        $check = wp_check_filetype_and_ext($file['tmp_name'], isset($file['name']) ? $file['name'] : '');
        $mime = isset($check['type']) ? (string) $check['type'] : '';
        if ($mime !== '' && !in_array($mime, $allowed_mimes, true)) {
            return new WP_Error('mime_not_allowed', '不允许的图片格式。');
        }
    }

    // 关键：用 getimagesize 判断“真实图片”，伪装文件通常会失败
    $img_info = @getimagesize($file['tmp_name']);
    if ($img_info === false) {
        return new WP_Error('not_image', '上传内容不是有效图片。');
    }

    $w = isset($img_info[0]) ? (int) $img_info[0] : 0;
    $h = isset($img_info[1]) ? (int) $img_info[1] : 0;
    if ($w <= 0 || $h <= 0) {
        return new WP_Error('invalid_dimensions', '图片尺寸无效。');
    }
    if ($w > $max_width || $h > $max_height) {
        return new WP_Error('dimension_limit', '图片分辨率超过限制。');
    }

    // getimagesize 返回的 mime 在多数情况下可靠
    $img_mime = isset($img_info['mime']) ? (string) $img_info['mime'] : '';
    if ($img_mime !== '' && !in_array($img_mime, $allowed_mimes, true)) {
        return new WP_Error('mime_not_allowed', '不允许的图片格式。');
    }

    return true;
}

/**
 * 评论图片上传安全策略：
 * - 可选仅登录用户可上传（默认 false，可通过过滤器打开）
 * - 按 IP 限流（默认每小时 3 次，可通过过滤器调整）
 */
function notebook_comment_image_require_login() {
    return (bool) apply_filters('notebook_comment_image_require_login', false);
}

function notebook_comment_image_rate_limit_per_hour() {
    return (int) apply_filters('notebook_comment_image_rate_limit_per_hour', 3);
}

function notebook_comment_image_rate_limit_key($ip) {
    $ip = (string) $ip;
    if ($ip === '') {
        $ip = '0.0.0.0';
    }
    $hour_bucket = gmdate('YmdH');
    return 'nb_cimg_rate_' . md5($ip . '|' . $hour_bucket);
}

function notebook_comment_image_rate_limit_check_and_mark($ip) {
    $max = notebook_comment_image_rate_limit_per_hour();
    if ($max <= 0) {
        return true;
    }
    $key = notebook_comment_image_rate_limit_key($ip);
    $count = (int) get_transient($key);
    if ($count >= $max) {
        return new WP_Error('rate_limit', sprintf('上传过于频繁，请稍后再试（每小时最多 %d 次）。', $max));
    }
    set_transient($key, $count + 1, HOUR_IN_SECONDS + 120);
    return true;
}

// 评论提交前先校验图片（失败则给出明确提示并阻止提交）
function notebook_preprocess_comment_image_validation($commentdata) {
    if (!empty($_FILES['comment_image']['name'])) {
        if (notebook_comment_image_require_login() && !is_user_logged_in()) {
            wp_die(esc_html__('仅登录用户可上传评论图片。', 'notebook'), '评论图片上传失败', ['response' => 403, 'back_link' => true]);
        }

        $ip = notebook_get_ip();
        $rate_check = notebook_comment_image_rate_limit_check_and_mark($ip);
        if (is_wp_error($rate_check)) {
            wp_die(esc_html($rate_check->get_error_message()), '评论图片上传失败', ['response' => 429, 'back_link' => true]);
        }

        $validation = notebook_validate_comment_image_upload($_FILES['comment_image']);
        if (is_wp_error($validation)) {
            $msg = $validation->get_error_message();
            $limit_kb = (int) ceil(notebook_comment_image_max_bytes() / 1024);
            if ($validation->get_error_code() === 'size_limit') {
                $msg = sprintf('评论图片大小不能超过 %dKB。', $limit_kb);
            }
            wp_die(esc_html($msg), '评论图片上传失败', ['response' => 400, 'back_link' => true]);
        }
    }
    return $commentdata;
}
add_filter('preprocess_comment', 'notebook_preprocess_comment_image_validation');

/**
 * 评论内容：允许的 HTML（安全白名单）
 */
function notebook_comment_kses_allowed_html() {
    return array(
        'a'      => array(
            'href'   => true,
            'title'  => true,
            'rel'    => true,
            'target' => true,
        ),
        'br'     => array(),
        'p'      => array(),
        'strong' => array(),
        'em'     => array(),
        'blockquote' => array(),
        'img'    => array(
            'src'    => true,
            'alt'    => true,
            'class'  => true,
            'style'  => true,
            'loading' => true,
            'width'  => true,
            'height' => true,
        ),
    );
}

/**
 * 仅允许 http/https 的媒体直链（禁止 javascript:、data: 等）
 */
function notebook_comment_safe_media_url($url) {
    $url = trim((string) $url);
    if ($url === '') {
        return '';
    }
    $clean = esc_url_raw($url, array('http', 'https'));
    if ($clean === '' || strpos($clean, 'http') !== 0) {
        return '';
    }
    return $clean;
}

/**
 * 在纯文本中把图片直链替换为 img（不处理已有 HTML 标签内部）
 */
function notebook_comment_embed_media_urls_in_plain_text($text) {
    $image_ext = 'jpe?g|png|gif|webp|bmp';

    $build_img = function ($url) {
        $safe = notebook_comment_safe_media_url($url);
        if ($safe === '') {
            return esc_html($url);
        }
        return '<img src="' . esc_attr($safe) . '" alt="" loading="lazy" style="max-width:100%;height:auto;" />';
    };

    $text = preg_replace_callback(
        '#https?://[^\s<>"\']+\.(?:' . $image_ext . ')(?:\?[^\s<>"\']*)?#iu',
        function ($m) use ($build_img) {
            return $build_img($m[0]);
        },
        $text
    );

    return $text;
}

/**
 * 仅在「文本节点」中嵌入媒体，避免替换已有标签属性里的 URL
 */
function notebook_comment_process_html_text_nodes($html, $callback) {
    if ($html === '' || $html === null) {
        return '';
    }
    $parts = preg_split('~(<[^>]*>)~', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
    if ($parts === false) {
        return $html;
    }
    $out = '';
    foreach ($parts as $i => $part) {
        // 奇数段为标签，偶数段为文本
        if ($i % 2 === 0) {
            $out .= call_user_func($callback, $part);
        } else {
            $out .= $part;
        }
    }
    return $out;
}

/**
 * 评论内容：去危险标签 + 图片直链转 img + 最终 wp_kses
 */
function notebook_sanitize_comment_content($content) {
    $content = (string) $content;
    // 先去掉 script/style 等（wp_kses 白名单）
    $allowed = notebook_comment_kses_allowed_html();
    $content = wp_kses($content, $allowed);
    // 文本节点内：直链 → 媒体标签
    $content = notebook_comment_process_html_text_nodes($content, 'notebook_comment_embed_media_urls_in_plain_text');
    // 再次 kses，确保新插入的标签属性安全
    $content = wp_kses($content, $allowed);
    return $content;
}

function notebook_filter_pre_comment_content($content) {
    return notebook_sanitize_comment_content($content);
}
add_filter('pre_comment_content', 'notebook_filter_pre_comment_content', 9);

function notebook_filter_comment_text($content) {
    return notebook_sanitize_comment_content($content);
}
add_filter('comment_text', 'notebook_filter_comment_text', 5);

// 评论提交时保存 IP、UA、位置等到 comment_meta（来自 twentyten4.3）
function notebook_save_comment_meta($comment_id) {
    $ip = notebook_get_ip();
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

    list($os, $browser) = notebook_parse_user_agent($ua);

    add_comment_meta($comment_id, 'nb_ip', $ip, true);
    add_comment_meta($comment_id, 'nb_ua', $ua, true);
    add_comment_meta($comment_id, 'nb_os', $os, true);
    add_comment_meta($comment_id, 'nb_browser', $browser, true);

    // 可选：调用第三方API获取地理位置（示例：ip-api.com）
    $location = '';
    $response = wp_remote_get("http://ip-api.com/json/{$ip}?lang=zh-CN");
    if (!is_wp_error($response)) {
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if (!empty($data) && isset($data['country'])) {
            $location = $data['country'] . ' ' . $data['regionName'] . ' ' . $data['city'];
        }
    }
    if ($location) {
        add_comment_meta($comment_id, 'nb_location', $location, true);
    }

    // 评论图片上传（简单示例：一个 input name="comment_image"）
    if (!empty($_FILES['comment_image']['name'])) {
        // 先做真实图片检测：通过才允许进入 WP 上传流程
        $validation = notebook_validate_comment_image_upload($_FILES['comment_image']);
        if (is_wp_error($validation)) {
            // 不上传非法文件，避免伪装成图片的内容进入媒体库
            return;
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $uploaded = media_handle_upload('comment_image', 0);
        if (!is_wp_error($uploaded)) {
                $url = wp_get_attachment_url($uploaded);
                add_comment_meta($comment_id, 'nb_comment_image_id', (int) $uploaded, true);
                if ($url) {
                    add_comment_meta($comment_id, 'nb_comment_image', esc_url_raw($url), true);
                }
        }
    }
}
add_action('comment_post', 'notebook_save_comment_meta', 10, 1);

/**
 * 评论删除/拉黑/回收时，清理对应评论图片附件，防止占用空间。
 */
function notebook_delete_comment_image_attachment($comment_id) {
    $attachment_id = (int) get_comment_meta($comment_id, 'nb_comment_image_id', true);
    if ($attachment_id > 0) {
        wp_delete_attachment($attachment_id, true);
    }
    delete_comment_meta($comment_id, 'nb_comment_image_id');
    delete_comment_meta($comment_id, 'nb_comment_image');
}
add_action('trashed_comment', 'notebook_delete_comment_image_attachment');
add_action('spam_comment', 'notebook_delete_comment_image_attachment');
add_action('deleted_comment', 'notebook_delete_comment_image_attachment');

/**
 * 定时清理：删除“待审核超过 N 天”的评论图片附件，防止长期占用空间。
 */
function notebook_comment_image_pending_cleanup_days() {
    return (int) apply_filters('notebook_comment_image_pending_cleanup_days', 7);
}

function notebook_comment_image_cleanup_event_name() {
    return 'notebook_daily_comment_image_cleanup';
}

function notebook_schedule_comment_image_cleanup() {
    $event = notebook_comment_image_cleanup_event_name();
    if (!wp_next_scheduled($event)) {
        wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', $event);
    }
}
add_action('init', 'notebook_schedule_comment_image_cleanup');

function notebook_unschedule_comment_image_cleanup() {
    $event = notebook_comment_image_cleanup_event_name();
    $ts = wp_next_scheduled($event);
    if ($ts) {
        wp_unschedule_event($ts, $event);
    }
}
add_action('switch_theme', 'notebook_unschedule_comment_image_cleanup');

function notebook_cleanup_old_pending_comment_images() {
    $days = notebook_comment_image_pending_cleanup_days();
    if ($days <= 0) {
        return;
    }
    $before = gmdate('Y-m-d H:i:s', time() - ($days * DAY_IN_SECONDS));
    $comments = get_comments([
        'status'       => 'hold',
        'number'       => 1000,
        'date_query'   => [
            [
                'column' => 'comment_date_gmt',
                'before' => $before,
            ],
        ],
        'fields'       => 'ids',
        'no_found_rows' => true,
    ]);
    if (empty($comments)) {
        return;
    }
    foreach ($comments as $comment_id) {
        notebook_delete_comment_image_attachment((int) $comment_id);
    }
}
add_action('notebook_daily_comment_image_cleanup', 'notebook_cleanup_old_pending_comment_images');

// 让评论表单支持图片上传
function notebook_comment_form_fields($fields) {
    $limit_kb = (int) ceil(notebook_comment_image_max_bytes() / 1024);
    ob_start();
    ?>
    <p class="comment-form-image">
        <label for="comment_image"><?php echo esc_html(sprintf('评论图片（可选，最大 %dKB）', $limit_kb)); ?></label>
        <input type="file" name="comment_image" id="comment_image" accept="image/jpeg,image/png,image/gif,image/webp" />
    </p>
    <?php
    echo ob_get_clean();
    return $fields;
}
add_action('comment_form_after_fields', 'notebook_comment_form_fields');
add_action('comment_form_logged_in_after', 'notebook_comment_form_fields');

// 评论图片上传需要 multipart/form-data
function notebook_comment_form_enable_multipart() {
    $limit_bytes = notebook_comment_image_max_bytes();
    $limit_kb = (int) ceil($limit_bytes / 1024);
    ?>
    <script>
        (function () {
            var form = document.getElementById('commentform');
            var fileInput = document.getElementById('comment_image');
            var maxBytes = <?php echo (int) $limit_bytes; ?>;
            var maxKb = <?php echo (int) $limit_kb; ?>;

            function checkImageSize() {
                if (!fileInput || !fileInput.files || !fileInput.files.length) {
                    return true;
                }
                var file = fileInput.files[0];
                if (file.size > maxBytes) {
                    alert('评论图片大小不能超过 ' + maxKb + 'KB，请重新选择。');
                    fileInput.value = '';
                    return false;
                }
                return true;
            }

            if (form) {
                form.setAttribute('enctype', 'multipart/form-data');
                form.addEventListener('submit', function (e) {
                    if (!checkImageSize()) {
                        e.preventDefault();
                    }
                });
            }

            if (fileInput) {
                fileInput.addEventListener('change', checkImageSize);
            }
        })();
    </script>
    <?php
}
add_action('comment_form_after', 'notebook_comment_form_enable_multipart');

/**
 * 访客信息封装：IP、位置、系统、浏览器
 * 复用 twentyten4.3 中的 IP 和 User-Agent 解析 + ip-api 位置查询
 */
function notebook_get_visitor_info() {
    $ip = notebook_get_ip();
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

    list($os, $browser) = notebook_parse_user_agent($ua);

    $location = '';
    if ($ip && $ip !== '0.0.0.0') {
        $response = wp_remote_get("http://ip-api.com/json/{$ip}?lang=zh-CN");
        if (!is_wp_error($response)) {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            if (!empty($data) && isset($data['country'])) {
                $location = $data['country'] . ' ' . $data['regionName'] . ' ' . $data['city'];
            }
        }
    }

    return [
        'ip'       => $ip,
        'ua'       => $ua,
        'os'       => $os,
        'browser'  => $browser,
        'location' => $location,
    ];
}

/**
 * 在侧边栏输出当前访客信息（使用 twentyten4.3 的获取方式）
 */
function notebook_visitor_widget_output() {
    $info = notebook_get_visitor_info();
    ?>
    <section class="widget widget_notebook_visitor">
        <h2 class="widget-title"><?php esc_html_e('你的访问信息', 'notebook'); ?></h2>
        <ul class="visitor-info-list">
            <li><?php echo 'IP：' . esc_html($info['ip']); ?></li>
            <li><?php echo '位置：' . ($info['location'] ? esc_html($info['location']) : '未知'); ?></li>
            <li><?php echo '系统：' . esc_html($info['os']); ?></li>
            <li><?php echo '浏览器：' . esc_html($info['browser']); ?></li>
        </ul>
    </section>
    <?php
}

/**
 * 给已有标签追加 class（不覆盖已有 class）
 */
function notebook_add_class_to_html_tag($html, $tag, $class_name) {
    $pattern = '/<' . preg_quote($tag, '/') . '\b([^>]*)>/i';
    return preg_replace_callback($pattern, function ($m) use ($tag, $class_name) {
        $attrs = $m[1];
        $to_add = preg_split('/\s+/', trim((string) $class_name));
        $to_add = array_values(array_filter((array) $to_add));
        if (preg_match('/\bclass\s*=\s*([\'"])(.*?)\1/i', $attrs, $cm)) {
            $quote = $cm[1];
            $classes = trim($cm[2]);
            $existing = preg_split('/\s+/', $classes);
            $existing = array_values(array_filter((array) $existing));
            foreach ($to_add as $c) {
                if (!in_array($c, $existing, true)) {
                    $existing[] = $c;
                }
            }
            $new_classes = implode(' ', $existing);
            $attrs = preg_replace('/\bclass\s*=\s*([\'"])(.*?)\1/i', 'class=' . $quote . $new_classes . $quote, $attrs, 1);
        } else {
            $attrs .= ' class="' . esc_attr(implode(' ', $to_add)) . '"';
        }
        return '<' . $tag . $attrs . '>';
    }, $html);
}

/**
 * 自动识别正文中的媒体与代码块：
 * - 裸链接图片/视频/音频 -> 自动补标签
 * - 统一给 img/video/audio 添加固定尺寸样式类
 * - ```code``` -> <pre><code>...</code></pre>
 */
function notebook_auto_format_post_content($content) {
    if (is_admin()) {
        return $content;
    }

    // 1) Markdown 样式代码块：```lang ... ```
    $content = preg_replace_callback('/```([a-zA-Z0-9_-]+)?\s*(.*?)```/is', function ($m) {
        $lang = isset($m[1]) ? trim((string) $m[1]) : '';
        $code = isset($m[2]) ? trim((string) $m[2]) : '';
        if ($code === '') {
            return '';
        }
        $lang_attr = $lang !== '' ? ' class="language-' . esc_attr($lang) . '"' : '';
        return '<pre class="notebook-code-block"><code' . $lang_attr . '>' . esc_html($code) . '</code></pre>';
    }, $content);

    // 2) 裸链接自动识别为媒体标签（逐行）
    $lines = preg_split("/\r\n|\n|\r/", (string) $content);
    if (is_array($lines)) {
        foreach ($lines as &$line) {
            $trim = trim($line);
            if ($trim === '' || preg_match('/<\s*(img|video|audio|pre|code)\b/i', $trim)) {
                continue;
            }
            if (!preg_match('/^https?:\/\/\S+$/i', $trim)) {
                continue;
            }
            if (preg_match('/\.(jpg|jpeg|png|gif|webp|bmp|svg)(\?.*)?$/i', $trim)) {
                $url = esc_url($trim);
                $line = '<img class="notebook-media notebook-media--image" src="' . $url . '" alt="" loading="lazy" decoding="async">';
                continue;
            }
            if (preg_match('/\.(mp4|webm|ogg|mov|m4v)(\?.*)?$/i', $trim)) {
                $url = esc_url($trim);
                $line = '<video class="notebook-media notebook-media--video" controls preload="metadata"><source src="' . $url . '"></video>';
                continue;
            }
            if (preg_match('/\.(mp3|wav|ogg|m4a|aac|flac)(\?.*)?$/i', $trim)) {
                $url = esc_url($trim);
                $line = '<audio class="notebook-media notebook-media--audio" controls preload="metadata"><source src="' . $url . '"></audio>';
                continue;
            }
        }
        unset($line);
        $content = implode("\n", $lines);
    }

    // 2.1) 兼容 wpautop 后的段落形式：<p>https://xx/aa.webp</p>
    $content = preg_replace_callback('/<p>\s*(https?:\/\/[^\s<]+?\.(?:jpg|jpeg|png|gif|webp|bmp|svg)(?:\?[^\s<]*)?)\s*<\/p>/i', function ($m) {
        $url = esc_url($m[1]);
        return '<img class="notebook-media notebook-media--image" src="' . $url . '" alt="" loading="lazy" decoding="async">';
    }, $content);
    $content = preg_replace_callback('/<p>\s*(https?:\/\/[^\s<]+?\.(?:mp4|webm|ogg|mov|m4v)(?:\?[^\s<]*)?)\s*<\/p>/i', function ($m) {
        $url = esc_url($m[1]);
        return '<video class="notebook-media notebook-media--video" controls preload="metadata"><source src="' . $url . '"></video>';
    }, $content);
    $content = preg_replace_callback('/<p>\s*(https?:\/\/[^\s<]+?\.(?:mp3|wav|ogg|m4a|aac|flac)(?:\?[^\s<]*)?)\s*<\/p>/i', function ($m) {
        $url = esc_url($m[1]);
        return '<audio class="notebook-media notebook-media--audio" controls preload="metadata"><source src="' . $url . '"></audio>';
    }, $content);

    // 3) 已有媒体标签补 class 与 controls
    $content = notebook_add_class_to_html_tag($content, 'img', 'notebook-media notebook-media--image');
    $content = notebook_add_class_to_html_tag($content, 'video', 'notebook-media notebook-media--video');
    $content = notebook_add_class_to_html_tag($content, 'audio', 'notebook-media notebook-media--audio');

    $content = preg_replace('/<video\b(?![^>]*\bcontrols\b)([^>]*)>/i', '<video controls$1>', $content);
    $content = preg_replace('/<audio\b(?![^>]*\bcontrols\b)([^>]*)>/i', '<audio controls$1>', $content);

    return $content;
}
add_filter('the_content', 'notebook_auto_format_post_content', 99);
