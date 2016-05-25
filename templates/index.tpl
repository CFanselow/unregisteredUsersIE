{**
 * @file plugins/importexport/unregisteredUsersIE/templates/index.tpl
 *
 * Copyright (c) 2016 Language Science Press
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING. 
 *}

{strip}
{assign var="pageTitle" value="plugins.importexport.unregisteredUsers.displayName"}
{include file="common/header.tpl"}
{/strip}

<script type="text/javascript">
	// Attach the JS file tab handler.
	$(function() {ldelim}
		$('#importExportTabs').pkpHandler('$.pkp.controllers.TabHandler');
		$('#importExportTabs').tabs('option', 'cache', true);
	{rdelim});
</script>

<p>{translate key="plugins.importexport.unregisteredUsers.instruction"} <a href="{$urlUnregisteredUsers}">click here</a>.</p>

<div id="importExportTabs" class="pkp_controllers_tab">
	<ul>
		<li><a href="#import-tab">{translate key="plugins.importexport.unregisteredUsers.import"}</a></li>
		<li><a href="#export-tab">{translate key="plugins.importexport.unregisteredUsers.export"}</a></li>
	</ul>
	<div id="import-tab">
		<script type="text/javascript">
			$(function() {ldelim}
				// Attach the form handler.
				$('#importForm').pkpHandler('$.pkp.controllers.form.FileUploadFormHandler',
					{ldelim}
						$uploader: $('#plupload'),
							uploaderOptions: {ldelim}
								uploadUrl: {plugin_url|json_encode path="uploadImport" escape=false},
								baseUrl: {$baseUrl|json_encode}
							{rdelim}
					{rdelim}
				);
			{rdelim});
		</script>
		<form id="importForm" class="pkp_form" action="{plugin_url path="importBounce"}" method="post">
			{fbvFormArea id="importForm"}
				{* Container for uploaded file *}
				<input type="hidden" name="temporaryFileId" id="temporaryFileId" value="" />

				<p>{translate key="plugins.importexport.unregisteredUsers.import.instructions"}</p>

				{fbvFormArea id="file"}
					{fbvFormSection}
						{include file="controllers/fileUploadContainer.tpl" id="plupload"}
					{/fbvFormSection}
				{/fbvFormArea}

				{fbvFormButtons hideCancel="true"}

			{/fbvFormArea}
		</form>
	</div>
	<div id="export-tab">
		<script type="text/javascript">
			$(function() {ldelim}
				// Attach the form handler.
				$('#exportForm').pkpHandler('$.pkp.controllers.form.FormHandler');
			{rdelim});
		</script>
		<form id="exportForm" class="pkp_form" action="{plugin_url path="export"}" method="post">
			{fbvFormArea id="exportForm"}
				<p>{translate key="plugins.importexport.unregisteredUsers.export.instructions"}</p>
				{fbvFormButtons hideCancel="true"}
			{/fbvFormArea}
		</form>
	</div>
</div>

{include file="common/footer.tpl"}
