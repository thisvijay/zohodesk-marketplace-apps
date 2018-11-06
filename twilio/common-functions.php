<?php
    
    $DESK_API_ROOT = "https://desk.zoho.com";

    function callTwilioAPI($SID, $authtoken, $url, $method, $data){
         error_log(" calling twilio api ....  ".$url);
        $ch= curl_init($url);
        curl_setopt($ch, CURLOPT_USERPWD, $SID . ":" . $authtoken);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
        if($method=="POST"){
            curl_setopt($ch,CURLOPT_POST,TRUE);
            if($data!=NULL){
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
        }

        $response= curl_exec($ch);
        $info= curl_getinfo($ch);
        
         error_log(" twilio api response....  ".$response);
        
        curl_close($ch);

        if($info['http_code']<400){
            return json_decode($response);
        }
        else{
            return null;
        }
    }
    
    function callDeskInvokeAPI($security_context, $target_url, $orgId, $method, $post_feelds=NULL){
        global $r_orgId;
        global $DESK_API_ROOT;
        
        error_log(" calling desk invoke api ....  ".$target_url);
        
//        $target_headers = array(
//            "Authorization" => 'Zoho-oauthtoken ${ZOHODESKAUTHTOKEN}',
//            "orgId" => $orgId
//        );
        $target_headers = array(
            "orgId" => $orgId
        );
        if($method=='POST' && $post_feelds!=NULL){
            $target_headers['Content-Type'] = 'application/json';
        }
        $headers_json = json_encode($target_headers);
        $deskConnectionName = 'for_twilio_bird';
        $postFields = array(
            'securityContext'=>$security_context,
            'requestURL'=>$target_url,
            'requestType'=>$method,
            'headers'=>$headers_json,
            'connectionLinkName'=> $deskConnectionName
        );
        
        if($method=='POST' && $post_feelds!=NULL){
            $postFields['postBody'] = $post_feelds;
        }
        
        $url="$DESK_API_ROOT/api/v1/invoke?orgId=$r_orgId";
        //order is importsnt while genereating hash
        $hashKey = createHashForDeskInvokeAPI(array(
            "requestURL" => $target_url,
            "requestType" => $method,
            "postBody" => $post_feelds,
            "headers" => $headers_json,
            'connectionLinkName'=> $deskConnectionName
        ));
        
        $headers=array(
            "HASH: ".$hashKey
        );
        
        error_log(" calling desk invoke api ....  ".$url);

        $ch= curl_init($url);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,TRUE);
        curl_setopt($ch,CURLOPT_POST,TRUE);
        curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query($postFields));

        $response= curl_exec($ch);
        
        error_log(" calling desk invoke api response....  ".$url." response is ".$response);
        
        $info= curl_getinfo($ch);

        curl_close($ch);
        if($info['http_code']==200){
            $log_ticket_silent = TRUE;
        }
        $response_response = json_decode($response) -> response;
        return json_decode($response_response);
    }
    
    function createHashForDeskInvokeAPI($params){
        $desk_secret = "vijayakumar.mk+20180801@secretkey.zohocorp.com";
        $to_be_hashed = "";
        foreach ($params as $key => $value) {
            if($value==NULL){
                continue;
            }
            if($to_be_hashed!=""){
                $to_be_hashed.="&";
            }
            $to_be_hashed .= "$key=$value"; 
        }
        $hash_key = hash_hmac('sha256', $to_be_hashed, $desk_secret);
        error_log("calculated has for dayt = ".$to_be_hashed." is ".$hash_key);
        //logTicketInDesk(array("hashkey_generated"=>$hash_key, "data_hashed"=>$to_be_hashed));
        return $hash_key;
    }
?>
