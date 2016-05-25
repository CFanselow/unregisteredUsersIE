{**
 * @file plugins/importexport/unregisteredUsersIE/templates/results.tpl
 *
 * Copyright (c) 2016 Language Science Press
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING. 
 *}

{if not $errorMessage}

	<p>{translate key="plugins.importexport.unregisteredUsers.results.inserted"}</p>

	<ul>
		{foreach from=$insertedUsers item=user}
			<li>{$user}</li>
		{/foreach}
	</ul>

	{if $failedEntries}

		<p>{translate key="plugins.importexport.unregisteredUsers.results.notInserted"}</p>

		<ul>
			{foreach from=$failedEntries item=entry}
				<li>{$entry}</li>
			{/foreach}
		</ul>
	{/if}

{else}

	{$errorMessage}

{/if}


