<?php

$current_time = strtotime("now");

//Establish Reminders
$reminders = [];

//First Reminder
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

    function event_calendar_email_reminder() {
        //Set Event Parameters and grab future events
        $event_arg  = [ "start_date" => date( 'Y-m-d H:i:s' , time() ) ];
        $events     = tribe_get_events( $event_arg );

        foreach( $events as $event ) {
            //Get Tickets and RSVP of each Event
            $tickets = tribe_tickets_get_attendees( $event->ID );
            $emails = [];

            //Emails may be duplicated as they can have multiple tickets so we grab only unique ones
            foreach( $tickets as $ticket ) {
                if( !in_array($ticket["purchaser_email"],$emails) ) $emails[] = $ticket["purchaser_email"];
            }
            
            //When does the Event Start?
            $event_date = $event->event_date;

            foreach( $reminders as $reminder ) {
                
                //Establish the Reminder Time and Check if we have sent the Reminder alrready
                $reminder_flag = get_post_meta( $event->ID, "event_reminder_" . $reminder["label"] );
                $reminder_time = date_create($event_date)->modify($reminder["time"]);

                // Check if our Current Time has pass the Reminder Time
                // And if we have not sent the reminder, then we can send one
                if( $current_time > strtotime(date_format($reminder_time, "Y-m-d H:i:s")) && !$reminder_flag ) {
                    
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

    //Cron Job Feature
    add_filter( 'cron_schedules', 'event_reminder_cronjob' );
    function event_reminder_cronjob( $schedules ) {
        $schedules['event_reminder'] = array(
                'interval'  => 300,
                'display'   => __( 'Every 5 Minutes run the Event Reminder', 'event-reminder' )
        );
        return $schedules;
    }

    // Schedule an action if it's not already scheduled
    function event_calendar_reminder_activation(){
        if ( ! wp_next_scheduled( 'event_reminder_cronjob' ) ) {
            wp_schedule_event( time(), 'event_reminder', 'event_reminder_cronjob' );
        }
    }
    register_activation_hook(   __FILE__, 'event_calendar_reminder_activation' );

    function event_calendar_reminder_deactivation(){
        if( wp_next_scheduled( 'event_reminder_cronjob' ) ){
            wp_clear_scheduled_hook( 'event_reminder_cronjob' );
        }
    }
    register_deactivation_hook( __FILE__, 'event_calendar_reminder_deactivation' );
    
    // Hook into that action that'll fire every three minutes
    add_action( 'event_reminder_cronjob', 'event_calendar_email_reminder' );

}

?>