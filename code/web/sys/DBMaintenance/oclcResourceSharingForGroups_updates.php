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
    ];
}
