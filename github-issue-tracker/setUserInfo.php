<?php

ini_set("log_errors", 1);
ini_set("error_log", "errorlog-php.log");

    include_once './common-functions.php';
    header('Content-Type: application/json');
    $request_body = file_get_contents('php://input');
    $payload_body = json_decode($request_body);
    $r_orgId = $payload_body->orgId;
    $r_securityContext = $payload_body->securityContext;
    
    $invokeAPIResponse = callDeskInvokeAPI($r_securityContext, $DESK_API_ROOT."/api/v1/installedExtensions/{{installationId}}/configParams", $r_orgId, "GET");
    
    $config_params = $invokeAPIResponse -> statusMessage -> data;
    
    error_log("call for configparams ".json_encode($invokeAPIResponse));
    
    foreach ($config_params as $config_param) {
        switch ($config_param->name){
            case "credentials":
                $existing_desk_creds = $config_param -> value;
                break;
            default:
                break;
        }
    }
    
    if(!empty($existing_desk_creds) || $existing_desk_creds!=null || $existing_desk_creds!=""){
        error_log("already subscribed ..".$existing_desk_creds);
        die(json_encode(array("status"=>"already subscribed ".$existing_desk_creds)));
    }
    
    $userInfoResponse = callDeskInvokeAPI($r_securityContext, "https://api.github.com/user", $r_orgId, "GET");
    
    $serviceDetails = array(
                              "desk"=>array(
                                    "orgId"=> $r_orgId,
                                    "securityContext"=> $r_securityContext
                               ),
                              "github"=>array(
                                  "username"=>$userInfoResponse->login
                              )
                      );
    die(updateConfigParamInDesk($serviceDetails));
    
    function updateConfigParamInDesk($serviceDetails){
        global $r_securityContext;
        global $r_orgId;
        global $DESK_API_ROOT;
        $configParamsPayLoad = array(
            "variables"=>array(
                array(
                    "name"=>"credentials",
                    "value"=> json_encode($serviceDetails)
                )
            )
        );
        $deskDeskResponse = callDeskInvokeAPI($r_securityContext, $DESK_API_ROOT."/api/v1/installedExtensions/{{installationId}}/configParams", $r_orgId, "POST", json_encode($configParamsPayLoad));
        
        error_log("call for update configparam  ".json_encode($deskDeskResponse));
        return $deskDeskResponse;
    }
    
    
    ?>