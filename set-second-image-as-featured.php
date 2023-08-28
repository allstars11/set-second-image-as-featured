<?php
/*
Plugin Name: Set Second Image as Featured
Description: This plugin sets the second image in a post as the featured image.
Version: 1.0
Author: www.digitalforce.it 
*/

define( 'DEBUG', true ); // Set to true to enable debugging, false to disable

function set_second_image_as_featured($content) {
    global $post;
//    global $log_file;


    $log_folder = plugin_dir_path(__FILE__) . 'log';
    $log_file = $log_folder . '/logfile.txt';
    
    logwrite ('running set_second_image_as_featured v2  -   ');


    if (is_singular() && is_main_query() && !is_page()) {
   //      echo 'we are in a single post  -   ';
        $second_image_url = get_second_image_url();
        if ($second_image_url) {
   //         echo 'URL of the second image in logfile ';
            file_put_contents($log_file, 'url: ' . $second_image_url . PHP_EOL, FILE_APPEND);
            }  else {
    //        echo 'No second image block found.';
            }
        } else {
     //       echo ' we are not in a single post ---    ';
    }

    return $content;
}



function timestamp_logger_log_timestamp() {
 //   echo 'writing the logfile';
    $log_folder = plugin_dir_path(__FILE__) . 'log';
    $log_file = $log_folder . '/logfile.txt';

//     file_put_contents($log_file, ''); // Clear the content of the log file

    file_put_contents($log_file, 'START OF LOGFILE' . PHP_EOL, FILE_APPEND);
    
    // Write the current timestamp to the log file
    $current_time = current_time( 'timestamp' );
    $timestamp = date( 'Y-m-d H:i:s', $current_time );
    file_put_contents($log_file, $timestamp . PHP_EOL, FILE_APPEND);
}

function logwrite($log_message){
    if (DEBUG) {
        $log_folder = plugin_dir_path(__FILE__) . 'log';
         $log_file = $log_folder . '/logfile.txt';
        file_put_contents( $log_file, $log_message . PHP_EOL, FILE_APPEND );
    }

}



function get_second_image_url() {

    global $post;
    // Get the post content
    $post_content = $post->post_content;

    // Parse the content to get block data
    $blocks = parse_blocks($post_content);

    // Initialize a counter for image blocks
    $image_block_count = 0;

    // Loop through the blocks
    foreach ($blocks as $block) {
        // Check if the block is an image block
        if ($block['blockName'] === 'core/image') {
            $image_block_count++;

            // If it's the second image block, get its URL
            if ($image_block_count === 2) {
             //   var_dump($block); $image_url = '';
      //          $image_url = $block['attrs']['url'];
                  $image_url = $block['innerHTML'];
            //    var_dump($image_url);
                return $image_url;
            }
        }
    }

    // If there's no second image block, return false or a default URL
    return 'no url';
}

function my_save_post_function( $post_id ) {
    if ( DEBUG ) {
        // Debugging is enabled, include debugging code
        error_reporting( E_ALL );
        ini_set( 'display_errors', 1 );
        timestamp_logger_log_timestamp();
    } else {
        // Debugging is disabled, suppress error display
        error_reporting( 0 );
        ini_set( 'display_errors', 0 );
    }
    logwrite('running my_save_post_function');
    // Check if this is an autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    // Check if this is a post and not a pagee
    if ( 'post' !== get_post_type( $post_id ) ) {
        return;
    }
    // Check if it is a REST Request
    if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
        return;
    }

    $times = did_action('save_post_{the_post_type}');
    logwrite ('times = '.$times);

    // Your code to be executed when a post is saved
    $url = extract_image_url_from_figure(get_second_image_url());
    logwrite( 'url of the second image: ' . $url);

    $image_id = media_sideload_image($url, $post_id, 'Image description.');

    if (!is_wp_error($image_id)) {
            // Set the image as the post's featured image
            set_post_thumbnail($post_id, $image_id);
    }
}

function extract_image_url_from_figure($html) {
    // Create a new DOMDocument
    $doc = new DOMDocument();
    
    // Load the HTML
    $doc->loadHTML($html);
    
    // Locate the img element within the figure
    $imgElement = $doc->getElementsByTagName('img')->item(0);
    
    // Check if img element exists and has src attribute
    if ($imgElement && $imgElement->hasAttribute('src')) {
        $imageUrl = $imgElement->getAttribute('src');
        return $imageUrl;
    }
    
    // Return null if no img element with src found
    return null;
}

// MAIN

add_action( 'save_post_post', 'my_save_post_function' );
?>