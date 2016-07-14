
// Settings:

var SERVER = 'server.php';

var STATUS = 'status.php';

var WS_SERVER = 'ws://127.0.0.1:8887';

// Global variables:

var socket;
var connected = false;
var currentDrag = 0;
var gameFieldWidth;
var serverReady = true;
var buffer = [];
var players = [];
var myID;
var enemies = [];