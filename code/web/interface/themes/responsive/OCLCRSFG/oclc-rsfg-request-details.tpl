{strip}
	<header>
		<h1>{translate text="Interlibrary Loan Request" isAdminFacing=true}</h1>
	</header>
	<main>
		<section>
			<h2>General information</h2>
			<p>Interlibrary Loan OCLC Request Id: {$illRequest->oclcRequestId}</p>
			<p>Status: {$illRequest->requestStatus}</p>
			<p>Status Description: {$illRequest->requestStatusDescription} </p>
			<p>Service Type: {$illRequest->serviceType}</p>
			{* <p>Supplier: {$illRequest->supplier}</p> *}
			<p>Verification: {$illRequest->verification}</p>
		</section>
		<section>
			<h2>Item information</h2>
			<p>isbn: {$illRequest->isbn}</p>
			<p>issn: {$illRequest->issn}</p>
			<p>OCLC Number: {$illRequest->oclcNumber}</p>
			<p>Media Type: {$illRequest->mediaType}</p>
			<p>Created Date: {$illRequest->createdDate}</p> 
			<p>Language: {$illRequest->language}</p> 
			<p>Need By Date: {$illRequest->needed}</p>
			{* <p> Permitted Actions: </p> *}

			<p>Title: {$illRequest->title}</p>
			<p>Author: {$illRequest->author}</p>
			<p>Edition: {$illRequest->edition}</p>
		</section>
	</main>
{/strip}
