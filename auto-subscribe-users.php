<?php
/*
Plugin Name: Auto Subscribe Users by Email
Plugin URI: https://github.com/sanghviharshit/auto-subscribe-users
Description: Automatically subscribes users so that they can be notified of any new content on the site.
Author: Harshit Sanghvi
Author URI: https://sanghviharshit.com
Version: 0.0.6
License: GNU General Public License (Version 3 - GPLv3)
*/

/*
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 3 - GPLv3) as published by
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
 * @license GNU General Public License (Version 3 - GPLv3) {@link http://www.gnu.org/licenses/gpl-3.0.html}
 */

define( 'AUTO_SUBSCRIBE_USERS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AUTO_SUBSCRIBE_USERS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

class Auto_Subscribe_Users {


		private $sbe_key;
		private $esn_key;

    /**
     * Constructor.
     */
    function __construct() {
    	 	$this->sbe_key = 'asu_sbe';
    	 	$this->esn_key = 'asu_esn';

        add_action( 'init', array( $this, 'init' ), 1 );
    }

    /**
     * Initiate plugin.
     *
     * @return void
     */
    function init() {
        //In multisite, auto-subscribe on 'wpmu_new_user' or 'user_register' hook can only work if the subscribe-by-email plugin is also network activated.
        if(is_multisite()) {
            add_action( 'wpmu_new_blog', array( $this, 'add_admin_to_subscribers' ), 10, 6 );
        } else {
            add_action( 'user_register', array( $this, 'add_user_to_subscribers' ), 10, 6 );
        }
        
        
    }

    /**
     * Example of wpmu_new_user usage
     * 
     * @param int    $user_id User ID.
     */
    function add_user_to_subscribers( $user_id ) {
        // Makes sure the plugin is defined before trying to use it
        /*
        if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
            require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
        }
        if(!is_plugin_active_for_network(AUTO_SUBSCRIBE_USERS_PLUGIN_BASENAME))
        */
        if(is_multisite()) {
            return;
        }

        /** 
         * switch_to_blog() doesn't switch active plugins - https://core.trac.wordpress.org/ticket/14941
         */
        /*
        if(is_multisite()) {
            
             // Main blog's blog id - http://wordpress.stackexchange.com/questions/5094/how-to-get-the-main-blogs-id-and-db-prefix-from-a-mu-plugin
             
            $main_blog_id = BLOG_ID_CURRENT_SITE;
            if ( is_main_site($main_blog_id) ) {
                switch_to_blog($main_blog_id);
            }
            else {
                return;
            }
        }
        */
        
        if( function_exists( 'es_sync_registereduser') ) {
            // If Email Subscribers & Newsletter Plugin is active on the main site
            $es_c_emailsubscribers = get_option('es_c_emailsubscribers', 'norecord');
            self::write_log("es_c_emailsubscribers" . $es_c_emailsubscribers);
            if($es_c_emailsubscribers == 'norecord' || $es_c_emailsubscribers == "" || (!empty($es_c_emailsubscribers['es_registered']) && $es_c_emailsubscribers['es_registered'] <> "YES"))  {
                $es_c_emailsubscribers_bak = $es_c_emailsubscribers;
                $es_c_emailsubscribers['es_registered'] = "YES";
                $es_c_emailsubscribers['es_registered_group'] = "Auto Subscribe";
                update_option('es_c_emailsubscribers', $es_c_emailsubscribers);
                self::write_log("$es_c_emailsubscribers" . $es_c_emailsubscribers);
                es_sync_registereduser($user_id);
                update_option('es_c_emailsubscribers', $$es_c_emailsubscribers_bak);
            }
            
        } else if ( class_exists( 'Incsub_Subscribe_By_Email' ) && method_exists( 'Incsub_Subscribe_By_Email', 'subscribe_user' ) && method_exists( 'Incsub_Subscribe_By_Email', 'send_confirmation_mail' ) ) {
            // If WPMYU's Subscribe by Email plugin is not active, there's nothing to do.
            $user_info = get_userdata($user_id);
            //Force email confirmation
            $subscription_id = Incsub_Subscribe_By_Email::subscribe_user( $user_info->user_email, __( 'Auto Subscribe', INCSUB_SBE_LANG_DOMAIN ), __( 'Auto Subscribed on User Registered Action', INCSUB_SBE_LANG_DOMAIN ), true );
            //Incsub_Subscribe_By_Email::send_confirmation_mail( $subscription_id, $force = true );

        }
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
        // Makes sure the plugin is defined before trying to use it
        if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
            require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
        }
        if(!is_multisite() || !is_plugin_active_for_network(AUTO_SUBSCRIBE_USERS_PLUGIN_BASENAME)) {
            return;
        }

  			$user_esn = get_user_meta($user_id, $this->esn_key, $single = true);

        // Check if Email Subscribers & Newsletter Plugin is active on the main site
        if( $user_esn == '' || $user_esn != 'true' ) {
        	if( function_exists( 'es_sync_registereduser') ) {
        		
        		$es_c_emailsubscribers = get_option('es_c_emailsubscribers', 'norecord');
            //self::write_log("es_c_emailsubscribers" . $es_c_emailsubscribers);
            if($es_c_emailsubscribers == 'norecord' || $es_c_emailsubscribers == "" || (!empty($es_c_emailsubscribers['es_registered']) && $es_c_emailsubscribers['es_registered'] <> "YES"))  {
                $es_c_emailsubscribers_bak = $es_c_emailsubscribers;
                $es_c_emailsubscribers['es_registered'] = "YES";
                $es_c_emailsubscribers['es_registered_group'] = "Auto Subscribe";
                update_option('es_c_emailsubscribers', $es_c_emailsubscribers);
                self::write_log("$es_c_emailsubscribers" . $es_c_emailsubscribers);
                es_sync_registereduser($user_id);
                update_option('es_c_emailsubscribers', $$es_c_emailsubscribers_bak);
            
                //Add auto subscribed boolean to user meta, so if the user unsubscribes, we don't auto subscribe that user next time the user registers new site.
                update_user_meta( $user_id, $this->esn_key, 'true' );
            }
          }
        }

  			$user_sbe = get_user_meta($user_id, $this->sbe_key, $single = true);
  			
				if( $user_sbe == '' || $user_sbe != 'true' ) {
	        // Check if WPMU Subscribe By Email plugin is active
	        if ( class_exists( 'Incsub_Subscribe_By_Email' ) && method_exists( 'Incsub_Subscribe_By_Email', 'subscribe_user' ) && method_exists( 'Incsub_Subscribe_By_Email', 'send_confirmation_mail' ) ) {
	            // The Subscription plugin has to be active on the main site. If WPMYU's Subscribe by Email plugin is not active, there's nothing to do. 
	            $user_info = get_userdata($user_id);
	            //Force email confirmation
	            $subscription_id = Incsub_Subscribe_By_Email::subscribe_user( $user_info->user_email, __( 'Auto Subscribe', INCSUB_SBE_LANG_DOMAIN ), __( 'Auto Subscribed on Create Site Action', INCSUB_SBE_LANG_DOMAIN ), true );
	            //ToDo: settings page
	            //Incsub_Subscribe_By_Email::send_confirmation_mail( $subscription_id, $force = true );

	            //Add auto subscribed boolean to user meta, so if the user unsubsribes, we don't auto subscribe that user next time the user registers new site.
              update_user_meta( $user_id, $this->sbe_key, 'true' );
	        }
	      }

    }

    public static function write_log ( $log )  {
        if ( true === WP_DEBUG ) {
            if ( is_array( $log ) || is_object( $log ) ) {
                error_log( print_r( $log, true ) );
            } else {
                error_log( $log );
            }
        }
    }
}

global $auto_subscribe_users;
$auto_subscribe_users = new Auto_Subscribe_Users();

