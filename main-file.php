<?php
/**
*Plugin Name: Test Project
*Description: Create a Plugin given from ropstam as a Interview/trial project
*Version: 1.0.0
*Author: Muhammad Farhan
*License: GPL2
*Text Domain: test-project
*project Prefix: tp_
*/


add_action('init', 'tp_create_custom_posttypes'); // create custom post type of name Website of type "tp_website" on init hook
add_action( 'add_meta_boxes', 'tp_add_meta_boxes' ); // add metabox against my custom posttype
add_shortcode('Form_getting_name_and_website', 'tp_Form_getting_name_and_website_callback'); // create shortcode for form which collect name and website url from user
add_action( 'admin_menu', function () {
    remove_meta_box( 'submitdiv', 'tp_website', 'side' );
}); // remove publish meta box from my custom post type


// Create end-point(custom url) for json api which display all post of "tp_website" type posts
add_action('rest_api_init', function() {

    register_rest_route('tp', 'websites', [
        'methods' => 'GET',
        'callback' => 'tp_all_websites',
    ]);
    // json_api_url = 'yoursiteurl/wp-json/tp/websites'
}); 


// callback function of init hook
function tp_create_custom_posttypes() {
    $website_defination_array = array(

        'name' => __('Websites', 'Post Type General Name', 'test-project'),
        'singular_name' => __('Websites', 'Post Type Singular Name', 'test-project'),
        'menu_name' => __('Websites', 'test-project'),
        'name_admin_bar' => __('Admin Name', 'test-project'),
        'archives' => __('Archives', 'test-project'),
        'all_items' => __('All Websites', 'test-project')

    );

    $website_assigning_array = array(

        'label' => __('Custom Post','test-project'),
        'description' => __('This is Custom Post type','test-project'),
        'labels' => $website_defination_array,
        'menu_icon' => 'dashicons-admin-site',
        'supports' => array( 'title' ),
        'public' => true,
        'show_url' => true,
        'capabilities' => array(
            'create_posts' => false
        )
    );

    register_post_type('tp_website',$website_assigning_array);
}


// callback function of add meta box function
function tp_add_meta_boxes() {
    add_meta_box(
        'tp_form_entries_meta',
        __( 'Form Entries', 'test-project' ),
        'tp_form_entries_callback',
        'tp_website'
    );
} 

// callback function of add_meta_box hook
function tp_form_entries_callback(){
    global $post;
    ?>
    <div id="tp_form_entries_div">
        <div> 
            <span class="ed_label">Website Link: </span>
            <input style="width: 70%;" type="text" name="tp_website_url" readonly="readonly" value="<?php echo get_post_meta($post->ID, 'tp_website_url', true); ?>">
            <br>
        </div>
    </div>
    <?php
}


//callback function of add_shortcode
function tp_Form_getting_name_and_website_callback() {
    if (isset($_POST['tp_submiting_form'])) {
        $tp_name = $_POST['tp_user_entered_name'];
        $tp_website_link = $_POST['tp_Website_link'];

        $tp_inserted_post_array = array(
          'post_title'  => trim($tp_name),
          'post_status' => 'publish',
          'post_type' => 'tp_website'
        );
        $tp_new_inserted_post_id = wp_insert_post( $tp_inserted_post_array );
        update_post_meta($tp_new_inserted_post_id,'tp_website_url',$tp_website_link);
    }
    ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css">
    <form action="" method="post">
        <div class="form-group">
            <label for="tp_user_entered_name">Name</label>
            <input type="text" class="form-control" id="tp_user_entered_name" name="tp_user_entered_name">
        </div>
        <div class="form-group">
            <label for="tp_Website_link">Website</label>
            <input type="url" class="form-control" name="tp_Website_link" id="tp_Website_link">
        </div>
        <input type="submit" class="btn btn-primary" name="tp_submiting_form" value="Submit">
    </form>
    <?php
}


// callback function for json api of websites
function tp_all_websites() {
    $tp_getting_websites_array = array(
        'post_type' => 'tp_website'
    );

    $tp_all_websites = get_posts($tp_getting_websites_array);

    $data = [];
    $i = 0;
    foreach($tp_all_websites as $website) {
        $data[$i]['id'] = $website->ID;
        $data[$i]['title'] = $website->post_title;
        $data[$i]['slug'] = $website->post_name;
        $data[$i]['website_url'] = get_post_meta($website->ID,'tp_website_url',true);
        $i++;
    }
    return $data;
}
