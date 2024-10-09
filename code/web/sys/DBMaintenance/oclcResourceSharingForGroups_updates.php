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
            // FIXME: edit weighting so permissions interact proerly with one another
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
        // '' => [
        //     'title' => 'Add the OCLC Resource Sharing For Groups Setting Table',
        //     'description' => 'Add a table to store the different OCLC resource settings (profiles) so that different libraries in one Aspen system can have or share different settings',
        //     // 'continueOnError' => true,
        //     'sql' => [
        //         "CREATE TABLE oclc_resource_sharing_for_groups_setting"
        //     ],
        // ],
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
