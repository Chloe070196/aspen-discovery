<?php
/** @noinspection SqlResolve */

function getOclcResourceSharingForGroupsUpdates()
{
    return [
        'create_oclc_resource_sharing_for_groups_module' => [
            'title' => 'Create OCLC Resource Sharing For Groups Module',
            'description' => 'Add OCLC Resource Sharing For Groups to the list of modules',
            'sql' => [
                "INSERT INTO modules (name) VALUES ('OCLC Resource Sharing For Groups')",
            ],
        ],
        'create_oclc_resource_sharing_for_groups_permissions' => [
            'title' => 'Create OCLC Resource Sharing For Groups Permissions',
            'description' => 'Add an OCLC Resource Sharing For Groups permission section containing the permissions to do with this module',
            'sql' => [
                "INSERT INTO permissions (name, sectionName, requiredModule, weight, description) VALUES ( 'Administer OCLC Resource Sharing For Groups Settings','ILL Integration','OCLC Resource Sharing For Groups', 0, 'Allows the user to administer the integration with OCLC Resource Sharing For Groups')",
                "INSERT INTO permissions (name, sectionName, requiredModule, weight, description) VALUES ( 'Administer OCLC Resource Sharing For Groups Forms','ILL Integration','OCLC Resource Sharing For Groups', 0, 'Allows the user to administer the OCLC Resource Sharing For Groups ILL request forms')",
            ],
            // TODO:consider an 'administer forms for their library' permission
        ],
		'create_oclc_resource_sharing_for_groups_form_table' => [
			'title' => 'Add the OCLC Resource Sharing For Groups Form Table',
			'description' => 'Add a table to store the forms created by admins to keep track of which optional fields should be displayed',
			'sql' => [
				"CREATE TABLE oclc_resource_sharing_for_groups_form (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					name VARCHAR(50),
					introText TEXT,
					showAuthor BOOLEAN,
					showEdition TINYINT(1) DEFAULT 0,
					showPublisher TINYINT(1) DEFAULT 0,
					showIsbn TINYINT(1) DEFAULT 0,
					showIssn TINYINT(1) DEFAULT 0,
					showOclcNumber TINYINT(1) DEFAULT 0,
					showAcceptFee TINYINT(1) DEFAULT 0,
					showMaximumFee TINYINT(1) DEFAULT 0,
					showCatalogKey TINYINT(1) DEFAULT 0,
					feeInformationText TEXT
				)"
			]
		],
        // TODO: write columns for each table  below
		'add_oclc_resource_sharing_for_groups_settings_id_to_library' => [
			'title' => 'Add OCLC Resource Sharing For Groups Settings Id To Library',
			'description' => 'Add an oclcResourceSharingForGroupsSettingsId property to libraries so that they can be assigned the relevant OCLC Resource Sharing For Groups Setting',
			'sql' => [
				"ALTER TABLE library ADD oclcResourceSharingForGroupsSettingsId INT NOT NULL DEFAULT -1",
			],
		],
		'add_oclc_resource_sharing_for_groups_form_id_to_library' => [
			'title' => 'Add OCLC Resource Sharing For Groups Form Id To Library',
			'description' => 'Add an oclcResourceSharingForGroupsFormId property to libraries so that they can be assigned the relevant OCLC Resource Sharing For Groups Form',
			'sql' => [
				"ALTER TABLE library ADD oclcResourceSharingForGroupsFormId INT NOT NULL DEFAULT -1",
			],
		],
		'create_oclc_resource_sharing_for_groups_setting_table' => [
			'title' => 'Add the OCLC Resource Sharing For Groups Setting Table',
			'description' => 'Add a table to store the different OCLC resource settings (profiles) so that different libraries in one Aspen system can have or share different settings',
			'sql' => [
				"CREATE TABLE oclc_resource_sharing_for_groups_setting (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					name VARCHAR(50),
					clientKey VARCHAR(255) NOT NULL,
					clientSecret VARCHAR(255) NOT NULL,
					oclcSymbol VARCHAR(50) NOT NULL,
					oclcRegistryId INT NOT NULL,
					serviceBaseUrl VARCHAR(255) NOT NULL,
					authBaseUrl VARCHAR(255) NOT NULL DEFAULT 'https://oauth.oclc.org/',
					urlResourceOwnerDetails VARCHAR(255),
					scopes VARCHAR(255) NOT NULL DEFAULT 'resource-sharing:my-requests resource-sharing:create-requests resource-sharing:manage-requests resource-sharing:read-requests resource-sharing:search-requests',
					expirationDate DATETIME NOT NULL
				)"
			],
		],
        // '' => [
        //     'title' => 'Add the User OCLC Resource Sharing For Groups Request Table',
        //     'description' => ' Add a table to store the ILL requests made by a patron via the OCLC Resource Sharing Request API',
        //     // 'continueOnError' => true,
        //     'sql' => [
        //         "CREATE TABLE user_oclc_resource_sharing_for_groups_request"
        //     ],
        // ],

    ];
}
