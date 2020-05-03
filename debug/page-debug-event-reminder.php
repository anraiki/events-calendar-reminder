<?php

/*
 * Template Name: Event Tester
 * description: >-
  Page template without sidebar
 */

// * For Debugging Purpose, 
// * * Stick this page template into your theme
// * * Make a New Page
// * * Run it as a template under "Event Tester"
// * * And customize away

$reminders = [];

//Last Reminder
$reminders[] = [
    "label" => "first",
    "time" => "-72 hours", 
];

//Last Reminder
$reminders[] = [
    "label" => "last",
    "time" => "-3 hours", 
];
 
if( class_exists("Tribe__Tickets__Tickets") ) {

    //Set Event Parameters and grab the events
    $event_arg  = [ "start_date" => date( 'Y-m-d H:i:s' , time() ) ];
    $events     = tribe_get_events( $event_arg );

    foreach( $events as $event ) {
        //Get Tickets
        $tickets = tribe_tickets_get_attendees( $event->ID );
        $emails = [];

        //Emails may be duplicated as they can have multiple tickets so we grab only unique ones
        foreach( $tickets as $ticket ) {
            if( !in_array($ticket["purchaser_email"],$emails) ) $emails[] = $ticket["purchaser_email"];
        }
        
        //When does the Event Start?
        $event_date = $event->event_date;

        foreach( $reminders as $reminder ) {
            
            $reminder_flag = get_post_meta( $event->ID, "event_reminder_" . $reminder["label"] );
            $reminder_time = date_create($event_date)->modify($reminder["time"]);

            // Check if our Current Time has pass the Reminder Time
            // And if we have not sent the reminder, then we can send one
            if( strtotime($current_time) > strtotime($reminder_time) && !$reminder_flag ) {
                
                // We are ready to Email
                foreach( $emails as $email ) {
                    $subject = "Event Reminder for Event";
                    $message = "Event Body";
                    wp_mail( $email, $subject, $message );
                }

                //Update the Reminder Flag to no longer subsequently remind
                update_post_meta( $event->ID,  "event_reminder_" . $reminder["label"], true );

            }

        }

    }

}

?>