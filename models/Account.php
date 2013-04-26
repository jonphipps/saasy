<?php

namespace saasy;

/**
 * Contains the logic around managing accounts.
 *
 * Fields:
 *
 * - id - Auto-incrementing ID for each account row
 * - user - User ID, ref to \User
 * - customer - Customer ID, ref to saasy\Customer
 * - type - 'owner' or 'member' (expandable)
 * - enabled - 0 = no, 1 = yes
 *
 * Indexed by user and customer. User/customer combo must be unique.
 */
class Account extends \Model {
	public $table = '#prefix#saasy_acct';

	/**
	 * Return the associated user.
	 */
	public function user () {
		return new \User ($this->user);
	}

	/**
	 * Return the associated customer.
	 */
	public function customer () {
		return new Customer ($this->customer);
	}

	/**
	 * Enable this user account.
	 */
	public function enable () {
		$this->enabled = 1;
		return $this->put ();
	}

	/**
	 * Disable this user account.
	 */
	public function disable () {
		$this->enabled = 0;
		return $this->put ();
	}

	/**
	 * Returns a correctly sized profile photo if available.
	 * If not, returns a default profile photo.
	 */
	public function photo ($width = 160, $height = 160) {
		$files = glob ('cache/saasy/accounts/' . $this->id . '.{jpg,png,gif}', GLOB_BRACE);
		if (count ($files) > 0) {
			$photo = array_shift ($files);
			$ext = strtolower (pathinfo ($photo, PATHINFO_EXTENSION));
			return '/' . \Image::resize ($photo, $width, $height, 'cover', $ext);
		}
		return '/apps/saasy/pix/profile.png';
	}

	/**
	 * Save a new profile photo.
	 */
	public function save_photo ($upload) {
		$ext = strtolower (pathinfo ($upload['name'], PATHINFO_EXTENSION));

		if (! is_dir ('cache/saasy')) {
			mkdir ('cache/saasy');
			chmod ('cache/saasy', 0777);
		}

		if (! is_dir ('cache/saasy/accounts')) {
			mkdir ('cache/saasy/accounts');
			chmod ('cache/saasy/accounts', 0777);
		}

		if (file_exists ('cache/saasy/accounts/' . $this->id . '.jpg')) {
			unlink ('cache/saasy/accounts/' . $this->id . '.jpg');
		}
		if (file_exists ('cache/saasy/accounts/' . $this->id . '.png')) {
			unlink ('cache/saasy/accounts/' . $this->id . '.png');
		}
		if (file_exists ('cache/saasy/accounts/' . $this->id . '.gif')) {
			unlink ('cache/saasy/accounts/' . $this->id . '.gif');
		}

		if (! move_uploaded_file (
			$upload['tmp_name'],
			'cache/saasy/accounts/' . $this->id . '.' . $ext
		)) {
			return false;
		}
		chmod ('cache/saasy/accounts/' . $this->id . '.' . $ext, 0666);
		return true;
	}

	/**
	 * Remove the profile photo.
	 */
	public function remove_photo () {
		if (file_exists ('cache/saasy/accounts/' . $this->id . '.jpg')) {
			unlink ('cache/saasy/accounts/' . $this->id . '.jpg');
		}
		if (file_exists ('cache/saasy/accounts/' . $this->id . '.png')) {
			unlink ('cache/saasy/accounts/' . $this->id . '.png');
		}
		if (file_exists ('cache/saasy/accounts/' . $this->id . '.gif')) {
			unlink ('cache/saasy/accounts/' . $this->id . '.gif');
		}
	}
}

?>