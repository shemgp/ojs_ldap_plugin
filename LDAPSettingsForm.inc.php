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
 * @brief Form for managers to modify LDAP
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
			new FormValidatorCustom(
				$this,
				'ldapUrl',
				FORM_VALIDATOR_REQUIRED_VALUE,
				'plugins.generic.ldap.manager.settings.ldapUrlRequired',
				create_function('$s', 'return (preg_match("|^ldaps?://.+$|i", $s) === 1);')
			)
		);
		$this->addCheck(
			new FormValidator(
				$this,
				'ldapSuffix',
				FORM_VALIDATOR_REQUIRED_VALUE,
				'plugins.generic.ldap.manager.settings.ldapSuffixRequired'
			)
		);
		$this->addCheck(
			new FormValidatorCustom(
				$this,
				'ldapFilter',
				FORM_VALIDATOR_REQUIRED_VALUE,
				'plugins.generic.ldap.manager.settings.ldapFilterRequired',
				create_function('$s', 'return (preg_match("/^[(].+%USER%.*[)]$/", $s) === 1);')
			)
		);
		$this->addCheck(
			new FormValidatorCustom(
				$this,
				'ldapBindUser',
				FORM_VALIDATOR_OPTIONAL_VALUE,
				'plugins.generic.ldap.manager.settings.ldapBindUserRequired',
				array(&$this, '_canBindAnonymous')
			)
		);
		$this->addCheck(
			new FormValidatorCustom(
				$this,
				'ldapBindPassword',
				FORM_VALIDATOR_REQUIRED_VALUE,
				'plugins.generic.ldap.manager.settings.ldapBindPasswordRequired',
				array(&$this, '_canBindCredentialed')
			)
		);
		$this->addCheck(
			new FormValidatorUrl(
				$this,
				'ldapSelfServiceUrl',
				FORM_VALIDATOR_OPTIONAL_VALUE,
				'plugins.generic.ldap.manager.settings.ldapSelfServiceUrlRequired'
			)
		);
		$this->addCheck(
			new FormValidator(
				$this,
				'ldapLocalLoginOrder',
				FORM_VALIDATOR_REQUIRED_VALUE,
				'plugins.generic.ldap.manager.settings.ldapLocalLoginOrderRequired'
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
			'ldapLocalLoginOrder' => $this->_plugin->getSetting($this->_contextId, 'ldapLocalLoginOrder'),
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
		$this->readUserVars(array('ldapLocalLoginOrder'));
	}

	/**
	 * Fetch the form.
	 * @copydoc Form::fetch()
	 */
	function fetch($request, $template = NULL, $display = false) {
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
		$this->_plugin->updateSetting(
			$this->_contextId,
			'ldapLocalLoginOrder',
			trim($this->getData('ldapLocalLoginOrder'), "\"\';"),
			'string'
		);
	}

	/**
	 * If no bind user / password is given, check whether anonymous bind is possible
	 * @param $fieldValue mixed the value of the field being checked
	 * @return boolean
	 */
	function _canBindAnonymous($fieldValue) {
		if ($fieldValue) {
			// don't validate if the bind user is provided
			return true;
		}
		if (!$this->getData('ldapUrl')) {
			// don't validate if the LDAP URL is missing
			return true;
		}
		$ldapConn = $this->_plugin->_getLdapResource($this->getData('ldapUrl'));
		if ($ldapConn) {
			// try anonymous bind
			if (ldap_bind($ldapConn)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * If bind user and password is set, check bind credentials
	 * @param $fieldValue mixed the value of the field being checked
	 * @return boolean
	 */
	function _canBindCredentialed($fieldValue) {
		if (!$this->getData('ldapUrl')) {
			// don't validate if the LDAP URL is missing
			return true;
		}
		if (!$fieldValue && !$this->getData('ldapBindUser')) {
			// don't validate if no bind user is set and no bind password is set
			return true;
		}
		if ($fieldValue && !$this->getData('ldapBindUser')) {
			// fail if no bind user is specified
			return false;
		}
		$ldapConn = $this->_plugin->_getLdapResource($this->getData('ldapUrl'));
		if ($ldapConn) {
			// try bind
			if (ldap_bind($ldapConn, $this->getData('ldapBindUser'), $this->getData('ldapBindPassword'))) {
				return true;
			}
		}
		return false;
	}

}
