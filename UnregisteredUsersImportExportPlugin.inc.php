<?php

/**
 * @file plugins/importexport/unregisteredUsersIE/UnregisteredUsersImportExportPlugin.inc.php
 *
 * Copyright (c) 2016 Language Science Press
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING. 
 *
 * @class UnregisteredUsersImportExportPlugin
 *
 */

import('lib.pkp.classes.plugins.ImportExportPlugin');
import('plugins.importexport.unregisteredUsersIE.UnregisteredUsersImportExportDAO');

class UnregisteredUsersImportExportPlugin extends ImportExportPlugin {
	/**
	 * Constructor
	 */
	function UnregisteredUsersImportExportPlugin() {
		parent::ImportExportPlugin();
	}

	/**
	 * Called as a plugin is registered to the registry
	 * @param $category String Name of category plugin was registered to
	 * @param $path string
	 * @return boolean True iff plugin initialized successfully; if false,
	 * 	the plugin will not be registered.
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		$this->addLocaleData();
		return $success;
	}

	/**
	 * @see Plugin::getTemplatePath($inCore)
	 */
	function getTemplatePath($inCore = false) {
		return parent::getTemplatePath($inCore) . 'templates/';
	}

	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category.
	 * @return String name of plugin
	 */
	function getName() {
		return 'UnregisteredUsersImportExportPlugin';
	}

	/**
	 * Get the display name.
	 * @return string
	 */
	function getDisplayName() {
		return __('plugins.importexport.unregisteredUsers.displayName');
	}

	/**
	 * Get the display description.
	 * @return string
	 */
	function getDescription() {
		return __('plugins.importexport.unregisteredUsers.description');
	}

	/**
	 * Display the plugin.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function display($args, $request) {
		$templateMgr = TemplateManager::getManager($request);
		$press = $request->getPress();
		$context = $request->getContext();		
		$contextId = $context->getId();

		parent::display($args, $request);
		$templateMgr->assign('plugin', $this);
		$templateMgr->assign('urlUnregisteredUsers', $request->url($press,'unregisteredUsers','index'));

		// if Unregistered Users Plugin does not exist, this plugin will not work
		$unregisteredUsersPlugin = PluginRegistry::getPlugin('generic','unregisteredusersplugin');
		if (!$unregisteredUsersPlugin) {
			$templateMgr->assign('errorMessage', 'You have to install and enable the generic plugin "Unregistered Users Plugin" to use this import/export function');
			$templateMgr->display($this->getTemplatePath() . 'index.tpl');
		} else {

		switch (array_shift($args)) {
			case 'index':
			case '':

				$templateMgr->display($this->getTemplatePath() . 'index.tpl');
				break;

			case 'uploadImport':

				$user = $request->getUser();

				import('lib.pkp.classes.file.TemporaryFileManager');
				$temporaryFileManager = new TemporaryFileManager();
				$temporaryFile = $temporaryFileManager->handleUpload('uploadedFile', $user->getId());


				if ($temporaryFile) {
					$json = new JSONMessage(true);
					$json->setAdditionalAttributes(array(
						'temporaryFileId' => $temporaryFile->getId()
					));
				} else {

					$json = new JSONMessage(false, __('common.uploadFailed'));

				}

				return $json->getString();

			case 'importBounce':

				$json = new JSONMessage(true);
				$json->setEvent('addTab', array(
					'title' => __('plugins.importexport.unregisteredUsers.results'),
					'url' => $request->url(null, null, null, array('plugin', $this->getName(), 'import'), array('temporaryFileId' => $request->getUserVar('temporaryFileId'))),
				));
				return $json->getString();

			case 'import':

				// get data from file
				$temporaryFileId = $request->getUserVar('temporaryFileId');
				$temporaryFileDao = DAORegistry::getDAO('TemporaryFileDAO');
				$user = $request->getUser();

				$temporaryFile = $temporaryFileDao->getTemporaryFile($temporaryFileId, $user->getId());
				if (!$temporaryFile) {

					$json = new JSONMessage(true, __('plugins.inportexport.unregisteredUsers.uploadFile'));
					return $json->getString();
				}

				$temporaryFilePath = $temporaryFile->getFilePath();
				$data_strings = str_getcsv(file_get_contents($temporaryFilePath),"\n");

				// create array and check data
				$numberOfRows =  sizeof($data_strings);
				$data = array();
				$numberOfColumns = true;
				for ($i=0;$i<$numberOfRows;$i++) {
					$data[$i] = str_getcsv($data_strings[$i],",");
					if (sizeof($data[$i])!==7) {
						$numberOfColumns = false;	
					}
				}

				if ($numberOfRows==0 || !$numberOfColumns) {
					$templateMgr->assign('correctDataFormat',false);
					$templateMgr->assign('errorMessage','Incorrect data format. Please import comma separated csv-file with 7 columns.');
					$json = new JSONMessage(true, $templateMgr->fetch($this->getTemplatePath() . 'results.tpl'));
					return $json->getString();
				}

				// create DAO object
				$unregisteredUsersImportExportDAO = new UnregisteredUsersImportExportDAO();

				// extract new groups
				$newGroups = array();
				for ($i=0; $i<$numberOfRows;$i++) {

					$row = $data[$i];	
					$groupName = trim($row[0]);
					$groupId = $unregisteredUsersImportExportDAO->getGroupId($groupName,$contextId);

					// group names that are not yet in the database, ignore header, ignore empty fields
					if (!$groupId && !$groupName=="" && $groupName!=="Group name") {
						if (!in_array($groupName,array_keys($newGroups))) {
							$groupNotes =  $row[6];
							$newGroups[$groupName] = $groupNotes;
						}
					}
				}

				// save groups to database
				foreach ($newGroups as $groupName => $groupNotes) {
					$unregisteredUsersImportExportDAO->insertGroup($groupName,$groupNotes,$contextId);
				}

				// save users to database
				$insertedUsers = array();
				$failedEntries = array();

				for ($i=0; $i<$numberOfRows;$i++) {

					$row = $data[$i];
					$groupName = trim($row[0]);
					$groupId = $unregisteredUsersImportExportDAO->getGroupId($groupName,$contextId);
					$firstName = trim($row[1]);
					$lastName = trim($row[2]);
					$email = trim($row[3]);
					$ompUsername = trim($row[4]);
					$notes = trim($row[5]);

					// entries have to have a first name and a last name, the referenced user group must exist
					if ($groupId && $firstName && $lastName) {

						// does the user already exist (all variables must match)
						$existingUserId = $unregisteredUsersImportExportDAO->userExists($firstName,$lastName,$email,$ompUsername,$notes);

						// insert new user
						if (!$existingUserId) {
							$existingUserId = $unregisteredUsersImportExportDAO->insertUser($groupId,$firstName,$lastName,$email,$ompUsername,$notes,$contextId);
						}
						// insert new reference
						if (!$unregisteredUsersImportExportDAO->checkReference($existingUserId, $groupId, $contextId)) {
							$unregisteredUsersImportExportDAO->insertReference($existingUserId, $groupId, $contextId);
							$insertedUsers[] = implode(", ",$row);
						}	else {
							$failedEntries[] = implode(", ",$row);
						}

					} else {
					 	if (!$data[$i]=="") {
							$failedEntries[] = implode(", ",$row);
						}
					}
				}

				// prepare and load template
				$templateMgr->assign('insertedUsers',$insertedUsers);
				$templateMgr->assign('failedEntries',$failedEntries);

				$json = new JSONMessage(true, $templateMgr->fetch($this->getTemplatePath() . 'results.tpl'));
				return $json->getString();

			case 'export':

				$unregisteredUsersImportExportDAO = new UnregisteredUsersImportExportDAO();
				$data = $unregisteredUsersImportExportDAO->getUsers($context->getId());
				$data = array_merge(array('Group name','First name','Last name','Email','OMP username','Notes on the user','Notes on the group'),$data);

				header("Content-Type: text/csv; charset=utf-8");
				header("Content-Disposition: attachment; filename=unregisteredUsers.csv");
				$output = fopen("php://output", "w");
				foreach ($data as $row) {
				  fputcsv($output, $row); // here you can change delimiter/enclosure
				}
				fclose($output);
				break;

			default:
				$dispatcher = $request->getDispatcher();
				$dispatcher->handle404();
		}
		}
	}

	/**
	 * @copydoc ImportExportPlugin::executeCLI
	 */
	function executeCLI($scriptName, &$args) {
		fatalError('Not implemented.');
	}

	/**
	 * @copydoc ImportExportPlugin::usage
	 */
	function usage($scriptName) {
		fatalError('Not implemented.');
	}
}

?>
