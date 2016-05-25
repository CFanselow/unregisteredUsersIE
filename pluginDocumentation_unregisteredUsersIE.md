Key data
============

- name of the plugin: Unregistered Users IE
- author: Carola Fanselow
- current version: 1.0
- tested on OMP version: 1.2.0
- github link: https://github.com/langsci/unregisteredUsersIE.git
- community plugin: yes
- date: 2016/05/11

Description
============

This plugin imports users into the unregistered user management (%press%)/unregisteredUsers and exports all unregistered user groups. It uses the csv-format. To use this plugin, the generic plugin 'Unregistered Users" must be installed.

Implementation
================

Hooks
-----
- used hooks: 0

New pages
------
- new pages: 1

		[press]/management/importexport/plugin/UnregisteredUsersIEPlugin

Templates
---------
- templates that substitute other templates: 0
- templates that are modified with template hooks: 0
- new/additional templates: 2

		index.tpl
		results.tpl

Database access, server access
-----------------------------
- reading access to OMP tables: 1

		temporary_files

- writing access to OMP tables: 1

		temporary_files

- new tables: 0

- writing access to new tables: 3 (create by the Unregistered Users Plugin)

		langsci_unregistered_users
		langsci_unregistered_groups
		langsci_unregistered_users_groups

- nonrecurring server access: no

- recurring server access: yes

		saving files to temporary file folder
 
Classes, plugins, external software
-----------------------
- OMP classes used (php): 3
	
		ImportExportPlugin
		TemporaryFileManager
		JSONMessage

- OMP classes used (js, jqeury, ajax): 1

		FileUploadFormHandler
		TabHandler

- necessary plugins: 1

		Unregistered Users Plugin

- optional plugins: 0
- use of external software: no
- file upload: yes
 
Metrics
--------
- number of files 11
- lines of code: 797

Settings
--------
- settings: no

Plugin category
----------
- plugin category: importexport

Other
=============
- does using the plugin require special (background)-knowledge?: no
- access restrictions: access restricted to admins and managers
- adds css: no


