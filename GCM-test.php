<?php
 
function sendGoogleCloudMessage( $data, $ids )
{
    // Insert real GCM API key from Google APIs Console
    $apiKey = 'AIzaSyBLMqT9x-REzumotJuiyFN6y4P9SSOMTL0';

    // Define URL to GCM endpoint
    $url = 'https://gcm-http.googleapis.com/gcm/send';

    // Set GCM post variables (device IDs and push payload)     
    $post = array(
		'registration_ids'  => $ids,
		'data'              => $data, 
        'content_available'    => true,                   
        'priority'              => 'high',    
        'notification' => $data               
    );

    // Set CURL request headers (authentication and type)       
    $headers = array( 
		'Authorization: key=' . $apiKey,
        'Content-Type: application/json'
    );

    // Initialize curl handle       
    $ch = curl_init();

    // Set URL to GCM endpoint      
    curl_setopt( $ch, CURLOPT_URL, $url );

    // Set request method to POST       
    curl_setopt( $ch, CURLOPT_POST, true );

    // Set our custom headers       
    curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

    // Get the response back as string instead of printing it       
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

    // Set JSON post data
    curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $post ) );

    // Actually send the push   
    $result = curl_exec( $ch );

    // Error handling
    if ( curl_errno( $ch ) )
    {
        echo 'GCM error: ' . curl_error( $ch );
    }

    // Close curl handle
    curl_close( $ch );

    // Debug GCM response       
    echo $result;
}

$push = array();
$push['reciever_id'] = 14;
$push['video_id'] = 510;
$push['body'] = "test liked your video";
$push['type'] = "like";
$push['image'] = 'path to pic';
 sendGoogleCloudMessage($push, array('kabs2dMczf8:APA91bHfIUKgTItLwLAMm8CHdwFVEOGMBkGKspIM9yoGdKpCV6HoW1rfoNrsicbLViLWWbw8uaOKoYmH2v5DpmfVEDo60EJdfd9uQ8lpBaueJMAp_K0YFyQ76GfEBGtFxGF5RFdfCB10'));
?>