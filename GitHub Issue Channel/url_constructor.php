<?php

    function getUserProfileURL($externalId){
        return _getUserProfileURL(getExternalIdMap($externalId));
    }

    function getRepositoryURL($externalId){
        return _getRepositoryURL(getExternalIdMap($externalId));
    }
    
    function getIssueURL($externalId){
        return _getIssueURL(getExternalIdMap($externalId));
    }
    
    function getIssueCommentURL($issueId, $commentId){
        return _getIssueCommentURL(getExternalIdMap($issueId), $commentId);
    }
    
    function getExternalIdMap($externalId){
        return explode(":-:", $externalId);
    }
    
    function _getUserProfileURL($external_id_map){
        $author_name = $external_id_map[2];
        return "https://github.com/$author_name";
    }
    
    function _getRepositoryURL($external_id_map){
        $repo_name = $external_id_map[0];
        return _getUserProfileURL($external_id_map)."/$repo_name";
    }
    
    function _getIssueURL($external_id_map){
        $issue_number = $external_id_map[1];
        return _getRepositoryURL($external_id_map)."/issues/$issue_number";
    }
    
    function _getIssueCommentURL($external_id_map, $comment_id){
        return _getIssueURL($external_id_map)."#issuecomment-".$comment_id;
    }
    
?>