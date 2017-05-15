
CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Default setup
 * Usage
 * Uninstall
 * FAQ
 * Maintainer and credits


INTRODUCTION
------------

Guardian forces specified Users a.k.a. 'guared users' to log in with only
`drush uli [uid]` or password reset token url. In this way there will be
no need to store and share complex passwords within your organization in a
secure environment. To secure this setup, any account details of the
'guarded user' will be protected against changes and if needed restored to its
default state.


REQUIREMENTS
------------

A working e-mail address or e-mail group.


INSTALLATION
------------

 * Set the preferred mail address of USER 1 in your `settings.php`:
   `$settings['guardian_mail'] = 'admin@example.com';`

 * Install the module and Guardian will send a notification to the configured
   mail address.


DEFAULT SETUP
-------------

* **Default User 1** (uid:1) is protected by the setting
  `$settings['guardian_mail']`. You can add more users, but not remove the
  default protection of User 1.

* **Automated logout** is set for 2 hours. This will ensure that a guarded user
  can't be exposed to miss-use in public spaces. Also it gives the developer
  the option to logout guarded users earlier than other 'normal' users. In
  addition, if a former e-mailgroup user has denied access to the e-mailgroup
  of the guarded user. You will know that any existing session will be
  terminated in time. The setting itself can be changed by adding
  `$settings['guardian_hours'] = [number];` in your project settings file.

* **Account description label** can be changed withing the config
  `admin/config/system/guardian` to nofity guarded users why they can't change
  their account details like 'username', 'password', 'e-mail address and roles'.


USAGE
-----
* Login as a guarded user can be done in two ways:
  * Ask for a password reset: `/user/password`
  * Use the Drush command: `drush @alias uli [uid]`
     Where alias can be any website alias in `~/.drush/aliases.drushrc.php`

* **Hooks** can be used to define more guared users than only uid 1. Or add
  extra metadata to the password reset mails. See file `guardian.api.php` for
  more info about these hooks.


UNINSTALL
---------

A notification will be send to the `guardian_mail` address and all
'guareded users' can ask for a new password and change it to something they can
remember.


FAQ
---

Q: How do you use this with multiple developers?

A: Create a mail group and use that mail address, add as many
   developers as you like to the group and everyone knows when a USER 1
   password request has been sent. Also if someone leaves the group you just
   remove that person from the mail group and your system is still secure from
   unwanted access.

Q: Is it safe to send a password reset to your e-mail?

A: It is safer than writing down your passwords for your temporary intern.

Q: What if someone changes the the password?

A: On cron every guarded user will be checked, if something has changed the
   account will be reset to it defaults.

Q: I didn't touch the site for 2 hours and now I have to log in again?

A: Yes, after 2 hours of inactivity guarded user sessions will be terminated.
   This can be changed by adding `$settings['guardian_hours']` in `settings.php`


MAINTAINER AND CREDITS
----------------------

Maintainer:

 * Tessa Bakker (Tessa Bakker) - https://drupal.org/user/592104

Credits:

 * Albert Skibinski (askibinski) - https://www.drupal.org/user/248999
   Initial Drupal 8 port

 * ezCompany - https://www.drupal.org/ezcompany
   Donating time and resources
