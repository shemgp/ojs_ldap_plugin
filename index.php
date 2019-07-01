<?php
/**
 * @defgroup plugins_generic_ldap LDAP Authentication Plugin
 */
 
/**
 * @file plugins/generic/ldap/index.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Copyright (c) 2019 Shem Pasamba
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_generic_ldap
 * @brief Wrapper for loading the LDAP authentication plugin.
 *
 */

require_once('LDAPAuthPlugin.inc.php');

return new LDAPAuthPlugin();
