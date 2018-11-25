<?php
ini_set("log_errors", 1);
ini_set("error_log", "errorlog-php.log");

    include './common-functions.php';
    header('Content-Type: application/json');
    parse_str(file_get_contents('php://input'), $message_object);
    $message_object = json_decode(file_get_contents('php://input'));//((object)$message_object);
    error_log(" call from github ....  ".file_get_contents('php://input'));
    
    $convertedMessage = getConvertedDataForDesk($message_object);
    
    die(json_encode($convertedMessage));
    /*
    $data_to_be_pushed = json_encode(convertMessageForDesk($message_object));
    
    $r_security_context = $_REQUEST['appSecurityContext'];
    $r_orgId            = $_REQUEST['appOrgId'];
    
    $target_url = $DESK_API_ROOT."/api/v1/channels/{{installationId}}/import";
    
    error_log(" data ready to be pushed ".$data_to_be_pushed);
    callDeskInvokeAPI($r_security_context, $target_url, $r_orgId, "POST", $data_to_be_pushed);
     * 
     * 
     */
    
    function getConvertedDataForDesk($issue_payload){
        $threads = array();
        $tickets = array();
        
        $repo_name = $issue_payload->repository->name;
        $repo_owner = $issue_payload->repository->owner->login;
        
        if(isset($issue_payload->issue)){
            $ticket = convertIssueForDesk($issue_payload);
            array_push($tickets, $ticket);
        }
        
        if(isset($issue_payload->comment)){
            $issue = $issue_payload->issue;
            $issue_comment = $issue_payload->comment;
            $issue_comment_author = $issue_comment->user;
            $thread =   array(
                            "extId"=>$issue_comment->id,
                            "extParentId"=>$repo_name.":-:".$issue->number.":-:".$repo_owner,
                            "content"=> $issue_comment->body,
                            "createdTime"=>$issue_comment->created_at,
                            "contentType"=>"text/html",
                            "direction"=> ($issue_comment->author_association=="NONE" ? "in" : "out"),
                            "from"=>$issue_comment_author->login,
                            "canReply" => true,
                            "extra"=>array(
                                "key" => '{{thread.id}}_channel_details',
                                "value" => array(
                                    'url' => $issue_comment->html_url,
                                    'issue_url' => $issue->html_url,
                                    'node_id' => $issue_comment->node_id,
                                    'author_association' => $issue_comment->author_association,
                                    'updated_at'=>$issue_comment->updated_at
                                ),
                                "queriableValue" => 'github_thread_extras'
                            ),
                            "actor" => array(
                                "name"=> $issue_comment_author->login,
                                "displayName"=> $issue_comment_author->login." (Github)",
                                "extId"=>$issue_comment_author->login,
                                "photoURL"=>$issue_comment_author->avatar_url
                            )
                        );
                array_push($threads, $thread);
        }
        return  array(
                    "data" => array(
                        "tickets"=> $tickets,
                        "threads"=> $threads
                    )
                );
    }
    
    function convertIssueForDesk($issue_payload){
        $repo_name = $issue_payload->repository->name;
        $repo_owner = $issue_payload->repository->owner->login;
        $issue = $issue_payload->issue;
        $issue_author = $issue->user;
        $ticket = array(
                            "extId"=>$repo_name.":-:".$issue->number.":-:".$repo_owner,
                            "subject"=>$issue->title,
                            "description"=> $issue->body,
                            "createdTime"=> $issue->created_at,
                            "extra"=>array(
                                "key" => '{{ticket.id}}_channel_details',
                                "value" => array(
                                    'url' => $issue->html_url,
                                    'number' => $issue->number,
                                    'node_id' => $issue->node_id,
                                    'labels' => $issue->labels,
                                    'state'=>$issue->state,
                                    'locked'=>$issue->locked,
                                    'assignee'=>$issue->assignee,
                                    'assignees'=>$issue->assignees,
                                    'milestone'=>$issue->milestone,
                                    'comments'=>$issue->comments,
                                    'author_association'=>$issue->author_association,
                                    'closed_at'=>$issue->closed_at
                                    
                                ),
                                "queriableValue" => 'github_ticket_extras'
                            ),
                            "actor" => array(
                                "name"=> $issue_author->login,
                                "displayName"=> $issue_author->login." (Github)",
                                "extId"=>$issue_author->login,
                                "photoURL"=>$issue_author->avatar_url
                            )
                        );
        return $ticket;
    }
    
?>