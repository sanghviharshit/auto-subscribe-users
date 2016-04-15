<?php
/*
Plugin Name: Auto Subscribe Users by Email
Plugin URI: https://github.com/sanghviharshit/auto-subscribe-users
Description: Automatically subscribes all users who registers to your site or gets added by the site admins, so that they can be notified of any new content on the site using WPMU's Subscribe by Email plugin.
Author: Harshit Sanghvi
Author URI: https://about.me/harshit
Version: 0.0.2
License: GNU General Public License (Version 2 - GPLv2)
*/

/*
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/**
 * Auto_Subscribe_Users
 *
 * @package Auto_Subscribe_Users
 * @author Harshit Sanghvi {@link http://about.me/harshit}
 * @license GNU General Public License (Version 2 - GPLv2) {@link http://www.gnu.org/licenses/gpl-2.0.html}
 */

define( 'AUTO_SUBSCRIBE_USERS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

class Auto_Subscribe_Users {

    /**
     * Constructor.
     */
    function __construct() {
        $this -> init();
        add_action( 'wpmu_new_blog', array( $this, 'add_admin_to_subscribers' ), 10, 6 );
        //add_action( 'wpmu_new_user', array( $this, 'add_user_to_subscribers' ), 10, 6 );
        add_action( 'user_register', array( $this, 'add_user_to_subscribers' ), 10, 6 );
    }

    /**
     * Initiate plugin.
     *
     * @return void
     */
    function init() {

    }

    /**
     * Example of wpmu_new_user usage
     * 
     * @param int    $user_id User ID.
     */
    function add_user_to_subscribers( $user_id ) {
        
        // If WPMYU's Subscribe by Email plugin is not active, there's nothing to do.
        if ( ! class_exists( 'Incsub_Subscribe_By_Email' ) || ! method_exists( 'Incsub_Subscribe_By_Email', 'subscribe_user' ) || ! method_exists( 'Incsub_Subscribe_By_Email', 'send_confirmation_mail' ) ) {
            return;
        }

        $user_info = get_userdata($user_id);
        //Force email confirmation
        $subscription_id = Incsub_Subscribe_By_Email::subscribe_user( $user_info->user_email, __( 'Auto Subscribe', INCSUB_SBE_LANG_DOMAIN ), __( 'Auto Subscribed on User Registered Action', INCSUB_SBE_LANG_DOMAIN ), true );
        //Incsub_Subscribe_By_Email::send_confirmation_mail( $subscription_id, $force = true );

    }

    /**
     * Example of wpmu_new_blog usage
     * 
     * @param int    $blog_id Blog ID.
     * @param int    $user_id User ID.
     * @param string $domain  Site domain.
     * @param string $path    Site path.
     * @param int    $site_id Site ID. Only relevant on multi-network installs.
     * @param array  $meta    Meta data. Used to set initial site options.
     */
    function add_admin_to_subscribers( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
        
        // If WPMYU's Subscribe by Email plugin is not active, there's nothing to do.
        if ( ! class_exists( 'Incsub_Subscribe_By_Email' ) || ! method_exists( 'Incsub_Subscribe_By_Email', 'subscribe_user' ) || ! method_exists( 'Incsub_Subscribe_By_Email', 'send_confirmation_mail' ) ) {
            return;
        }

        $user_info = get_userdata($user_id);
        //Force email confirmation
        $subscription_id = Incsub_Subscribe_By_Email::subscribe_user( $user_info->user_email, __( 'Auto Subscribe', INCSUB_SBE_LANG_DOMAIN ), __( 'Auto Subscribed on Create Site Action', INCSUB_SBE_LANG_DOMAIN ), true );
        //Incsub_Subscribe_By_Email::send_confirmation_mail( $subscription_id, $force = true );

    }

}

global $auto_subscribe_users;
$auto_subscribe_users = new Auto_Subscribe_Users();

