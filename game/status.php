<?php

    /*
    * 
    * Supports incoming requests "status" and "run"
    * 
    * @author Alex Azimov <nicrt@mail.ru>
    * 
    */
    
    require_once "ClassAutoloader.php";
    
    error_reporting(E_ALL);
    set_time_limit(0);
    ob_implicit_flush(true);
    ignore_user_abort(true);
    
    const PASSWORD = '0000';
    
    if (isset($HTTP_RAW_POST_DATA)) {
        
        $request = json_decode($HTTP_RAW_POST_DATA);
        
        if (($request === 'status') || ($request === 'run') || ($request === PASSWORD)) {
            
            $async = new Async();
            $counter = 0;
            $catch = array();
            $nibble = false;
            
            function castaline() {
                global $async;
                global $counter;
                global $catch;
                if ((($hookin = DETECTOR::get()) !== false) && (strlen($hookin) === 8)) {
                    $catch[] = $hookin;
                }
                if (($counter += 1) < 5) {
                    $async('castaline', 380);
                }
            }
            
            $async('castaline', 380);
            
            while (true) {
                
                $async();
                
                if ($counter >= 5) {
                    
                    break;
                }
            }
            
            if (($size = count($catch)) > 1) {
                
                for ($i = 1; $i < $size; $i++) {
                    
                    for ($j = 0; $j < $i; $j++) {
                        
                        if ($catch[$j] != $catch[$i]) {
                            
                            $nibble = true;
                        }
                    }
                }
                
            }
            
            if ($nibble === true) {
                
                if ($request === 'status') {
                    
                    echo json_encode('online');
                    
                } elseif ($request === 'run') {
                    
                    echo json_encode('false');
                    
                } elseif ($request === PASSWORD) {
                    
                    if (TOOL::createStopSign() !== false) {
                    
                        echo json_encode('Server stopped correctly.');
                        
                    } else {
                        
                        echo json_encode('ERROR: Server not stopped.');
                    }
                    
                } else {
                    
                    echo json_encode('ERROR: Unexpected shit happend.');
                }
                
            } else {
                
                if ($request === 'status') {
                    
                    echo json_encode('offline');
                    
                } elseif ($request === 'run') {
                    
                    $RKey = TOOL::createRunKey();
                    
                    echo json_encode($RKey);
                    
                } elseif ($request === PASSWORD) {
                    
                    echo json_encode('ERROR: Server is not running.');
                    
                } else {
                    
                    echo json_encode('ERROR: Unexpected shit happend.');
                }
            }
            
        } else {
            
            echo json_encode('ERROR: Incorrect request. Use SEND Ajax request in JSON format with "status" or "run" text.');
        }
        
    } else {
        
        echo 'ERROR: Incorrect request. This page avaliable only by AJAX request. Use SEND Ajax request in JSON format with "status" or "run" text.';
    }