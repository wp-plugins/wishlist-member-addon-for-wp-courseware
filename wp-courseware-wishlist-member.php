<?php
/*
 * Plugin Name: WP Courseware - Wishlist Member Add On
 * Version: 1.0
 * Plugin URI: http://flyplugins.com
 * Description: The official extension for <strong>WP Courseware</strong> to add support for the <strong>Wishlist Member membership plugin</strong> for WordPress.
 * Author: Fly Plugins
 * Author URI: http://flyplugins.com
 */
/*
 Copyright 2013 Fly Plugins - Evolution Media Services, LLC

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


// Main parent class
include_once 'class_members.inc.php';

// Hook to load the class
// Set to priority of 1 so that it works correctly with WishList Member
// that specifically needs this to be a priority of 1.
add_action('init', 'WPCW_Members_WishList_init', 1);


/**
 * Initialise the membership plugin, only loaded if WP Courseware 
 * exists and is loading correctly.
 */
function WPCW_Members_WishList_init()
{
	$item = new WPCW_Members_WishList();
	
	// Check for WP Courseware
	if (!$item->found_wpcourseware()) {
		$item->attach_showWPCWNotDetectedMessage();
		return;
	}
	
	// Not found the membership tool
	if (!$item->found_membershipTool()) {
		$item->attach_showToolNotDetectedMessage();
		return;
	}
	
	// Found the tool and WP Coursewar, attach.
	$item->attachToTools();
}


/**
 * Membership class that handles the specifics of the WishList Member WordPress plugin and
 * handling the data for levels for that plugin.
 */
class WPCW_Members_WishList extends WPCW_Members
{
	const GLUE_VERSION  	= 1.00; 
	const EXTENSION_NAME 	= 'WishList Member';
	const EXTENSION_ID 		= 'WPCW_members_wishlist';
	
	
	
	/**
	 * Main constructor for this class.
	 */
	function __construct()
	{
		// Initialise using the parent constructor 
		parent::__construct(WPCW_Members_WishList::EXTENSION_NAME, WPCW_Members_WishList::EXTENSION_ID, WPCW_Members_WishList::GLUE_VERSION);
	}
	
	
	
	/**
	 * Get the membership levels for this specific membership plugin. (id => array (of details))
	 */
	protected function getMembershipLevels()
	{
		$levelData = WLMAPI::GetLevels();		
		
		if ($levelData && count($levelData) > 0)
		{
			$levelDataStructured = array();
			
			// Format the data in a way that we expect and can process
			foreach ($levelData as $levelDatum)
			{
				$levelItem = array();
				$levelItem['name'] 	= $levelDatum['name'];
				$levelItem['id'] 	= $levelDatum['ID'];
				$levelItem['raw'] 	= $levelDatum;
								
				$levelDataStructured[$levelDatum['ID']] = $levelItem;
			}
			
			return $levelDataStructured;
		}
		
		return false;
	}

	
	/**
	 * Function called to attach hooks for handling when a user is updated or created.
	 */	
	protected function attach_updateUserCourseAccess()
	{
		// Events called whenever the user levels are changed, which updates the user access.
		add_action('wishlistmember_add_user_levels', 		array($this, 'handle_updateUserCourseAccess'), 10, 2);
		add_action('wishlistmember_remove_user_levels', 	array($this, 'handle_updateUserCourseAccess'), 10, 2);
		add_action('wishlistmember_unapprove_user_levels', 	array($this, 'handle_updateUserCourseAccess'), 10, 2);
		add_action('wishlistmember_approve_user_levels', 	array($this, 'handle_updateUserCourseAccess'), 10, 2);
		add_action('wishlistmember_unconfirm_user_levels', 	array($this, 'handle_updateUserCourseAccess'), 10, 2);
		add_action('wishlistmember_confirm_user_levels', 	array($this, 'handle_updateUserCourseAccess'), 10, 2);
		add_action('wishlistmember_cancel_user_levels', 	array($this, 'handle_updateUserCourseAccess'), 10, 2);
		add_action('wishlistmember_uncancel_user_levels', 	array($this, 'handle_updateUserCourseAccess'), 10, 2);
	}
	

	/**
	 * Function just for handling the membership callback, to interpret the parameters
	 * for the class to take over.
	 * 
	 * @param Integer $id The ID if the user being changed.
	 * @param Array $levels The list of levels for the user.
	 */
	public function handle_updateUserCourseAccess($id, $levels)
	{
		// Get all user levels, with IDs.
		$userLevels = WLMAPI::GetUserLevels($id, 'all', 'skus');
		
		// Over to the parent class to handle the sync of data.
		parent::handle_courseSync($id, $userLevels);
	}
	
	
	/**
	 * Detect presence of the membership plugin.
	 */
	public function found_membershipTool()
	{
		return class_exists('WLMAPI');
	}
	
	
}


?>