<?php
/** @noinspection SqlDialectInspection */

/** @noinspection PhpUnused */
function getUpdates26_03_00(): array {
	$now = time();

	return [
		/*'name' => [
			 'title' => '',
			 'description' => '',
			 'continueOnError' => false,
			 'sql' => [
				 ''
			 ]
		 ], //name*/

		//mark n

		//kirstien

		//kodi

		//yanjun

		//imani

		//galen

		//alexander
		'add_ability_for_admin_to_control_whether_holds_can_be_grouped' => [
			'title' => 'Add Ability for Admin to Control Whether Holds Can Be Grouped',
			'description' => 'Allow admin to control whether holds can be grouped',
			'sql' => [
				"ALTER TABLE library ADD COLUMN allowHoldsToBeGrouped TINYINT(1) DEFAULT 0",
			],
		], //add_ability_for_admin_to_control_whether_holds_can_be_grouped
		'add_grouped_hold_id_to_user_hold' => [
			'title' => 'Add Grouped Hold Id To User Hold',
			'description' => 'Add grouped hold id and visual hold group id to user hold',
			'sql' => [
				"ALTER TABLE user_hold ADD COLUMN holdGroupId INT(11) DEFAULT NULL",
				"ALTER TABLE user_hold ADD COLUMN visualHoldGroupId VARCHAR(50) DEFAULT NULL",
			],
		], //add_grouped_hold_id_to_user_hold

		//chloe

		//mark j

		//lucas


		//tomas

		//other


	];
}
