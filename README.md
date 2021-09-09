# Drupal Security Pack

CONTENTS OF THIS FILE
---------------------

* Introduction
* Requirements
* Installation
* Configuration
* Troubleshooting
* FAQ
* Maintainers

INTRODUCTION
------------
Security Pack automatically installs a number of
recommended security modules onto your Drupal site.

Out of the box configuration is tailored to be as unobtrusive as possible, 
while still providing good base security configuration.

After installation, it enables/configures the following:

1. Password Policy:
    * All authenticated users will have a password expiration of 90 days.
    * Passwords must be at least 12 characters in length.
    * Passwords must have at least 3 different character types.
    
2. Auto Logout:
    * All users will be automatically logged out 
      after 30 minutes of inactivity.
    
3. Antibot:
    * All forms on the site (including content, login and registration forms)
      are protected with Antibot.
    * This requires JavaScript to be enabled before a form can be used, 
      reducing the number of spam submissions.
    
4. Login Security:
    * Login Security is configured to soft-block hosts after 
      10 invalid login attempts within an hour.
    * Soft blocked hosts can still use the site anonymously
      but will not be able to use any login forms.
    
5. Security Kit:
    * Content Security Policy Headers are enabled in reporting only mode.
    * Out of the box CSP configuration provided by Security Pack 
      works fine in enforcement mode with a vanilla Drupal install, 
      but other custom modules or themes might require additional fine tuning.
    * A good way to test this is to do a thorough rundown of your site with
      the browser console open. If any Content Security Policy errors
      are logged, you can add them manually to the SecKit settings. 
    * Once you're satisfied that nothing legitimate is being blocked, 
      you should disable "Report Only" in the SecKit settings to 
      start enforcing the Content Security Policy.
    * CSRF protection is enabled to stop other domains 
      making requests to your Drupal site.
    * The HTTP Strict Transport Security (HSTS) header is enabled 
      to force browsers to serve your site over HTTPS.

6. Username Enumeration Prevention:
    * Tries to prevent anonymous users from discovering the IDs 
      or usernames of registered accounts.

REQUIREMENTS
------------

This module requires the following:

* [Password Policy] (https://www.drupal.org/project/password_policy)
* [Auto Logout] (https://www.drupal.org/project/autologout)
* [Antibot] (https://www.drupal.org/project/antibot)
* [Login Security] (https://www.drupal.org/project/login_security)
* [Security Kit] (https://www.drupal.org/project/seckit)
* [Username Enumeration Prevention] (https://www.drupal.org/project/username_enumeration_prevention)

INSTALLATION
------------

* It is recommended that you install and manage Security Pack using Composer.
  However, if you prefer not to use it, simply download
  and copy the modules listed in the requirements section
  to your site's modules folder. When you enable Security Pack, 
  it will install and configure them automatically.

CONFIGURATION
------------

* Security Pack does not have very much configuration in and of itself.
  However, a reset form is available at 
  Administration >> System >> Security Pack. This will allow you to reset
  provided configuration to default if you have made any manual changes.

TROUBLESHOOTING
---------------
* As described above, you can reset Security Pack 
  to default by using the configuration form.
* If you decide to no longer use Security Pack, 
  you can safely uninstall it and its accompanying modules via 
  Drush or the admin UI.

FAQ
---

Q: Who is Security Pack for?

A: This module is aimed at site builders who lack PHP knowledge
   or are still new to Drupal in general.
   It hopes to provide a good base level of protection against common
   threats such as spambots or attackers who try to 
   bruteforce login credentials.


MAINTAINERS
-----------

Current maintainers:
   * Ming Quah (FleetAdmiralButter) - 
     https://www.drupal.org/u/fleetadmiralbutter
   