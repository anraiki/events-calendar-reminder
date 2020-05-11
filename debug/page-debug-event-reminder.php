<?php

/*
 * Template Name: Event Tester
 */

// * For Debugging Purpose, 
// * * Stick this page template into your theme
// * * Make a New Page
// * * Run it as a template under "Event Tester"
// * * And customize away

$current_time = strtotime("now");
$reminders = [];

echo date("Y-m-d H:i:s", strtotime("now")) . " - " . $current_time . "<br/>";
//Last Reminder
// $reminders[] = [
//     "label" => "first",
//     "time" => "-485 hours", 
// ];

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
        
        echo "<br/>";
        echo "<table>";
        echo "<tr>";
            echo "<td>Event Name:</td>";
            echo "<td>" . $event->post_title . "</td>";
        echo "</tr>";

        foreach( $reminders as $reminder ) {
            
            $reminder_flag = get_post_meta( $event->ID, "event_reminder_" . $reminder["label"], true );
            $reminder_time = date_create($event_date)->modify($reminder["time"]);
            $flag = ($reminder_flag === "1") ? "TRUE" : "FALSE";

            echo "<tr>";
                echo "<td style='padding-right: 25px'>" . ucwords($reminder["label"]) . " Reminder Flag:</td>";
                echo "<td>" . $flag . "</td>";
            echo "</tr>";
            
            echo "<tr>";
                echo "<td>" . ucwords($reminder["label"]) . " String Time</td>";
                echo "<td>" . strtotime(date_format($reminder_time, "Y-m-d H:i:s")) . "</td>";
            echo "</tr>";

            echo "<tr>";
                echo "<td>" . ucwords($reminder["label"]) . " Reminder Time:</td>";
                echo "<td>" . date_format($reminder_time, "Y-m-d H:i:s") . "</td>";
            echo "</tr>";

            // Check if our Current Time has pass the Reminder Time
            // And if we have not sent the reminder, then we can send one
            if( $current_time > strtotime(date_format($reminder_time, "Y-m-d H:i:s")) && !$reminder_flag ) {
                
                // We are ready to Email
                // foreach( $emails as $email ) {
                //     $subject = "Event Reminder for Event";
                //     $message = "Event Body";
                //     wp_mail( $email, $subject, $message );
                // }

                echo "<br/> Emailing:";
                var_dump($emails);

                //Update the Reminder Flag to no longer subsequently remind
                update_post_meta( $event->ID,  "event_reminder_" . $reminder["label"], true );
                
                echo "</table>";
            }

        }

    }

}

?>