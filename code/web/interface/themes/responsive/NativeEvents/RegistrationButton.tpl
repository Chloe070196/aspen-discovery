{strip}
	<button id="native-events-registration-button-{$userId}" type="button" class="btn btn-xs btn-primary" onclick="return AspenDiscovery.Account.registerToEvent({$eventInstanceId}, {$userId});">{if !empty($payFinesLinkText)}{$payFinesLinkText}{else}{translate text = 'Register' isPublicFacing=true}{/if}</button>
{/strip}