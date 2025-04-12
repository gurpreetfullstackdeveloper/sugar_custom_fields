# sugar_custom_fields
Add Custom Fields to Contacts and Create a Scheduler, Scheduler should run once per day and Create a Before Save Logic Hook for the Contacts module.

SugarCRM 14 - XAMPP Setup Checklist (Windows)


Required PHP Extensions
--------------------------
1. Enable the following in php.ini (C:\xampp\php\php.ini):
- extension=soap --> Required for login/authentication
- extension=imap --> For Inbound Email and Campaigns
- extension=mysqli --> MySQL DB connection
- extension=mbstring --> Multibyte string support
- extension=zip --> Module loader/upgrades
- extension=curl --> External API communication
- extension=json --> REST API & data exchange
Action: Remove the semicolon (;) in front of each if present, then restart Apache.


2. MySQL Configuration Fixes
----------------------------
File: my.ini (inside XAMPP MySQL config folder)
- Increase packet size to support dummy/sample data load.
Change: max_allowed_packet=128M
Then restart MySQL from XAMPP Control Panel.


3. PHP Session Configuration
----------------------------
Ensure sessions work properly:
- session.save_path = "C:\xampp\tmp"


4. Cron Job / Scheduler Setup
-----------------------------
To run Sugar Schedulers (cron jobs): Create a batch file (e.g., sugar_cron.bat): cd C:\xampp\htdocs\SugarFresh
C:\xampp\php\php.exe -f cron.php
Then schedule it in Windows Task Scheduler to run whenever you want to execute.


5. Common Issues and Fixes
-------------------------- -
Login Not Redirecting to Dashboard: => Fixed by enabling "extension=soap"
- Dummy Data Insert Error (max_allowed_packet): => Increased packet size in my.ini
- Session Not Set: => Verified session.save_path and tested with script - sugarcrm.log
- SOAP not being loaded, fixed after enabling it


Summary
-------
Must-have PHP extensions for SugarCRM 14 on XAMPP (Windows): - soap - imap - mbstring - curl - json - mysqli - zip
-------

Got error while installation:-
Error - Unable to connect to Full Text Search server, please verify your settings. sugar crm provided details :- Search Engine Type - ElasticSearch Host - localhost Port - 9200

That error means SugarCRM is trying to connect to Elasticsearch (for Full Text Search), but it's failing — likely because:
Elasticsearch is not running

Then Download Elasticsearch 7.17.9 (recommended)
https://www.elastic.co/downloads/past-releases/elasticsearch-7-17-9


Step-by-Step Troubleshooting
✅ 1. After extracting the correct version:

Start Elasticsearch
Open Command Prompt:

cd C:\elasticsearch-7.17.9\bin
Elasticsearch.bat

Now visit:
📍 http://localhost:9200 in your browser

If Elasticsearch is running, you'll see a JSON response like:
{
"name": "node-name",
"cluster_name": "elasticsearch",
"cluster_uuid": "...",
"version": {
"number": "7.17.0",
...
}
}

Check if Elasticsearch is Running
Open your browser and visit:
http://localhost:9200/





<h1>TASK1 - Add Custom Fields to Contacts 
	and add these fields to the Record Layout. </h1>

<h2>SugarCRM 14 – Checklist: Add Custom Fields to Contacts Using Studio (Windows)</h2>

Step 1: Login
☐ Login to SugarCRM as an Administrator.

Step 2: Open Studio
☐ Navigate to Admin > Studio.

Step 3: Select Contacts Module
☐ In Studio, click on "Contacts".

Step 4: Add Custom Field
☐ Click on "Fields".
☐ Click on "Add Field".
☐ Choose field type (e.g., Text, Dropdown).
☐ Set the field name and label.
☐ Save the field.

Step 5: Edit Record View Layout
☐ In Contacts module (still in Studio), go to "Layouts" > "Record View".

Step 6: Add a New Row (Important)
☐ In the left panel, locate the "Toolbox".
☐ Drag "Blank Space" or another tool (like "Spacer") into the layout grid between two existing rows.
☐ This action creates a new row in the layout.

Step 7: Add Custom Field to Layout
☐ From the left panel under "New Fields", find your custom field.
☐ Drag and drop the field into the newly created row.
☐ Arrange as needed.

Step 8: Save and Deploy
☐ Click "Save & Deploy" to apply layout changes.

Step 9: Verify
☐ Go to a Contact record and confirm the new field appears in the Record View.



=========================================================

<h1>Task 2: Implement Custom Functionality</h1>

<h2>Add a Scheduler</h2>
a. Create a Scheduler (time-based process, similar to a cron job). <br>
b. The Scheduler should run once per day and perform the following: <br>
c. Loop through all Contacts and resave them. <br>
d. Bonus Points: Ensure the date_modified field does not get updated when saving. <br>

<br><br>

Scheduler created by code
http://localhost/SugarFresh/create_scheduler.php
<br><br>
Code shown as below :-

```php
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
```

And it calls function “resave_contacts_without_modifying_date'” created in
C:\xampp\htdocs\SugarFresh\custom\modules\Schedulers\Jobs\ResaveContactsJob.php
Its code as shown below :

```php
<?php

function resave_contacts_without_modifying_date()
{
	echo ">>> ResaveContactsJob started\n";
	$GLOBALS['log']->fatal(">>> ResaveContactsJob started");

	// Use SugarQuery for a more efficient query
	$query = new SugarQuery();
	$query->select(array('id'));
	$query->from(BeanFactory::getBean('Contacts'));
	$query->where()->equals('deleted', 0);
	$query->limit(10); // 👈 Limit to 10 contacts for faster testing

	$results = $query->execute();

	$count = 0;

	foreach ($results as $result) {
    	$contact = BeanFactory::getBean('Contacts', $result['id']);
    	if ($contact) {
        	$contact->update_date_modified = false;
        	$contact->save();

        	echo ">>> Resaved Contact ID: {$contact->id}\n";
        	$GLOBALS['log']->fatal(">>> Resaved Contact ID: " . $contact->id);
        	$count++;
    	}
	}

	echo ">>> ResaveContactsJob completed: {$count} contacts processed.\n";
	$GLOBALS['log']->fatal(">>> ResaveContactsJob completed: {$count} contacts processed.");

	return true;
}

?>
```

Also, created file “resave_contacts_ext.php”

```php
<?php
$job_strings[] = 'resave_contacts_without_modifying_date';
?>
```

having path 
C:\xampp\htdocs\SugarFresh\custom\modules\Schedulers\Ext\ScheduledTasks\resave_contacts_ext.php

So, all 4 below points implemented

Add a Scheduler  <br>
A. Create a Scheduler (time-based process, similar to a cron job).<br>
B. The Scheduler should run once per day and perform the following:<br>
C. Loop through all Contacts and resave them.<br>
D. Bonus Points: Ensure the date_modified field does not get updated when saving.<br>
<br><br><br>


<h1>For below task :- </h1>

<h4> Add a Before Save Logic Hook on Contacts </h4>
<h4> Create a Before Save Logic Hook for the Contacts module.</h4>
<h4> When a Contact record is saved, perform the following:</h4>
<h4> Increment counter_c by 1. </h4>
<h4> If the record is new, set counter_c = 1. </h4>
<h4> If the record already exists, increment the counter by 1.</h4>
<h4> Set epoch_time_c to the current epoch timestamp.</h4>
<h4> Set epoch_time_utc_c to a formatted UTC timestamp in the format:</h4>

is as follows

Created  “logic_hooks.php”   file with the path 
C:\xampp\htdocs\SugarFresh\custom\modules\Contacts\logic_hooks.php

Having below code:-
```php
<?php
$hook_array['before_save'][] = array(
	1,
	'Update counter_c, epoch_time_c, epoch_time_utc_c fields',
	'custom/modules/Contacts/CustomBeforeSaveHook.php',
	'CustomBeforeSaveHook',
	'updateFields'
);
?>
```

Also, created “CustomBeforeSaveHook.php” file having path 
C:\xampp\htdocs\SugarFresh\custom\modules\Contacts\CustomBeforeSaveHook.php
Having below code:

```php
<?php

class CustomBeforeSaveHook
{
	public function updateFields($bean, $event, $arguments)
	{
    	// counter_c: Set to 1 for new, or increment existing
    	if (empty($bean->fetched_row['id'])) {
        	$bean->counter_c = 1;
    	} else {
        	$oldValue = isset($bean->fetched_row['counter_c']) ? (int)$bean->fetched_row['counter_c'] : 0;
        	$bean->counter_c = $oldValue + 1;
    	}

    	// epoch_time_c: Current epoch timestamp (seconds)
    	$bean->epoch_time_c = time();

    	// epoch_time_utc_c: UTC time with microseconds
    	$microtime = microtime(true);
    	$dt = DateTime::createFromFormat('U.u', sprintf('%.6f', $microtime));
    	$dt->setTimezone(new DateTimeZone('UTC'));
    	$bean->epoch_time_utc_c = $dt->format('Y-m-d H:i:s.u');
	}
}

?>
```

So, hook was created and it called the class  “CustomBeforeSaveHook” and all the required functionality of 

<br>
Add a Before Save Logic Hook on Contacts<br>
Create a Before Save Logic Hook for the Contacts module.<br>
When a Contact record is saved, perform the following:<br>
Increment counter_c by 1.<br>
If the record is new, set counter_c = 1.<br>
If the record already exists, increment the counter by 1.<br>
Set epoch_time_c to the current epoch timestamp.<br>
Set epoch_time_utc_c to a formatted UTC timestamp in the format:<br>
<br>
implemented using above two files.<br><br><br>


Then followed major step as below:- <br>
<br>
<h2>Quick Repair and Rebuild</h2><br>
Run:<br>
<h2>Admin → Repair → Quick Repair and Rebuild</h2><br>
<br>
</h3>This will register the logic hook properly.</h3><br><br><br>


Then, batch file will call the cron.php and will ultimately trigger all active schdulers <br>
<br>
But for testing purpose, I just created and executed the custom scheduler file named “run_custom_scheduler.php” having path <br><br>
C:\xampp\htdocs\SugarFresh\run_custom_scheduler.php <br>
<br>
Below is the code of above file ➖
<br>

```php
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

?>
```
<br>
So, it called resave_contacts_without_modifying_date();  function  <br>
<br>
And while re-saving the data “before save” hook is called. This is the flow.
<br>
==================================<br>
<br>
When executing  run_custom_scheduler.php on browser :-<br>
http://localhost/SugarFresh/run_custom_scheduler.php<br>
<br>
It gave below output on browser:-<br>
<br><br>
Running custom scheduler job: resave_contacts_without_modifying_date()... >>> ResaveContactsJob started >>> Resaved Contact ID: 0003f2f8-16bd-11f0-bb02-0068eb5af528 >>> Resaved Contact ID: 007c9712-16bd-11f0-97db-0068eb5af528 >>> Resaved Contact ID: 013166e2-16bd-11f0-b62c-0068eb5af528 >>> Resaved Contact ID: 019d9b6e-16bd-11f0-b5b9-0068eb5af528 >>> Resaved Contact ID: 022885b2-16bd-11f0-942b-0068eb5af528 >>> Resaved Contact ID: 0310d998-16bd-11f0-8c37-0068eb5af528 >>> Resaved Contact ID: 037ef450-16bd-11f0-b0c3-0068eb5af528 >>> Resaved Contact ID: 03dbf9fc-16bd-11f0-9c98-0068eb5af528 >>> Resaved Contact ID: 0446e078-16bd-11f0-bfbc-0068eb5af528 >>> Resaved Contact ID: 04b6ff0c-16bd-11f0-8005-0068eb5af528 >>> ResaveContactsJob completed: 10 contacts processed. ✅ Custom scheduler executed successfully.
<br><br>
The count increased by 1 on every new hit of scheduler as shown below :-<br>
<br><br>
And thus below requirement <br><br>
<br>
If the record is new, set counter_c = 1.<br>
If the record already exists, increment the counter by 1.<br>
<br>
is satisfied.<br>
