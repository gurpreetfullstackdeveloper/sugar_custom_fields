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
