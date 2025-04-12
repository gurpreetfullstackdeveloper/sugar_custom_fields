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
