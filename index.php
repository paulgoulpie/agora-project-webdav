<?php

use Sabre\DAV;
use Sabre\DAV\Auth;

// The autoloader
require 'vendor/autoload.php';
require_once '../stock_fichiers/config.inc.php';
require_once 'PDOAgoraProject.php';
require_once 'DirectoryAgoraProject.php';

$pdo = new \PDO('mysql:dbname='.db_name,db_login,db_password);
$pdo->exec("set names utf8");

// Throwing exceptions when PDO comes across an error:
$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

$authBackend = new PDOAgoraProject($pdo);

// Now we're creating a whole bunch of objects
//$rootDirectory = new DAV\FS\Directory('public');
$rootDirectory = new DirectoryAgoraProject(1,'',0,$pdo,$authBackend,NULL);

// The server object is responsible for making sense out of the WebDAV protocol
$server = new DAV\Server($rootDirectory);

// If your server is not on your webroot, make sure the following line has the
// correct information
//$server->setBaseUri('/url/to/server.php');
//$server->setBaseUri('/www.harmonievenerque.goulpie.com.03/webdav/index.php');
//$server->setBaseUri('/www.harmonievenerque.goulpie.com.03/webdav/index.php');

$server->setBaseUri($_SERVER['SCRIPT_NAME']);

// The lock manager is reponsible for making sure users don't overwrite
// each others changes.
$lockBackend = new DAV\Locks\Backend\File('data/locks');
$lockPlugin = new DAV\Locks\Plugin($lockBackend);
$server->addPlugin($lockPlugin);

// This ensures that we get a pretty index in the browser, but it is
// optional.
//$server->addPlugin(new DAV\Browser\Plugin());

// Creating the backend
//$authBackend = new Auth\Backend\PDO($pdo,'gt_utilisateur');
// $authBackend = new PDOAgoraProject($pdo);

// Creating the plugin. We're assuming that the realm
// name is called 'SabreDAV'.
$authPlugin = new Auth\Plugin($authBackend,'SabreDAV');

// Adding the plugin to the server
$server->addPlugin($authPlugin);

// init_test($pdo);

// All we need to do now, is to fire up the server
$server->exec();

?>
