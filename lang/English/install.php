<?php

// Language definitions used in install.php, localized by adaur

$lang_install = array(

'Choose install language'		=>	'Choose the install script language',
'Choose install language info'	=>	'The language used for this install script. The default language used for the board itself can be set below.',
'Install language'				=>	'Install language',
'Change language'				=>	'Change language',
'Already installed'				=>	'It seems like ForkBB is already installed. You should go <a href="index.php">here</a> instead.',
'You are running error'			=>	'You are running %1$s version %2$s. ForkBB %3$s requires at least %1$s %4$s to run properly. You must upgrade your %1$s installation before you can continue.',
'My ForkBB Forum'				=>	'My ForkBB Forum',
'Description'					=>	'Unfortunately no one can be told what ForkBB is - you have to see it for yourself.',
'Username 1'					=>	'Usernames must be at least 2 characters long.',
'Username 2'					=>	'Usernames must not be more than 25 characters long.',
'Username 3'					=>	'The username guest is reserved.',
'Username 4'					=>	'Usernames may not be in the form of an IP address.',
'Username 5'					=>	'Usernames may not contain all the characters \', " and [ or ] at once.',
'Username 6'					=>	'Usernames may not contain any of the text formatting tags (BBCode) that the forum uses.',
'Short password'				=>	'Passwords must be at least 6 characters long.',
'Passwords not match'			=>	'Passwords do not match.',
'Wrong email'					=>	'The administrator email address you entered is invalid.',
'No board title'				=>	'You must enter a board title.',
'Error default language'		=>	'The default language chosen doesn\'t seem to exist.',
'Error default style'			=>	'The default style chosen doesn\'t seem to exist.',
'No DB extensions'				=>	'This PHP environment does not have support for any of the databases that ForkBB supports. PHP needs to have support for either MySQL, PostgreSQL or SQLite in order for ForkBB to be installed.',
'Administrator username'		=>	'Administrator\'s username',
'Administrator email'			=>	'Administrator\'s email',
'Board title'					=>	'Board title',
'Base URL'						=>	'The URL (without trailing slash) of your ForkBB forum. This must be correct.',
'Required field'				=>	'is a required field in this form.',
'ForkBB Installation'			=>	'ForkBB Installation',
'Welcome'						=>	'You are about to install ForkBB. In order to install ForkBB, you must complete the form set out below. If you encounter any difficulties with the installation, please refer to the documentation.',
'Install'						=>	'Install ForkBB %s',
'Errors'						=>	'The following errors need to be corrected:',
'Database setup'				=>	'Database setup',
'Info 1'						=>	'All information we need to create a connection with your database.',
'Select database'				=>	'Select your database type',
'Info 2'						=>	'Select a database. We support SQLite, MySQL and PostgreSQL.',
'Database type'					=>	'Database type',
'Required'						=>	'(Required)',
'Database hostname'				=>	'Enter your database server hostname',
'Info 3'						=>	'You should be able to get this info from your web host, if <code>localhost</code> does not work.',
'Database server hostname'		=>	'Database server hostname',
'Database enter name'			=>	'Enter the name of your database',
'Info 4'						=>	'The name of the database you want to install ForkBB on.',
'Database name'					=>	'Database name',
'Database enter informations'	=>	'Enter your database username and password',
'Database username'				=>	'Database username',
'Info 5'						=>	'Your MySQL username and password (ignore of SQLite).',
'Database password'				=>	'Database password',
'Database enter prefix'			=>	'Enter database table prefix',
'Info 6'						=>	'If you want to run multiple ForkBB installations in a single database, change this.',
'Table prefix'					=>	'Table prefix',
'Administration setup'			=>	'Administration setup',
'Info 7'						=>	'Create the very first account on your board.',
'Info 8'						=>	'Your username should be between 2 and 25 characters long. Your password must be at least 6 characters long. Salt must be at least 10 characters long. Remember that passwords and salt are case-sensitive.',
'Password'						=>	'Password',
'Confirm password'				=>	'Confirm password',
'Board setup'					=>	'Board setup',
'Info 11'						=>	'Settings for your board. You can change this later.',
'General information'			=>	'Enter your board\'s title and description.',
'Board description'				=>	'Board description (supports HTML)',
'Appearance'					=>	'Appearance',
'Info 15'						=>	'Make your forum yours. Choose a language and a style for your board.',
'Default language'				=>	'Default language',
'Default style'					=>	'Default style',
'Start install'					=>	'Start install',
'DB type not valid'				=>	'\'%s\' is not a valid database type',
'Table prefix error'			=>	'The table prefix \'%s\' contains illegal characters or is too long. The prefix may contain the letters a to z, any numbers and the underscore character. They must however not start with a number. The maximum length is 40 characters. Please choose a different prefix',
'Prefix reserved'				=>	'The table prefix \'sqlite_\' is reserved for use by the SQLite engine. Please choose a different prefix',
'Existing table error'			=>	'A table called \'%susers\' is already present in the database \'%s\'. This could mean that ForkBB is already installed or that another piece of software is installed and is occupying one or more of the table names ForkBB requires. If you want to install multiple copies of ForkBB in the same database, you must choose a different table prefix',
'InnoDB off'					=>	'InnoDB does not seem to be enabled. Please choose a database layer that does not have InnoDB support, or enable InnoDB on your MySQL server',
'Administrators'				=>	'Administrators',
'Administrator'					=>	'Administrator',
'Moderators'					=>	'Moderators',
'Moderator'						=>	'Moderator',
'Guests'						=>	'Guests',
'Guest'							=>	'Guest',
'Members'						=>	'Members',
'Announcement'					=>	'Enter your announcement here.',
'Rules'							=>	'Enter your rules here',
'Maintenance message'			=>	'The forums are temporarily down for maintenance. Please try again in a few minutes.',
'Test post'						=>	'Test topic',
'Message'						=>	'If you are looking at this (which I guess you are), the install of ForkBB appears to have worked! Now log in and head over to the administration control panel to configure your forum.',
'Test category'					=>	'Test category',
'Test forum'					=>	'Test forum',
'This is just a test forum'		=>	'This is just a test forum',
'Alert cache'					=>	'<strong>The cache directory is currently not writable!</strong> In order for ForkBB to function properly, the directory <em>%s</em> must be writable by PHP. Use chmod to set the appropriate directory permissions. If in doubt, chmod to 0777.',
'Alert avatar'					=>	'<strong>The avatar directory is currently not writable!</strong> If you want users to be able to upload their own avatar images you must see to it that the directory <em>%s</em> is writable by PHP. You can later choose to save avatar images in a different directory (see Admin/Options). Use chmod to set the appropriate directory permissions. If in doubt, chmod to 0777.',
'Alert upload'					=>	'<strong>File uploads appear to be disallowed on this server!</strong> If you want users to be able to upload their own avatar images you must enable the file_uploads configuration setting in PHP. Once file uploads have been enabled, avatar uploads can be enabled in Administration/Options/Features.',
'ForkBB has been installed'		=>	'ForkBB has been installed. To finalize the installation please follow the instructions below.',
'Final instructions'			=>	'Final instructions',
'Info 17'						=>	'To finalize the installation, you need to click on the button below to download a file called config.php. You then need to upload this file to directory /include of your ForkBB installation.',
'Info 18'						=>	'Once you have uploaded config.php, ForkBB will be fully installed! At that point, you may <a href="index.php">go to the forum index</a>.',
'Download config.php file'		=>	'Download config.php file',
'ForkBB fully installed'		=>	'ForkBB has been fully installed! You may now <a href="index.php">go to the forum index</a>.',
'Salt for password' => 'Salt for passwords',

);
