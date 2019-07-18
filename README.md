# ojs_ldap_plugin
LDAP Authorization Plugin for OJS

# Installing
1. Clone to `plugins/generic/ldap` folder of OJS 3.

  ```bash
  cd plugins/generic
  git clone https://github.com/shemgp/ojs_ldap_plugin.git ldap
  ```
2. Enable in global site settings (Administration->Site Settings->Plugins->LDAP Authentication Plugin) or in Settings->Website->Plugins.
3. Set settings of the plugin.  (Note: works with `ldaps://` and `:636` or will always use `tls`).

**Sample Settings**

![Sample Settings](sample_settings.png)

# Notes
* Need to use `ldaps` or `tls`.
* Source was taken from the [Shibboleth Authorization Plugin](https://github.com/pkp/shibboleth).
