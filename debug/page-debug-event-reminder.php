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

 if ( class_exists( "Tribe__Tickets__Tickets" ) ) {

    //Set Event Parameters
    $event_arg = [
        "start_date" => date('Y-m-d H:i:s', time() )
    ];

    //Grab Events
    $events = tribe_get_events( $event_arg );

    foreach( $events as $event ) {
        //Get Tickets
        $tickets = tribe_tickets_get_attendees( $event->ID );
        $emails = [];

        //Emails may be duplicated as they can have multiple tickets so we grab only unique ones
        foreach( $tickets as $ticket ) {
            if( !in_array($ticket["purchaser_email"],$emails) ) $emails[] = $ticket["purchaser_email"];
        }

        //We are ready to Email
        foreach( $emails as $email ) {
            $subject = "Event Reminder for Event";
            $message = "Event Body";
            wp_mail( $email, $subject, $message );
        }
    }

}

?>