<?php
    
    require_once "ClassAutoloader.php";
    
    error_reporting(E_ALL);
    set_time_limit(0);
    ob_implicit_flush(true);
    ignore_user_abort(true);
    
    if ((($RunKey = TOOL::getRunKey()) !== false) && ($RunKey === json_decode($HTTP_RAW_POST_DATA)) && (unlink('RunKey.wss') !== false)) {
        
        $Async = new Async();
        
        $wss = new WebSocketServer('tcp://127.0.0.1:8887');
        
        $wss->setListenDuration(1000);
        
        $connects = array();
        
        $players = array();
        
        $enemyCounter = 1;
        
        function sendPlayers($value) {
            global $wss;
            global $players;
            foreach($players as $connect => $player) {
                $wss->sendTo($connect, $value);
            }
        }
        
        function sendExcept($client, $value) {
            global $wss;
            global $players;
            foreach($players as $connect => $player) {
                if ($connect !== $client) {
                    $wss->sendTo($connect, $value);
                }
            }
        }
        
        function newPlayerID() {
            global $players;
            $IDs = array();
            foreach ($players as $player) {
                $IDs[] = $player['id'];
            }
            $breakPoint = false;
            $counter = 1;
            do {
                $expect = 'player' . $counter;
                if (in_array($expect, $IDs)) {
                    $counter += 1;
                } else {
                    $newID = $expect;
                    $breakPoint = true;
                }
            } while ($breakPoint === false);
            return $newID;
        }
        
        function updateDetector() {
            global $Async;
            DETECTOR::update();
            $Async('updateDetector', 500);
        }
        
        $Async('updateDetector', 500);
        
        function deadlyConnectsChecker() {
            global $Async;
            global $connects;
            global $wss;
            global $players;
            $currentTime = time();
            foreach ($connects as $client => $connect) {
                if (($currentTime - $connect['recentActivity']) > 5) {
                    if ($connect['pinged'] === true) {
                        $prepared = TOOL::PFS(TOOL::newScenario('system', 'deletePlayer', $players[$client]['id']));
                        sendExcept($client, $prepared);
                        unset($players[$client]);
                        $wss->closeConnection($client);
                        unset($connects[$client]);
                    } else {
                        $connects[$client]['pinged'] = true;
                        $prepared = TOOL::PFS(TOOL::newScenario('system', 'ping'));
                        $wss->sendTo($client, $prepared);
                    }
                }
            }
            $Async('deadlyConnectsChecker', 5000);
        }
        
        $Async('deadlyConnectsChecker', 5000);
        
        function createNewEnemy() {
            global $Async;
            global $players;
            global $enemyCounter;
            
            if (count($players) > 0) {
                $newEnemyID = 'enemy' . $enemyCounter;
                $enemyCounter += 1;
                $newEnemyLeft = 1500;
                $newEnemyTop = rand(50,350);
                $newEnemyRandomKind = rand(1,3);
                switch ($newEnemyRandomKind) {
                    case 1: $newEnemyKind = 'blue';
                            break;
                    case 2: $newEnemyKind = 'green';
                            break;
                    case 3: $newEnemyKind = 'swarm';
                            break;
                }
                $newEnemySpeed = rand(10,25);
                
                $prepared = TOOL::PFS(TOOL::newScenario('scenario', 'enemy', $newEnemyID, $newEnemyLeft, $newEnemyTop, $newEnemyKind, $newEnemySpeed));
                sendPlayers($prepared);
            }
            
            $Async('createNewEnemy', 2500);
        }
        
        $Async('createNewEnemy', 2500);
        
        TOOL::_log_clear();
        
        while (true) {
            
            $Async();
            
            if ($wss->listen() != 0) {
                
                if (($connectionKey = $wss->getConnect()) !== false) {
                    
                    $connects[$connectionKey] = array('recentActivity' => time(), 'pinged' => false);
                }
                
                $recivedData = $wss->getData();
                
                if (count($recivedData) > 0) {
                    
                    foreach($recivedData as $msg) {
                        
                        if ($msg['data'] === $wss->getCloseConnectionMarker()) {
                            if (isset($players[$msg['connect']])) {
                                $prepared = TOOL::PFS(TOOL::newScenario('system', 'deletePlayer', $players[$msg['connect']]['id']));
                                sendExcept($msg['connect'], $prepared);
                                unset($players[$msg['connect']]);
                            }
                            unset($connects[$msg['connect']]);
                            
                        } else {
                            
                            $connects[$msg['connect']]['recentActivity'] = time();
                            $connects[$msg['connect']]['pinged'] = false;
                            $prepared = TOOL::PFS(TOOL::newScenario('system', 'serverReady'));
                            $wss->sendTo($msg['connect'], $prepared);
                            
                            $client = $msg['connect'];
                            $drama = json_decode($msg['data'], true);
                            
                            if ($drama[0]['type'] === 'system') {
                                
                                if ($drama[0]['command'] === 'authentication') {
                                    
                                    $players[$client]['id'] = newPlayerID();
                                    $players[$client]['name'] = $drama[0]['name'];
                                    
                                    $newPlayerLeft = rand(50, 200);
                                    $newPlayerTop = rand(50, 350);
                                    
                                    $prepared = TOOL::PFS(TOOL::newScenario('system', 'authentication', $players[$client]['id'], $newPlayerLeft, $newPlayerTop, $players));
                                    $wss->sendTo($client, $prepared);
                                    
                                    $prepared = TOOL::PFS(TOOL::newScenario('system', 'newPlayer', $players[$client]['id'], $newPlayerLeft, $newPlayerTop, $players[$client]['name']));
                                    sendExcept($client, $prepared);
                                    
                                } elseif ($drama[0]['command'] === 'forward') {
                                    
                                    sendExcept($client, $msg['data']);
                                    
                                }
                            }
                        }
                    }
                }
            }
            
            if (TOOL::stopSign() === true) {
                DETECTOR::close();
                unset($wss);
                break;
            }
        }
    }
    
    