
--------------------------------------------------------------------------------
Matomo Reports
--------------------------------------------------------------------------------

Maintainer:  xlyz, xlyz@tiscali.it, shelane

This module adds a matomo reports section and imports key traffic information 
from your matomo server.

Project homepage: https://drupal.org/project/matomo_reports

Issues: https://drupal.org/project/issues/matomo_reports

Sponsored by Relinc: http://www.relinc.it


Installation
------------

 * composer...//TODO: add composer instructions

 * Add your Matomo reports token_auth either globally (at 
   admin/config/system/matomo) or individually (in each user profile)
 

Documentation
-------------

Reports
This modules provides some of the matomo reports directly in your Drupal
site. Just follow the installation instructions and go to 
admin/reports/matomo_reports.

Multisite
Matomo reports will show statistics of every site the token_auth has view
permissions on the matomo server. Administrators can limit access to only  
allowed sites.

Block
A matomo page report block is available for in-page statistics. You must 
enable the block and place it in the region you desire.

Matomo Matomo Web Analytics
Matomo Matomo Web Analytics (https://drupal.org/project/matomo) is not a dependency
any more, but matomo is required to track your site.

