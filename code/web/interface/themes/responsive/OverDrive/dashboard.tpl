{strip}
	<div id="main-content" class="col-sm-12">
		<h1>{translate text="%1% Dashboard" 1=$readerName isAdminFacing=true}</h1>
		{include file="Admin/selectInterfaceForm.tpl"}
		<div class="row">
			<div class="col-xs-12">
				<a href="/OverDrive/UsageGraphs?stat=general&instance={$selectedInstance}" title="{translate text="Show Graph" inAttribute="true" isAdminFacing=true}"><i class="fas fa-chart-line"></i> {translate text="View as graph" isAdminFacing=true}</a>
			</div>
		</div>
		<div class="row">
			<div class="dashboardCategory col-sm-6">
				<div class="row">
					<div class="col-sm-10 col-sm-offset-1">
						<h2 class="dashboardCategoryLabel">
							{translate text="Active Users" isAdminFacing=true}
							{' '}
							<a href="/OverDrive/UsageGraphs?stat=activeUsers&instance={$selectedInstance}" title="{translate text="Show Active Users Graph" inAttribute="true" isAdminFacing=true}"><i class="fas fa-chart-line"></i></a>
						</h2>
					</div>
				</div>
				<div class="row">
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="This Month" isAdminFacing=true}</div>
						<div class="dashboardValue">{$activeUsersThisMonth}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="Last Month" isAdminFacing=true}</div>
						<div class="dashboardValue">{$activeUsersLastMonth}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="This Year" isAdminFacing=true}</div>
						<div class="dashboardValue">{$activeUsersThisYear}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="All Time" isAdminFacing=true}"</div>
						<div class="dashboardValue">{$activeUsersAllTime}</div>
					</div>
				</div>
			</div>

			<div class="dashboardCategory col-sm-6">
				<div class="row">
					<div class="col-sm-10 col-sm-offset-1">
						<h2 class="dashboardCategoryLabel">{translate text="Records With Usage" isAdminFacing=true}
							{' '}
							<a href="/OverDrive/UsageGraphs?stat=recordsWithUsage&instance={$selectedInstance}" title="{translate text="Show Records With Usage Graph" inAttribute="true" isAdminFacing=true}"><i class="fas fa-chart-line"></i></a>
						</h2>
					</div>
				</div>
				<div class="row">
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="This Month" isAdminFacing=true}</div>
						<div class="dashboardValue">{$activeRecordsThisMonth}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="Last Month" isAdminFacing=true}</div>
						<div class="dashboardValue">{$activeRecordsLastMonth}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="This Year" isAdminFacing=true}</div>
						<div class="dashboardValue">{$activeRecordsThisYear}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="All Time" isAdminFacing=true}</div>
						<div class="dashboardValue">{$activeRecordsAllTime}</div>
					</div>
				</div>
			</div>

			<div class="dashboardCategory col-sm-6">
				<div class="row">
					<div class="col-sm-10 col-sm-offset-1">
						<h2 class="dashboardCategoryLabel">{translate text="Loans" isAdminFacing=true}
							{' '}
							<a href="/OverDrive/UsageGraphs?stat=loans&instance={$selectedInstance}" title="{translate text="Show Loans Graph" inAttribute="true" isAdminFacing=true}"><i class="fas fa-chart-line"></i></a>
						</h2>
					</div>
				</div>
				<div class="row">
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="This Month" isAdminFacing=true}</div>
						<div class="dashboardValue">{$loansThisMonth}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="Last Month" isAdminFacing=true}</div>
						<div class="dashboardValue">{$loansLastMonth}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="This Year" isAdminFacing=true}</div>
						<div class="dashboardValue">{$loansThisYear}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="All Time" isAdminFacing=true}</div>
						<div class="dashboardValue">{$loansAllTime}</div>
					</div>
				</div>
			</div>

			<div class="dashboardCategory col-sm-6">
				<div class="row">
					<div class="col-sm-10 col-sm-offset-1">
						<h2 class="dashboardCategoryLabel">{translate text="Failed Loans" isAdminFacing=true}
							{' '}
							<a href="/OverDrive/UsageGraphs?stat=failedLoans&instance={$selectedInstance}" title="{translate text="Show Failed Loans Graph" inAttribute="true" isAdminFacing=true}"><i class="fas fa-chart-line"></i></a>
						</h2>
					</div>
				</div>
				<div class="row">
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="This Month" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsThisMonth->numFailedCheckouts|number_format}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="Last Month" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsLastMonth->numFailedCheckouts|number_format}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="This Year" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsThisYear->numFailedCheckouts|number_format}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="All Time" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsAllTime->numFailedCheckouts|number_format}</div>
					</div>
				</div>
			</div>

			<div class="dashboardCategory col-sm-6">
				<div class="row">
					<div class="col-sm-10 col-sm-offset-1">
						<h2 class="dashboardCategoryLabel">{translate text="Renewals" isAdminFacing=true}
							{' '}
							<a href="/OverDrive/UsageGraphs?stat=renewals&instance={$selectedInstance}" title="{translate text="Show Renewals Graph" inAttribute="true" isAdminFacing=true}"><i class="fas fa-chart-line"></i></a>
						</h2>
					</div>
				</div>
				<div class="row">
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="This Month" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsThisMonth->numRenewals|number_format}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="Last Month" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsLastMonth->numRenewals|number_format}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="This Year" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsThisYear->numRenewals|number_format}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="All Time" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsAllTime->numRenewals|number_format}</div>
					</div>
				</div>
			</div>

			<div class="dashboardCategory col-sm-6">
				<div class="row">
					<div class="col-sm-10 col-sm-offset-1">
						<h2 class="dashboardCategoryLabel">{translate text="Early Returns" isAdminFacing=true}
							{' '}
							<a href="/OverDrive/UsageGraphs?stat=earlyReturns&instance={$selectedInstance}" title="{translate text="Show Early Returns Graph" inAttribute="true" isAdminFacing=true}"><i class="fas fa-chart-line"></i></a>
						</h2>
					</div>
				</div>
				<div class="row">
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="This Month" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsThisMonth->numEarlyReturns|number_format}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="Last Month" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsLastMonth->numEarlyReturns|number_format}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="This Year" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsThisYear->numEarlyReturns|number_format}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="All Time" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsAllTime->numEarlyReturns|number_format}</div>
					</div>
				</div>
			</div>

			<div class="dashboardCategory col-sm-6">
				<div class="row">
					<div class="col-sm-10 col-sm-offset-1">
						<h2 class="dashboardCategoryLabel">{translate text="Holds" isAdminFacing=true}
							{' '}
							<a href="/OverDrive/UsageGraphs?stat=holds&instance={$selectedInstance}" title="{translate text="Show Holds Graph" inAttribute="true" isAdminFacing=true}"><i class="fas fa-chart-line"></i></a>
						</h2>
					</div>
				</div>
				<div class="row">
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="This Month" isAdminFacing=true}</div>
						<div class="dashboardValue">{$holdsThisMonth}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="Last Month" isAdminFacing=true}</div>
						<div class="dashboardValue">{$holdsLastMonth}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="This Year" isAdminFacing=true}</div>
						<div class="dashboardValue">{$holdsThisYear}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="All Time" isAdminFacing=true}</div>
						<div class="dashboardValue">{$holdsAllTime}</div>
					</div>
				</div>
			</div>

			<div class="dashboardCategory col-sm-6">
				<div class="row">
					<div class="col-sm-10 col-sm-offset-1">
						<h2 class="dashboardCategoryLabel">{translate text="Failed Holds" isAdminFacing=true}
							{' '}
							<a href="/OverDrive/UsageGraphs?stat=failedHolds&instance={$selectedInstance}" title="{translate text="Show Failed Holds Graph" inAttribute="true" isAdminFacing=true}"><i class="fas fa-chart-line"></i></a>
						</h2>
					</div>
				</div>
				<div class="row">
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="This Month" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsThisMonth->numFailedHolds|number_format}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="Last Month" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsLastMonth->numFailedHolds|number_format}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="This Year" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsThisYear->numFailedHolds|number_format}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="All Time" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsAllTime->numFailedHolds|number_format}</div>
					</div>
				</div>
			</div>

			<div class="dashboardCategory col-sm-6">
				<div class="row">
					<div class="col-sm-10 col-sm-offset-1">
						<h2 class="dashboardCategoryLabel">{translate text="Cancelled Holds" isAdminFacing=true}
							{' '}
							<a href="/OverDrive/UsageGraphs?stat=holdsCancelled&instance={$selectedInstance}" title="{translate text="Show Cancelled Holds Graph" inAttribute="true" isAdminFacing=true}"><i class="fas fa-chart-line"></i></a>
						</h2>
					</div>
				</div>
				<div class="row">
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="This Month" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsThisMonth->numHoldsCancelled|number_format}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="Last Month" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsLastMonth->numHoldsCancelled|number_format}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="This Year" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsThisYear->numHoldsCancelled|number_format}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="All Time" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsAllTime->numHoldsCancelled|number_format}</div>
					</div>
				</div>
			</div>

			<div class="dashboardCategory col-sm-6">
				<div class="row">
					<div class="col-sm-10 col-sm-offset-1">
						<h2 class="dashboardCategoryLabel">{translate text="Holds Frozen" isAdminFacing=true}
							{' '}
							<a href="/OverDrive/UsageGraphs?stat=holdsFrozen&instance={$selectedInstance}" title="{translate text="Show Holds Frozen Graph" inAttribute="true" isAdminFacing=true}"><i class="fas fa-chart-line"></i></a>
						</h2>
					</div>
				</div>
				<div class="row">
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="This Month" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsThisMonth->numHoldsFrozen|number_format}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="Last Month" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsLastMonth->numHoldsFrozen|number_format}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="This Year" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsThisYear->numHoldsFrozen|number_format}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="All Time" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsAllTime->numHoldsFrozen|number_format}</div>
					</div>
				</div>
			</div>

			<div class="dashboardCategory col-sm-6">
				<div class="row">
					<div class="col-sm-10 col-sm-offset-1">
						<h2 class="dashboardCategoryLabel">{translate text="Holds Thawed" isAdminFacing=true}
							{' '}
							<a href="/OverDrive/UsageGraphs?stat=holdsThawed&instance={$selectedInstance}" title="{translate text="Show Holds Thawed Graph" inAttribute="true" isAdminFacing=true}"><i class="fas fa-chart-line"></i></a>
						</h2>
					</div>
				</div>
				<div class="row">
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="This Month" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsThisMonth->numHoldsThawed|number_format}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="Last Month" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsLastMonth->numHoldsThawed|number_format}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="This Year" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsThisYear->numHoldsThawed|number_format}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="All Time" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsAllTime->numHoldsThawed|number_format}</div>
					</div>
				</div>
			</div>

			<div class="dashboardCategory col-sm-6">
				<div class="row">
					<div class="col-sm-10 col-sm-offset-1">
						<h2 class="dashboardCategoryLabel">{translate text="Downloads" isAdminFacing=true}
							{' '}
							<a href="/OverDrive/UsageGraphs?stat=downloads&instance={$selectedInstance}" title="{translate text="Show Downloads Graph" inAttribute="true" isAdminFacing=true}"><i class="fas fa-chart-line"></i></a>
						</h2>
					</div>
				</div>
				<div class="row">
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="This Month" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsThisMonth->numDownloads|number_format}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="Last Month" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsLastMonth->numDownloads|number_format}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="This Year" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsThisYear->numDownloads|number_format}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="All Time" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsAllTime->numDownloads|number_format}</div>
					</div>
				</div>
			</div>

			<div class="dashboardCategory col-sm-6">
				<div class="row">
					<div class="col-sm-10 col-sm-offset-1">
						<h2 class="dashboardCategoryLabel">{translate text="Previews" isAdminFacing=true}
							{' '}
							<a href="/OverDrive/UsageGraphs?stat=previews&instance={$selectedInstance}" title="{translate text="Show Previews Graph" inAttribute="true" isAdminFacing=true}"><i class="fas fa-chart-line"></i></a>
						</h2>
					</div>
				</div>
				<div class="row">
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="This Month" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsThisMonth->numPreviews|number_format}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="Last Month" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsLastMonth->numPreviews|number_format}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="This Year" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsThisYear->numPreviews|number_format}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="All Time" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsAllTime->numPreviews|number_format}</div>
					</div>
				</div>
			</div>

			<div class="dashboardCategory col-sm-6">
				<div class="row">
					<div class="col-sm-10 col-sm-offset-1">
						<h2 class="dashboardCategoryLabel">{translate text="Options Updates" isAdminFacing=true}
							{' '}
							<a href="/OverDrive/UsageGraphs?stat=optionUpdates&instance={$selectedInstance}" title="{translate text="Show Options Updates Graph" inAttribute="true" isAdminFacing=true}"><i class="fas fa-chart-line"></i></a>
						</h2>
					</div>
				</div>
				<div class="row">
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="This Month" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsThisMonth->numOptionsUpdates|number_format}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="Last Month" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsLastMonth->numOptionsUpdates|number_format}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="This Year" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsThisYear->numOptionsUpdates|number_format}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="All Time" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsAllTime->numOptionsUpdates|number_format}</div>
					</div>
				</div>
			</div>

			<div class="dashboardCategory col-sm-6">
				<div class="row">
					<div class="col-sm-10 col-sm-offset-1">
						<h2 class="dashboardCategoryLabel">{translate text="API Errors" isAdminFacing=true}
							{' '}
							<a href="/OverDrive/UsageGraphs?stat=apiErrors&instance={$selectedInstance}" title="{translate text="Show API Errors Graph" inAttribute="true" isAdminFacing=true}"><i class="fas fa-chart-line"></i></a>
						</h2>
					</div>
				</div>
				<div class="row">
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="This Month" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsThisMonth->numApiErrors|number_format}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="Last Month" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsLastMonth->numApiErrors|number_format}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="This Year" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsThisYear->numApiErrors|number_format}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="All Time" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsAllTime->numApiErrors|number_format}</div>
					</div>
				</div>
			</div>

			<div class="dashboardCategory col-sm-6">
				<div class="row">
					<div class="col-sm-10 col-sm-offset-1">
						<h2 class="dashboardCategoryLabel">{translate text="Connection Failures" isAdminFacing=true}
							{' '}
							<a href="/OverDrive/UsageGraphs?stat=connectionFailures&instance={$selectedInstance}" title="{translate text="Show Connection Failures Graph" inAttribute="true" isAdminFacing=true}"><i class="fas fa-chart-line"></i></a>
						</h2>
					</div>
				</div>
				<div class="row">
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="This Month" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsThisMonth->numConnectionFailures|number_format}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="Last Month" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsLastMonth->numConnectionFailures|number_format}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="This Year" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsThisYear->numConnectionFailures|number_format}</div>
					</div>
					<div class="col-tn-6">
						<div class="dashboardLabel">{translate text="All Time" isAdminFacing=true}</div>
						<div class="dashboardValue">{$statsAllTime->numConnectionFailures|number_format}</div>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}