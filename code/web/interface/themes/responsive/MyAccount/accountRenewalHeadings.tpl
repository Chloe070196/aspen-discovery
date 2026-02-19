{strip}
	<h2>
		{if $currentStep == 'start'}
			{translate text='Start' isPublicFacing=true}
		{elseif $currentVerificationCheck}
			{translate text='Verification Questions' isPublicFacing=true}
		{elseif $confirmContactInformation}
			{translate text='Verification Questions' isPublicFacing=true}
		{elseif $currentStep == 'done'}
			{translate text='Request Submission.' isPublicFacing=true}
		{else}
			{translate text='Thank you!.' isPublicFacing=true}
		{/if}
	</h2>
{/strip}