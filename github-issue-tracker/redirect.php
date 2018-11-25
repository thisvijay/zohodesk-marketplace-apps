<?php
    include_once './url_constructor.php';
    $link = "https://github.com";
    switch ($_GET['entity']) {
        case 'user_profile':
            $link .= "/".$_GET["id"];
            break;
        case 'ticket':
            $link = getIssueURL($_GET["id"]);
        break;
        case 'thread':
            $link = getIssueCommentURL($_GET["parentId"], $_GET['id']);
        break;
        default:
            break;
    }
    header("location:$link");
   
?>