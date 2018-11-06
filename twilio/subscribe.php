<?php
    include_once './common-functions.php';
    header('Content-Type: application/json');
    $request_body = file_get_contents('php://input');
    $payload_body = json_decode($request_body);
    $r_orgId = $payload_body->orgId;
    $r_securityContext = $payload_body->securityContext;
    $invokeAPIResponse = callDeskInvokeAPI($r_securityContext, $DESK_API_ROOT."/api/v1/installedExtensions/{{installationId}}/configParams", $r_orgId, "GET");
    
    $config_params = $invokeAPIResponse -> statusMessage -> data;
    
    error_log("call for configparams ".json_encode($invokeAPIResponse));
    
    $SID = NULL;//"ACf72043dda78b9b180f379f1300b27dc8";
    $authtoken = NULL;//"4e0180b904ee4f6016f2f5122da4cc07";
    
    foreach ($config_params as $config_param) {
        switch ($config_param->name){
            case "Twilio_SID":
                $SID = $config_param->value;
                break;
            case "Twilio_Authtoken":
                $authtoken = $config_param -> value;
                break;
            case "Twilio_Service_SID":
                $existing_service_sid = $config_param -> value;
                break;
            default:
                break;
        }
    }
    
    if(!empty($existing_service_sid) || $existing_service_sid!=null || $existing_service_sid!=""){
        error_log("already subscribed ..".$existing_service_sid);
        die(json_encode(array("status"=>"already subscribed ".$existing_service_sid)));
    }
    
    error_log("calculated configparams sid:".$SID." auth : ".$authtoken);
    
    $subscription_url = "https://zohodesk-extension.herokuapp.com/twilio/syncMessage.php?appUserOrgId=$r_orgId&appUserSecurityContext=$r_securityContext";
    
    $createdService = addSubscriptionService($subscription_url);
    
    error_log("call for servuice creation ".json_encode($createdService));
    
    die(json_encode(updateConfigParamInDesk($createdService)));
    
    function addSubscriptionService($subscribe_url){
        global $SID;
        global $authtoken;
        if($SID!=null && $authtoken!=null){
            $phone_numbers = callTwilioAPI($SID, $authtoken, "https://api.twilio.com/2010-04-01/Accounts/$SID/IncomingPhoneNumbers.json", "GET", null);
            $defaultPhoneNumber = null;
            $defaultPhoneNumberSID = null;
            if($phone_numbers!=null && (count($phone_numbers->incoming_phone_numbers) >0)){
                foreach ($phone_numbers->incoming_phone_numbers as $phone_number) {
                    if($phone_number->status === "in-use"){
                        $defaultPhoneNumber = $phone_number->phone_number;
                        $defaultPhoneNumberSID = $phone_number->sid;
                        break;
                    }
                }
            }
            $servicePayload = array(
                "FriendlyName" => "Desk-Twilio Integration Service",
                "InboundRequestUrl" => $subscribe_url
            );
            $service = callTwilioAPI($SID, $authtoken, "https://messaging.twilio.com/v1/Services", "POST", $servicePayload);
            if($service!=null){
                $service_sid = $service -> sid;
                if($defaultPhoneNumberSID!=null){
                    $phoneNumberPayload = array(
                        "PhoneNumberSid"=>$defaultPhoneNumberSID
                    );
                    $ServicePhoneNumber = callTwilioAPI($SID, $authtoken, "https://messaging.twilio.com/v1/Services/$service_sid/PhoneNumbers", "POST", $phoneNumberPayload);
                    if($ServicePhoneNumber!=null){
                        return array(
                            "Service_SID" => $service -> sid,
                            "Service_PhoneNumber" => $ServicePhoneNumber->phone_number
                        );
                    }
                }
                return array(
                    "Service_SID" => $service -> sid
                );
            }
            else{
                return null;
            }
        }
    }
    
    function updateConfigParamInDesk($serviceDetails){
        global $r_securityContext;
        global $r_orgId;
        global $DESK_API_ROOT;
        $configParamsPayLoad = array(
            "variables"=>array(
                array(
                    "name"=>"Twilio_Service_SID",
                    "value"=>$serviceDetails['Service_SID']
                ),
                array(
                    "name"=>"Twilio_Service_Phone_Number",
                    "value"=>empty($serviceDetails['Service_PhoneNumber']) ? "": $serviceDetails['Service_PhoneNumber']
                )
            )
        );
        $deskDeskResponse = callDeskInvokeAPI($r_securityContext, $DESK_API_ROOT."/api/v1/installedExtensions/{{installationId}}/configParams", $r_orgId, "POST", json_encode($configParamsPayLoad));
        
        error_log("call for update configparam  ".json_encode($deskDeskResponse));
        return $deskDeskResponse;
    }
?>
