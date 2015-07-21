<?php
/*
Plugin Name: WordPress Tribe Events API Plugin
Plugin URI:
Description: Trigger Tribe Event update upon new post through WP XML-RPC API
Version: 1.0
Author: Joshua Padgett
Author URI: https://www.github.com/Chef
License: Apache License, Version 2.0
*/
/*
Copyright 2015 CHEF

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*/

if(!class_exists('WP_Tribe_Events_Plugin')) {
  class WP_Tribe_Events_Plugin {
    /**
     * Construct the plugin object
     */
    public function __construct() {
      add_action('save_post_tribe_events', array($this, 'call_tribe_api'), 10, 3);
    }

    /**
     * Activate the plugin
     */
    public static function activate() {
      add_action('save_post_tribe_events', array($this, 'call_tribe_api'), 10, 3);
    }

    /**
     * Deactivate the plugin
     */
    public static function deactivate() {
      remove_action('save_post_tribe_events', array($this, 'call_tribe_api'), 10, 3);
    }

    /**
     * Call the Tribe API to update our meta
     */
    public function call_tribe_api($post_id, $post, $update) {
      //Get our temporary meta
      /* Tribe sets these defaults, start/end date seem to be the only required fields
      * _EventShowMapLink	''
      * _EventShowMap	''
      * _EventStartDate	'<postdate> 08:00:00'
      * _EventEndDate	'<postdate> 17:00:00'
      * _EventDuration 32400
      * _EventVenueID	0
      * _EventCurrencySymbol '$
      * _EventCurrencyPosition 'prefix'
      * _EventCost ''
      * _EventURL	''
      * _EventOrganizerID	0
      */
      $data = get_metadata('post', $post_id);

      //Disable our hook as saveEventMeta fires wp_update_post
      remove_action('save_post_tribe_event', array($this, 'call_tribe_api'), 10, 3);

      //Pass to Tribe Events API
      Tribe__Events__API::saveEventMeta($post_id, $data);

      //Reenable hook
      add_action('save_post_tribe_event', array($this, 'call_tribe_api'), 10, 3);
    }
  }
}

if(class_exists('WP_Tribe_Events_Plugin'))
{
  // Installation and uninstallation hooks
  register_activation_hook(__FILE__, array('WP_Tribe_Events_Plugin', 'activate'));
  register_deactivation_hook(__FILE__, array('WP_Tribe_Events_Plugin', 'deactivate'));

  // instantiate the plugin class
  $wp_tribe_events_plugin = new WP_Tribe_Events_Plugin();
}
