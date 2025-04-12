<?php

define('sugarEntry', true);
require_once('include/entryPoint.php');

global $current_user;
$current_user = BeanFactory::getBean('Users', 1); // Admin user

require_once('modules/Schedulers/Scheduler.php');

// Create the scheduler
$scheduler = BeanFactory::newBean('Schedulers');
$scheduler->name = 'Resave Contacts Daily';
$scheduler->job = 'function::resave_contacts_without_modifying_date';
$scheduler->date_time_start = gmdate('Y-m-d H:i:s');
$scheduler->job_interval = '0::0::*::*::*'; // Daily at midnight
$scheduler->status = 'Active';
$scheduler->created_by = $current_user->id;
$scheduler->modified_user_id = $current_user->id;
$scheduler->save();

echo "Scheduler created successfully!";
?>