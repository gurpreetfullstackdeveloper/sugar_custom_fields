<?php
define('sugarEntry', true);
require_once('include/entryPoint.php');

// Set current user as admin
global $current_user;
$current_user = BeanFactory::getBean('Users', 1);

// Include your custom scheduler job file
require_once('custom/modules/Schedulers/Jobs/ResaveContactsJob.php');

echo "Running custom scheduler job: resave_contacts_without_modifying_date()...\n";

// Call your custom function
resave_contacts_without_modifying_date();

echo "✅ Custom scheduler executed successfully.\n";

