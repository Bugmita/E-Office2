<?php
require_once 'db.php';


// store signature
function storeSignature(){}


// to store action at each level
function logAction($appId, $from, $to, $action, $authId, $comment =null){
    $pdo= getPDO();
    $data= $pdo->prepare("INSERT INTO (appId, from, to, action, authId, comment) VALUES (?, ?, ?, ?, ?, ?)");
    $data->execute([$appId, $from, $to, $action, $authId, $comment]);
    return $pdo->lastInsertId();
}


//create application
function createApplication(){}


//submit application
function submitApplication(){}


//fetch application
function fetchApplication(){}


// ask for consultations
function createConsult(){}


//forward application
function forwardApplication(){}


//backward application
function backwardApplication(){}


//reject application
function rejectApplication(){}


?>