

function ajax_request(url, content, callback, timeout_duration, timeout_callback) {
    
    // Inside callback function you have 'ajax_response' with parsed response.
    // On server: $HTTP_RAW_POST_DATA
    
    var request = new XMLHttpRequest();
    
    request.open('POST', url);
    
    request.setRequestHeader('Content-type', 'application/json; charset=utf-8');
    
    var request_body = JSON.stringify(content);
    
    request.onreadystatechange = function() {
        
        if (this.readyState != 4) {
            return;
        }
        
        if (this.status != 200) {
            
            var ajax_response = 'ERROR: ' + this.status;
            return;
        }
        //alert(this.responseText);
        var ajax_response = JSON.parse(this.responseText);
        
        callback(ajax_response);
    }
    
    if (timeout_duration !== undefined) {
        
        request.timeout = timeout_duration;
    }
    
    if (timeout_callback !== undefined) {
        
        request.ontimeout = timeout_callback;
    }
    
    request.send(request_body);
}



function server_start(callback) {
    
    if (callback === undefined) {
        
        callback = function(response) {
            console.log(response);
        }
    }
    
    ajax_request(STATUS, 'run', function(response) {
        
        var RunKey = response;
        
        if (RunKey !== 'false') {
            
            ajax_request(SERVER, RunKey, function(response) {
                
                callback('ERROR: Server crashed.');
                
            }, 1000, function() {
                
                callback('Server started correctly.');
            });
            
        } else {
            
            callback('ERROR: Server already launched.');
        }
        
    }, 10000, function() {
        
        callback('ERROR: Server not responding.');
    });
}



function server_stop(password_key, callback) {
    
    if (callback === undefined) {
        
        callback = function(response) {
            console.log(response);
        }
    }
    
    if (password_key === undefined) {
        
        password_key = '0000';
    }
    
    ajax_request(STATUS, password_key, function(response) {
        
        callback(response);
        
    }, 10000, function() {
        
        callback('ERROR: Server not responding.');
    });
}



function newScenario(type, vA, vB, vC, vD, vE, vF) {
    var scenario = {};
    
    scenario.type = type;
    
    if (type === 'system') {
        scenario.command = vA;
        
        if (vA === 'authentication') {
            scenario.name = vB;
        }
        
    } else if (type === 'scenario') {
        scenario.action = vA;
        
        if (vA === 'enemy') {
            scenario.id = vB;
            scenario.left = vC;
            scenario.top = vD;
            scenario.kind = vE;
            scenario.speed = vF;
            
        } else if (vA === 'move') {
            scenario.id = vB;
            scenario.left = vC;
            scenario.top = vD;
			
        } else if (vA === 'deleteEnemy') {
			scenario.id = vB;
		}
    }
    
    return scenario;
}



function sendServer(value) {
    var encoded = JSON.stringify(value) + '!^%';
    window.socket.send(encoded);
}



function sendBuffer() {
    if (window.serverReady === true) {
        if (window.buffer.length > 0) {
            sendServer(window.buffer);
            window.buffer.length = 0;
            window.serverReady = false;
        }
    }
    setTimeout(sendBuffer, 40);
}



function sendScenario(value) {
    if (window.buffer.length === 0) {
        window.buffer.push(newScenario('system', 'forward'));
    }
    window.buffer.push(value);
}



function pong() {
    if (window.buffer.length === 0) {
        window.serverReady = false;
        sendServer([newScenario('system', 'pong')]);
    }
}



function drawPlayer(id) {
    
    document.getElementById(id).style.left = window.players[id].left - 25 + 'px';
    document.getElementById(id).style.top = window.players[id].top - 25 + 'px';
}



function createPlayer(id, name, x_left, y_top) {
    
    window.players[id] = {};
    
    window.players[id].name = name;
    window.players[id].left = x_left;
    window.players[id].top = y_top;
    
    var newPlayerDOM = document.createElement('div');
    
    newPlayerDOM.className = 'player';
    newPlayerDOM.id = id;
    newPlayerDOM.innerHTML = '<div class="playerName">' + name + '</div><div class="playerImage"></div>';
    
    gameField.appendChild(newPlayerDOM);
    
    drawPlayer(id);
}



function deletePlayer(id) {
    
    window.players[id] = undefined;
    
    document.getElementById(id).remove();
}









