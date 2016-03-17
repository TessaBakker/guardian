
CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Installation
 * Uninstall
 * Usage
 * FAQ
 * Maintainer


INTRODUCTION
------------

Guardian protects you against complex security policies for storing and sharing
USER 1 passwords. And protects USER 1 account data in the most simple way
against unwanted user, password, role or status changes.

If there is a change that something has changed the USER 1 account, Guardian
sends a notice on cron.


REQUIREMENTS
------------

A working e-mail address or e-mail group.


INSTALLATION
------------

 * Set the preferred mail address of USER 1 in your settings.php:
   $conf['guardian_mail'] = 'admin@example.com'

 * Install the module and Guardian will send a notification to the configured
   mail address.


UNINSTALL
---------

When you disable Guardian a notification will be send
and a new password will be set.


USAGE
-----
Login as USER 1 can be done in two ways:

 * Ask for a password reset: /user/password

 * Use the Drush command: drush @alias uli 1
   Where alias can be any website alias in ~/.drush/aliases.drushrc.php


FAQ
---

Q: How do you use this with multiple developers?

A: Create a mail group and use that mail address for USER 1, add as many
   developers as you like to the group and everyone knows when a USER 1
   password request has been sent. Also if someone leaves the group you just
   remove that person from the mail group and your system is still secure from
   unwanted access.

Q: Is it save to send a password reset to your e-mail?

A: It is saver than writing down your USER 1 password for your temporary intern.

Q: What if someone changes the the password?

A: The USER 1 profile can only be edited by USER 1. And could it happen that
   someone changed the password in any other way, a mail will be send on every
   cron to the preferred mail address, USER 1 account details will be fixed and
   any USER 1 session will be terminated.

Q: I didn't touch the site for 2 hours and suddenly I needed to login again,
   is this normal?

A: Yes, after 2 hours of inactivity USER 1 sessions will be terminated.
   This can be changed by adding $conf['guardian_hours']


MAINTAINER
----------

Current maintainer:
 * Tessa Bakker (Tessa Bakker) - https://drupal.org/user/592104
