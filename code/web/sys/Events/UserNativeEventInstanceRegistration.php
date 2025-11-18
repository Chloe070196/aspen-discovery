<?php /** @noinspection PhpMissingFieldTypeInspection */
require_once ROOT_DIR . '/sys/Events/EventInstance';

class UserNativeEventInstanceRegistration extends DataObject {
	public $__table = 'user_native_event_instance_registration';
	public $userId;
	public $eventInstanceId;
	public $success;
	public $attended;
	public $cancelled;
}