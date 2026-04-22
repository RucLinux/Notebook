        </main>
    </div><!-- .site-main-wrapper -->

<footer class="site-footer<?php echo is_front_page() ? ' site-footer--cover-home' : ''; ?>">
    <?php if (!is_front_page()) : ?>
    <div class="footer-inner">
        <?php
        $cfg = function_exists('notebook_theme_config') ? notebook_theme_config() : [];
        $site_name = !empty($cfg['site_name']) ? $cfg['site_name'] : get_bloginfo('name');
        $icp_html = isset($cfg['icp_html']) ? notebook_sanitize_footer_html($cfg['icp_html'], true) : '';
        $wordpress_link_html = isset($cfg['wordpress_link_html']) ? notebook_sanitize_footer_html($cfg['wordpress_link_html']) : '';
        $notebook_link_html = isset($cfg['notebook_link_html']) ? notebook_sanitize_footer_html($cfg['notebook_link_html']) : '';

        $start_year = function_exists('notebook_get_first_post_year') ? notebook_get_first_post_year() : (int) date('Y');
        $end_year = (int) date('Y');
        $year_range = $start_year === $end_year ? (string) $start_year : ($start_year . '-' . $end_year);
        ?>

        <div class="footer-credit">
            <div class="footer-line footer-line--primary">
                <?php echo '© ' . esc_html($year_range) . ' '; ?>
                <a href="<?php echo esc_url(home_url('/')); ?>"><?php echo esc_html($site_name); ?></a>
            </div>
            <div class="footer-line">
                <?php
                echo $wordpress_link_html;
                echo ' & ';
                echo $notebook_link_html;
                ?>
            </div>
            <?php if ($icp_html !== '') : ?>
                <div class="footer-line footer-icp">
                    <?php echo $icp_html; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php wp_footer(); ?>
</footer>
</div><!-- .site-wrapper -->
</body>
</html>