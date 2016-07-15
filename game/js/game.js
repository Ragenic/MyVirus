
function setSound() {
    
    if (window.sound === 'on') {
        var music = document.getElementById('audioPlayer');
        music.loop = true;
        music.play();
    }
}

function hideCBar() {
    connectionProgress.style.display = 'none';
}

function _logCBar(someText) {
    connectionContent.innerHTML += '# ' + someText + '<br><br>';
}

function showGameInterface() {
    
    gameWrapper.style.display = 'block';
    
    document.body.style.overflow = 'hidden';
    
    setTimeout(function drag() {
        window.currentDrag = window.currentDrag - 10;
        gameBackground.style.left = window.currentDrag + 'px';
        if (window.currentDrag < -500) {
            window.currentDrag = 0;
        }
        setTimeout(drag, 40);
    }, 40);
}

function deleteEnemy(id) {
    
    if (window.myID === 'player1') {
        clearTimeout(window.enemies[id].pushTimer);
    }
    
    clearTimeout(window.enemies[id].timer);
    
    document.getElementById(id).remove();
    
    window.enemies[id] = undefined;
}

function createEnemy(id, x_left, y_top, kind, speed) {
    
    if (window.enemies[id] === undefined) {
        window.enemies[id] = {};
        window.enemies[id].left = x_left;
        window.enemies[id].top = y_top;
        window.enemies[id].kind = kind;
        window.enemies[id].speed = speed;
        
        var newEnemyDOM = document.createElement('div');
        if (kind === 'blue') {
            newEnemyDOM.className = 'enemyBlue';
        } else if (kind === 'green') {
            newEnemyDOM.className = 'enemyGreen';
        } else if (kind === 'swarm') {
            newEnemyDOM.className = 'enemySwarm';
        }
        
        newEnemyDOM.id = id;
        
        gameField.appendChild(newEnemyDOM);
        
        document.getElementById(id).style.top = y_top - 32 + 'px';
        document.getElementById(id).style.left = x_left - 32 + 'px';
        
        window.enemies[id].timer = setTimeout(function moveEnemy(id) {
            window.enemies[id].left = window.enemies[id].left - speed;
            document.getElementById(id).style.left = window.enemies[id].left - 32 + 'px';
            if (window.enemies[id].left < -200) {
                deleteEnemy(id);
            }
            window.enemies[id].timer = setTimeout(moveEnemy, 40, id);
        }, 40, id);
        
        if (window.myID === 'player1') {
            
            window.enemies[id].pushTimer = setTimeout(function pushEnemy(id) {
				if ((Math.abs(window.enemies[id].left - window.players['player1'].left) < 50) && ((Math.abs(window.enemies[id].top - window.players['player1'].top) < 50))) {
					sendScenario(newScenario('scenario', 'deleteEnemy', id));
					deleteEnemy(id);
				} else if ((window.players['player2'] !== undefined) && ((Math.abs(window.enemies[id].left - window.players['player2'].left) < 50) && ((Math.abs(window.enemies[id].top - window.players['player2'].top) < 50)))) {
					sendScenario(newScenario('scenario', 'deleteEnemy', id));
					deleteEnemy(id);
				} else {
					sendScenario(newScenario('scenario', 'enemy', id, window.enemies[id].left, window.enemies[id].top, window.enemies[id].kind, window.enemies[id].speed));
				}
                window.enemies[id].pushTimer = setTimeout(pushEnemy, 200, id);
            }, 200, id);
        }
        
    } else {
        window.enemies[id].left = x_left;
    }
}

function movePlayer(id, x_left, y_top) {
    
    window.players[id].left = x_left;
    window.players[id].top = y_top;
    
    drawPlayer(id);
}








