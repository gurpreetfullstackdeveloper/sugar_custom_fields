<?php
$hook_array['before_save'][] = array(
    1,
    'Update counter_c, epoch_time_c, epoch_time_utc_c fields',
    'custom/modules/Contacts/CustomBeforeSaveHook.php',
    'CustomBeforeSaveHook',
    'updateFields'
);
