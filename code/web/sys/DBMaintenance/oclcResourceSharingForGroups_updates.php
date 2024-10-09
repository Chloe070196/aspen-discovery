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
    ];
}
