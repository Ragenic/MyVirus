<?php

    /*
     * Async PHP class. Contains setTimeout(), clearTimeout() and runTimeouts() functions.
     * Alternate names:
     *     $timerId = $objectName->setTimeout(func, delay) >>> $timerId = $objectName(func, delay)
     *     $objectName->clearTimeout($timerId) >>> $objectName($timerId)
     *     $objectName->runTimeouts() >>> $objectName()
     *
     * delay = [milliseconds] = 1/1000[seconds]
     *
     * @author Alex Azimov <nicrt@mail.ru>
     */
    
    class Async {
      
        private $timeouts = array();
        
        private $freeKeys = array();
        
        public function setTimeout($task, $delay) {
            
            $currentTime = microtime(1);
            
            if (!empty($freeKeys)) {
                
                $newKey = array_shift($freeKeys);
                
                $currentTimeout = & $this->timeouts[$newKey];
                
            } else {
                
                $currentTimeout = & $this->timeouts[];
                
                end($this->timeouts);
                
                $newKey = key($this->timeouts);
            }
            
            $protectionCode = rand(1, 999999);
            
            $currentTimeout['runtime'] = $currentTime + ( $delay / 1000 );
            
            $currentTimeout['callback'] = $task;
            
            $currentTimeout['protection'] = $protectionCode;
            
            $timeoutLink = array();
            
            $timeoutLink['index'] = $newKey;
            
            $timeoutLink['protection'] = $protectionCode;
            
            return $timeoutLink;
        }
        
        public function clearTimeout(&$timeoutLink) {
            
            if ((isset($timeoutLink)) && (isset($this->timeouts[$timeoutLink['index']]))) {
                
                if ($this->timeouts[$timeoutLink['index']]['protection'] === $timeoutLink['protection']) {
                    
                    unset($this->timeouts[$timeoutLink['index']]);
                    
                    $this->freeKeys[] = $timeoutLink['index'];
                    
                    $timeoutLink = NULL;
                }
            }
        }
        
        public function runTimeouts() {
            
            $currentTime = microtime(1);
            
            foreach ($this->timeouts as $key => $value) {
                
                if ($this->timeouts[$key]['runtime'] <= $currentTime) {
                    
                    $this->timeouts[$key]['callback']();
                    
                    unset($this->timeouts[$key]);
                    
                    $this->freeKeys[] = $key;
                }
            }
        }
        
        public function __invoke($firstArg = -1783, $secondArg = -1783) {
            
            if (($firstArg === -1783) && ($secondArg === -1783)) {
                
                $this->runTimeouts();
                
            } else if (($firstArg !== -1783) && ($secondArg === -1783)) {
                
                $this->clearTimeout($firstArg);
                
            } else if (($firstArg !== -1783) && ($secondArg !== -1783)) {
                
                return $this->setTimeout($firstArg, $secondArg);
            }
        }
    }