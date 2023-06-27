<?php
/**
Plugin Name: Restrict Admin Access
Plugin URI: https://martinenrique.com/restrict-admin-access/
Description: Restricts access to the admin panel for selected user roles and hides the admin bar.
Version: 1.0
Author: Martín Enrique
Author URI: https://martinenrique.com
License: GPL2
*/

// Check if the file is accessed directly
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Add a settings field in the General Settings section
function ra_add_settings_field() {
	add_settings_section("restrict_admin_access_section", "Restrict Admin Access", null, "general");
    add_settings_field(
        'ra_restricted_roles',
        'Restricted Roles',
        'ra_restricted_roles_callback',
        'general',
		'restrict_admin_access_section'
    );
    register_setting('general', 'ra_restricted_roles');
}
add_action('admin_init', 'ra_add_settings_field');

// Render the configuration field in the General Settings section
function ra_restricted_roles_callback() {
    $roles = wp_roles()->get_names();
    $restricted_roles = get_option('ra_restricted_roles', array());
    ?>
    <select name="ra_restricted_roles[]" multiple>
        <?php foreach ($roles as $role => $name) : ?>
            <option value="<?php echo esc_attr($role); ?>" <?php selected(in_array($role, $restricted_roles)); ?>><?php echo esc_html($name); ?></option>
        <?php endforeach; ?>
    </select>
    <p class="description">Select the user roles you want to restrict from accessing the admin panel and hide the admin bar.</p>
    <?php
}

// Redirect to the homepage if a restricted user tries to access the admin panel
function ra_restrict_admin_access() {
    if (is_admin() && !current_user_can('manage_options')) {
        $restricted_roles = get_option('ra_restricted_roles', array());
        $user = wp_get_current_user();
        $user_roles = (array) $user->roles;
        $intersect = array_intersect($user_roles, $restricted_roles);

        if (!empty($intersect)) {
            wp_redirect(home_url());
            exit;
        }
    }
}
add_action('admin_init', 'ra_restrict_admin_access');

// Hide the admin bar for restricted users
function ra_hide_admin_bar() {
    if (!current_user_can('manage_options')) {
        show_admin_bar(false);
    }
}
add_action('after_setup_theme', 'ra_hide_admin_bar');
