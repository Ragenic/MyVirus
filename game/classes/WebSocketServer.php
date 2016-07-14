<?php
    
    /*
    * WebSoket Server class.
    * 
    * @author Alex Azimov <nicrt@mail.ru>
    *
    * Functions 'handshake', 'encode' and 'decode' written by:
    *   Nico Kaiser <nico@kaiser.me>
    *   Simon Samtleben <web@lemmingzshadow.net>
    */
    
    class WebSocketServer {
      
        private $socket;
        
        private $connects = array();
        
        private $read = array();
        
        private $write;
        
        private $except;
        
        private $dataBufferIDs = array();
        
        private $dataBufferContent = array();
        
        private $listenDuration = 0;
        
        private $endlineMarker = '!^%';
        
        private $closeConnectionMarker = 'X^%';
        
        
        
        public function __construct($location) {
            
            $this->socket = stream_socket_server($location, $errno, $errstr);
            
            if ($this->socket === false) {
                die($errstr. "(" .$errno. ")\n");
            }
        }
        
        
        
        public function __destruct() {
            
            fclose($this->socket);
        }
        
        
        
        public function listen($customDuration = null) {
          
            if ($customDuration !== null) {
                
                $localDuration = $customDuration * 1000;
                
            } else {
                
                $localDuration = $this->listenDuration;
            }
            
            $this->read = $this->connects;
            
            $this->read[] = $this->socket;
            
            $this->write = null;
            
            $this->except = null;
            
            $surprises = stream_select($this->read, $this->write, $this->except, 0, $localDuration);
            
            return $surprises;
        }
        
        
        
        public function getConnect() {
            
            if (in_array($this->socket, $this->read)) {
                
                if (($connect = stream_socket_accept($this->socket, -1)) && $this->handshake($connect)) {
                    
                    $this->connects[] = $connect;
                    
                    end($this->connects);
                    
                    $newKey = key($this->connects);
                    
                } else {
                    
                    $newKey = false;
                }
                
                unset($this->read[ array_search($this->socket, $this->read) ]);
                
                return $newKey;
                
            }
            
            return false;
        }
        
        
        
        public function getData() {
            
            $data = array();
            
            foreach ($this->read as $key => $connect) {
                
                $intermediate = fread($connect, 8192);
                
                if (in_array($connect, $this->dataBufferIDs)) {
                    
                    $DBKey = array_search($connect, $this->dataBufferIDs);
                    
                    $prepare = $this->dataBufferContent[$DBKey];
                    
                    unset($this->dataBufferIDs[$DBKey]);
                    
                    unset($this->dataBufferContent[$DBKey]);
                    
                } else {
                    
                    $prepare = '';
                }
                
                $prepare .= $intermediate;
                
                $frame = $prepare;
                $result = array();
                $firstFrame = true;
                
                while(strlen($frame) > 0) {
                    
                    $part = $this->decode($frame);
                    
                    if ($part === false) {
                        
                        $result = $part;
                        
                        break;
                    }
                    
                    if ($firstFrame === true) {
                        
                        $result['payload'] = $part['payload'];
                        $result['type'] = $part['type'];
                        
                        $firstFrame = false;
                        
                    } else {
                        
                        $result['payload'] .= $part['payload'];
                    }
                    
                    $completed = strlen($part['payload']) + $part['offset'];
                    
                    $frame = substr($frame, $completed);
                }
                
                if ($result === false) {
                    
                    $newDBKey = $this->newDBKey();
                    
                    $this->dataBufferIDs[$newDBKey] = $connect;
                    
                    $this->dataBufferContent[$newDBKey] = $prepare;
                    
                } else {
                  
                    if ($result['type'] === 'close') {
                        
                        $data[] = array('connect' => array_search($connect, $this->connects), 'data' => $this->closeConnectionMarker);
                    
                        fclose($connect);
                        
                        unset($this->connects[array_search($connect, $this->connects)]);
                        
                    } else {
                        
                        $lineLength = strlen($result['payload']);
                        
                        $endlineMarkerLength = strlen($this->endlineMarker);
                        
                        $endlineMark = substr($result['payload'], -$endlineMarkerLength);
                        
                        if ($endlineMark === $this->endlineMarker) {
                            
                            $data[] = array('connect' => array_search($connect, $this->connects), 'data' => substr($result['payload'], 0, -$endlineMarkerLength));
                            
                        } else {
                          
                            $newDBKey = $this->newDBKey();
                            
                            $this->dataBufferIDs[$newDBKey] = $connect;
                            
                            $this->dataBufferContent[$newDBKey] = $prepare;
                        }
                    }
                }
            }
            
            return $data;
        }
        
        
        
        public function sendTo($client, $data) {
            
            $prepared = $this->encode($data);
            
            if ($prepared === false) {
                
                $prepared = $this->encode('ENCODING ERROR: 1004 FRAME TOO LONG');
            }
            
            fwrite($this->connects[$client], $prepared);
        }
        
        
        
        public function sendAll($data) {
            
            $prepared = $this->encode($data);
            
            if ($prepared === false) {
                
                $prepared = $this->encode('ENCODING ERROR: 1004 FRAME TOO LONG');
            }
            
            foreach($this->connects as $connect) {
                
                fwrite($connect, $prepared);
            }
        }
        
        
        public function closeConnection($client) {
            
            if (in_array($this->connects[$client], $this->dataBufferIDs)) {
                
                $DBKey = array_search($this->connects[$client], $this->dataBufferIDs);
                
                unset($this->dataBufferIDs[$DBKey]);
                
                unset($this->dataBufferContent[$DBKey]);
            }
            
            fclose($this->connects[$client]);
            
            unset($this->connects[$client]);
        }
        
        
        
        public function checkConnection($client) {
            
            return isset($this->connects[$client]);
        }
        
        
        
        public function setListenDuration($value) {
            
            $this->listenDuration = ($value * 1000);
        }
        
        
        
        public function setEndlineMarker($value) {
            
            $this->endlineMarker = $value;
        }
        
        
        
        public function setCloseConnectionMarker($value) {
            
            $this->closeConnectionMarker = $value;
        }
        
        
        
        public function getCloseConnectionMarker($value) {
            
            return $this->closeConnectionMarker;
        }
        
        
        
        private function newDBKey() {
            
            $pointer = 0;
            
            while (true) {
                
                if (!isset($this->dataBufferIDs[$pointer])) {
                    
                    return $pointer;
                    
                } else {
                    
                    $pointer += 1;
                }
            }
        }
        
        
        
        private function handshake($connect) {
            
            $info = array();

            $line = fgets($connect);
            $header = explode(' ', $line);
            $info['method'] = $header[0];
            $info['uri'] = $header[1];

            while ($line = rtrim(fgets($connect))) {
                if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
                    $info[$matches[1]] = $matches[2];
                } else {
                    break;
                }
            }

            $address = explode(':', stream_socket_get_name($connect, true));
            $info['ip'] = $address[0];
            $info['port'] = $address[1];

            if (empty($info['Sec-WebSocket-Key'])) {
                return false;
            }

            $SecWebSocketAccept = base64_encode(pack('H*', sha1($info['Sec-WebSocket-Key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
            $upgrade = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
                "Upgrade: websocket\r\n" .
                "Connection: Upgrade\r\n" .
                "Sec-WebSocket-Accept:".$SecWebSocketAccept."\r\n\r\n";
            fwrite($connect, $upgrade);

            return $info;
        }
        
        
        
        private function encode($payload, $type = 'text', $masked = false) {
            
            $payload .= '';
            
            $frameHead = array();
            
            $payloadLength = strlen($payload);

            switch ($type) {
                case 'text':
                    // first byte indicates FIN, Text-Frame (10000001):
                    $frameHead[0] = 129;
                    break;

                case 'close':
                    // first byte indicates FIN, Close Frame(10001000):
                    $frameHead[0] = 136;
                    break;

                case 'ping':
                    // first byte indicates FIN, Ping frame (10001001):
                    $frameHead[0] = 137;
                    break;

                case 'pong':
                    // first byte indicates FIN, Pong frame (10001010):
                    $frameHead[0] = 138;
                    break;
            }

            // set mask and payload length (using 1, 3 or 9 bytes)
            if ($payloadLength > 65535) {
                $payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
                $frameHead[1] = ($masked === true) ? 255 : 127;
                for ($i = 0; $i < 8; $i++) {
                    $frameHead[$i + 2] = bindec($payloadLengthBin[$i]);
                }
                // most significant bit MUST be 0
                if ($frameHead[2] > 127) {
                    return false;
                }
            } elseif ($payloadLength > 125) {
                $payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
                $frameHead[1] = ($masked === true) ? 254 : 126;
                $frameHead[2] = bindec($payloadLengthBin[0]);
                $frameHead[3] = bindec($payloadLengthBin[1]);
            } else {
                $frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
            }

            // convert frame-head to string:
            foreach (array_keys($frameHead) as $i) {
                $frameHead[$i] = chr($frameHead[$i]);
            }
            if ($masked === true) {
                // generate a random mask:
                $mask = array();
                for ($i = 0; $i < 4; $i++) {
                    $mask[$i] = chr(rand(0, 255));
                }

                $frameHead = array_merge($frameHead, $mask);
            }
            $frame = implode('', $frameHead);

            // append payload to frame:
            for ($i = 0; $i < $payloadLength; $i++) {
                $frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
            }

            return $frame;
        }
        
        
        
        private function decode($data) {
            
            $unmaskedPayload = '';
            
            $decodedData = array();

            // estimate frame type:
            $firstByteBinary = sprintf('%08b', ord($data[0]));
            $secondByteBinary = sprintf('%08b', ord($data[1]));
            $opcode = bindec(substr($firstByteBinary, 4, 4));
            $isMasked = ($secondByteBinary[0] == '1') ? true : false;
            $payloadLength = ord($data[1]) & 127;

            switch ($opcode) {
                // text frame:
                case 1:
                    $decodedData['type'] = 'text';
                    break;

                case 2:
                    $decodedData['type'] = 'binary';
                    break;

                // connection close frame:
                case 8:
                    $decodedData['type'] = 'close';
                    break;

                // ping frame:
                case 9:
                    $decodedData['type'] = 'ping';
                    break;

                // pong frame:
                case 10:
                    $decodedData['type'] = 'pong';
                    break;
            }

            if ($payloadLength === 126) {
                $mask = substr($data, 4, 4);
                $payloadOffset = 8;
                $dataLength = bindec(sprintf('%08b', ord($data[2])) . sprintf('%08b', ord($data[3]))) + $payloadOffset;
            } elseif ($payloadLength === 127) {
                $mask = substr($data, 10, 4);
                $payloadOffset = 14;
                $tmp = '';
                for ($i = 0; $i < 8; $i++) {
                    $tmp .= sprintf('%08b', ord($data[$i + 2]));
                }
                $dataLength = bindec($tmp) + $payloadOffset;
                unset($tmp);
            } else {
                $mask = substr($data, 2, 4);
                $payloadOffset = 6;
                $dataLength = $payloadLength + $payloadOffset;
            }

            /**
             * We have to check for large frames here. socket_recv cuts at 1024 bytes
             * so if websocket-frame is > 1024 bytes we have to wait until whole
             * data is transferd.
             */
            if (strlen($data) < $dataLength) {
                return false;
            }

            if ($isMasked) {
                for ($i = $payloadOffset; $i < $dataLength; $i++) {
                    $j = $i - $payloadOffset;
                    if (isset($data[$i])) {
                        $unmaskedPayload .= $data[$i] ^ $mask[$j % 4];
                    }
                }
                $decodedData['payload'] = $unmaskedPayload;
            } else {
                $payloadOffset = $payloadOffset - 4;
                $decodedData['payload'] = substr($data, $payloadOffset);
            }
            $decodedData['offset'] = $payloadOffset;

            return $decodedData;
        }
    }