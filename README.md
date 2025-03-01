# External Authentication

**MantisExtAuth** - A MantisBT plugin provides an external authentication (via AUTH_USER or REMOTE_USER server variables).

## Presentation

The plugin was originally designed for seamless integration with Microsoft Active Directory (AD) domains. It uses a combination of NTLM authentication using the Apache web server's `mod_authn_ntml` module and LDAP user data retrieval directly from AD.

To get the avatars of AD users, you can use my other plugin: [MantisLdapAvatar](https://github.com/raspopov/MantisLdapAvatar).

This plugin makes MantisBT believe that the user whose name is specified in the `AUTH_USER` or `REMOTE_USER` web server variables is already authenticated. The entire authentication process is passed to the web server. The users are created automatically.

## Installation

- Download and extract the plugin files to your computer.
- Copy the MantisExtAuth catalogue into the MantisBT plugin directory.
- In MantisBT, go to the Manage -> Manage Plugins page. You will see a list of installed and currently not installed plugins.
- Click the Install button next to "External Authentication" to install a plugin.

## Configuration

- None

**Note:** For MantisBT 2.27 you need to set a `$g_login_method = LDAP;` in **config_inc.php** to retrieve user data (real name, mail, etc) directly from Active Directory, and a `$g_reauthentication = OFF;` to skip re-authentication when plugin is not temporarily loaded during some MantisBT operation like plugin installation or update.

## Application use

Example of MantisBT configuration for Apache web server and NTLM authentication:

```
# for Auth*
LoadModule authn_core_module modules/mod_authn_core.so

# for Require*
LoadModule authz_core_module modules/mod_authz_core.so

# for NTLM* 
# mod_authn_ntml 1.0.8 stable for Apache 2.4.x x64 (25.05.2020)
LoadModule auth_ntlm_module modules/mod_authn_ntlm.so

<Directory "${SRVROOT}/htdocs/mantisbt">
	AllowOverride All
</Directory>
<Location /mantisbt>
	AuthType SSPI
	AuthName "MantisBT"
	NTLMAuth On
	NTLMOmitDomain On
	<RequireAny>
		AuthMerging And
		Require sspi-group "DevOps"
		Require sspi-user "Administrator"
	</RequireAny>
</Location>
```

## Similar plugins

- [adLogin](https://github.com/mantisbt-plugins/adLogin) by Cas Nuy
- [BasicAuth](https://github.com/davewood/mantis-basic-auth) by David Schmidt (outdated)
