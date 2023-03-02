<?php get_header();

while (have_posts()) {
    the_post();
    pageBanner();
?>

    <div class="container container--narrow page-section">
        <div class="metabox metabox--position-up metabox--with-home-link">
            <p>
                <a class="metabox__blog-home-link" href="<?php echo get_post_type_archive_link('campus') ?>"><i class="fa fa-home" aria-hidden="true"></i>All Campuses</a> <span class="metabox__main"><?php the_title() ?></span>
            </p>
        </div>
        <div class="generic-content">
            <p><?php the_content(); ?></p>
        </div>

        <?php
        $relatedProgram = new WP_Query(array(
            'posts_per_page' => -1,
            'post_type' => 'program',
            'orderby' => 'title',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key' => 'related_campus',
                    'compare' => 'LIKE',
                    'value' => '"' . get_the_ID() . '"'
                )
            )
        ));

        if ($relatedProgram->have_posts()) {
            echo '<hr class="section-break">';
            echo '<h2 class="headline headline-medium">Programs available at this campus.</h2></br>';

            echo '<ul class="min-list link-list">';
            while ($relatedProgram->have_posts()) {
                $relatedProgram->the_post(); ?>
                <li>
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </li>
            <?php };

            wp_reset_postdata();

            echo '</ul>';

            $today = date('Ymd');
            $relatedEvents = new WP_Query(array(
                'post_per_page' => -1,
                'post_type' => 'event',
                'meta_key' => 'event_date',
                'orderby' => 'meta_value_num',
                'order' => 'ASC',
                'meta_query' => array(
                    array(
                        'key' => 'event_date',
                        'compare' => '>=',
                        'value' => $today,
                        'type' => 'numeric'
                    ),
                    array(
                        'key' => 'related_campus',
                        'compare' => 'LIKE',
                        'value' => '"' . get_the_ID() . '"'
                    )
                )
            ));

            if ($relatedEvents->have_posts()) {;
                $relatedEvents->the_post();
            ?>
                <hr class="section-break">
                <h2 class="headline headline-medium">Events Coming on at this campus: </h2>
                </br>
        <?php }
            get_template_part('/template-parts/content', 'event');
        }
        wp_reset_postdata();

        $today = date('Ymd');
        $homePageEvents = new WP_Query(array(
            'posts_per_page' => 2,
            'post_type' => 'event',
            'meta_key' => 'event_date',
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key' => 'event_date',
                    'compare' => '>=',
                    'value' => $today,
                    'type' => 'numeric'
                ),
                array(
                    'key' => 'related_programs',
                    'compare' => 'LIKE',
                    'value' => '"' . get_the_ID() . '"'
                )
            )
        ));

        if ($homePageEvents->have_posts()) {
            echo '<hr class="section-break">';
            echo '<h2 class="headline headline-medium">Upcoming ' . get_the_title() . ' Events</h2></br>';

            while ($homePageEvents->have_posts()) {
                $homePageEvents->the_post();
                get_template_part('/template-content', 'event');
            };
        }
        ?>
        <?php

        $mapLocation = get_field('google_map');
        ?>
        <div class="acf-map">
            <div class="marker" data-lat="<?php echo $mapLocation['lat']  ?>" data-lng="<?php echo $mapLocation['lng'] ?>">
                <h3><?php the_title() ?></h3>
                <?php echo $mapLocation['address'] ?>
            </div>
        </div>
    </div>
<?php };
get_footer();
?>