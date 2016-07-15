




function start_game() {
    
    hideCBar();
    
    setSound();
    
    showGameInterface();
    
    var prepared = [newScenario('system', 'authentication', window.playerName)];
    
    sendServer(prepared);
}



function message_handler(e) {
    
    var drama = JSON.parse(e.data);
    dramaDuration = drama.length;
    
    for (var i = 0; i < dramaDuration; i++) {
        
        if (drama[i].type === 'system') {
            
            if (drama[i].command === 'ping') {
              
                pong();
                
            } else if (drama[i].command === 'serverReady') {
                
                window.serverReady = true;
                
            } else if (drama[i].command === 'authentication') {
                
                window.myID = drama[i].id;
                
                createPlayer(drama[i].id, window.playerName, drama[i].left, drama[i].top);
                
                if (drama[i].players > 0) {
                    
                    for (var j = 1; j <= drama[i].players; j++) {
                        
                        var colID = 'id' + j;
                        var colName = 'name' + j;
                        
                        createPlayer(drama[i][colID], drama[i][colName], -500, 0);
                        
                    }
                }
                
                sendBuffer();
                
            } else if (drama[i].command === 'newPlayer') {
                
                createPlayer(drama[i].id, drama[i].name, drama[i].left, drama[i].top);
				
				sendScenario(newScenario('scenario', 'move', window.myID, window.players[window.myID].left, window.players[window.myID].top));
                
            } else if (drama[i].command === 'deletePlayer') {
                
                deletePlayer(drama[i].id);
                
            }
            
        } else if (drama[i].type === 'scenario') {
            
            if (drama[i].action === 'enemy') {
                
                createEnemy(drama[i].id, drama[i].left, drama[i].top, drama[i].kind, drama[i].speed);
                
            } else if (drama[i].action === 'move') {
                
                movePlayer(drama[i].id, drama[i].left, drama[i].top);
                
            } else if (drama[i].action === 'deleteEnemy') {
				
				deleteEnemy(drama[i].id);
			}
        }
    }
}



function connectionOpen() {
    
    window.connected = true;
    
    _logCBar('Connection successful.');
    
    window.startTimeout = setTimeout(start_game, 2000);
}



function server_connect() {
  
    _logCBar('Trying to connect ' + WS_SERVER + '...');
    
    window.socket = new WebSocket(WS_SERVER);
    window.socket.onopen = connectionOpen;
    window.socket.onmessage = message_handler;
}



function start_sequence() {
    
    server_connect();
    
    setTimeout(function() {
        if (window.connected === false) {
            window.socket.close();
            clearTimeout(window.startTimeout);
            _logCBar('Connection failed.');
            _logCBar('Trying to start server...');
            server_start(function(response) {
                _logCBar(response);
            });
            setTimeout(function() {
                window.connected = false;
                server_connect();
                setTimeout(function() {
                    if (window.connected === false) {
                        window.socket.close();
                        clearTimeout(window.startTimeout);
                        _logCBar('Connection failed.');
                        _logCBar('Can not connect to server. Please, try again leter.');
                    }
                }, 5000);
            }, 5000);
        }
    }, 5000);
    
}



function resize_handler() {
    
    var screenHeight = document.documentElement.clientHeight;
    var topMargin;
    
    if (screenHeight > 470) {
        topMargin = Math.round((screenHeight - 470) / 2);
    } else {
        topMargin = 0;
    }
    
    gameBackground.style.top = topMargin + 50 + 'px';
    gameInterface.style.top = topMargin + 50 + 'px';
    
    window.gameFieldWidth = document.documentElement.clientWidth;
    gameInterface.style.width = window.gameFieldWidth + 'px';
    gameField.style.width = window.gameFieldWidth + 'px';
}



function load_handler() {
    
    resize_handler();
    
    start_sequence();
}



function key_handler(e) {
    
    var left = window.players[window.myID].left;
    var top = window.players[window.myID].top;
  
    if (((left > (window.gameFieldWidth - 100)) && (e.keyCode === 39)) ||
    ((left < 50) && (e.keyCode === 37)) ||
    ((top > 320) && (e.keyCode === 40)) ||
    ((top < 30) && (e.keyCode === 38))) {

        return;
    }
    
    if (e.keyCode === 39) {
        
        var newLeft = window.players[window.myID].left + 10;
        var newTop = window.players[window.myID].top;
        
        movePlayer(window.myID, newLeft, newTop);
        sendScenario(newScenario('scenario', 'move', window.myID, newLeft, newTop));
    }

    if (e.keyCode === 37) {
        
        var newLeft = window.players[window.myID].left - 10;
        var newTop = window.players[window.myID].top;
        
        movePlayer(window.myID, newLeft, newTop);
        sendScenario(newScenario('scenario', 'move', window.myID, newLeft, newTop));
    }

    if (e.keyCode === 38) {
        
        var newLeft = window.players[window.myID].left;
        var newTop = window.players[window.myID].top - 10;
        
        movePlayer(window.myID, newLeft, newTop);
        sendScenario(newScenario('scenario', 'move', window.myID, newLeft, newTop));
    }

    if (e.keyCode === 40) {
        
        var newLeft = window.players[window.myID].left;
        var newTop = window.players[window.myID].top + 10;
        
        movePlayer(window.myID, newLeft, newTop);
        sendScenario(newScenario('scenario', 'move', window.myID, newLeft, newTop));
    }
}



window.addEventListener('load', load_handler);
window.addEventListener('resize', resize_handler);

window.onkeydown = key_handler;
