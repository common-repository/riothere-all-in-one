<?php

class Riothere_All_In_One_Followers
{
    private static $post_type = 'followers';

    public function __construct()
    {
        add_action('init', [$this, 'riothere_create_follower_cpt'], 0);
    }

    // Register Custom Post Type Follower
    public function riothere_create_follower_cpt()
    {

        $labels = array(
            'name' => _x('Followers', 'Post Type General Name', 'textdomain'),
            'singular_name' => _x('Follower', 'Post Type Singular Name', 'textdomain'),
            'menu_name' => _x('Followers', 'Admin Menu text', 'textdomain'),
            'name_admin_bar' => _x('Follower', 'Add New on Toolbar', 'textdomain'),
            'archives' => __('Follower Archives', 'textdomain'),
            'attributes' => __('Follower Attributes', 'textdomain'),
            'parent_item_colon' => __('Parent Follower:', 'textdomain'),
            'all_items' => __('All Followers', 'textdomain'),
            'add_new_item' => __('Add New Follower', 'textdomain'),
            'add_new' => __('Add New', 'textdomain'),
            'new_item' => __('New Follower', 'textdomain'),
            'edit_item' => __('Edit Follower', 'textdomain'),
            'update_item' => __('Update Follower', 'textdomain'),
            'view_item' => __('View Follower', 'textdomain'),
            'view_items' => __('View Followers', 'textdomain'),
            'search_items' => __('Search Follower', 'textdomain'),
            'not_found' => __('Not found', 'textdomain'),
            'not_found_in_trash' => __('Not found in Trash', 'textdomain'),
            'featured_image' => __('Featured Image', 'textdomain'),
            'set_featured_image' => __('Set featured image', 'textdomain'),
            'remove_featured_image' => __('Remove featured image', 'textdomain'),
            'use_featured_image' => __('Use as featured image', 'textdomain'),
            'insert_into_item' => __('Insert into Follower', 'textdomain'),
            'uploaded_to_this_item' => __('Uploaded to this Follower', 'textdomain'),
            'items_list' => __('Followers list', 'textdomain'),
            'items_list_navigation' => __('Followers list navigation', 'textdomain'),
            'filter_items_list' => __('Filter Followers list', 'textdomain'),
        );
        $args = array(
            'label' => __('Follower', 'textdomain'),
            'description' => __('', 'textdomain'),
            'labels' => $labels,
            'menu_icon' => '',
            'supports' => array(),
            'taxonomies' => array(),
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 5,
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => true,
            'can_export' => true,
            'has_archive' => false,
            'hierarchical' => false,
            'exclude_from_search' => true,
            'show_in_rest' => true,
            'publicly_queryable' => false,
            'capability_type' => 'post',
        );
        register_post_type(self::$post_type, $args);

    }

    private static function handle_follow_unfollow($followed_id, $action)
    {
        $user = wp_get_current_user();
        $follower_id = $user->ID;

        $followed_user = get_user_by('id', $followed_id);

        // followed seller not available or user available but doesn't have a seller role
        if (!$followed_user || !in_array('seller', $followed_user->roles) || (int) $follower_id === (int) $followed_id) {

            return 'invalid_seller_followed_id';
        }

        // all validations passed at this point, the only error that might happens is if unexpected happened
        // from database side
        $args = array(
            'post_type' => self::$post_type,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'followed_id',
                    'value' => $followed_id,
                    'compare' => '=',
                ),
                // this array results in no return for both arrays
                array(
                    'key' => 'follower_id',
                    'value' => $follower_id,
                    'compare' => '=',
                ),
            ),
        );

        $followers = get_posts($args);

        if (count($followers) > 0) {
            // update record
            $follower_row_id = $followers[0]->ID;
            $followers_action = get_post_meta($follower_row_id, 'action');
            // to check if the action is not duplicated
            if ($followers_action[0] == $action) {
                return 'action_already_done';
            }

        } else {
            // add record
            $title = 'user ID: ' . $follower_id . ' follow ID' . $followed_id;
            // Create post object
            $my_post = array(
                'post_type' => self::$post_type,
                'post_title' => $title,
                'post_status' => 'publish',
            );

            // Insert the post into the database
            $follower_row_id = wp_insert_post($my_post);

            // unexpected error check
            if ($follower_row_id instanceof WP_Error) {
                return 'error_create_line';
            }
        }

        update_post_meta($follower_row_id, 'followed_id', $followed_id);
        update_post_meta($follower_row_id, 'follower_id', $follower_id);
        update_post_meta($follower_row_id, 'action', $action);

        add_post_meta($follower_row_id, 'logs', [
            'action' => $action,
            'time' => current_datetime(),
        ]);
        return 'success';
    }

    public static function follow($followed_id)
    {
        return self::handle_follow_unfollow($followed_id, 'follow');
    }

    public static function unfollow($followed_id)
    {
        return self::handle_follow_unfollow($followed_id, 'unfollow');
    }

    public static function is_user_following_seller($followed_id)
    {
        $user = wp_get_current_user();
        $follower_id = $user->ID;

        $followed_user = get_user_by('id', $followed_id);

        // followed seller not available or user available but doesn't have a seller role
        if (!$followed_user || !in_array('seller', $followed_user->roles)) {
            $result['status'] = 'invalid_seller_followed_id';

            return false;
        }

        $args = array(
            'post_type' => self::$post_type,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'followed_id',
                    'value' => $followed_id,
                    'compare' => '=',
                ),
                // this array results in no return for both arrays
                array(
                    'key' => 'follower_id',
                    'value' => $follower_id,
                    'compare' => '=',
                ),
                array(
                    'key' => 'action',
                    'value' => 'follow',
                    'compare' => '=',
                ),
            ),
        );

        return count(get_posts($args)) > 0;

    }

    public static function get_seller_followers($seller_id)
    {
        $args = array(
            'post_type' => self::$post_type,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'followed_id',
                    'value' => $seller_id,
                    'compare' => '=',
                ),
                array(
                    'key' => 'action',
                    'value' => 'follow',
                    'compare' => '=',
                ),
            ),
        );

        return get_posts($args);
    }

    public static function get_seller_follows($seller_id)
    {
        $args = array(
            'post_type' => self::$post_type,
            'meta_query' => array(
                'relation' => 'AND',
                // this array results in no return for both arrays
                array(
                    'key' => 'follower_id',
                    'value' => $seller_id,
                    'compare' => '=',
                ),
                array(
                    'key' => 'action',
                    'value' => 'follow',
                    'compare' => '=',
                ),
            ),
        );

        return get_posts($args);
    }
}

new Riothere_All_In_One_Followers();
