# External Authentication

**MantisExtAuth** - A MantisBT plugin provides an external authentication (via AUTH_USER or REMOTE_USER server variables).

## Configuration

- None

**Note:** For MantisBT 2.27 you need to set a `$g_login_method = LDAP;` in **config_inc.php** to retrieve user data (real name, mail, etc) directly from Active Directory.

## Using

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
		Require sspi-group "DevOps"
		Require sspi-user "Administrator"
	</RequireAny>
</Location>
```