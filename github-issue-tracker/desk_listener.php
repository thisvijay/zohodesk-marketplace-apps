<?php

    include_once './common-functions.php';
    header('Content-Type: application/json');
    $request_body = file_get_contents('php://input');
    error_log(" payload received ".$request_body);
    $request_object = json_decode($request_body);
    $config_params = $request_object->configParams;
    $thread_json = $request_object->resource;
    
    $request_response = convertAndPush($thread_json);
    
    die(json_encode($request_response));
    
    function convertAndPush($ZohoDeskThread){
        global $r_security_context;
        global $r_orgId;
        $comment_payload = array(
                "body" => empty($ZohoDeskThread->content) ? $ZohoDeskThread->summary : htmlspecialchars_decode(strip_tags($ZohoDeskThread->content), ENT_QUOTES)
            );
            $externalId = isset($ZohoDeskThread->extParentId) ? $ZohoDeskThread->extParentId : $ZohoDeskThread->replyToExtId;
            $id = explode(":-:", $externalId);
            $repo_name = $id[0];
            $issue_number = $id[1];
            $author_name = $id[2];
            $post_url = "https://api.github.com/repos/$author_name/$repo_name/issues/$issue_number/comments";
            $invokeAPIResponse = callDeskInvokeAPI($r_security_context, $post_url, "POST", $r_orgId, json_encode($comment_payload));
            if($invokeAPIResponse==NULL){
                return null;
            }
            $convertedThread = array(
                                "extId"=>$invokeAPIResponse->id,
                                "extParentId"=> $externalId,
                                "canReply" => true,
                                "from"=>$author_name,
                                "extra"=>array(
                                "key" => '{{thread.id}}_channel_details',
                                "value" => array(
                                    'url' => $invokeAPIResponse->html_url,
                                    'issue_url' => getIssueURL($externalId),
                                    'node_id' => $invokeAPIResponse->node_id,
                                    'author_association' => $invokeAPIResponse->author_association
                                ),
                                "queriableValue" => 'github_thread_extras'
                            ),
                        );
            return $convertedThread;
    }

    

?>