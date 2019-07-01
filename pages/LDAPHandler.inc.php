<?php

/**
 * @file plugins/generic/ldap/pages/LDAPHandler.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Copyright (c) 2019 Shem Pasamba
 * Distributed under the GNU GPL v2 or later. For full terms see the file docs/COPYING.
 *
 * @class LDAPHandler
 * @ingroup plugins_generic_ldap
 *
 * @brief Handle Shibboleth responses
 */

import('classes.handler.Handler');

class LDAPHandler extends Handler {
	/** @var LDAPAuthPlugin */
	var $_plugin;

	/** @var int */
	var $_contextId;

	/**
	 * Intercept normal login/registration requests; defer to Shibboleth.
	 * 
	 * @param $args array
	 * @param $request Request
	 * @return bool
	 */
	function activateUser($args, $request) {
		return $this->_ldapRedirect($request);
	}

	/**
	 * @copydoc LDAPHandler::activateUser()
	 */
	function changePassword($args, $request) {
		return $this->_ldapRedirect($request);
	}

	/**
	 * @copydoc LDAPHandler::activateUser()
	 */
	function lostPassword($args, $request) {
		return $this->_ldapRedirect($request);
	}

	/**
	 * @copydoc LDAPHandler::activateUser()
	 */
	function register($args, $request) {
		return $this->_ldapRedirect($request);
	}

	/**
	 * @copydoc LDAPHandler::activateUser()
	 */
	function registerUser($args, $request) {
		return $this->_ldapRedirect($request);
	}

	/**
	 * @copydoc LDAPHandler::activateUser()
	 */
	function requestResetPassword($args, $request) {
		return $this->_ldapRedirect($request);
	}

	/**
	 * @copydoc LDAPHandler::activateUser()
	 */
	function savePassword($args, $request) {
		return $this->_ldapRedirect($request);
	}

	/**
	 * @copydoc LDAPHandler::activateUser()
	 */
	function signIn($args, $request) {
		$context = $this->getTargetContext($request);
		$router = $request->getRouter();

		$input =  $request->getUserVars();

		// check csrf
		if ($input['csrfToken'] != $request->getSession()->getCSRFToken())
			die('Please refresh form.');

		// get data from settings
		$this->_plugin = $this->_getPlugin();
		$this->_contextId = $this->_plugin->getCurrentContextId();
		$ldapUrl = $this->_plugin->getSetting(
			$this->_contextId,
			'ldapUrl'
		);
		$ldapSuffix = $this->_plugin->getSetting(
			$this->_contextId,
			'ldapSuffix'
		);
		$ldapFilter = $this->_plugin->getSetting(
			$this->_contextId,
			'ldapFilter'
		);
		$ldapBindUser = $this->_plugin->getSetting(
			$this->_contextId,
			'ldapBindUser'
		);
		$ldapBindPassword = $this->_plugin->getSetting(
			$this->_contextId,
			'ldapBindPassword'
		);

		// try to connect
		$username = $input['username'];
		$ldapConn = ldap_connect($ldapUrl);
		if (!$ldapConn)
			die('Unable to connect to ldap: '.$ldapUrl);

		// set settings for making it work with AD
		ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);

		// use tls if not using ldaps
		if (!preg_match('/ldaps.*636/', $ldapUrl))
			ldap_start_tls($ldapConn);


		// try anonymous bind
		$ldapBind = null;
		if (!$ldapBindUser && !$ldapBindPassword)
		{
			$ldapBind = ldap_bind($ldapConn, $username, $input['password']);
		}
		// try admin bind
		else
		{
			$ldapBind = ldap_bind($ldapConn, $ldapBindUser, $ldapBindPassword);
		}
		if ($ldapBind)
		{
			$ldapFilter = str_replace('%USER%', $username, $ldapFilter);
			$ldapSearchResult = ldap_search($ldapConn, $ldapSuffix, $ldapFilter, ['dn', 'givenName', 'sn', 'mail', 'telephoneNumber']);
			$data =	ldap_get_entries($ldapConn, $ldapSearchResult);

			// not in ldap, so
			if (isset($data['count']) && $data['count'] == 0)
			{
				// test if normal user login will work
				$reason = null;
				$result = Validation::login($username, $input['password'], $reason, $input['remember']);
				
				if ($result)
					return $this->_redirectAfterLogin($request);
			}
			// found in ldap, so
			else
			{
				$data = $data[0];
				$givenName = $data['givenname'][0]??null;
				$sn = $data['sn'][0]??null;

				// test password
				if (ldap_bind($ldapConn, $data['dn'], $input['password']))
				{
					$userDao = DAORegistry::getDAO('UserDAO');
					// test if user exists in database
					$user = $userDao->getByUsername($username);
					if ($user)
					{
						$this->_updateUserInfoFromLDAP($userDao, $user, $input['password'], $data['mail'][0], $givenName, $sn, $data['telephonenumber'][0]??null, $data['streetaddress'][0]??null);
					}
					// user doesn't exist so create it in database
					else
					{
						$this->_registerFromLDAP($userDao, $username, $input['password'], $data['mail'][0], $givenName, $sn, $data['telephonenumber'][0]??null, $data['streetaddress'][0]??null);
					}
					$result = Validation::login($username, $input['password'], $reason, $input['remember']);

					if ($result)
						return $this->_redirectAfterLogin($request);
				}
				else
				{
					Validation::logout();
				}
			}
		}

		$contextPath = is_null($context) ? "" : $context->getPath();
		$returnUrl = $router->url($request, $contextPath);
		return $request->redirectUrl($returnUrl);
	}

	/**
	 * Intercept normal logout; redirect to context home page instead
	 * of login (which would send back to Shibboleth again).
	 * 
	 * @param $args array
	 * @param $request Request
	 * @return bool
	 */
	function signOut($args, $request) {
		$context = $this->getTargetContext($request);
		$router = $request->getRouter();

		Validation::logout();

		$contextPath = is_null($context) ? "" : $context->getPath();
		$returnUrl = $router->url($request, $contextPath);
		return $request->redirectUrl($returnUrl);
	}

	/**
	 * @copydoc LDAPHandler::activateUser()
	 */
	function validate($requiredContexts = null, $request = null) {
		return $this->_ldapRedirect($request);
	}


	//
	// Private helper methods
	//
	/**
	 * Get the Shibboleth plugin object
	 * 
	 * @return LDAPAuthPlugin
	 */
	function _getPlugin() {
		$plugin = PluginRegistry::getPlugin('generic', LDAP_PLUGIN_NAME);
		return $plugin;
	}

	/**
	 * @copydoc LoginHandler::_redirectAfterLogin
	 */
	function _redirectAfterLogin($request) {
		$context = $this->getTargetContext($request);
		// If there's a context, send them to the dashboard after login.
		if ($context && $request->getUserVar('source') == '' &&
			array_intersect(
				array(ROLE_ID_SITE_ADMIN, ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR, ROLE_ID_AUTHOR, ROLE_ID_REVIEWER, ROLE_ID_ASSISTANT),
				(array) $this->getAuthorizedContextObject(ASSOC_TYPE_USER_ROLES)
			)) {
			return $request->redirect($context->getPath(), 'dashboard');
		}

		return $request->redirectHome();
	}

	/**
	 * Update user info from the LDAP-provided information.
	 *
	 * @return User
	 */
	function _updateUserInfoFromLDAP($userDao, $user, $password, $email, $givenName, $sn, $phone = null, $address = null) {
		$username = $user->getUsername();
		$user->setEmail($email);

		$locale = $this->_getCurrentLocale();
		$user->setGivenName($givenName, $locale);
		$user->setFamilyName($sn, $locale);

		if (!empty($phone)) {
			$user->setPhone($phone);
		}
		if (!empty($address)) {
			$user->setMailingAddress($address);
		}

		$user->setPassword(
			Validation::encryptCredentials(
				$username,
				$password
			)
		);

		$userDao->updateObject($user);
		$userId = $user->getId();
		if ($userId) {
			return $user;
		} else {
			return null;
		}
	}

	/**
	 * Create a new user from the LDAP-provided information.
	 *
	 * @return User
	 */
	function _registerFromLDAP($userDao, $username, $password, $email, $givenName, $sn, $phone = null, $address = null) {
		$user = $userDao->newDataObject();
		$user->setUsername($username);
		$user->setEmail($email);

		$locale = $this->_getCurrentLocale();
		$user->setGivenName($givenName, $locale);
		$user->setFamilyName($sn, $locale);

		if (!empty($phone)) {
			$user->setPhone($phone);
		}
		if (!empty($address)) {
			$user->setMailingAddress($address);
		}

		$user->setDateRegistered(Core::getCurrentDate());
		$user->setPassword(
			Validation::encryptCredentials(
				$username,
				$password
			)
		);

		$userDao->insertObject($user);
		$userId = $user->getId();
		if ($userId) {
			return $user;
		} else {
			return null;
		}
	}

	/**
	 * Intercept normal login/registration requests; defer to LAM.
	 * 
	 * @param $request Request
	 * @return bool
	 */
	function _ldapRedirect($request) {
		$this->_plugin = $this->_getPlugin();
		$this->_contextId = $this->_plugin->getCurrentContextId();
                $ldapSelfServiceUrl = $this->_plugin->getSetting(
                        $this->_contextId,
                        'ldapSelfServiceUrl'
		);
		if ($ldapSelfServiceUrl)
			return $request->redirectUrl($ldapSelfServiceUrl);
		return $request->redirectHome();
	} 

	/**
	 * Return current Locale
	 *
	 * @return string
	 */
	function _getCurrentLocale()
	{
                $request = Application::getRequest();
                $site = $request->getSite();
                $sitePrimaryLocale = $site->getPrimaryLocale();
                $currentLocale = AppLocale::getLocale();
		return $currentLocale;
	}
}
