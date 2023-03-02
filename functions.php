<?php

require get_theme_file_path('/inc/search-route.php');

function university_custom_rest()
{
    register_rest_field('post', 'author_name', array(
        'get_call_back' => function () {
            return get_the_author();
        },
    ));
    register_rest_field('note', 'userNoteCount', array(
        'get_call_back' => function () {
            return count_user_posts(get_current_user_id(), 'note');
        },
    ));
}

add_action('rest_api_init', 'university_custom_rest');

function pageBanner($args = NULL)
{

    if (!isset($args['title'])) {
        $args['title'] = get_the_title();
    }

    if (!isset($args['subtitle'])) {
        $args['subtitle'] = get_field('page_banner_subtitle');
    }

    if (!isset($args['photo'])) {
        if (get_field('page_banner_image') and !is_archive() and !is_home()) {
            $args['photo'] = get_field('page_banner_image')['sizes']['pageBanner'];
        } else {
            $args['photo'] = get_theme_file_uri('/images/ocean.jpg');
        }
    }
?>
    <div class="page-banner">
        <div class="page-banner__bg-image" style="background-image: url(<?php
                                                                        echo $args['photo'];
                                                                        ?>)"></div>
        <div class="page-banner__content container container--narrow">
            <h1 class="page-banner__title"><?php echo $args['title'] ?></h1>
            <div class="page-banner__intro">
                <p><?php echo $args['subtitle'] ?></p>
            </div>
        </div>
    </div>
<?php };

function university_files()
{
    wp_enqueue_script('google_map', '//maps.googleapis.com/maps/api/js?key=AIzaSyBbK4p3MjLS3r8DN4g5vur9Ig9kSGw1Tjo', NULL, 1.0, true);
    wp_enqueue_script('main_university_js', get_theme_file_uri('/build/index.js'), array('jquery'), filemtime(get_theme_file_uri('/build/index.js')), true);
    wp_enqueue_style('font_awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
    wp_enqueue_style('custom_font', '//fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i');
    wp_enqueue_style('university_main_styles', get_theme_file_uri('/build/style-index.css'));
    wp_enqueue_style('university_extra_styles', get_theme_file_uri('/build/index.css'));

    wp_localize_script('main_university_js', 'universityData', array(
        "root_url" => get_site_url(),
        'nonce' => wp_create_nonce('wp_rest')
    ));
};

add_action('wp_enqueue_scripts', 'university_files');

function university_features()
{
    /*
    Dynamic menus registrations
    register_nav_menu('headerMenuLocation', "Header Menu Location");
    register_nav_menu('footerLocationOne', "Footer Menu Location One");
    register_nav_menu('footerLocationTwo', "Footer Menu Location Two");
    */
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_image_size('professorLandscape', 400, 260, true);
    add_image_size('professorPotrait', 480, 650, true);
    add_image_size('pageBanner', 1500, 350, true);
}

add_action('after_setup_theme', 'university_features');

function university_adjust_queries($query)
{
    $today = date('Ymd');
    if (!is_admin() and is_post_type_archive('event') and $query->is_main_query()) {
        $query->set('meta_key', 'event_date');
        $query->set('orderby', 'meta_value_num');
        $query->set('order', 'ASC');
        $query->set('meta_query', array(
            array(
                'key' => 'event_date',
                'compare' => '>=',
                'value' => $today,
                'type' => 'numeric'
            )
        ));
    }

    if (!is_admin() and is_post_type_archive('program') and $query->is_main_query()) {
        $query->set('posts_per_page', -1);
        $query->set('orderby', 'title');
        $query->set('order', 'ASC');
    }
    if (!is_admin() and is_post_type_archive('campus') and $query->is_main_query()) {
        $query->set('posts_per_page', -1);
    }
};

add_action('pre_get_posts', 'university_adjust_queries');

function universityMapKey($api)
{
    $api['key'] = 'AIzaSyBbK4p3MjLS3r8DN4g5vur9Ig9kSGw1Tjo';
    return $api;
}

add_filter('acf/fields/google_map/api', 'universityMapKey');

//Redirect subscriber accounts to main page after login

add_action('admin_init', 'redirectUsers');

function redirectUsers()
{
    $current_user = wp_get_current_user();
    if (count($current_user->roles) == 1 and $current_user->roles[0] == 'subscriber') {
        wp_redirect(site_url('/'));
        exit;
    }
}

//Hide admin bar on for subscriber accounts

add_action('wp_loaded', 'hideAdminBar');

function hideAdminBar()
{
    $current_user = wp_get_current_user();
    if (count($current_user->roles) == 1 and $current_user->roles[0] == 'subscriber') {
        show_admin_bar(false);
    }
}

//Customized Wordpress Login page

add_filter('login_headerurl', 'headerURL');

function headerURL()
{
    return esc_url(site_url('/'));
}

add_action('login_enqueue_scripts', 'loginCSS');

function loginCSS()
{
    wp_enqueue_style('font_awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
    wp_enqueue_style('custom_font', '//fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i');
    wp_enqueue_style('university_main_styles', get_theme_file_uri('/build/style-index.css'));
    wp_enqueue_style('university_extra_styles', get_theme_file_uri('/build/index.css'));
}

add_filter('login_headertitle', 'loginTitle');

function loginTitle()
{
    return get_bloginfo('name');
}

//Force note posts to be private

add_filter('wp_insert_post_data', 'makeNotePrivate', 10, 2);

function makeNotePrivate($data, $postarr)
{
    if ($data['post_type'] == 'note') {

        if(count_user_posts(get_current_user_id()) > 4 AND !$postarr['ID']) {
            die('You have reached your note limit!');
        } 

        $data['post_content'] = sanitize_textarea_field($data['post_content']);
        $data['post_title'] = sanitize_textarea_field($data['post_title']);
    }
    if ($data['post_type'] == 'note' and $data['post_status'] != 'trash') {
        $data['post_status'] = 'private';
    }

    return $data;
}
