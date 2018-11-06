<?php
    include './common-functions.php';
    parse_str(file_get_contents('php://input'), $message_object);
    $message_object = ((object)$message_object);
    error_log(" call from Twilio ....  ".file_get_contents('php://input'));
    
    $data_to_be_pushed = json_encode(convertMessageForDesk($message_object));
    
    $r_security_context = $_REQUEST['appUserSecurityContext'];
    $r_orgId            = $_REQUEST['appUserOrgId'];
    
    $target_url = $DESK_API_ROOT."/api/v1/channels/{{installationId}}/import";
    
    error_log(" data ready to be pushed ".$data_to_be_pushed);
    callDeskInvokeAPI($r_security_context, $target_url, $r_orgId, "POST", $data_to_be_pushed);
    
    function convertMessageForDesk($message){
            $sender_number = $message -> From;
            $receiver_number = $message -> To;
            $subject = $message -> Body;
            $externalId = $message -> MessageSid;
            $authorName = $sender_number;
            $authorId = $sender_number;
            $message_status =  $message -> SmsStatus;
            $message_direction = $message -> direction;
            $segments = $message -> NumSegments;
            $num_media = $message->NumMedia;
            $FromCity = $message ->FromCity;
            $direction = "in";
            $authorPhoneNumber = strpos($authorId, "whatsapp")>-1 ? str_replace("whatsapp:", "",$authorId) : $authorId;
                $ticket = array(
                       "subject"=>$subject,
                       "extId"=>($direction=="out" ? $receiver_number : $sender_number),
                       "extra"=>array(
                                    "key" => '{{ticket.id}}_channel_details',
                                    "value" => array(
                                        'status' => $message_status,
                                        'direction' => $message_direction,
                                        'Segments' => $segments,
                                        'num_media'=>$num_media,
                                        'fromCity'=>$FromCity
                                    ),
                                    "queriableValue" => 'twilio_ticket_extras'
                       ),
                       "actor"=>array(
                            "name"=>$authorName." (twilio)",
                            "displayName"=>$authorName." (Twilio)",
                            "extId"=>$authorId,
                            "phone"=>$authorPhoneNumber,
                            "photoURL"=>"https://eabiawak.com/wp-content/uploads/2017/07/photo.png"
                        )
                    );
                $thread = array(
                                "extId"=>$externalId,
                                "extParentId"=>($direction=="out" ? $receiver_number : $sender_number),
                                "content"=>$subject,
                                "direction"=> $direction,
                                "from"=>$sender_number,
                                "to"=>array($receiver_number),
                                "canReply" => true,
                                "extra"=>array(
                                    "key" => '{{thread.id}}_channel_details',
                                    "value" => array(
                                        'status' => $message_status,
                                        'direction' => $message_direction,
                                        'Segments' => $segments,
                                        'num_media'=>$num_media,
                                        'fromCity'=>$FromCity
                                    ),
                                    "queriableValue" => 'twilio_thread_extras'
                                ),
                                "actor" => array(
                                    "name"=>$sender_number." (twilio)",
                                    "displayName"=>$sender_number." (Twilio)",
                                    "phone"=>$authorPhoneNumber,
                                    "extId"=>$sender_number,
                                    "photoURL"=>"https://eabiawak.com/wp-content/uploads/2017/07/photo.png"
                                )
                        );
        
        return array(
                    "data" => array(
                        "threads"=> array($thread)
                    )
                );
    }
?>