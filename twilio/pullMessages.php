<?php

    include_once './common-functions.php';
    header('Content-Type: application/json');
    $request_body = file_get_contents('php://input');
    $config_params = json_decode($request_body);
    $SID = $config_params->Twilio_SID;//"ACf72043dda78b9b180f379f1300b27dc8";//$config_params -> Twilio_SID;
    $authtoken = $config_params->Twilio_Authtoken;//"4e0180b904ee4f6016f2f5122da4cc07";//$config_params -> Twilio_Authtoken;
    $Service_SID = $config_params->Twilio_Service_SID;//"ACf72043dda78b9b180f379f1300b27dc8";//$config_params -> Twilio_SID;
    $prev_last_message_fetched_time = json_decode($config_params->channelState)->last_message_fetched_time;
    error_log("prev_last_message_fetched_time ..".$prev_last_message_fetched_time." .. ".$config_params->channelState);
    $pageFilterParam = !empty($prev_last_message_fetched_time) ? "DateSent>=".substr($prev_last_message_fetched_time, 0,10): "";
    $last_message_time_in_response = NULL;
    $tickets = array();
    $threads = array();
    $ticket_numbers = array();
    $response_item_index = 0;
    $convertedData = convertMessageForDesk("https://api.twilio.com/2010-04-01/Accounts/$SID/Messages.json?PageSize=90&$pageFilterParam");
    $last_message_time_in_response = $tickets[0]["createdTime"];
    error_log(" count will be tickets : ".count($tickets). " threads: ". count($threads) ." last message time ".$prev_last_message_fetched_time." msg resp time ".$last_message_time_in_response);
    $dataToPush -> data = array(
        "tickets" => $tickets,
        "threads" => $threads
    );
    if($last_message_time_in_response!=NULL){
        $dataToPush -> channelState = array(
            "last_message_fetched_time" => $last_message_time_in_response,
            "last_pull_request_time"=> date("Y-m-d\TH:i:s.000\Z")
        );
    }
    die(json_encode($dataToPush));
    
    function convertMessageForDesk($url){
        global $SID;
        global $authtoken;
        global $response_item_index;
        global $ticket_numbers;
        global $tickets;
        global $threads;
        global $last_message_time_in_response;
        global $prev_last_message_fetched_time;
        $last_message_time_in_response = NULL;
        $messages_response = callTwilioAPI($SID, $authtoken, $url, "GET", NULL);
        $messages = $messages_response -> messages;
        $next_page_uri = $messages_response->next_page_uri;
        $skip_next_pages = 0;
        $total_messages = 0;
        foreach ($messages as $message) {
            $response_item_index++;
            $createdDateTime = date_create_from_format('D, d M Y H:i:s +O', $message -> date_created);
            $createdDateTimeGMT = date_format($createdDateTime, "Y-m-d\TH:i:s.000\Z");
            if($response_item_index==1){
                error_log("response time index ".$response_item_index." ".$createdDateTimeGMT);
                $last_message_time_in_response = $createdDateTimeGMT;
            }
            if(!empty($prev_last_message_fetched_time) && isMessageOld($createdDateTimeGMT, $prev_last_message_fetched_time)){
                error_log($createdDateTimeGMT. " - Message may be older than ".$prev_last_message_fetched_time." .. skipping ");
                $skip_next_pages++;
                break;
            }
            $sender_number = $message -> from;
            $receiver_number = $message -> to;
            $subject = $message -> body;
            $pending_status = array("accepted", "queued", "sending");
            $externalId = $message -> sid;
            $message_status =  $message -> status;
            $message_direction = $message -> direction;
            $direction = strpos($message -> direction, "inbound")>-1 ? "in" : "out";
            if(!in_array($sender_number, $ticket_numbers) && !in_array($receiver_number, $ticket_numbers) && !empty($subject)){
                if($direction==="in"){
                    $authorName = $sender_number;
                    $authorId = $sender_number;
                }
                else{
                    $authorName = $receiver_number;
                    $authorId = $receiver_number;
                }
                $authorPhoneNumber = strpos($authorId, "whatsapp")>-1 ? str_replace("whatsapp:", "",$authorId) : $authorId;
                array_push($tickets, 
                    array(
                       "subject"=>$subject,
                       "createdTime"=>$createdDateTimeGMT,
                       "extId"=>($direction=="out" ? $receiver_number : $sender_number),
                       "extra"=>array(
                                    "key" => '{{ticket.id}}_channel_details',
                                    "value" => array(
                                        'status' => $message_status,
                                        'direction' => $message_direction,
                                        'Segments' => $message -> num_segments,
                                        'error_message' => $message -> error_message,
                                        'uri' => $message->uri,
                                        'num_media' => $message->price
                                    ),
                                    "queriableValue" => 'twilio_ticket_extras'
                       ),
                       "actor"=>array(
                            "name"=>$authorName." (twilio)",
                            "displayName" => $authorName." (Twilio)",
                            "phone"=>$authorPhoneNumber,
                            "extId"=>$authorId,
                            "photoURL"=>"https://eabiawak.com/wp-content/uploads/2017/07/photo.png"
                        )
                    )
                );
                array_push($ticket_numbers, $authorId);
                if(count($tickets)>=90){ // 100 is the max limit
                    $skip_next_pages = true;
                    break;
                }
            }
            else{
                $authorPhoneNumber = strpos($sender_number, "whatsapp")>-1 ? str_replace("whatsapp:", "",$sender_number) : $sender_number;
                array_push($threads,
                        array(
                                "extId"=>$externalId,
                                "extParentId"=>($direction=="out" ? $receiver_number : $sender_number),
                                "createdTime"=>$createdDateTimeGMT,
                                "content"=>$subject,
                                "direction"=> $direction,
                                "from"=>$sender_number,
                                "to"=>array($receiver_number),
                                "canReply" => true,
                                "extra"=>array(
                                    "key" => '{{thread.id}}_channel_details',
                                    "value" => array(
                                        'status' => $message_status,
                                        'Segments' => $message -> num_segments,
                                        'direction' => $message_direction,
                                        'error_message' => $message -> error_message,
                                        'uri' => $message->uri,
                                        'num_media' => $message->price
                                    ),
                                    "queriableValue" => 'twilio_thread_extras'
                                ),
                                "actor" => array(
                                    "name"=> $sender_number." (twilio)",
                                    "displayName" => $sender_number." (Twilio)",
                                    "phone"=>$authorPhoneNumber,
                                    "extId"=>$sender_number,
                                    "photoURL"=>"https://eabiawak.com/wp-content/uploads/2017/07/photo.png"
                                )
                        )
                    );
                if(count($threads)>=90){ // 100 is the max limit
                    $skip_next_pages = true;
                    break;
                }
            }
        }
        /*if((!$skip_next_pages) && !empty($next_page_uri)){
             error_log(" ticket count ". count($tickets). " threads ". count($threads)." whole merged ");
            error_log("going to get next page items ".$next_page_uri);
            $next_page_messages = convertMessageForDesk("https://api.twilio.com".$next_page_uri);
            //array_merge($tickets, $next_page_messages["tickets"]);
            //array_merge($threads, $next_page_messages["threads"]);
            error_log(" ticket count ". count($tickets). " threads ". count($threads)." whole merged ". count($next_page_messages["tickets"])." titit ".count($next_page_messages["threads"]));
        }*/
        error_log(" count will be ".count($tickets). " threads ". count($threads));
        return array(
                "tickets"=>$tickets,
                "threads"=>$threads
            );
    }
    
    function isMessageOld($newtime, $basetime){
        $basedatetime = new DateTime($basetime);
        $newdatetime = new DateTime($newtime);
        //$diff = $datetime2->diff($datetime1);
        //$isOld = ($diff->invert<0) || (($time1!=$time1) && ($diff->y || $diff->m || $diff->d || $diff->h || $diff->i));
        return $newdatetime<$basedatetime;
    }
?>
