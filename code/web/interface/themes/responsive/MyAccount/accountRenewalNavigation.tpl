{strip}
	<div class="form-group row">
		<div class="col-sm-12 text-right">
			{*Allows users to leave the workflow entirely*}
			<a href="/MyAccount/Home" class="btn btn-default">
				{if $currentStep == "done"}
					{translate text="Finish" isPublicFacing=true}
				{else}
					{translate text="Cancel" isPublicFacing=true}
				{/if}
			</a>
			{*Allows users to move backwards in the flow once started*}
			{if $currentStep != "start" && $currentStep != "done"}
				<button type="submit" name="navigation" value="{$previousStep}" class="btn btn-default">{translate text="Back" isPublicFacing=true}</button>
			{/if}
			{*Allows users to move forwards in the flow*}
			{if $currentStep != "done"}
				<button type="submit" name="navigation" value="{$nextStep}" class="btn btn-default">
					{if $currentStep == "submit"}
						{translate text="Submit Application" isPublicFacing=true}
					{else}
						{translate text="Continue" isPublicFacing=true}
					{/if}
				</button>
			{/if}
		</div>
	</div>

	{* Hidden fields to manage state. The controller uses these to determine next action. *}
	<input type="hidden" name="currentStep" value="{$currentStep|escape}">
	<input type="hidden" name="previousStep" value="{$previousStep|escape}">
	<input type="hidden" name="nextStep" value="{$nextStep|escape}">
{/strip}
