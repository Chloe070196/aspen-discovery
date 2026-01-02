<?php /** @noinspection SqlResolve */

function getAspenEventRegistrationUpdates() {
	return [
		'create_user_aspen_event_instance_registrations_table' => [
			'title' => 'Create the User Aspen Event Instance Registrations table',
			'description' => 'Adds the ability to save registration to aspen a aspen event instance',
			'sql' => [
				'CREATE TABLE IF NOT EXISTS user_aspen_event_instance_registrations (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					userId INT NOT NULL,
					eventInstanceId INT NOT NULL,
					success TINYINT(1) DEFAULT NULL,
					attended TINYINT(1) DEFAULT NULL,
					cancelled TINYINT(1) DEFAULT NULL
				)ENGINE = InnoDB',
			],
		], // create_user_aspen_event_instance_registrations_table
		'create_aspen_event_settings_table' => [
			'title' => 'Define events settings for Aspen Native Events module',
			'description' => 'Initial setup of the Aspen Native Events',
			'sql' => [
				'CREATE TABLE IF NOT EXISTS aspen_event_settings (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					name VARCHAR(100) NOT NULL UNIQUE,
					numberOfDaysToIndex INT DEFAULT 365,
					runFullUpdate TINYINT(1) DEFAULT 0,
					lastUpdateOfAllEvents INT,
					lastUpdateOfChangedEvents INT,
    				registrationModalBody mediumtext
				) ENGINE = INNODB',
			],
		], // create_aspen_event_settings_table
		'add_registrationRequired_to_events' => [
			'title' => 'Add registrationRequired to Events',
			'description' => 'Add an registration required column to events',
			'sql' => [
				'ALTER TABLE event ADD COLUMN registrationRequired TINYINT(1) DEFAULT 0',
			],
		], // add_registrationRequired_to_events
		'add_numberOfSeats_to_events' => [
			'title' => 'Add numberOfSeats to Events and Event Instances',
			'description' => 'Add number of seats columns for capacity management. NULL or 0 means unlimited.',
			'sql' => [
				'ALTER TABLE event ADD COLUMN numberOfSeats INT DEFAULT NULL',
				'ALTER TABLE event_instance ADD COLUMN numberOfSeats INT DEFAULT NULL',
			],
		], // add_numberOfSeats_to_events 
		'add_fieldUse_to_event_field' => [
			'title' => 'Add fieldUse to Event Fields',
			'description' => 'Add an field use column to event fields',
			'sql' => [
				'ALTER TABLE event_field ADD COLUMN fieldUse TINYINT(1) DEFAULT 0',
			],
		], // add_fieldUse_to_event_field
		'add_fieldSetUse_to_event_field' => [
			'title' => 'Add fieldSetUse to Event Field Sets',
			'description' => 'Add an field use column to event field sets',
			'sql' => [
				'ALTER TABLE event_field_set ADD COLUMN fieldSetUse TINYINT(1) DEFAULT 0',
			],
		], // add_fieldSetUse_to_event_field
		'update_event_type_table' => [
			'title' => 'Update Event Type Table',
			'description' => 'Update the Event Type table to link to information and/or registration field set ids',
			'sql' => [
				'ALTER TABLE event_type RENAME COLUMN eventFieldSetId to eventInformationFieldSetId',
				'ALTER TABLE event_type ADD COLUMN eventRegistrationFieldSetId TINYINT(1) DEFAULT 0',
			],
		], // update_event_type_table
		'add_staffNotes_to_event_table' => [
			'title' => 'Add staffNotes to Event Table',
			'description' => 'Add staff only notes to events',
			'sql' => [
				'ALTER TABLE event ADD COLUMN staffNotes longtext DEFAULT NULL',
			],
		], // add_staffNotes_to_event_table
		'add_user_aspen_event_instance_registrations_event_field' => [
			'title' => 'Add User Events Instance Registration Event Field Table',
			'description' => 'Stores custom registration fields as tied to event instance registrations to record patron information',
			'sql' =>  [
				"CREATE TABLE IF NOT EXISTS user_aspen_event_instance_registrations_event_field (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					eventInstanceRegistrationId INT NOT NULL,
					eventFieldId INT NOT NULL,
					value TEXT DEFAULT NULL
				) ENGINE INNODB CHARACTER SET utf8 COLLATE utf8_general_ci",
			] 
		] //add_user_aspen_event_instance_registrations_event_field
	];
}