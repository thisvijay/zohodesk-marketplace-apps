var storage = {
    
};
var username = "thisvijay";
$(document).ready(function(){
    storage = JSON.parse(localStorage.getItem("subscriptions"));
    getRepos();
});
var authorization = "Basic "+btoa("thisvijaymail@gmail.com:28b7aaa81664ef46f687b5bd08e4092175e8fe16");
function getRepos(){
    $.ajax({
            url : `https://api.github.com/users/${username}/repos`,
            headers: {
                "Authorization" : authorization
            },
            method : "GET",
            success : function(data){
                for (var item in data) {
                    $('.skeleton').hide();
                    $('#table-repodir').append(getRepoRowTemplate(data[item], storage.hasOwnProperty(data[item].name)));
                }
                initEvents();
            },
            error: function (jqXHR, exception) {
                $('#table-repodir').append("<i>Cannot get repository details, Please try again. Error "+jqXHR.status+" :<code>"+jqXHR.responseText+"</code></i>");
            }
        });
}
function subscribe(repo_name){
    var subscribe_url = "https://5ebba2c4.ngrok.io/github_listener.php?appOrgId=$r_orgId&appSecurityContext=$r_securityContext";
    $.ajax({
            url : `https://api.github.com/repos/thisvijay/${repo_name}/hooks`,
            method : "POST",
            headers: {
                "Authorization" : authorization,
                "Content-Type": "application/json"
            },
            contentType: 'json',
            data: JSON.stringify({
                                    "name": "web",
                                    "active": true,
                                    "events": [
                                      "issues",
                                      "issue_comment"
                                    ],
                                    "config": {
                                      "url": subscribe_url,
                                      "content_type": "json"
                                    }
                                }),
            success : function(data){
                console.log(data);
                storeToStorage(repo_name, data.id);
                successSubscribe(repo_name);
            }
        });
}
function un_subscribe(repo_name){
    var hook_id = storage[repo_name];
    $.ajax({
            url : `https://api.github.com/repos/thisvijay/${repo_name}/hooks/${hook_id}`,
            method : "DELETE",
            headers: {
                "Authorization" : authorization
            },
            success : function(data){
                console.log(data);
                deleteFromStorage(repo_name);
                successUnSubscribe(repo_name);
            }
        });
}
function storeToStorage(key, value){
    storage[key] = value;
    localStorage.setItem("subscriptions", JSON.stringify(storage));
}
function deleteFromStorage(key){
    delete storage[key];
    localStorage.setItem("subscriptions", JSON.stringify(storage));
}
function successSubscribe(repo_name){
    repo_name = repo_name.replace('.','\\.');
    var elem_id = `#repo-${repo_name} .sub-btn`;
    $(elem_id).removeClass('processing').removeClass('subscribe').addClass('un-subscribe').text('Un-Subscribe').removeAttr('disabled');
}
function successUnSubscribe(repo_name){
    repo_name = repo_name.replace('.','\\.').replace('#','\\#');
    var elem_id = `#repo-${repo_name} .sub-btn`;
    $(elem_id).removeClass('processing').removeClass('un-subscribe').addClass('subscribe').text('Subscribe').removeAttr('disabled');
}
function initEvents(){
    $(document).on('click', '.subscribe', function(e){
        $(e.target).addClass('processing').text('Subscribing..').attr({'disabled':'true'});
        var reponame = $(e.target).attr('data-reponame');
        console.log(reponame);
        e.preventDefault();
        subscribe(reponame);
    });
    $(document).on('click','.un-subscribe', function(e){
        $(e.target).addClass('processing').text('Un-subscribing..').attr({'disabled':'true'});
        var reponame = $(e.target).attr('data-reponame');
         e.preventDefault();
         un_subscribe(reponame);
    });
}
function getRepoRowTemplate(obj, isSubscribed){
    return `<tr id="repo-${obj.name}">
                <td title="${obj.language} : ${obj.description}">
                    <a class="link-repo-name" href="${obj.html_url}" target="_blank">${obj.name}</a> <br>
                    <div class="repo-fullname">${obj.full_name}</div>
                </td>
                <td>
                    <button data-reponame="${obj.name}" class="sub-btn ${!isSubscribed ? "subscribe" : "un-subscribe"}">
                        ${!isSubscribed ? "Subscribe" : "Un-Subscribe"}
                    </button>
                </td>
            </tr>`;
}