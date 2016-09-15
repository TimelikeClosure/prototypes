console.log('hello');

var http = require('http'); //  includes http module
var dispatcher = require('httpdispatcher'); //  includes httpdispatcher module

const PORT = 8881;  //  define client access port number

//  Define client request handler
function handleRequest(request, response) {
    //response.end('It works!! Path Hit: ' + request.url);
    try {
        console.log("requested url: " + request.url);
        dispatcher.dispatch(request, response);
    } catch(err) {
        console.log(err);
    }
}

dispatcher.setStatic('/resources');
dispatcher.setStaticDirname('static');

dispatcher.beforeFilter(/\//, function(req, res, chain){
    console.log("Before filter");
    chain.next(req, res, chain);
});

dispatcher.afterFilter(/\//, function(req, res, chain){
    console.log("After filter");
    chain.next(req, res, chain);
});

dispatcher.onGet("/page1", function(req, res){  //  Defines the route for a GET request with a relative address of '/page1'
    res.writeHead(200, {'Content-Type': 'text/plain'}); //  Sets the response HTML status code to 200 and the header `Content-Type`
    res.end('Page One');    //  Sets the response body and closes the message
});

dispatcher.onPost("/post1", function(req, res){ //  Defines the route for a POST request with a relative address of '/post1'
    res.writeHead(200, {'Content-Type': 'text/plain'}); //  Sets the response HTML status code to 200 and the header 'Content-Type'
    res.end('Got Post Data');   //  Sets the response body and closes the message
});

dispatcher.onPost("/page2", function(req, res){ //  Defines the route for a POST request with a relative address of '/page2'
    res.writeHead(200, {'Content-Type': 'text/plain'}); //  Sets the response HTML status code to 200 and the header 'Content-Type'
    res.end('Page Two');   //  Sets the response body and closes the message
});

dispatcher.onError(function(req, res){
    res.writeHead(404);
    res.end();
});

var server = http.createServer(handleRequest);  //  Start server that handles client requests

//  Tells server to start accepting requests on the previously defined port number, and to log a message to the console once it is successful
server.listen(PORT, function(){
    console.log("Server listening on: http://localhost:%s", PORT);
});