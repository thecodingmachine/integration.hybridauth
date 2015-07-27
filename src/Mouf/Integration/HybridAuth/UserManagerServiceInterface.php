<?php
namespace Mouf\Integration\HybridAuth;

/**
 * Objects implementing the UserManagerServiceInterface can edit (create/save) a user.
 *
 */
interface UserManagerServiceInterface {

	/**
	 * Creates or update the user $user.
	 * The User ID (in database) is returned.
	 *
	 * @param AdvancedUserInterface $user The user to save
	 * @return string The user ID
	 */
	public function saveUser(\Hybrid_User_Profile $user);
}
?>
