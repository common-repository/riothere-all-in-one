<?php
add_action('admin_menu', 'riothere_register_my_page');
function admin_callback()
{
    echo 'Slides and Sections';
}
function riothere_register_my_page()
{
    add_menu_page(
        'Homepage Control',
        'Homepage Control',
        'edit_others_posts',
        'homepage_control',
        'admin_callback',
        'dashicons-admin-customizer',
        6
    );
}
