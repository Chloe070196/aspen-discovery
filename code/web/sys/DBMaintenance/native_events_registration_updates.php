<?php /** @noinspection SqlResolve */

function getNativeEventRegistrationtUpdates() {
	return [
		'create_user_native_event_instance_registration_table' => [
			'title' => 'Create The User Native Event Instance Registration Table',
			'description' => 'Adds the ability to save registration to native a native event instance',
			'sql' => [
				"CREATE TABLE IF NOT EXISTS user_native_event_instance_registration (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					userId INT NOT NULL,
					eventInstanceId INT NOT NULL,
					success TINYINT(1) DEFAULT NULL,
					attended TINYINT(1) DEFAULT NULL,
					cancelled TINYINT(1) DEFAULT NULL
				)ENGINE = InnoDB",
			],
		], // create_user_native_event_instance_registration_table
	];
}