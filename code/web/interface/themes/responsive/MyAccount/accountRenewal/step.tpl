{strip}
	<p>{translate text="{$currentStep.description}" isPublicFacing=true}</p>
    {if $currentStep.name == "verification_check"}
    	<button type="button" id="noButton" class="btn btn-default">{translate text="No" isPublicFacing=true}</button>
    	<button type="button" id="yesButton" class="btn btn-default">{translate text="Yes" isPublicFacing=true}</button>
    	<input type="hidden" name="userAgrees" id="userAgrees" value="">
    {elseif $currentStep.name == 'verifyContactInformation'}
		{if !empty($patronUpdateForm)}
			{$patronUpdateForm}
		{else}
			{include file="MyAccount/contactInformationForm.tpl"}
		{/if}
    {/if}
{/strip}				
