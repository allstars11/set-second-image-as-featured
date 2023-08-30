<?php
/*
Plugin Name: Set Second Image as Featured
Description: This plugin sets the second image in a post as the featured image.
Version: 1.0
Author: www.digitalforce.it Cesare Capobianco
License: GPL-3.0 or later
*/

define( 'DEBUG', true ); // Set to true to enable debugging, false to disable

function timestamp_logger_log_timestamp() {
 //   echo 'writing the logfile';
    $log_folder = plugin_dir_path(__FILE__) . 'log';
    $log_file = $log_folder . '/logfile.txt';

//     file_put_contents($log_file, ''); // Clear the content of the log file

    // Write the current timestamp to the log file
    $current_time = current_time( 'timestamp' );
    $timestamp = date( 'Y-m-d H:i:s', $current_time );
    file_put_contents($log_file, 'START OF LOGFILE  ------->>>   ' . $timestamp . PHP_EOL, FILE_APPEND);
}

function logwrite($message){
    if (DEBUG) {

        $log_folder = plugin_dir_path(__FILE__) . 'log';
        $log_file = $log_folder . '/logfile.txt';

        // Convert the argument to a string if it's not already
        if (!is_string($message)) {
            $message = print_r($message, true);
        }
        file_put_contents( $log_file, $message . PHP_EOL, FILE_APPEND );
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


function my_save_post_function( $post_id ) {
    logwrite('running my_save_post_function');
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
    
    // Check if this is an autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        logwrite('AUTOSAVE exit');
        return;
    }
    // Check if this is a post and not a page
    if ( 'post' !== get_post_type( $post_id ) ) {
        logwrite('wrong post type exit');
        return;
    }
    // Check if it is a REST Request
    if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
        logwrite('REST_REQUEST exit');
        return;
    }

    if (has_post_thumbnail($post_id)) {
            // Post has a featured image
            logwrite('Post has a featured image.');
        } else {
            // Post doesn't have a featured image
            logwrite('Post does not have a featured image.');
            $image_url = extract_image_url_from_figure(get_second_image_url());
            logwrite( 'url of the second image: ' . $image_url);
            // Get the image ID from the URL
            $image_id = attachment_url_to_postid($image_url);
            logwrite('about to log image_id');
            logwrite($image_id);
            // Set the featured image
            logwrite('about to set the featured image');
            set_post_thumbnail($post_id, $image_id);
        }

}

// MAIN

add_action( 'save_post_post', 'my_save_post_function' );
?>