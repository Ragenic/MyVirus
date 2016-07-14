<?php
    
    /*
    * MyVirus Server Toolbar.
    * 
    * @author Alex Azimov <nicrt@mail.ru>
    * 
    */
    
    class TOOL {
        
        public static function getRunKey() {
            
            if (file_exists('RunKey.wss')) {
                
                if (($file = fopen('RunKey.wss', 'rb')) !== false) {
                    
                    if (($RKey = fread($file, 8)) !== false) {
                        
                        if (fclose($file) !== false) {
                            
                            return $RKey;
                        }
                    }
                }
            }
            
            return false;
        }
        
        
        
        public static function createRunKey() {
            
            if (($file = fopen('RunKey.wss', 'wb')) !== false) {
                
                $RKey = sprintf('%08s', rand(1,99999999));
                
                if (fwrite($file, $RKey) !== false) {
                    
                    if (fclose($file) !== false) {
                        
                        return $RKey;
                    }
                }
            }
            
            return false;
        }
        
        
        
        public static function createStopSign() {
            
            if (($file = fopen('stop', 'w')) !== false) {
                
                if (fclose($file) !== false) {
                    
                    return true;
                }
            }
            
            return false;
        }
        
        
        
        public static function stopSign() {
            
            if (file_exists('stop')) {
                unlink('stop');
                return true;
            }
            
            return false;
        }
        
        
        
        public static function _to_string($value, $mod = 'clear') {
            
            $stringRepresentation = '';
            
            $type = gettype($value);
            
            switch ($type) {
                
                case 'boolean':
                    if ($value === true) {
                        $stringRepresentation = 'TRUE';
                    } else {
                        $stringRepresentation = 'FALSE';
                    }
                    $type = 'bool';
                    break;
                    
                case 'integer':
                    $stringRepresentation .= $value;
                    $type = 'int';
                    break;
                    
                case 'double':
                    $stringRepresentation .= $value;
                    break;
                    
                case 'string':
                    $stringRepresentation = $value;
                    $type = 'str';
                    $type .= '(' . strlen($value) . ')';
                    break;
                    
                case 'array':
                    $size = count($value);
                    $counter = 1;
                    foreach ($value as $key => $element) {
                        $stringRepresentation .= '<' . $key . '><' . self::_to_string($element, $mod) . '>';
                        if ($counter < $size) {
                            $stringRepresentation .= ' ';
                            $counter += 1;
                        }
                    }
                    $type .= '(' . $size . ')';
                    break;
                    
                case 'object':
                    $stringRepresentation = 'Object';
                    $type = 'obj';
                    break;
                
                case 'resource':
                    $stringRepresentation = 'Resource';
                    $type = 'res';
                    break;
                    
                case 'NULL':
                    $stringRepresentation = 'NULL';
                    break;
                    
                default:
                    $stringRepresentation = 'Unknown Type';
                    break;
            }
            if ($mod === 'dump') {
                
                $resultString = '[' . $type . ']=[' . $stringRepresentation . ']';
                
            } else {
                
                $resultString = $stringRepresentation;
            }
            
            return $resultString;
        }
        
        
        
        public static function _log_dump($value) {
            
            $logInformation = date('d.m.Y H:i:s') . " " . self::_to_string($value, 'dump') . "\r\n";
            
            $file = fopen('log.wss', 'ab');
            
            fwrite($file, $logInformation);
            
            fclose($file);
        }
        
        
        
        public static function _log($value) {
            
            $logInformation = date('d.m.Y H:i:s') . " " . self::_to_string($value) . "\r\n";
            
            $file = fopen('log.wss', 'ab');
            
            fwrite($file, $logInformation);
            
            fclose($file);
        }
        
        
        
        public static function _log_clear() {
            
            $file = fopen('log.wss', 'w');
            
            fclose($file);
        }
        
        
        
        public static function newScenario($type, $vA = null, $vB = null, $vC = null, $vD = null, $vE = null, $vF = null) {
          
            /*
            * $vA: system->command                                                                        scenario->action
            * $vB: system->authentication->id         system->newPlayer->id     system->deletePlayer->id  scenario->id
            * $vC: system->authentication->left       system->newPlayer->left                             scenario->left
            * $vD: system->authentication->top        system->newPlayer->top                              scenario->top
            * $vE: system->authentication->[players]  system->newPlayer->name                             scenario->kind
            * $vF:                                                                                        scenario->speed
            */
            
            $scenario = array();
            
            $scenario['type'] = $type;
            
            if ($type === 'system') {
                
                $scenario['command'] = $vA;
                
                if ($vA === 'authentication') {
                    $scenario['id'] = $vB;
                    $scenario['left'] = $vC;
                    $scenario['top'] = $vD;
                    $scenario['players'] = count($vE) - 1;
                    
                    $counter = 1;
                    foreach($vE as $player) {
                        if ($counter <= $scenario['players']) {
                            $colID = 'id' . $counter;
                            $colName = 'name' . $counter;
                            $scenario[$colID] = $player['id'];
                            $scenario[$colName] = $player['name'];
                        }
                        $counter += 1;
                    }
                    
                } elseif ($vA === 'newPlayer') {
                    $scenario['id'] = $vB;
                    $scenario['left'] = $vC;
                    $scenario['top'] = $vD;
                    $scenario['name'] = $vE;
                    
                } elseif ($vA === 'deletePlayer') {
                    $scenario['id'] = $vB;
                }
                
            } elseif ($type === 'scenario') {
                $scenario['action'] = $vA;
                $scenario['id'] = $vB;
                $scenario['left'] = $vC;
                $scenario['top'] = $vD;
                $scenario['kind'] = $vE;
                $scenario['speed'] = $vF;
            }
            
            return $scenario;
        }
        
        
        
        public static function PFS($value) {
            $prepare = array($value);
            $prepare = json_encode($prepare);
            return $prepare;
        }
    }
    
    
    
    