<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<aside class="sidebar-area">
    <?php
    notebook_visitor_widget_output();

    if (is_active_sidebar('sidebar-1')) {
        dynamic_sidebar('sidebar-1');
    }
    ?>
</aside>

