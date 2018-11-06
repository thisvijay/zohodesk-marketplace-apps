<?php
    include_once './common-functions.php';
    header('Content-Type: application/json');
    $request_body = file_get_contents('php://input');
    error_log(" payload received ".$request_body);
    $request_object = json_decode($request_body);
    $config_params = $request_object->configParams;
    $thread_json = $request_object->resource;
    $SID = $config_params->Twilio_SID;//"ACf72043dda78b9b180f379f1300b27dc8";//$config_params -> Twilio_SID;
    $authtoken = $config_params->Twilio_Authtoken;//"4e0180b904ee4f6016f2f5122da4cc07";//$config_params -> Twilio_Authtoken;
    $Service_SID = $config_params->Twilio_Service_SID;//"ACf72043dda78b9b180f379f1300b27dc8";//$config_params -> Twilio_SID;
    $Configured_Whatsapp_Number = $config_params->Configured_Whatsapp_Number;
    
    $request_response = convertAndPush($thread_json);
    
    die(json_encode($request_response));
    
    function convertAndPush($ZohoDeskThread){
        global $SID;
        global $Service_SID;
        global $authtoken;
        global $Configured_Whatsapp_Number;
        $pending_status = array("accepted", "queued", "sending");
        if(isset($ZohoDeskThread->extParentId)){
            $recipient_number = $ZohoDeskThread->extParentId;
            $servicePayload = array(
                "To"=> $recipient_number,
                "Body" => empty($ZohoDeskThread->content) ? $ZohoDeskThread->summary : htmlspecialchars_decode(strip_tags($ZohoDeskThread->content), ENT_QUOTES),
                "MessagingServiceSid"=> $Service_SID
            );
            if(strpos($recipient_number, "whatsapp")>-1){
                $servicePayload["From"] = "whatsapp:$Configured_Whatsapp_Number";
            }
            error_log("body calculated .. ".json_encode($servicePayload));
            $message = callTwilioAPI($SID, $authtoken, "https://api.twilio.com/2010-04-01/Accounts/$SID/Messages.json", "POST", $servicePayload);
            if($message==NULL){
                return null;
            }
            $sender_number = $message -> from;
            $receiver_number = $message -> to;
            $subject = $message -> body;
            $createdDateTime = date_create_from_format('D, d M Y H:i:s +O', $message -> date_created);
            $createdDateTimeGMT = date_format($createdDateTime, "Y-m-d\TH:i:s.000\Z");
            $externalId = $message -> sid;
            $authorName = $sender_number;
            $authorId = $sender_number;
            $authorPhoneNumber = strpos($authorId, "whatsapp")>-1 ? str_replace("whatsapp:", "",$authorId) : $authorId;
            $message_direction = $message -> direction;
            $message_status =  $message -> status;
            $direction = strpos($message -> direction, "inbound")>-1 ? "in" : "out";
            $convertedThread = array(
                                "extId"=>$externalId,
                                "extParentId"=> ($direction=="out" ? $receiver_number : $sender_number),
                                "createdTime"=>$createdDateTimeGMT,
                                "content"=>$subject,
                                "direction"=> $direction,
                                "canReply" => true,
                                "from"=>$sender_number,
                                "to"=>$receiver_number,
                                "extra"=>array(
                                    "key" => '{{thread.id}}_channel_details',
                                    "value" => array(
                                        'status' => $message_status,
                                        'direction' => $message_direction,
                                        'Segments' => $message -> num_segments,
                                        'error_message' => $message -> error_message,
                                        'uri' => $message->uri,
                                        'num_media' => $message->price
                                    ),
                                    "queriableValue" => 'twilio_thread_extras'
                                ),
                                "actor" => array(
                                    "extId"=>$authorId,
                                    "phone"=>$authorPhoneNumber,
                                    "name"=> $authorName." (twilio)",
                                    "photoURL"=>"https://eabiawak.com/wp-content/uploads/2017/07/photo.png"
                                )
                        );
            return $convertedThread;
        }
    }
?>
