{strip}
	{if $showExpirationWarnings && $ilsSummary->isExpirationClose()}
		<div id="myAccountExpirationNotice">
			<div class="alert alert-warning">
				{if $ilsSummary->isExpired()}
					{if !empty($expiredMessage)}
						{translate text=$expiredMessage isPublicFacing=true}
					{else}
						{translate text="Your library card expired on %1%." 1=$ilsSummary->expirationDate|date_format:"%D" isPublicFacing=true}
					{/if}
				{else}
					{if !empty($expirationNearMessage)}
						{translate text=$expirationNearMessage isPublicFacing=true}
					{else}
						{translate text="Your library card will expire on %1%." 1=$ilsSummary->expirationDate|date_format:"%D" isPublicFacing=true}
					{/if}
				{/if}
			</div>
			{if $showRenewalLink}
				{if $useILSCardRenewalFlow} 
					<div class="text-center">
						<a class="btn btn-info btn-sm" onclick="return AspenDiscovery.Account.displayCardRenewalModal()">{translate text="Renew your card" isPublicFacing=true}</a>
					</div>
				{else}
					<div class="text-center">
						<a class="btn btn-info btn-sm" href="{$cardRenewalLink}">{translate text="Renew your card" isPublicFacing=true}</a>
					</div>
				{/if}
			{/if}
		</div>
		<hr class="menu">
	{/if}
{/strip}