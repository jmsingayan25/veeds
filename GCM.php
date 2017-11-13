<?php
 
function sendGoogleCloudMessage( $data, $ids )
{
    // Insert real GCM API key from Google APIs Console
    // $apiKey = 'AAAAeh-qrnM:APA91bFjj7cGX2xpmXVLtQOyaMSeRZI0dPsYWuch1O9fwT013oMjPtsoGfufb9Ue-B6SvaHivd3eOGyyWg1rE7Y_yiSFsmJDbHqwHGYW9zLU3aCmrCoCDxmHARr1A53bbWnWApDkWFBH';
    $apiKey = 'AAAALNBhQzA:APA91bEtLBNxtiuHsTryy5z1NUJRaYbTogSPU6nwDVbWKSHZYsXJrM31hm4TskBcDo8R9jxt4_qB2S8MoFHo5nP_fscwcz4WOO--VkXTnjla-TMKB8xfJzDpb7s4fQs8oo_B4If9fvoh';

    // Define URL to GCM endpoint
    // 524517289587@gcm.googleapis.com
    // $url = 'http://524517289587@gcm.googleapis.com';
    $url = 'https://fcm.googleapis.com/fcm/send';
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

    // curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );

    // curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );

    // Actually send the push   
    $resultGCM = curl_exec( $ch );

    // Error handling
    if ( curl_errno( $ch ) )
    {
        // echo curl_getinfo($ch, CURLINFO_HTTP_CODE);
        echo 'GCM error: ' . curl_error( $ch );
    }
    return $resultGCM;
    // Close curl handle
    curl_close( $ch );
    
    // Debug GCM response 

    // echo $result;
}
 
?>