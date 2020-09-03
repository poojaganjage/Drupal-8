CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Installation
 * Requirements
 * Configuration
 * Maintainers


INTRODUCTION
------------

The Flood Unblock module allows site administrators to remove ip-addresses and
users from the flood table.

Drupal blocks logins by a user that has more than 5 failed login attempts
(within six hours) or an IP address that has more than 50 failed login attempts
(within one hour). This module provides a simple user interface to allow site
administrators to clear entries from the flood table, which unblocks the user
and allows them to log in without waiting.

 * For a full description of the module, visit the project page:
   https://drupal.org/project/flood_unblock

 * To submit bug reports and feature suggestions, or to track changes:
   https://drupal.org/project/issues/flood_unblock


INSTALLATION
------------

 * Install the module as you would normally install a contributed Drupal
   module. 
 * Visit https://www.drupal.org/docs/extending-drupal/installing-modules
   for further information.


REQUIREMENTS
------------

This module requires no modules outside of Drupal core.


CONFIGURATION
-------------

 * Go to Flood Unblock administration under People menu. You can also access
   it directly 'admin -> people -> flood-unblock'.

 * The user interface displays a table with flood entries, including the
   blocked status, type of block (ip or user), count, account/user name, and
   ip address.

 * Select the flood entries you want to clear and then click Clear flood.


MAINTAINERS
-----------

Current maintainers:

 * Boris Doesborg (batigolix) (https://drupal.org/u/batigolix)
 * Fabian de Rijk (fabianderijk) (https://www.drupal.org/u/fabianderijk)

Supporting organizations:
 
 * Finalist - https://www.drupal.org/finalist
