NOTICE:

Bellevue College has moved in a different direction for SSO and is no longer actively working
on this repository. If you are looking for a CAS solution for WordPress, try the following:

* https://github.com/uthsc/UTHSC-WPCAS


=== Fork: wpCAS-with-LDAP ===

This project was forked from http://wordpress.org/support/plugin/wpcas-w-ldap
which appears to have been abandoned. We've fixed the issue discussed here

http://wordpress.org/support/topic/fix-for-role-reset

and are now sharing with the community in the hopes that further fixes,
improvements and development can continue.

- shawn.south@bellevuecollege.edu

(The remainder of this file is the contents of the previous project.)

=== wpCAS-w-LDAP ===
Contributors: yianniy
Tags: cas, phpcas, wpCAS-w-LDAP, central authentication service, authentication, auth, ldap, lightweight directory access protocol
Requires at least: 2.7
Tested up to: 2.9.2
Stable tag: trunk

Description: Plugin to integrate WordPress or WordPressMU with existing <a href="http://en.wikipedia.org/wiki/Central_Authentication_Service">CAS</a> single sign-on architectures and <a href="http://en.wikipedia.org/wiki/Ldap">LDAP</a> for grabbing user information.

== Description ==

wpCAS-w-LDAP allows you to use your own CAS architecture to authenticate users in your Wordpress blog. It also allows you to configure an LDAP connection to get user information for user not already members of your WordPress installation.

Based largely on <a href="http://wordpress.org/extend/plugins/wpcas/">wpCAS</a> by <a href="http://maisonbisson.com/blog/">Casey Bisson</a>, which was largely based on <a href="http://schwink.net">Stephen Schwink</a>'s <a href="http://wordpress.org/extend/plugins/cas-authentication/">CAS Authentication</a> plugin.

<a href="http://en.wikipedia.org/wiki/Central_Authentication_Service">CAS From Wikipedia</a>:

<blockquote>The <b>Central Authentication Service (CAS)</b> is a single sign-on protocol for the web. Its purpose is to permit a user to log into multiple applications simultaneously and automatically. It also allows untrusted web applications to authenticate users without gaining access to a user's security credentials, such as a password. The name CAS also refers to a software package that implements this protocol.</blockquote>

Users who attempt to login to WordPress are redirected to the central CAS sign-on screen. After the user's credentials are verified, s/he is then redirected back to the WordPress site. If the CAS username matches the WordPress username, the user is recognized as valid and allowed access.

<a href="http://en.wikipedia.org/wiki/AuthZ">Authorization</a> of that user's capabilities is based on native WordPress settings and functions. CAS only authenticates that the user is who s/he claims to be.

If the CAS user does not have an account in the WordPress site, depending on the plugin's settings, the user is either
1. Denied access or
1. Added to the user database with the default role set on the plugin's options page.

LDAP is included as an option for getting user information when they are being added to the database. If LDAP is available on your installation of PHP, you will be given the option of configuring it for this purpose.

<a href="http://en.wikipedia.org/wiki/Ldap">LDAP From Wikipedia</a>

<blockquote>The <b>Lightweight Directory Access Protocol</b>, or <b>LDAP</b>, is an <a href="/wiki/Application_protocol" title="Application protocol" class="mw-redirect">application protocol</a> for querying and modifying <a href="/wiki/Directory_service" title="Directory service">directory services</a> running over <a href="/wiki/Internet_protocol_suite" title="Internet protocol suite" class="mw-redirect">TCP/IP</a>.</blockquote>

== Installation ==

1. Download <a href="http://www.ja-sig.org/wiki/display/CASC/phpCAS">phpCAS</a> and place it on your webserver so that it can be included by the wpCAS-w-LDAP plugin.
1. Place the plugin folder in your `wp-content/plugins/` directory and activate it.
1. Set any options you want in Settings -> wpCAS with LDAP _or_ in the `wpcasldap-conf.php` file.
1. The plugin starts intercepting authentication attempts as soon as you activate it. Use another browser or another computer to test the configuration.

= wpcasldap-conf.php =
wpCAS-w-LDAP can be configured either via the settings page in the WordPress dashboard, or via a configuration file. See `wpcasldap-conf-sample.php` for an example. If a config file is used, it overrides any settings that might have been made via the settings page and configured portion of that page are hidden.

Use of `wpcasldap-conf.php` is recommended for the CAS and LDAP portions of an WordPressMU installations, as doing so hides the settings menu from users. The option to use LDAP, add users, and default role can be left to blog administrators.

== Frequently Asked Questions ==

= What version of phpCAS should I use? =
wpCAS-w-LDAP has been tested with phpCAS version 1.0.1.

= Where do I get phpCAS =
<a href="http://www.ja-sig.org/wiki/display/CASC/phpCAS">http://www.ja-sig.org/wiki/display/CASC/phpCAS</a>

= How's it work? =
Users who attempt to login to WordPress are redirected to the central CAS sign-on screen. After the user's credentials are verified, s/he is then redirected back to the WordPress site. If the CAS username matches the WordPress username, the user is recognized as valid and allowed access. If the CAS username does not exist in WordPress, you can define a function that could provision the user in the site.

= What's the relationship between LDAP and CAS? =
There is none.

= What if LDAP is not installed on my server? =
wpCAS-w-LDAP will ignore attempts to use LDAP and will essentially work just like <a href="http://wordpress.org/extend/plugins/wpcas/">wpCAS</a> by <a href="http://maisonbisson.com/blog/">Casey Bisson</a>.

= Doesn't this already exist? =
wpCAS-w-LDAP replicates the functionality of <a href="http://wordpress.org/extend/plugins/wpcas/">wpCAS</a> by <a href="http://maisonbisson.com/blog/">Casey Bisson</a>. It adds LDAP functionality to his original code. I created wpCAS-w-LDAP so that when new users are added my WordpressMU install, they will be added with a full set of information.
