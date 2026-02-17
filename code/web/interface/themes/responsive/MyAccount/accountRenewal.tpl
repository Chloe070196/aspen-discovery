{strip}
	<div id="main-content">
		{if empty($loggedIn)}
			<div class="page">
				{translate text="You must sign in to view this information." isPublicFacing=true}<a href='/MyAccount/Login' class="btn btn-primary">{translate text="Sign In" isPublicFacing=true}</a>
			</div>
		{elseif $ilsUnsupported} 
			<div class="page">
				{translate text="Card and account renewals are not supported." isPublicFacing=true}
			</div>
		{else}
		<h1>{translate text='Account Renewal' isPublicFacing=true}</h1>
		<form method="post" action="/MyAccount/AccountRenewal">
			{if $currentStep == 'verification'}
				{* {include file='MyAccount/accountRenewalVerificationStep.tpl'} *}
			{else if $currentStep == 'contactInformationConfirmation'}
				{* {include file='MyAccount/contactInformationForm.tpl'}*}
			{else if $currentStep == 'requestSubmission'}
				{* {include file='MyAccount/submitAccountRenewalRequest.tpl'} *}
			{else if $currentStep == 'done'}
				{* {include file='MyAccount/accountRenewalOutcome.tpl'} *}
			{else}
				<p>{translate text='Welcome to the account renewal process. Please click Continue to begin.'}</p>
			{/if}
			{include file="MyAccount/accountRenewalNavigation.tpl"}
		</form>
		{/if}
	</div>
{/strip}
