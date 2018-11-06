subscribe.php     --   used for adding webhook for incoming sms in twilio. ( Called from Desk )

addReply.php      --   used for receiving the agent reply from desk and sends it to twilio ( Called from Desk )

pullMessages.php  --   desk calls this url periodically, and the old twilio messages fetched from twilio and 						   are pushed to the desk ( Called from Desk )

syncMessage.php   --   used for receiving the incoming sms from twilio and sends it to Desk.
						(Called from Twilio since this URL is configured as webhook callback URL in twilio)