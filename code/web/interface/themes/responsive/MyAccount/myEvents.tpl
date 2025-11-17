{strip}
	{if !empty($loggedIn)}
	<div class="resultHead">
		<h1>{translate text='Your Events' isPublicFacing=true}</h1>

		<div class="page">
			<div class="row">
				<div class="form-group col-sm-6" id="eventsFilter">
					<select aria-label="{translate text="Filter Events" inAttribute=true isPublicFacing=true}" class="eventsFilter form-control" id="eventsFilter" name="accountEventFilter" onchange="return AspenDiscovery.Account.loadEvents(1, $('#eventsFilter option:selected').val())">
						<option value="upcoming" {if $eventsFilter == "upcoming"}selected="selected"{/if}>{translate text="Upcoming Events" isPublicFacing=true inAttribute=true}</option>
						<option value="past" {if $eventsFilter == "past"}selected="selected"{/if}>{translate text="Past Events" isPublicFacing=true inAttribute=true}</option>
						<option value="all" {if $eventsFilter == "all"}selected="selected"{/if}>{translate text="All Events" isPublicFacing=true inAttribute=true}</option>
					</select>
				</div>
			</div>
			{include file="./myEventsSection.tpl" sectionTitle='Your Saved Events' sectionName='Saved'}
			{include file="./myEventsSection.tpl" sectionTitle='Events You Registered For' sectionName='Registered'}
		</div>
	</div>
	{else}
	<div class="page">
		{translate text="You must sign in to view this information." isPublicFacing=true}<a href='/MyAccount/Login' class="btn btn-primary">{translate text="Sign In" isPublicFacing=true}</a>
	</div>
	{/if}
{/strip}