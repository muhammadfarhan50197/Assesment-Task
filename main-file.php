<?php
/**
*Plugin Name: Assessment Task
*Description: Create a Plugin given from IKONIC as a Assesment Task
*Version: 1.0.0
*Author: Muhammad Farhan
*License: GPL2
*Text Domain: assessment-task
*Project Prefix : at_
*/
//Project Prefix is used for making names of functions unique

add_action('init', 'at_create_custom_posttypes'); //Create Custom Post Type of name "Projects" and post type "at_projects"
add_action('wp_head', 'at_redirect_user_outside_on_specfic_ip'); // If anyone whom ip start with "77.29" redirects toward www.google.com also run my small jquery 
add_shortcode('at_show_quotes', 'at_show_quotes_callback'); //Create Shortcode for displaying quotes from quotes api just place it in any page and read random 5 quotes 
add_shortcode('at_coffee_cup', 'hs_give_me_coffee'); //Create Shortcode for displaying Random Coffee image and some detail from api
add_shortcode('at_show_projects', 'at_show_projects_callback'); //Create Shortcode for displaying Projects
add_shortcode('at_create_github', 'at_create_github_callback');
add_action('rest_api_init', function() { //Create a json api which displays number of posts on basis of user login and also of its project-type
    $GLOBALS['user_id'] = get_current_user_id(); //for getting user id in its callback function where we find user is login or not
    register_rest_route('at', 'projects', [
        'methods' => 'GET',
        'callback' => 'at_all_projects_on_login_basis',
    ]);
});

function at_create_custom_posttypes() {
    $labels = array(
        'name' => __( 'Project Type', 'taxonomy general name' ),
        'singular_name' => __( 'Project Type', 'taxonomy singular name' ),
        'search_items' =>  __( 'Search Project Types' ),
        'all_items' => __( 'All Project Types' ),
        'parent_item' => __( 'Parent Project Type' ),
        'parent_item_colon' => __( 'Parent Project Type:' ),
        'edit_item' => __( 'Edit Project Type' ), 
        'update_item' => __( 'Update Project Type' ),
        'add_new_item' => __( 'Add New Project Type' ),
        'new_item_name' => __( 'New Project Type Name' ),
        'menu_name' => __( 'Project Type' ),
    );    

    register_taxonomy('project-type', array('at_projects'), array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_in_rest' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array( 'slug' => 'project-types' ),
    ));


    $project_defination_array = array(
        'name' => __('Projects', 'Post Type General Name', 'assessment-task'),
        'singular_name' => __('Projects', 'Post Type Singular Name', 'assessment-task'),
        'menu_name' => __('Projects', 'assessment-task'),
        'name_admin_bar' => __('Admin Name', 'assessment-task'),
        'archives' => __('Archives', 'assessment-task'),
        'all_items' => __('All Projects', 'assessment-task')
    );
    register_post_type('at_projects', array(
        'label' => __('Custom Post','assessment-task'),
        'description' => __('This is Testing Plugin','assessment-task'),
        'labels' => $project_defination_array,
        'menu_icon' => 'dashicons-database',
        'supports' => array( 'title', 'editor', 'thumbnail' ),
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_admin_bar'   => true,
        'show_in_nav_menus'   => false,
        'can_export'          => true,
        'has_archive'         => true,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'post',
        'show_in_rest'        => true
    ));
}

function at_redirect_user_outside_on_specfic_ip() {
    $ip = $_SERVER["REMOTE_ADDR"]; //fetch ip address 
    $ip_breakdown = explode(".",$ip); //breaks the ip address into array 
    if (intval($ip_breakdown[0]) == 77 && intval($ip_breakdown[1]) == 29) {
        wp_redirect('https://www.google.com/');
        exit;
    }
    ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.2/jquery.min.js"></script>
<script>
jQuery(document).ready(function() {
    jQuery('.pagination').click(() => {
        jQuery('.rows').addClass('dispplaynone');
        var start = jQuery(this).attr('start');
        var end = start + 6;
        for (var i = start; i <= end; i++) {
            jQuery('#row_' + i + '').removeClass('dispplaynone');
        }
    });
});
</script>
<?php
}
function at_all_projects_on_login_basis() {
    global $wpdb;
    if ($GLOBALS['user_id'] > 0) { // user is logged in
        $post_numbers = 6;
    }
    if ($GLOBALS['user_id'] == 0) { // user is not logged in
        $post_numbers = 3;
    }
    
    $define_array = array(
        'post_type' => 'at_projects',
        'numberposts' => $post_numbers,
        'post_status' => 'Publish',
        'orderby'   => 'ID',
        'order' => 'DESC',
        'tax_query' => array(
            array(
                'taxonomy' => 'project-type',
                'field' => 'name',
                'terms' => 'Architecture',
            )
        )
    );
    $getting_projects = get_posts($define_array);
    $data = [];
    $i = 0;
    foreach($getting_projects as $projects) {
        $permalink = get_permalink($projects->ID);
        $data[$i]['id'] = $projects->ID;
        $data[$i]['title'] = $projects->post_title;
        $data[$i]['link'] = $permalink;
        $i++;
    }
    return $data;
}

function at_show_quotes_callback() {
    $quotes = '<center><div><h2>Quotes</h2>';
    for($i=1; $i<6; $i++) { //showing 5 quotes
        $response = wp_remote_get('https://api.kanye.rest/'); // hit api of quotes 
        $response = json_decode($response['body'], true); // convert json into array 
        $quotes .= '<p>' . $response['quote'] . '</p>'; //show quotes  
    }
    $quotes .= '</div></center>';
    return $quotes;
}

function at_show_projects_callback() {
    global $wp;
    $currenturl = home_url( $wp->request ); // for current url 
    $fetching_project = get_posts(array(
        'post_type' => 'at_projects',
        'post_status' => 'publish',
        'posts_per_page' => -1,
    ));
    
    $total_pages = ceil(sizeof($fetching_project)/6); //find number of pages for pagination
    $table_output = '<style>.dispplaynone {
        display: none!important;
    }</style>';
    $table_output .= '<div><h2 class="text-align:center">Projects</h2><table style="width:100%;border:1px solid black;"><thead><tr><th>ID</th><th>Title</th><th>Link</th></tr></thead>';
    $count = 0;
    foreach ($fetching_project as $project) {
        $count++;
        if ($count > 6) {
            $class = 'dispplaynone';
        }
        $table_output .= '<tr class="rows '. $class.'" id="row_'.$count.'" style="text-align:center;">';
        $table_output .= '<td>'. $project->ID. '</td><td>'. $project->post_title. '</td><td><a href="'. get_permalink($project->ID).'">Permalink</a></td>';
        $table_output .= '</tr>';
    }
    $table_output .= '</table>';
    $pages = '<center><div>';
    for($i=0; $i < $total_pages; $i++) {
        $next = $i+1;
        if ($i == 0) {
            $start = 1;
        } else {
            $start=$i*6;
        }
        $pages .= '<button class="pagination" start="'.$start.'">'.$next.'</button>';
    }
    $pages .= '</div></center>';
    

    $table_output .= $pages.'</div>';
    return $table_output;
    
}

function hs_give_me_coffee() {
    $randomNumber = rand(1,20); //get random number between 1 and 20
    $response = wp_remote_get('https://api.sampleapis.com/coffee/hot'); //hit api of coffee 
    $response = json_decode($response['body'], true); //convert json into array 
    $coffee = '<center><div><img style="width: 50%;" src="'.$response[$randomNumber]['image'].'" /><h3>'.$response[$randomNumber]['title'].'</h3><p>'.$response[$randomNumber]['description'].'</p></div></center>';
    return $coffee;

}

function at_create_github_callback() {
    echo 'successfully created';
}