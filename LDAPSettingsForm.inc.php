<?php

/**
 * @file plugins/generic/ldap/LDAPSettingsForm.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Copyright (c) 2019 Shem Pasamba
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LDAPSettingsForm
 * @ingroup plugins_generic_ldap
 *
 * @brief Form for managers to modify Shibboleth
 * authentication plugin settings
 */

import('lib.pkp.classes.form.Form');

class LDAPSettingsForm extends Form {

	/** @var int */
	var $_contextId;

	/** @var object */
	var $_plugin;

	/**
	 * Constructor
	 * @param $plugin LDAPAuthPlugin
	 * @param $contextId int
	 */
	function __construct($plugin, $contextId) {
		$this->_contextId = $contextId;
		$this->_plugin = $plugin;

		parent::__construct($plugin->getTemplateResource('settingsForm.tpl'));

		$this->addCheck(
			new FormValidator(
				$this,
				'ldapUrl',
				'required',
				'plugins.generic.ldap.manager.settings.ldapUrlRequired'
			)
		);
		$this->addCheck(
			new FormValidator(
				$this,
				'ldapSuffix',
				'required',
				'plugins.generic.ldap.manager.settings.ldapSuffixRequired'
			)
		);
		$this->addCheck(
			new FormValidator(
				$this,
				'ldapFilter',
				'required',
				'plugins.generic.ldap.manager.settings.ldapFilterRequired'
			)
		);
		$this->addCheck(
			new FormValidator(
				$this,
				'ldapBindUser',
				'',
				'plugins.generic.ldap.manager.settings.ldapBindUserRequired'
			)
		);
		$this->addCheck(
			new FormValidator(
				$this,
				'ldapBindPassword',
				'',
				'plugins.generic.ldap.manager.settings.ldapBindPasswordRequired'
			)
		);
		$this->addCheck(
			new FormValidator(
				$this,
				'ldapSelfServiceUrl',
				'required',
				'plugins.generic.ldap.manager.settings.ldapSelfServiceUrlRequired'
			)
		);
		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));
	}

	/**
	 * Initialize form data.
	 */
	function initData() {
		$this->_data = array(
			'ldapUrl' => $this->_plugin->getSetting($this->_contextId, 'ldapUrl'),
			'ldapSuffix' => $this->_plugin->getSetting($this->_contextId, 'ldapSuffix'),
			'ldapFilter' => $this->_plugin->getSetting($this->_contextId, 'ldapFilter'),
			'ldapBindUser' => $this->_plugin->getSetting($this->_contextId, 'ldapBindUser'),
			'ldapBindPassword' => $this->_plugin->getSetting($this->_contextId, 'ldapBindPassword'),
			'ldapSelfServiceUrl' => $this->_plugin->getSetting($this->_contextId, 'ldapSelfServiceUrl'),
		);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('ldapUrl'));
		$this->readUserVars(array('ldapSuffix'));
		$this->readUserVars(array('ldapFilter'));
		$this->readUserVars(array('ldapBindUser'));
		$this->readUserVars(array('ldapBindPassword'));
		$this->readUserVars(array('ldapSelfServiceUrl'));
	}

	/**
	 * Fetch the form.
	 * @copydoc Form::fetch()
	 */
	function fetch($request) {
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('pluginName', $this->_plugin->getName());
		return parent::fetch($request);
	}

	/**
	 * Save settings.
	 */
	function execute() {
		$this->_plugin->updateSetting(
			$this->_contextId,
			'ldapUrl',
			trim($this->getData('ldapUrl'), "\"\';"),
			'string'
		);
		$this->_plugin->updateSetting(
			$this->_contextId,
			'ldapSuffix',
			trim($this->getData('ldapSuffix'), "\"\';"),
			'string'
		);
		$this->_plugin->updateSetting(
			$this->_contextId,
			'ldapFilter',
			trim($this->getData('ldapFilter'), "\"\';"),
			'string'
		);
		$this->_plugin->updateSetting(
			$this->_contextId,
			'ldapBindUser',
			trim($this->getData('ldapBindUser'), "\"\';"),
			'string'
		);
		$this->_plugin->updateSetting(
			$this->_contextId,
			'ldapBindPassword',
			trim($this->getData('ldapBindPassword'), "\"\';"),
			'string'
		);
		$this->_plugin->updateSetting(
			$this->_contextId,
			'ldapSelfServiceUrl',
			trim($this->getData('ldapSelfServiceUrl'), "\"\';"),
			'string'
		);
	}
}
