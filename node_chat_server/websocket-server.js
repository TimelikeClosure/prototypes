
var ws = require("nodejs-websocket");

var server = ws.createServer(function(conn){
    //  stuff for your server here

    conn.on("text", function(str){
        console.log("Received "+str);
        conn.sendText("you said: "+str);
    }); //  basic echo. Take what you get and send it back

    conn.on("close", function(code, reason){
        console.log("Connection closed: "+reason);
    });


}).listen(8001);    //  in this case, I'm going to listen on port 8001
