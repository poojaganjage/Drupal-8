CONTENTS OF THIS FILE
=====================

 * Introduction
 * Requirements
 * Recommended modules
 * Installation
 * Configuration
 * Maintainers

INTRODUCTION
============

Layout Builder Lock allows administrators to lock sections of a
default layout so users can't perform certain actions when overriding
the layout for an individual entity.

Lock options:

* Update default blocks
* Move default blocks
* Delete default blocks
* Configure a section
* Add a section before or after
* Move blocks from other sections into this section
* Add new blocks: New blocks will be placed under default blocks.
  Users will be able to update, move and delete these blocks.
  Blocks from other sections can be moved into this section
  then, so the 'Move blocks from other sections into this section'
  lock setting will not apply anymore.

As soon as one lock setting is enabled, a user won't be able to
delete the section either.

* For a full description of this module, visit the project page:
   https://www.drupal.org/project/layout_builder_lock

* To submit bug reports and feature suggestions, or track changes:
   https://www.drupal.org/project/issues/layout_builder_lock

REQUIREMENTS
============

This module requires the following core and module:

Drupal core ^8 || ^9
layout_builder (core) (https://www.drupal.org/project/layout_builder)

RECOMMENDED MODULES
===================

This module requires no modules outside of Drupal core.

INSTALLATION
============

Install/Enable the layout_builder_lock module as you would normally
install a contributed Drupal module.

* Visit (https://www.drupal.org/node/1897420) for further information.

* Optionally you can also install using composer:
   composer require 'drupal/layout_builder_lock:^1.0'

CONFIGURATION
=============

* Go to 'Admin -> People -> Permissions' and toggle the roles which
are allowed to configure lock settings. The permission is
'Manage lock settings on sections'.

* On the default display, go to 'Content Types -> Content Type
-> Edit -> Manage Display' and then check 'Layout Options', once
it's checked then save the configuration and click on 'Manage layout'.
Once it has open then add section and click on any 'Configure section'
link to open the section configure form and toggle the options you want
to lock for users.

MAINTAINERS
===========

Current maintainers:
* Kristof De Jaeger (swentel) (https://www.drupal.org/u/swentel)

This project has been sponsored by:

* DROPSOLID (https://www.drupal.org/dropsolid)
  Dropsolid is the Digital Experience Company
  We are a Digital Agency, Drupal integrator and Open Digital Experience
  Platform (DXP) in a single company.

* EPS & KAAS (https://www.drupal.org/eps-kaas)
  eps & kaas is a small digital agency that focusses on branding,
  (Drupal) websites and apps (Android and iPhone).