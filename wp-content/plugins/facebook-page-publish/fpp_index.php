<?php
/**
 * Facebook Page Publish - publishes your blog posts to your fan page.
 * Copyright (C) 2011  Martin Tschirsich
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * 
 * Plugin Name: Facebook Page Publish
 * Plugin URI:  http://wordpress.org/extend/plugins/facebook-page-publish/
 * Description: Publishes your posts on the wall of a Facebook profile or page.
 * Author:      Martin Tschirsich
 * Version:     0.3.0
 * Author URI:  http://www.tu-darmstadt.de/~m_t/
 */

#error_reporting(E_ALL);
define('VERSION', '3.0.0');
define('BASE_DIR', dirname(__file__));
define('BASE_URL', WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__), '', plugin_basename(__FILE__)));
define('ADMIN_URL', admin_url('admin.php?page='.urlencode(plugin_basename(__FILE__))));
define('DEFAULT_POST_TO_FACEBOOK', false); // Checkbox admin panel always preselected
define('FACEBOOK_LINK_DESCRIPTION_MAX_LENGTH', 420); // Facebook link description allows max. 420 characters

add_action('future_post', 'fpp_future_action');
add_action('publish_post', 'fpp_publish_action');

add_action('admin_init', 'fpp_admin_init_action');
add_action('admin_menu', 'fpp_admin_menu_action');
add_action('wp_head', 'fpp_head_action');
add_action('post_submitbox_start', 'fpp_post_submitbox_start_action');

class CommunicationException extends Exception {}

class FacebookUnreachableException extends CommunicationException {
        public function __construct($message = null, $code = 0) {
                if (empty($message)) {
                        $message = 'Facebook is not reachable from your server. <a target="_blank" href="'.BASE_URL.'diagnosis.htm">Check your connection!</a>';
                } else {
                        $message = 'Facebook is not reachable from your server: '.$message.'<br /><a target="_blank" href="'.BASE_URL.'diagnosis.htm">Check your connection!</a>';
                }
                parent::__construct($message, $code);
        }
}

class FacebookErrorException extends CommunicationException {
        public function __construct($message, $code = 0) {
                $message = 'Facebook returned an error: '.$message;
                parent::__construct($message, $code);
        }
}

class FacebookUnexpectedErrorException extends CommunicationException {
        public function __construct($message = null, $code = 0) {
                if (empty($message)) {
                        $message = 'Facebook returned an unexpected error. Try to resolve this issue, update the plugin or <a target="_blank" href="http://wordpress.org/tags/facebook-page-publish">inform the author</a> about the problem.';
                } else {
                        $message = 'Facebook returned an unexpected error: '.$message.'<br />Try to resolve this issue, update the plugin or <a target="_blank" href="http://wordpress.org/tags/facebook-page-publish">inform the author</a> about the problem.';
                }
                parent::__construct($message, $code);
        }
}

class FacebookUnexpectedDataException extends CommunicationException {
        public function __construct($message = null, $code = 0) {
                if (empty($message)) {
                        $message = 'Facebook returned an unexpected dataset. Try to resolve this issue, update the plugin or <a target="_blank" href="http://wordpress.org/tags/facebook-page-publish">inform the author</a> about the problem.';
                } else {
                        $message = 'Facebook returned an unexpected dataset: '.$message.'<br />Try to resolve this issue, update the plugin or <a target="_blank" href="http://wordpress.org/tags/facebook-page-publish">inform the author</a> about the problem.';
                }
                parent::__construct($message, $code);
        }
}

/**
 * @return v1 < v2
 */
function fpp_is_older_version($v1, $v2) {
        $v1_array = explode('.', $v1);
        $v2_array = explode('.', $v2);
        return ($v1_array[0] < $v2_array[0]) or 
                        (($v1_array[0] == $v2_array[0]) and 
                        (($v1_array[1] < $v2_array[1]) or 
                                (($v1_array[1] == $v2_array[1]) and 
                                ($v1_array[2] < $v2_array[2]))));
}

function fpp_check_update() {
        $version = get_option('fpp_installed_version');
        if ($version != VERSION) { // Only do something if plugin version != option version
                // default options:
                $options = array(
                        'app_id' => '',
                        'app_id_valid' => false,
                        'app_secret' => '',
                        'app_secret_valid' => false,
                        'object_id' => '',
                        'object_id_valid' => false,
                        'object_type' => '',
                        'ignore_ssl' => false,
                        'default_publishing' => 'all',
                        'default_publishing_categories' => array(),
                        'default_thumbnail_url' => BASE_URL.'line.png',
                        'show_post_categories' => true,
                        'show_post_author' => true,
                        'show_thumbnail' => 'gravatar');
                
                $current_options = get_option('fpp_options');
                if (!is_array($current_options)) { // No plugin version installed
                        update_option('fpp_options', $options);
                        update_option('fpp_object_access_token', '');
                        update_option('fpp_profile_access_token', '');
                        update_option('fpp_error', '');
                } else if (empty($version)) { // version <= 0.2.2
                        $options['app_id'] = $current_options['app_id'];
                        $options['app_id_valid'] = !empty($current_options['page_id']);
                        $options['app_secret'] = $current_options['app_secret'];
                        $options['app_secret_valid'] = !empty($current_options['page_id']);
                        $options['object_id'] = $current_options['page_id'];
                        $options['object_id_valid'] = !empty($current_options['page_id']);
                        $options['object_type'] = 'page';
                        $options['show_thumbnail'] = $current_options['show_gravatar'] ? 'gravatar' : 'post';
                        
                        update_option('fpp_options', $options);
                        update_option('fpp_object_access_token', get_option('fpp_page_access_token'));
                        update_option('fpp_profile_access_token', '');
                        
                        delete_option('fpp_page_access_token');
                        delete_option('fpp_post_to_facebook');
                } else if (fpp_is_older_version($version, VERSION)) { // currently none
                        update_option('fpp_options', $options);
                        update_option('fpp_object_access_token', '');
                        update_option('fpp_profile_access_token', '');
                        update_option('fpp_error', '');
                }
                update_option('fpp_installed_version', VERSION);
        }
}
	
/**
 * Called on html head rendering. Prints meta tags to make posts appear
 * correctly in Facebook. 
 */
function fpp_head_action() {
        global $post;

        if (is_object($post) && empty($post->post_password) && ($post->post_type == 'post') && is_single()) {
                fpp_render_meta_tags($post);
        }
}

/**
 * Called on any_to_future post state transition (scheduled post).
 * Marks any given post for publishing on Facebook
 * - if it was send directly from the admin panel and the 
 *   post-to-facebook checkbox was checked.
 * Marks any given post for NOT publishing on Facebook
 * - if it was send directly from the admin panel and the
 *   post-to-facebook checkbox was NOT checked.
 *
 * @last_review 3.0.0
 */
function fpp_future_action($post_id) {
        $send_from_admin = isset($_REQUEST['fpp_send_from_admin']);
        
        if ($send_from_admin) {
                $post = get_post($post_id);
                if (($post->post_type == 'post')) {
                        if (!empty($_REQUEST['fpp_post_to_facebook'])) { // Directly send from the user (admin panel) with active checkbox
                                $options = get_option('fpp_options');
                                update_post_meta($post->ID, '_fpp_is_scheduled', true);
                        } else {
                                update_post_meta($post->ID, '_fpp_is_scheduled', false);
                        }
                }
        }
}

/**
 * Called on any_to_publish post state transition (published post).
 * Publishes any given non password protected post to Facebook
 * - if it was send directly from the admin panel and the 
 *   post-to-facebook checkbox was checked.
 * - if it was previously marked for publishing on Facebook (scheduled).
 * - if it was NOT send directly from the admin panel and the plugin
 *   is set to always post on Facebook and the post is not already
 *   published (on wordpress or Facebook).
 *
 * @see fpp_render_post_button()
 */
function fpp_publish_action($post_id) {
        $post = get_post($post_id);
        if (($post->post_type == 'post')) {
        
                $object_access_token = get_option('fpp_object_access_token');
                if (!empty($object_access_token)) { // Incomplete plugin configuration, do nothing, report no error
                
                        $send_from_admin = isset($_REQUEST['fpp_send_from_admin']);
                        $options = get_option('fpp_options');
                        try {
                                if ($send_from_admin && !empty($_REQUEST['fpp_post_to_facebook'])) { // Directly send from the user (admin panel) with active post checkbox
                                        fpp_publish_to_facebook($post, $options['object_id'], get_option('fpp_object_access_token'));
                                        update_post_meta($post->ID, '_fpp_is_published', true);
                                }
                                else if (get_post_meta($post->ID, '_fpp_is_scheduled', true) == true) { // Scheduled post previously marked for Facebook publishing by the user
                                        fpp_publish_to_facebook($post, $options['object_id'], get_option('fpp_object_access_token'));
                                        update_post_meta($post->ID, '_fpp_is_published', true);
                                        delete_post_meta($post->ID, '_fpp_is_scheduled');
                                }
                                else if (!$send_from_admin && (array_search('_fpp_is_scheduled', get_post_custom_keys($post->ID)) === false) && !get_post_meta($post->ID, '_fpp_is_published', true)) { // not send from admin panel, not already published, user never decided for or against publishing
                                        if (empty($post->post_password) and fpp_get_default_publishing($post)) { // Dont post password protected posts without the users consient
                                                fpp_publish_to_facebook($post, $options['object_id'], get_option('fpp_object_access_token'));
                                                update_post_meta($post->ID, '_fpp_is_published', true);
                                        }
                                }
                        } catch (CommunicationException $exception) {
                                update_option('fpp_error', '<p>While publishing "'.$post->post_title.'" to Facebook, an error occured: </p><p><strong>'.$exception->getMessage().'</strong></p>');
                        }
                }
        }
}

function fpp_get_default_publishing($post) {
        $options = get_option('fpp_options');
        
        if ($options['default_publishing'] == 'all') return true;
        if ($options['default_publishing'] == 'category') {
                $categories = get_the_category($post->ID);
                foreach ($categories as $category) {
                        if (array_search($category->cat_ID, $options['default_publishing_categories']) !== false)
                                return true;
                }
        }
        return false;
}

/**
 * Called on admin menu rendering, adds an options page and its
 * rendering callback.
 *
 * @see fpp_render_options_page()
 */
function fpp_admin_menu_action() {
        $page = add_options_page('Facebook Page Publish Options', 'Facebook Page Publish', 'manage_options', __FILE__, 'fpp_render_options_page');
        
        add_action('admin_print_scripts-'.$page, 'fpp_admin_print_scripts_action');
        add_action('admin_print_styles-'.$page, 'fpp_admin_print_styles_action');
}

/**
 * Called when a user accesses the admin area. Registers settings and a
 * sanitization callback.
 *
 * @see fpp_validate_optios
 */
function fpp_admin_init_action() {
        register_setting('fpp_options_group', 'fpp_options', 'fpp_validate_options');
        fpp_check_update();
}

/**
 * Called when the submitbox is rendered. Renders a publish to Facebook
 * button if the current user is an author.
 *
 * @last_review 3.0.0
 */
function fpp_post_submitbox_start_action() {
        global $post;

        if (is_object($post) && ($post->post_type == 'post') && current_user_can('publish_posts')) {
                fpp_render_post_button();
        }
}

/**
 * Publishes the given post to a Facebook page.
 * 
 * @param post Wordpress post to publish
 * @param object_id Facebook page or wall ID
 * @param object_type Either 'profile' or 'page'
 * @param object_acces_token Access token for the given object
 *
 * @last_review 3.0.0
 */
function fpp_publish_to_facebook($post, $object_id, $object_acces_token) {
        if (empty($post->post_password)) {
                $message = stripslashes(html_entity_decode(wp_filter_nohtml_kses(strip_shortcodes(empty($post->post_excerpt) ? $post->post_content : $post->post_excerpt)), ENT_QUOTES, 'UTF-8'));

                if (strpos($message, '<!--more-->') !== false) {
                        $message = substr($message, 0, strpos($message, '<!--more-->'));
                }
                
                if (strlen($message) >= FACEBOOK_LINK_DESCRIPTION_MAX_LENGTH) {
                        $last_space_pos = strrpos(substr($message, 0, FACEBOOK_LINK_DESCRIPTION_MAX_LENGTH - 3), ' ');
                        $message = substr($message, 0, !empty($last_space_pos) ? $last_space_pos : FACEBOOK_LINK_DESCRIPTION_MAX_LENGTH - 3).'...';
                }
        } else {
                $message = ''; // Password protected, no content displayed.
        }
        
        // Publish:
        $request = new WP_Http;
        $api_url = 'https://graph.facebook.com/'.urlencode($object_id).'/links';
        $body = array('message' => $message, 'link' => get_permalink($post->ID), 'access_token' => $object_acces_token);
        $response = $request->request($api_url, array('method' => 'POST', 'body' => $body, 'sslverify' => fpp_get_ssl_verify()));

        // Error detection:
        if (array_key_exists('errors', $response))
                throw new FacebookUnreachableException(!empty($response->errors) ? array_pop(array_pop($response->errors)) : '');

        $json_response = json_decode($response['body']);
        if (is_object($json_response) and property_exists($json_response, 'error')) {
                throw new FacebookUnexpectedErrorException((is_object($json_response->error) and property_exists($json_response->error, 'message')) ? $json_response->error->message : '');
        }
}

function fpp_get_ssl_verify() {
        $options = get_option('fpp_options');
        return !$options['ignore_ssl'];
}

/**
 * Checks whether a given access_token is valid and has the give 
 * permissions.
 *
 * @param object_id Page or profile ID
 * @param object_type Either 'page' or 'profile'
 * @param object_access_token The access token to validate
 * @param permissions Array of permission strings to validate
 * @return True if access_token valid and all permissions granted
 *
 * @last_review 3.0.0
 */
function fpp_verify_facebook_access_permissions($object_id, $object_type, $object_access_token, $permissions) {
        if ($object_type == 'page') { // Workaround for missig FQL querying capabilities
                $request = new WP_Http;
                $api_url = 'https://graph.facebook.com/'.urlencode($object_id).'/links';
                $body = array('message' => 'dummy message', 'link' => 'invalid url', 'access_token' => $object_access_token);
                $response = $request->request($api_url, array('method' => 'POST', 'body' => $body, 'sslverify' => fpp_get_ssl_verify()));

                if (array_key_exists('errors', $response))
                        throw new FacebookUnreachableException(!empty($response->errors) ? array_pop(array_pop($response->errors)) : '');
                
                $json_response = json_decode($response['body']);
                if (is_object($json_response) and property_exists($json_response, 'error') and is_object($json_response->error) and property_exists($json_response->error, 'message')) {
                        return (strpos($json_response->error->message, '#1500') !== false);
                }
                return false;
        } else {
                $request = new WP_Http;
                $api_url =  'https://api.facebook.com/method/fql.query?access_token='.urlencode($object_access_token).'&format=json&query='.urlencode('SELECT '.implode(',', $permissions).' FROM permissions WHERE uid = '.urlencode($object_id)); 
                $response = $request->get($api_url, array('sslverify' => fpp_get_ssl_verify()));

                if (array_key_exists('errors', $response))
                        throw new FacebookUnreachableException((!empty($response->errors) ? array_pop(array_pop($response->errors)) : ''));

                $json_response = json_decode($response['body']);
                if (is_object($json_response) and property_exists($json_response, 'error_msg')) {
                        if (property_exists($json_response, 'error_code') and ($json_response->error_code == 190)) // Access token expired or invalid
                                return false;
                        if (property_exists($json_response, 'error_code') and ($json_response->error_code == 104)) // 'Requires valid signature'-error
                                return false;
                        throw new FacebookUnexpectedErrorException($json_response->error_msg);
                }
                if (!is_array($json_response)) {
                        throw new FacebookUnexpectedDataException();
                }
                $json_response = array_pop($json_response);
                if (!is_object($json_response)) {
                        throw new FacebookUnexpectedDataException();
                }
                
                foreach ($permissions as $permission) {
                        if (!property_exists($json_response, $permission) or empty($json_response->$permission)) return false;
                }
                return true;
        }
        throw new Exception('Unsupported object type');
}

/**
 * Classifies Facebook object ids.
 *
 * @param object_ids Array of Facebook object ids
 * @return map with a type string for each Facebook object id
 * 
 * @last_review 3.0.0
 */
function fpp_classify_facebook_objects($object_ids) {
        $numerical_object_ids = array_filter($object_ids, 'is_numeric'); // Alphabetical id's produce a Facebook error.
        
        $request = new WP_Http;
        $api_url = 'https://api.facebook.com/method/fql.query?format=json&query='.urlencode('SELECT id, type FROM object_url WHERE id IN ('.implode(',', $numerical_object_ids).')'); 
        $response = $request->get($api_url, array('sslverify' => fpp_get_ssl_verify()));

        if (array_key_exists('errors', $response))
                throw new FacebookUnreachableException(!empty($response->errors) ? array_pop(array_pop($response->errors)) : '');

        $json_response = json_decode($response['body']);
        if (is_object($json_response) and property_exists($json_response, 'error')) {
                throw new FacebookUnexpectedErrorException((is_object($json_response->error) and property_exists($json_response->error, 'message')) ? $json_response->error->message : '');
        }
        if (!is_array($json_response)) {
                throw new FacebookUnexpectedDataException();
        }
        
        $result = array();
        $float_object_ids = array_map('floatval', $numerical_object_ids); // PHP <= 5.2 is missing JSON_BIGINT
        foreach ($json_response as $json_response_entry) {
                if (!property_exists($json_response_entry, 'type') or !property_exists($json_response_entry, 'id'))
                        throw new FacebookUnexpectedDataException();
                $result[$numerical_object_ids[array_search($json_response_entry->id, $float_object_ids)]] = $json_response_entry->type;
        }
        
        foreach ($object_ids as $object_id) {
                if (!array_key_exists($object_id, $result))
                        $result[$object_id]= '';
        }
        
        return $result;
}

/**
 * Checks whether a given Facebook application id and its secret are
 * valid.
 *
 * @param app_id Application id to verify
 * @param app_secret Application secret
 * @param redirect_uri URL equal to the URL in the Facebook app settings
 * @return True if the given application id and secret are valid
 *
 * @last_review 3.0.0
 */
function fpp_is_valid_facebook_application($app_id, $app_secret, $redirect_uri) {
        $request = new WP_Http;
        $api_url = 'https://graph.facebook.com/oauth/access_token?client_id='.urlencode($app_id).'&client_secret='.urlencode($app_secret).'&redirect_uri='.urlencode($redirect_uri);
        $response = $request->get($api_url, array('sslverify' => fpp_get_ssl_verify()));

        if (array_key_exists('errors', $response))
                throw new FacebookUnreachableException(!empty($response->errors) ? array_pop(array_pop($response->errors)) : '');

        $object = json_decode($response['body']);
        if (property_exists($object, 'error')) {
                if (property_exists($object->error, 'message')) {
                        if (strpos($object->error->message, 'Error validating client secret') !== false)
                                return false;
                
                        if (strpos($object->error->message, 'Invalid verification code format') !== false)
                                return true;
                              
                        if (strpos($object->error->message, 'Invalid redirect_uri') !== false)  
                                throw new FacebookErrorException('The site URL in your Facebook application settings does not match your wordpress blog URL. Please refer to the <a target="_blank" href="'.BASE_URL.'setup.htm#site_url">detailed setup instructions</a>.');
                        
                        throw new FacebookUnexpectedErrorException($object->error->message);
                }
                throw new FacebookUnexpectedErrorException();
        }
        throw new FacebookUnexpectedDataException();
}

/**
 * Acquires an object access token with all these permissions that
 * were specified when retrieving the code.
 *
 * @param app_id Application ID
 * @param app_secret Application secret
 * @param object_id Facebook page or profile ID
 * @param object_type Either 'profile' or 'page'
 * @param redirect_uri URL used to get the transaction code
 * @param code Transaction code (refer to the OAuth protocoll docs)
 *
 * @last_rewiev 3.0.0
 */
function fpp_acquire_profile_access_token($app_id, $app_secret, $redirect_uri, $code) {
        $request = new WP_Http;
        $api_url = 'https://graph.facebook.com/oauth/access_token?client_id='.urlencode($app_id).'&redirect_uri='.urlencode($redirect_uri).'&client_secret='.urlencode($app_secret).'&code='.urlencode($code);
        $response = $request->get($api_url, array('sslverify' => fpp_get_ssl_verify()));

        if (array_key_exists('errors', $response))
                throw new FacebookUnreachableException(!empty($response->errors) ? array_pop(array_pop($response->errors)) : '');

        $json_response = json_decode($response['body']);
        if ($json_response != null) {
                if (is_object($json_response) and property_exists($json_response, 'error') and property_exists($json_response->error, 'message')) {
                        if (strpos($json_response->error->message, 'Code was invalid or expired') !== false) {
                                throw new FacebookErrorException('Your authorization code was invalid or expired. Please try again. If the problem persists update the plugin or <a target="_blank" href="http://wordpress.org/tags/facebook-page-publish">inform the author</a>.');
                        }
                        else throw new FacebookUnexpectedErrorException($json_response->error->message);
                }
                else throw new FacebookUnexpectedErrorException();
        }
        $access_token_url = $response['body'];

        preg_match('/^.+=\s*(.+)/', $access_token_url, $matches);
        if (!empty($matches[1])) return $matches[1];
        
        throw new FacebookUnexpectedDataException();
}

function fpp_acquire_page_access_token($page_id, $profile_access_token) {
        $request = new WP_Http;
        $api_url = 'https://graph.facebook.com/me/accounts?access_token='.urlencode($profile_access_token);
        $response = $request->get($api_url, array('sslverify' => fpp_get_ssl_verify()));

        if (array_key_exists('errors', $response))
                throw new FacebookUnreachableException(!empty($response->errors) ? array_pop(array_pop($response->errors)): '');

        $json_response = json_decode($response['body']);
        if (!is_object($json_response) || !property_exists($json_response, 'data'))
                throw new FacebookUnexpectedErrorException('Can\'t access Facebook user account information.');

        foreach ($json_response->data as $account) {
                if ($account->id == $page_id) {
                        if (!property_exists($account, 'access_token'))
                                throw new FacebookUnexpectedErrorException('Some or all access permissions for your page are missing.');
                        $page_access_token = $account->access_token;
                        break;
                }
        }
        if (!isset($page_access_token))
                throw new FacebookErrorException('Your Facebook user account data contains no page with the given ID. You have to be administrator of the given page.');
                
        return $page_access_token;
}

/**
 * @last_review 3.0.0
 */
function fpp_admin_print_scripts_action() {
        wp_enqueue_script('media-upload');
        wp_enqueue_script('thickbox');
        wp_register_script('fpp-upload', BASE_URL.'fpp_script.js', array('jquery','media-upload','thickbox'));
        wp_enqueue_script('fpp-upload');
}

/**
 * @last_review 3.0.0
 */
function fpp_admin_print_styles_action() {
        wp_enqueue_style('thickbox');
}

/**
 * Renders the options page. Uses the settings API (options validation, checking and storing by WP).
 * Also validates certain options (Facebook access) that need redirecting.
 */
function fpp_render_options_page() {
        $options = get_option('fpp_options');

        $error = get_option('fpp_error');
        if (!empty($error)) {
              echo '<div class="error">'.$error.'</div>';  
              update_option('fpp_error', '');
        }
        
        if ($options['app_id_valid'] && $options['app_secret_valid'] && $options['object_id_valid']) {
                // User clicked the authorize button:
                if (array_key_exists('code', $_GET)) {
                        try {
                                $profile_access_token = fpp_acquire_profile_access_token($options['app_id'], $options['app_secret'], ADMIN_URL, $_GET['code']);
                                
                                if ($options['object_type'] == 'page') {
                                        $page_access_token = fpp_acquire_page_access_token($options['object_id'], $profile_access_token);
                                        update_option('fpp_object_access_token', $page_access_token);
                                }
                                else update_option('fpp_object_access_token', $profile_access_token);
                                update_option('fpp_profile_access_token', $profile_access_token);
                              
                        } catch (CommunicationException $exception) {
                                echo '<div class="error"><p><strong>'.$exception->getMessage().'</strong></p><p>Your page or profile\'s access permissions could not be granted.</p></div>';
                        }
                }
        
                // Check if all necessary permissions are granted:
                try {
                        $object_access_token = get_option('fpp_object_access_token');
                        if (!empty($object_access_token) and !fpp_verify_facebook_access_permissions($options['object_id'], $options['object_type'], $object_access_token, fpp_get_required_permissions($options['object_type']))) {
                                update_option('fpp_object_access_token', '');
                                update_option('fpp_profile_access_token', '');
                                throw new CommunicationException('Some or all access permissions were revoked. Please click the button <em>Grant access rights!</em> and authorize the plugin to post to your Facebook profile or page.');
                        }
                } catch (CommunicationException $exception) {
                        echo '<div class="error"><p><strong>'.$exception->getMessage().'</strong></p><p>Your page or profile\'s access permissions could not be verified.</p></div>';
                }
        }
        ?>
        <div class="wrap">
                <div class="icon32" id="icon-options-general"><br /></div>
                
                <h2>Facebook Page Publish Plugin Options</h2>
                <form method="post" action="options.php">
                        <?php settings_fields('fpp_options_group'); ?>
                        <h3>Facebook Connection</h3>
                        <p>Connect your blog to Facebook. See <a target="_blank" href="<?php echo BASE_URL; ?>setup.htm">detailed setup instructions</a> for help.</p>
                        <table class="form-table">
                                <tr valign="top">
                                        <th scope="row"><label for="fpp_options-app_id">Application ID</label></th>
                                        <td><input style="color:<?php echo $options['app_id_valid'] ? 'green' : 'red' ?>" id="fpp_options-app_id" name="fpp_options[app_id]" type="text" value="<?php echo htmlentities($options['app_id']); ?>" />
                                        <a style="font-size:1.3em" target="_blank" href="<?php echo BASE_URL ?>setup.htm#app_id">?</a></td>
                                </tr>
                                <tr valign="top">
                                        <th scope="row"><label for="fpp_options-app_secret">Application Secret</label></th>
                                        <td><input style="color:<?php echo $options['app_secret_valid'] ? 'green' : ($options['app_id_valid'] ? 'red' : 'black') ?>" id="fpp_options-app_secret" name="fpp_options[app_secret]" type="text" value="<?php echo htmlentities($options['app_secret']); ?>" />
                                        <a style="font-size:1.3em" target="_blank" href="<?php echo BASE_URL ?>setup.htm#app_secret">?</a></td>
                                </tr>
                                <tr valign="top">
                                        <th scope="row"><label for="fpp_options-object_id">Page or profile ID</label></th>
                                        <td><input style="color:<?php echo $options['object_id_valid'] ? 'green' : 'red' ?>" id="fpp_options-object_id" name="fpp_options[object_id]" type="text" value="<?php echo htmlentities($options['object_id']); ?>" />
                                        <?php 
                                        if ($options['app_id_valid'] && $options['app_secret_valid'] && $options['object_id_valid']) {
                                                $object_access_token = get_option('fpp_object_access_token');
                                                if (empty($object_access_token)) {
                                                        echo '<a class="button-secondary" style="color:red" href="https://www.facebook.com/dialog/oauth?client_id='.urlencode($options['app_id']).'&redirect_uri='.urlencode(ADMIN_URL).'&scope='.urlencode(implode(',', fpp_get_required_permissions($options['object_type']))).'">Grant access rights!</a>';
                                                }
                                                else echo '<span style="color:green">Access granted.</span> <a class="button-secondary" style="color:green" href="https://www.facebook.com/dialog/oauth?client_id='.urlencode($options['app_id']).'&redirect_uri='.urlencode(ADMIN_URL).'&scope='.urlencode(implode(',', fpp_get_required_permissions($options['object_type']))).'">Renew</a>';
                                        }
                                        //else echo '<button class="button-secondary" disabled="disabled">Grant access rights!</button>';
                                        ?>
                                        <a style="font-size:1.3em" target="_blank" href="<?php echo BASE_URL ?>setup.htm#object_id">?</a>
                                        </td>
                                </tr>
                                <tr valign="top">
                                        <th scope="row">Compatibility</th>
                                        <td>
                                                <fieldset>
                                                <label style="<?php echo (!fpp_get_ssl_verify()) ? 'color:#aa6600' : '' ?>"><input id="fpp_options-ignore_ssl" type="checkbox" name="fpp_options[ignore_ssl]" value="1" <?php checked('1', $options['ignore_ssl']); ?> /> <span>Ignore SSL Certificate</span></label><br />
                                                </fieldset>
                                        </td>
                                </tr>
                        </table>
                        
                        <h3>Publishing</h3>
                        Publish 
                        <fieldset style="display:inline; vertical-align:middle; line-height:20px">
                                <label style="vertical-align:middle"><input name="fpp_options[default_publishing]" value="category" type="radio" <?php checked('1', $options['default_publishing'] == 'category'); ?> /> <span>posts from selected categories</span></label>
                                <div style="float:right; text-align:center"><select name="fpp_options[default_publishing_categories][]" multiple="multiple" style="margin:0 5px; height:60px; width:200px" size="4">
                                        <?php
                                        $categories = get_categories(array('hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC'));
                                        foreach ($categories as $category) { 
                                                echo '<option style="height:8pt" value="'.$category->cat_ID.'" '.((array_search($category->cat_ID, $options['default_publishing_categories']) !== false) ? 'selected="selected"' : '').'>'.$category->name.'</option>';
                                        }
                                        ?>
                                </select><br /><span style="color:#999; font-size:7pt; line-height:9pt">Hold [Ctrl] to select multiple categories</span></div><br />
                                <label style="vertical-align:middle; clear:both"><input name="fpp_options[default_publishing]" value="all" type="radio" <?php checked('1', $options['default_publishing'] == 'all'); ?> /> <span>all posts</label><br />
                                <label style="vertical-align:middle"><input name="fpp_options[default_publishing]" value="none" type="radio" <?php checked('1', $options['default_publishing'] == 'none'); ?> /> <span>nothing</span></label><br />
                        </fieldset>to Facebook unless statet otherwise.
                        
                        <h3>Customization</h3>
                        <p>Customize the appearance of your posts on Facebook</p>
                        <div style="width:450px; padding:5px; background-color:#FFF">
                                <div style="float:left; width:40px; height:40px; padding:5px; background-color:#EEE; font-size:7pt; line-height:9pt">Page or profile photo</div>
                                <div style="margin-left:55px">
                                        <span style="font-weight:bold; color:#3B5998">Page or profile name</span>
                                        <div style="width:400px; margin-bottom:10px; font-size:9pt; line-height:11pt">Lorem ipsum dolor sit amet, consectetur adipisici elit, sed eiusmod tempor incidunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud...</div>
                                        <div style="float:left; width:80px; height:45px; padding:5px; background-color:#EEE; font-size:7pt; line-height:9pt">Post thumbnail</div>
                                        <div style="margin-left:95px">
                                                <div style="font-weight:bold; color:#3B5998">Post title - link to post</div>
                                                <div style="color:gray; font-size:8pt; line-height:8pt">Blog domain</div>
                                                <div style="color:gray; font-size:8pt; line-height:20pt">Post categories | Post author</div>
                                        </div>
                                        <div style="clear:left"></div>
                                </div>
                        </div>
                        <table class="form-table">
                                <tr valign="top">
                                        <th scope="row">Post thumbnail</th>
                                        <td>
                                                <fieldset>
                                                <label><input name="fpp_options[show_thumbnail]" value="gravatar" type="radio" <?php checked('1', $options['show_thumbnail'] == 'gravatar'); ?> /> <span><a target="_blank" href="http://gravatar.com">Gravatar</a> of the post author</span></label><br />
                                                <label><input name="fpp_options[show_thumbnail]" value="post" type="radio" <?php checked('1', $options['show_thumbnail'] == 'post'); ?> /> <span>Random post image</span></label><br />
                                                <label><input name="fpp_options[show_thumbnail]" value="default" type="radio" <?php checked('1', $options['show_thumbnail'] == 'default'); ?> /> <span>Default thumbnail</span></label><br />
                                                <label><input name="fpp_options[show_thumbnail]" value="none" type="radio" <?php checked('1', $options['show_thumbnail'] == 'none'); ?> /> <span>No thumbnail</span></label><br />
                                                </fieldset>
                                        </td>
                                </tr>
                                <tr valign="top">
                                        <th scope="row">Default thumbnail</th>
                                        <td><label for="upload_image">
                                        <input id="upload_image" type="text" size="36" name="fpp_options[default_thumbnail_url]" value="<?php echo htmlentities($options['default_thumbnail_url']); ?>" />
                                        <input id="upload_image_button" type="button" value="Media gallery" />
                                        <br />Enter an URL or upload an image.
                                        </label></td>
                                </tr>
                                <tr valign="top">
                                        <th scope="row">Further information</th>
                                        <td>
                                                <fieldset>
                                                <label><input id="fpp_options-show_post_author" type="checkbox" name="fpp_options[show_post_author]" value="1" <?php checked('1', $options['show_post_author']); ?> /> <span>Show the post author</span></label><br />
                                                <label><input id="fpp_options-show_post_categories" type="checkbox" name="fpp_options[show_post_categories]" value="1" <?php checked('1', $options['show_post_categories']); ?> /> <span>Show the post categories</span></label><br />
                                                </fieldset>
                                        </td>
                                </tr>
                        </table>
                        <p class="submit">
                                <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
                        </p>
                </form>
        </div>
        <?php
}

/**
 * @last_review 3.0.0
 */
function fpp_get_required_permissions($object_type) {
        if ($object_type == 'page') {
                return array('manage_pages', 'offline_access', 'share_item');
        } else {
                return array('offline_access', 'share_item');
        }
}

/**
 * @last_review 3.0.0
 */
function fpp_get_post_author($post) {
        $user_info = get_userdata($post->post_author);
        return $user_info->user_login;
}

/**
 * @last_review 3.0.0
 */
function fpp_get_post_categories($post) {
        $categories = get_the_category($post->ID);
        $description = '';
        if (!empty($categories)) {
                $description = $categories[0]->cat_name;
                for ($i = 1; $i < sizeof($categories); ++$i)
                        $description .= ', '.$categories[$i]->cat_name;
        }
        return $description;
}

/**
 * @last_review 3.0.0
 */
function fpp_get_post_image($post) {
        if (!isset($image_url)) {
                preg_match('/<img .*src=["|\']([^"|\']+)/i', $post->post_content, $matches);
                if (!empty($matches[1])) return $matches[1];
        }

        if (!isset($image_url)) {
                $images = get_children('post_type=attachment&post_mime_type=image&post_parent='.$post->ID);
                if (!empty($images)) {
                        foreach ($images as $image_id => $value) {
                                $image = wp_get_attachment_image_src($image_id);
                                return $image[0];
                                break;
                        }
                }
        }
        return '';
}

/**
 * Render Facebook recognized meta tags (Open Graph protocol).
 * Facebooks uses them to refine shared links for example.
 *
 * @last_review 3.0.0
 */
function fpp_render_meta_tags($post) {
        $options = get_option('fpp_options');

        echo '<meta property="og:title" content="'.esc_attr($post->post_title)/*, ENT_COMPAT, 'UTF-8')*/.'"/>';
        
        $description = array();
        if ($options['show_post_author']) {
                $description[] = esc_attr(fpp_get_post_author($post))/*, ENT_COMPAT, 'UTF-8')*/;
        }
        if ($options['show_post_categories']) {
                $description[] = esc_attr(fpp_get_post_categories($post));/*, ENT_COMPAT, 'UTF-8')*/;
        }
        echo '<meta property="og:description" content="'.implode(' | ', $description).'"/>';
                
        if ($options['show_thumbnail'] == 'post') {
                $image_url = fpp_get_post_image($post, $options['show_thumbnail'] == 'gravatar');
        }
        else if ($options['show_thumbnail'] == 'gravatar') {
                preg_match('/<img .*src=["|\']([^"|\']+)/i', get_avatar($post->post_author), $matches);
                if (!empty($matches[1])) $image_url = $matches[1];
        }
        else if ($options['show_thumbnail'] == 'none') {
                $image_url = BASE_URL.'line.png';
        }
        if (!isset($image_url) or empty($image_url))
                 $image_url = $options['default_thumbnail_url'];
        echo '<meta property="og:image" content="'.esc_attr($image_url)/*, ENT_COMPAT, 'UTF-8')*/.'"/>';
}

/**
 * Renders a 'publish to facebook' checkbox. Renders the box only if 
 * the current post is a real post, not a page or something else.
 *
 * TODO: password protected -> disable checkbox? | plugin not configured -> disable checkbox
 */
function fpp_render_post_button() {
        global $post;
        
        $object_access_token = get_option('fpp_object_access_token');
        
        if (!array_pop(get_post_meta($post->ID, '_fpp_is_published'))) {
                echo '<label for="fpp_post_to_facebook"><img style="vertical-align:middle; margin:2px" src="'.BASE_URL.'publish_icon.png" /> Publish to Facebook </label><input '.(((DEFAULT_POST_TO_FACEBOOK or fpp_get_default_publishing($post)) and !empty($object_access_token)) ? 'checked="checked"' : '').' type="checkbox" value="1" id="fpp_post_to_facebook" name="fpp_post_to_facebook" '.(empty($object_access_token) ? 'disabled="disabled"' : '').' />';
        } else {
                echo '<label for="fpp_post_to_facebook"><img style="vertical-align:middle; margin:2px" src="'.BASE_URL.'publish_icon.png" /> Post <em>again</em> to Facebook </label><input type="checkbox" value="1" id="fpp_post_to_facebook" name="fpp_post_to_facebook" '.(empty($object_access_token) ? 'disabled="disabled"' : '').' />';
        }
        if (empty($object_access_token)) echo '<div><em style="color:#aa6600">Facebook Page Publish is not set up.</em></div>';
        if ($post->post_status == "private")
                echo '<div><em style="color:#aa6600">Private posts are not published</em></div>';
        echo '<input type="hidden" name="fpp_send_from_admin" value="1" />';
        
        $error = get_option('fpp_error');
        if (!empty($error)) echo '<div class="error"><strong>Your Facebook Page Publish plugin reports an error. Please check your <a href="options-general.php?page='.plugin_basename(__FILE__).'">plugin settings</a>.</strong></div>';
}

/**
 * @last_review 3.0.0
 */
function fpp_validate_options($input) {
        $options = get_option('fpp_options');
        
        // Customization options:
        $options['show_thumbnail'] = $input['show_thumbnail'];
        $options['show_post_author'] = array_key_exists('show_post_author', $input) && !empty($input['show_post_author']);
        $options['show_post_categories'] = array_key_exists('show_post_categories', $input) && !empty($input['show_post_categories']);
        $options['default_thumbnail_url'] = trim($input['default_thumbnail_url']);
        
        // Validate customization options:
        if (substr($options['default_thumbnail_url'], 0, 4) !== 'http') 
                add_settings_error('fpp_options', 'customization_error', 'The given default thumbnail URL is not valid. Any valid URL has to start with http:// or https://.</p><p><font style="font-weight:normal">Facebook won\'t be able to display the choosen default thumbnail.</p>');
        
        // Connection options:
        if ($options['app_id'] != $input['app_id'] || $options['object_id'] != $input['object_id']) {
                update_option('fpp_object_access_token', '');
                update_option('fpp_profile_access_token', '');
        }
        $options['app_id'] = $input['app_id'];
        $options['object_id'] = $input['object_id'];
        $options['app_secret'] = $input['app_secret'];
        $options['app_id_valid'] = false;
        $options['object_id_valid'] = false;
        $options['object_type'] = '';
        $options['app_secret_valid'] = false;
        $options['ignore_ssl'] = array_key_exists('ignore_ssl', $input) && !empty($input['ignore_ssl']);
        $options['default_publishing'] = $input['default_publishing'];
        $options['default_publishing_categories'] = array_key_exists('default_publishing_categories', $input) ? $input['default_publishing_categories'] : array();
        
        // Validate connection options:
        try {
                if (!empty($options['app_id']) or !empty($options['object_id'])) {
                        $object_classification = fpp_classify_facebook_objects(array($options['app_id'], $options['object_id']));
                        $options['app_id_valid'] = $object_classification[$options['app_id']] == 'application';
                        $options['object_type'] = $object_classification[$options['object_id']];
                        $options['object_id_valid'] = (($options['object_type'] == 'profile') or ($options['object_type'] == 'page')); 
                        if (!$options['app_id_valid'])
                                throw new FacebookErrorException('Invalid application ID. Please refer to the <a target="_blank" href="'.BASE_URL.'setup.htm#app_id">detailed setup instructions</a>.');
                        if (!$options['object_id_valid'])
                                throw new FacebookErrorException('Invalid user or page ID. Please refer to the <a target="_blank" href="'.BASE_URL.'setup.htm#object_id">detailed setup instructions</a>.');
                }
                        
                $options['app_secret_valid'] = (!empty($options['app_secret']) && $options['app_id_valid']) ? fpp_is_valid_facebook_application($options['app_id'], $options['app_secret'], ADMIN_URL) : false;
                if (!$options['app_secret_valid'] && $options['app_id_valid'])
                        throw new FacebookErrorException('Invalid application secret. Please refer to the <a target="_blank" href="'.BASE_URL.'setup.htm#app_secret">detailed setup instructions</a>.');
                  
        } catch (CommunicationException $exception) {
                add_settings_error('fpp_options', 'connection_error', $exception->getMessage().'</p><p><font style="font-weight:normal">Your connection options could not be validated.</p>');
        }
        return $options;
}
?>