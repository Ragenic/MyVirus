<?php
    /*
    * Detector for Web Socket Server.
    * 
    * @author Alex Azimov <nicrt@mail.ru>
    * 
    */
    
    class DETECTOR {
        
        public static function update() {
            
            if (($file = fopen('Detector.wss', 'wb')) !== false) {
                
                $DKey = sprintf('%08s', rand(1,99999999));
                
                if (fwrite($file, $DKey) !== false) {
                    
                    if (fclose($file) !== false) {
                        
                        return true;
                    }
                }
            }
            
            return false;
        }
        
        
        
        public static function get() {
            
            if (file_exists('Detector.wss')) {
                
                if (($file = fopen('Detector.wss', 'rb')) !== false) {
                    
                    if (($DKey = fread($file, 8)) !== false) {
                        
                        if (fclose($file) !== false) {
                            
                            return $DKey;
                        }
                    }
                }
            }
            
            return false;
        }
        
        
        
        public static function close() {
            
            if (unlink('Detector.wss') !== false) {
                
                return true;
            }
            
            return false;
        }
    }