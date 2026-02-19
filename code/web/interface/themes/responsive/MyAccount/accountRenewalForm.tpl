{strip}				
    <form method="post" action="/MyAccount/AccountRenewal">
    	{if $currentStep == 'start'}
    		<p>{translate text='Welcome to the account renewal process. Please click Continue to begin.' isPublicFacing=true}</p>
    	{elseif $currentVerificationCheck}
    		<p>{translate text="{$currentVerificationCheck.description}" isPublicFacing=true}</p>
    		<button type="button" id="noButton" class="btn btn-default">{translate text="No" isPublicFacing=true}</button>
    		<button type="button" id="yesButton" class="btn btn-default">{translate text="Yes" isPublicFacing=true}</button>
    		<input type="hidden" name="userAgrees" id="userAgrees" value="">
    	{elseif $currentStep == 'done'}
    		<p>{translate text='All verification steps complete. Proceed to final renewal.' isPublicFacing=true}</p>
    	{else}
    		<p>{translate text='An unexpected error occurred.' isPublicFacing=true}</p>
    	{/if}
    	{include file="MyAccount/accountRenewalNavigation.tpl"}
    </form>
{/strip}				
