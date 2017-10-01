<?php namespace Look\Conversation\Models;

use Model;

use Backend\Models\User as StaffMemberModel;

/**
 * Class Settings
 */
class Settings extends Model
{
	public $implement = ['System.Behaviors.SettingsModel'];

	/**
	 * @var string $settingsCode Unique code to namespace settings under
	 */
	public $settingsCode = 'look_conversation_settings';

	/**
	 * @var string $settingsFields Reference to field configuration
	 */
	public $settingsFields = 'fields.yaml';

	/**
	 * Provide the options for settings that require a StaffMember ID
	 */
	protected function getStaffMemberOptions()
	{
		$options = [];
		$staffMembers = StaffMemberModel::all();
		foreach ($staffMembers as $staffMember) {
			$options[$staffMember->id] = $staffMember->fullName;
		}
		return $options;
	}

	/**
	 * Provide the options for the default_client_recipient setting
	 */
	public function getDefaultClientRecipientOptions()
	{
		return $this->getStaffMemberOptions();
	}

	/**
	 * Accessor for the default_client_recipient attribute
	 */
	public function getDefaultClientRecipientAttribute($value)
	{
		if (!$value) {
			return 1;
		} else {
			return $value;
		}
	}

	/**
	 * Provide the options for the system_messages_from setting
	 */
	public function getSystemMessagesFromOptions()
	{
		return $this->getStaffMemberOptions();
	}

	/**
	 * Accessor for the system_messages_from attribute
	 */
	public function getSystemMessagesFromAttribute($value)
	{
		if (!$value) {
			return 1;
		} else {
			return $value;
		}
	}
}