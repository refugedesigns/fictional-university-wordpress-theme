<?php

add_action('rest_api_init', 'universityLikeRoutes');

function universityLikeRoutes() {
    register_rest_route('university/v1', '/manageLike', array(
        array(
           'methods' => 'POST',
            'callback' => 'create_like',
        ),
        array(
           'methods' => 'DELETE',
            'callback' => 'delete_like',
        )
    ));
}

function create_like($data) {

    if(is_user_logged_in()) {
        $professorId = sanitize_text_field($data['professorId']);
    
    $professor = new WP_Query(array(
        'post_type' => 'professor',
        'post_status' => 'published',
        'post_id' => $professorId,
    ));

    $currentUser = ucfirst(strtolower(get_current_user()));

    $existQuery = new WP_Query(array(
                    'post_type' => 'like',
                    'author' => get_current_user_id(),
                    'meta_query' => array(
                        array(
                            'key' => 'liked_professor_id',
                            'compare' => '=',
                            'value' => $professorId,
                        )
                    )
                ));

    if($existQuery->found_posts == 0 AND get_post_type($professorId) == 'professor') {
    return wp_insert_post(array(
            'post_type' => 'like',
            'post_status' => 'publish',
            'post_title' =>  $currentUser . ' liked ' . $professor->post->post_title,
            'meta_input' => array(
                'liked_professor_id' => $professorId
            )
        ));
    } else {
        die("Invalid professor id.");
    }

    }else {
        die("Only logged in users can create a like");
    }  
}

function delete_like($data) {
    $likeId = sanitize_text_field($data['like']);

    if(get_current_user_id() == get_post_field('post_author', $likeId) AND get_post_type($likeId) == "like") {
       wp_delete_post($likeId, true);
       return 'Congrats, like deleted!';
    } else {
        die('You do not have permission to delete this like!');
    }
}
