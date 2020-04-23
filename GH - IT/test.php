<?php
$response = '{
  "responseHeaders":{
    "Transfer-Encoding":"chunked",
    "Server":"ZGS",
    "X-Content-Type-Options":"nosniff",
    "Connection":"keep-alive",
    "Pragma":"no-cache",
    "Date":"Mon, 26 Nov 2018 06:49:20 GM
    T","X-Frame-Options":"DENY","Strict-Transport-Security":"max-age=15768000","Cache-Control":"no-cache","Set-Cookie":"drecn=9047363c-7ec9-4f8d-9ca5-6dcee306d501;pat
    h=/;Secure;priority=high","Vary":"Accept-Encoding","Expires":"Thu, 01 Jan 1970 00:00:00 GMT","X-XSS-Protection":"1","Content-Type":"application/json;charset=utf-8"
  },"response":"{
    \"statusMessage\":{\"author_association\":\"OWNER\",\"issue_url\":\"https://api.github.com/repos/yesiamvj/sedf/issues/2\",\"updated_at\":\"2018-11
    -26T06:49:20Z\",\"html_url\":\"https://github.com/yesiamvj/sedf/issues/2#issuecomment-441535658\",\"created_at\":\"2018-11-26T06:49:20Z\",\"id\":441535658,\"body\
":\"2nd\",\"user\":{\"gists_url\":\"https://api.github.com/users/yesiamvj/gists{/gist_id}\",\"repos_url\":\"https://api.github.com/users/yesiamvj/repos\",\"follow
    ing_url\":\"https://api.github.com/users/yesiamvj/following{/other_user}\",\"starred_url\":\"https://api.github.com/users/yesiamvj/starred{/owner}{/repo}\",\"logi
    n\":\"yesiamvj\",\"followers_url\":\"https://api.github.com/users/yesiamvj/followers\",\"type\":\"User\",\"url\":\"https://api.github.com/users/yesiamvj\",\"subsc
    riptions_url\":\"https://api.github.com/users/yesiamvj/subscriptions\",\"received_events_url\":\"https://api.github.com/users/yesiamvj/received_events\",\"avatar_
    url\":\"https://avatars3.githubusercontent.com/u/14248225?v=4\",\"events_url\":\"https://api.github.com/users/yesiamvj/events{/privacy}\",\"html_url\":\"https://g
    ithub.com/yesiamvj\",\"site_admin\":false,\"id\":14248225,\"gravatar_id\":\"\",\"node_id\":\"MDQ6VXNlcjE0MjQ4MjI1\",\"organizations_url\":\"https://api.github.com
    /users/yesiamvj/orgs\"},\"url\":\"https://api.github.com/repos/yesiamvj/sedf/issues/comments/441535658\",\"node_id\":\"MDEyOklzc3VlQ29tbWVudDQ0MTUzNTY1OA==\"},\"s
    tatus\":\"true\"}",
    "statusCode":200
  }

';
$response_response = json_decode($response);
var_dump($response_response);
?>