<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>MyVirus</title>


    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/app.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">

</head>
<body background="img/bloodstream.jpg">
<div class="container">
    <div class="row margin-top">
        <form class="login-form center-block" action="game/index.php" method="POST">
            <div class="form-group">
                <label for="exampleInputName" style="color: #5cb85c">Your name</label>
                <input type="Name" name="name" class="form-control" id="exampleInputName" placeholder="Name">
            </div>
            <button id="playButton" type="submit" class="btn btn-default login-btn">Play</button>
            <div id="soundContainer">
                <label class="checkbox-inline" style="color: #5cb85c"><input type="checkbox" name="sound" value="on">Sound</label>
            </div>
        </form>
    </div>

</div>



<script src="js/jquery-2.2.1.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/app.js"></script>
</body>
</html>