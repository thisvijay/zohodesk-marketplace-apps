window.onload = function () {
    ZOHODESK.extension.onload().then(function (App) {

        var threadId = App.meta.threadId;

        ZOHODESK.get('database', {'key': threadId + '_channel_details', 'queriableValue': 'twilio_thread_extras'}).then(function (response) {
            console.log(response);
            var resdata = response['database.get']['data'][0]['value'];
            for (var key in resdata) {
                if(resdata[key]===null){
                    continue;
                }
                switch(key){
                    case 'totalReplyCount':
                        document.getElementById('propertyholder').innerHTML += '<div id="replies" class="property"><div class="value">' + resdata[key] + '</div><div class="key">Replies</div></div>';
                        break;
                    case 'likeCount':
                        document.getElementById('propertyholder').innerHTML += '<div id="likes" class="property"><div class="value">' + resdata[key] + '</div><div class="key">Likes</div></div>';
                        break;
                    case 'canReply':
                        if(resdata[key]){
                            document.getElementById('propertyholder').innerHTML += '<div id="'+key+'" class="status-text true">Replies are acceptable</div>';
                        }
                        else{
                            document.getElementById('propertyholder').innerHTML += '<div id="'+key+'" class="status-text false">Replies are prohibited to this comment</div>';
                        }
                        break;
                    case 'isPublic':
                        if(resdata[key]){
                            document.getElementById('propertyholder').innerHTML += '<div id="'+key+'" class="status-text true">Public Comment</div>';
                        }
                        else{
                            document.getElementById('propertyholder').innerHTML += '<div id="'+key+'" class="status-text false">Private Comment</div>';
                        }
                        break;
                    default:
                        document.getElementById('propertyholder').innerHTML += '<div class=" extras property"><div class="key">' + key + '</div><div class="value">' + resdata[key] + '</div></div>';
                }
            }
        }).catch(function (err) {
            console.log(err);
        });

        document.getElementById('propertyholder').innerHTML += '<div id="mark_spam" class="action_item button back-crimson" onclick="markSpam('+threadId+')">Mark As Spam</div>';

        //Get ticket related data
//				ZOHODESK.get('ticket.email').then(function (res) {
//					//response Handling
//				}).catch(function (err) {
//					//error Handling
//				});
        /*	
         //To Set data in Desk UI Client
         ZOHODESK.set('ticket.comment', { 'content': "Test comment" }).then(function (res) {
         //response Handling
         }).catch(function (err) {
         //error Handling
         });
         
         //Access Data Storage for an extension
         //Get the saved data of an extension from data storage
         ZOHODESK.get('database', { 'key': 'key1', 'queriableValue': 'value1' }).then(function (response) {
         //response Handling
         }).catch(function (err) {
         //error Handling
         })            
         
         //Save data in to data staorage
         ZOHODESK.set('database', { 'key': 'key_1', 'value': { 'id': 123 }, 'queriableValue': 'value1' }).then(function (response) {
         //response Handling
         }).catch(function (err) {
         //error Handling
         })
         
         //Change tabs in ticket detailview
         ZOHODESK.invoke('ROUTE_TO', 'ticket.attachments');
         
         //To Insert the content in the current opened reply editor from extension
         ZOHODESK.invoke('Insert', 'ticket.replyEditor', { content: "<p>your content</p>" });
         
         //To listen to an event in desk
         App.instance.on('comment_Added', function(data){
         //data handling 
         });
         
         //To access locale
         App.locale;
         
         //To access localresources
         App.localeResource            
         
         //To Know more on these, please read the documentation
         */
    });
}

function markSpam(commentId){
    var reqObj = {
               url : "https://www.googleapis.com/youtube/v3/comments/markAsSpam",
               type : "POST",
               headers : {
                   "Authorization" : 'Bearer ${UTUBOAUT}'
               },
               postBody : {},
               data : {
                   "id":commentId
               }
            }
            ZOHODESK.request(reqObj).then(function(response){
                var responseJSON = JSON.parse(response);
                if(responseJSON["statusCode"]==204){
                    document.getElementById('mark_spam').innerHTML = "Successfully marked as spam";
                    document.getElementById("mark_spam").classList.remove("button");
                    document.getElementById("mark_spam").classList.remove("back-crimson");
                }
            });
}