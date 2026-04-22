<?php
get_header();
?>

<div class="content-area">
    <div class="notebook-page notebook-page--content">
        <div class="notebook-page-inner">
            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                <article <?php post_class(); ?>>
                    <header class="entry-header">
                        <h1 class="entry-title"><?php the_title(); ?></h1>
                    </header>
                    <div class="entry-content">
                        <?php the_content(); ?>
                    </div>
                    <?php comments_template(); ?>
                </article>
            <?php endwhile; endif; ?>
        </div>
    </div>
</div>

<?php get_sidebar(); ?>

<?php
get_footer();