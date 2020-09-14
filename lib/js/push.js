APE.Config.scripts = [APE.Config.baseUrl + '/Build/uncompressed/apeCoreSession.js'];

//Instantiate APE Client
var client = new APE.Client();

$(document).ready(function () {
        //1) Load APE Core
        client.load({'identifier': 'hdihris'});

        //2) Intercept 'load' event. This event is fired when the Core is loaded and ready to connect to APE Server
        client.addEvent('load', function() {
                //3) Call core start function to connect to APE Server, and prompt the user for a nickname
                client.core.start({"name": user.get_value('user_id')});
                console.log("connected");
        });

        //4) Listen to the ready event to know when your client is connected
        client.addEvent('ready', function() {
                //1) join 'testChannel'
                client.core.join(['default']);
                
                //2) Intercept pipeCreate event
                client.addEvent('multiPipeCreate', function(pipe, options) {
                        //3) Send the message on the pipe
                        //pipe.send('Hello world!');                                                                    
                        pipe.addEvent('userJoin', function(user, pipe) {
                                msg = 'User ID ' + user.properties.name + ' joined: ' + pipe.name + ' channel';                                
                                pipe.send(msg);
                                console.log(msg);
                        });                        
                });
                                 
                if (client.core.options.restore) {
                        //If it's a session restoration, ask the APE server for the custom session 'key1'
                        client.core.getSession('key1', function(resp) {
                           
                        });
                } else {
                        //Saving custom session key1 one the server
                        client.core.setSession({'key1': user.get_value('user_id')});
                }                  
        });

        //React to all raw and cmd events
        client.addEvent('onRaw', function(e) {
                if (e.raw == 'update_candidate') {                        
                        if (module.get_value('module_link') == 'recruitment/candidates') {
                                create_filter_list(e.data);                                  
                        }                                
                }
        }); 

});