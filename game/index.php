<?php
    
    if (!empty($_REQUEST['name'])) {
        $playerName = $_REQUEST['name'];
    } else {
        $randomName = rand(1,5);
        switch ($randomName) {
            case 1: $playerName = 'Asshole';
                    break;
            case 2: $playerName = 'Douchebag';
                    break;
            case 3: $playerName = 'Nigger';
                    break;
            case 4: $playerName = 'Twat';
                    break;
            case 5: $playerName = 'Bastard';
                    break;
        }
    }
    
    if (!empty($_REQUEST['sound'])) {
        if ($_REQUEST['sound'] === 'on') {
            $sound = 'on';
        } else {
            $sound = 'off';
        }
    } else {
        $sound = 'off';
    }
    
?>

<!DOCTYPE html>
<html>
    <head>
        <title>MyVirus</title>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="css/system.css" type="text/css">
        <script type="text/javascript">
            var playerName = '<?=$playerName?>';
            var sound = '<?=$sound?>';
        </script>
        <script type="text/javascript" src="js/settings.js"></script>
        <script type="text/javascript" src="js/tools.js"></script>
        <script type="text/javascript" src="js/game.js"></script>
        <script type="text/javascript" src="js/main.js"></script>
    </head>
    <body>
        
        <audio id="audioPlayer">
            <source src="/game/resources/background.mp3" type="audio/mpeg">
        </audio>
        
        <div id="connectionProgress">
            <div id="connectionImage"></div>
            <div id="connectionContent">
                - - - Connection to server - - - <br><br>
            </div>
        </div>
        
        <div id="gameWrapper">
            <div id="gameBackground">
                <div class="wall" id="topWall"></div>
                <div class="field" id="fieldBackground"></div>
                <div class="wall" id="bottomWall"></div>
            </div>
            <div id="gameInterface">
                <div class="field" id="gameField">
                    
                    
                    
                </div>
            </div>
        </div>
        
    </body>
</html>