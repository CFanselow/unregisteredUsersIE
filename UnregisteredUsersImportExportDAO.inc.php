<?php

/**
 * @file plugins/generic/unregisteredUsers/UnregisteredUsersImportExportDAO.inc.php
 *
 * Copyright (c) 2016 Language Science Press
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING. 
 *
 * @class UnregisteredUsersImportExportDAO
 */

import('lib.pkp.classes.db.DAO');

class UnregisteredUsersImportExportDAO extends DAO {
	/**
	 * Constructor
	 */
	function UnregisteredUsersImportExportDAO() {
		parent::DAO();
	}

	function getUsers($contextId) {

		$result = $this->retrieve(
			'select groups.group_name, groups.notes as group_notes, users.first_name, users.last_name, users.email, users.omp_username, users.notes as user_notes from langsci_unregistered_groups groups left join langsci_unregistered_users_groups comb on groups.group_id=comb.group_id left join langsci_unregistered_users users on users.user_id = comb.user_id where users.context_id='.$contextId.' and comb.context_id='.$contextId.' and groups.context_id='.$contextId.' order by groups.group_name, users.last_name'
		);

		if ($result->RecordCount() == 0) {
			$result->Close();
			return null;
		} else {
			$users = array();
			$rowcount=1;
			$users[0]['userGroup'] = 'Group name';
			$users[0]['firstName'] = 'First name';
			$users[0]['lastName'] = 'Last name';
			$users[0]['email'] = 'Email';
			$users[0]['ompUsername'] = 'OMP username'; 
			$users[0]['userNotes'] = 'Notes on the user';
			$users[0]['groupNotes'] = 'Notes on the group';
			while (!$result->EOF) {
				$row = $result->getRowAssoc(false);
				$users[$rowcount]['userGroup'] = $this->convertFromDB($row['group_name']);
				$users[$rowcount]['firstName'] = $this->convertFromDB($row['first_name']);
				$users[$rowcount]['lastName'] = $this->convertFromDB($row['last_name']);
				$users[$rowcount]['email'] = $this->convertFromDB($row['email']);
				$users[$rowcount]['ompUsername'] = $this->convertFromDB($row['omp_username']);
				$users[$rowcount]['userNotes'] = $this->convertFromDB($row['user_notes']);
				$users[$rowcount]['groupNotes'] = $this->convertFromDB($row['group_notes']);
				$rowcount++;
				$result->MoveNext();
			}
			$result->Close();
			return $users;	
		}
	}

	function getGroupByContextId($contextId) {
		$result = $this->retrieveRange(
			'SELECT * FROM langsci_unregistered_groups WHERE context_id = ? ORDER BY group_name',
			(int) $contextId,
			$rangeInfo
		);

		return new DAOResultFactory($result, $this, '_fromRow');
	}

	function getGroupId($groupName,$contextId) {

		$result = $this->retrieve(
			'select group_id from langsci_unregistered_groups where group_name="' . $groupName .'" and context_id = ' . $contextId
		);

		if ($result->RecordCount() == 0) {
			$result->Close();
			return false;
		} else {
			$row = $result->getRowAssoc(false);
			$result->Close();
			return $this->convertFromDB($row['group_id']);
		}
	}

	function insertReference($userId, $groupId, $contextId) {

		$this->update(
			'INSERT INTO langsci_unregistered_users_groups (user_id, group_id, context_id)
			VALUES (?,?,?)',
			array(
				$userId,
				$groupId,
				$contextId
			)
		);

		return true;
	}

	function checkReference($userId, $groupId, $contextId) {

		$result = $this->retrieve(
			'select group_id from langsci_unregistered_users_groups where group_id=' . $groupId .' and user_id ='.$userId.' and context_id='.$contextId
		);

		if ($result->RecordCount() == 0) {
			$result->Close();
			return false;
		} else {
			$result->Close();
			return true;
		}
	}


	function insertGroup($groupName,$notes,$contextId) {

		$this->update(
			'INSERT INTO langsci_unregistered_groups (group_name,notes,context_id)
			VALUES (?,?,?)',
			array(
				$groupName,
				$notes,
				$contextId	
			)
		);

	}

	function insertUser($groupId,$firstName,$lastName,$email,$ompUsername,$notes,$contextId) {

		$this->update(
			'INSERT INTO langsci_unregistered_users (first_name, last_name, email, omp_username,notes,context_id)
			VALUES (?,?,?,?,?,?)',
			array(
				$firstName,
				$lastName,
				$email,
				$ompUsername,
				$notes,
				$contextId	
			)
		);

		return $this->getInsertId();
	}

	/**
	 * Get the insert ID for the last inserted user.
	 * @return int
	 */
	function getInsertId() {
		return $this->_getInsertId('langsci_unregistered_users', 'user_id');
	}

	function userExists($firstName,$lastName,$email,$ompUsername,$notes) {

		$result = $this->retrieve(
			'select user_id, first_name, last_name, email, omp_username, notes from langsci_unregistered_users where ' .
			'first_name = "'.$firstName.'" and ' . 
			'last_name = "'.$lastName.'" and ' . 
			'email =  "'.$email.'" and ' .  
			'omp_username =  "'.$ompUsername.'" and ' . 
			'notes =  "'.$notes.'"'
		);

		if ($result->RecordCount() == 0) {
			$result->Close();
			return false;
		} else {
			$row = $result->getRowAssoc(false);
			$result->Close();
			return $this->convertFromDB($row['user_id']);
		}

	}


}

?>
