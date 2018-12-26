<?php
use Sabre\DAV;
use Sabre\DAV\Auth;

// The autoloader
require 'vendor/autoload.php';
require_once '../DATAS/config.inc.php';
require_once 'PDOAgoraProject.php';
require_once 'DirectoryAgoraProject.php';
require_once 'RootDirectoryAgoraProject.php';

$pdo = new \PDO('mysql:dbname='.db_name,db_login,db_password);
$pdo->exec("set names utf8");

// Throwing exceptions when PDO comes across an error:
$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

$authBackend = new PDOAgoraProject($pdo);

// Now we're creating a whole bunch of objects
$rootDirectory = new RootDirectoryAgoraProject(NULL,NULL,NULL,$pdo,$authBackend);

// The server object is responsible for making sense out of the WebDAV protocol
$server = new DAV\Server($rootDirectory);

// If your server is not on your webroot, make sure the following line has the
// correct information
$server->setBaseUri($_SERVER['SCRIPT_NAME']);

// The lock manager is reponsible for making sure users don't overwrite
// each others changes.
$lockBackend = new DAV\Locks\Backend\File('data/locks');
$lockPlugin = new DAV\Locks\Plugin($lockBackend);
$server->addPlugin($lockPlugin);

// Creating the plugin. We're assuming that the realm
// name is called 'SabreDAV'.
$authPlugin = new Auth\Plugin($authBackend,'SabreDAV');

// Adding the plugin to the server
$server->addPlugin($authPlugin);

// All we need to do now, is to fire up the server
$server->exec();

?>
