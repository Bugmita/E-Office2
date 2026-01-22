<?php



require_once 'db.php';





// to store action at each level
function logAction($appId, $from, $to, $action, $authId, $comment =null){
    $pdo= getPDO();
    $data= $pdo->prepare("INSERT INTO applicationLogs(appId, from, to, action, authId, comment) VALUES (?, ?, ?, ?, ?, ?)");
    $data->execute([$appId, $from, $to, $action, $authId, $comment]);
    return $pdo->lastInsertId();
}


//create application
function createApplication($userId, $type, $data){
    $pdo = getPDO();
    $data= $pdo->prepare("INSERT INTO applications(userId, type, data, status, currentLevel) VALUES (?, ?, ?, 'draft', 1");
    $data->execute([$userId, $type,json_encode($data)]);
    $appId =$pdo->lastInsertId();

    logAction($appId, null, 1, 'submit', $userId, 'created');
    return $appId;
}


//submit application
function submitApplication($appId){
    $pdo = getPDO();
    $data = $pdo->prepare("UPDATE applications SET status ='submitted', currentLevel =2 WHERE id =? AND status ='draft'");
    $data->execute([$appId]);
    return $data->rowCount()>0;
}


//get all applications 
function getApplications(){
    $pdo = getPDO();
    $data = $pdo->prepare("SELECT id, currentLevel, status, createdAt FROM applications ORDER BY createdAt DESC");
    $data->execute();
    return $data->fetchAll();
}


//fetch application
function fetchApplication($appId){
    $pdo = getPDO();
    $data =$pdo->prepare("SELECT * FROM applications WHERE id =?");
    $data->execute([$appId]);
    $app = $data->fetch();
    if($app and $app['data']){
        $app['data'] =json_decode($app['data'], true);
    }
    return $app;
}


// store signature done at each level
function storeSignature($appId, $logId, $actId, $level, $mode, $signatureData, $metaData = null){
    $pdo = getPDO();
    $metaJson = $metaData? json_encode($metaData) : null;
    $data = $pdo->prepare("INSERT INTO applicationSigantures(appId, logId, actId, level, mode, signautreData, metaData) VALUES(?, ?, ?, ?, ?, ?, ?");
    $data->execute([$appId, $logId, $actId, $level, $mode, $signatureData, $metaJson]);

    return $pdo->lastInsertId();
}


//verify if signature uploaded correctly
function verifySignature($signatureData){
    if (empty($signatureData)) return false;
    if(strpos($signatureData, 'data:image/') === 0) return true;
    if(base64_decode($signatureData, true) !== false) return true;

    return false;
}

// add signature 
function addSignature($userId, $signatureData){
    $pdo = getPDO();
    $data = $pdo->prepare("UPDATE users SET signatureData =? WHERE id=?");
    $data->execute([$signatureData, $userId]);

    return true;

}


// ask for consultations
function generateConsult(){}


//respond to consultation 
function respondToConsult(){}

//check pending consultations
function checkPendingConsult(){}


//forward application
function forwardApplication($appId, $actId, $fromLevel, $toLevel, $comment=null, $signature){
    $pdo = getPDO();
    try{
        $pdo->beginTransaction();
        $data= $pdo->prepare("SELECT currentLevel, status FROM applications WHERE id=? FOR UPDATE");
        $data->execute([$appId]);
        $app= $data->fetch();
        if(!$app) throw new Exception("Application not found");
        //check pending consultation
        //signature
        $data = $pdo->prepare("UPDATE applications SET currentLevel=?, status ='inReview' WHERE id=?");
        $data->execute([$toLevel, $appId]);
        $logId = logAction($appId, $fromLevel, $toLevel, 'forwarded', $actId, $comment);
        //add signature
        $pdo->commit();
        return true;
    } catch(Exception $e){
        $pdo->rollBack();
        throw $e;
    }
}


//backward application
function backwardApplication($appId, $actId, $fromLevel, $toLevel, $comment=null, $signature){
    $pdo = getPDO();
    try{
        $pdo->beginTransaction();
        $data= $pdo->prepare("SELECT currentLevel, status FROM applications WHERE id=? FOR UPDATE");
        $data->execute([$appId]);
        $app =$data->fetch();
        if(!$app){
            throw new Exception("Application not found");
        }
        //check for consultation 
        //check for signature
        $data =$pdo->prepare("UPDATE application SET currentLevel =?, status ='inReview' WHERE id=?");
        $data->execute([$toLevel, $appId]);
        $logId = logAction($app, $fromLevel, $toLevel, 'backward', $actId, $comment);
        //log signature data in application
        $pdo->commit();
        return true;
    } catch(Exception $e){
        $pdo->rollBack();
        throw $e;
    }

}


//reject application
function rejectApplication($appId, $actId, $from, $reason, $signature){
    $pdo = getPDO();
    try{
        $pdo->beginTransaction();
        $data = $pdo->prepare("SELECT currentLevel, status FROM applications WHERE id=? FOR UPDATE");
        $data->execute([$appId]);
        $app = $data->fetch();
        if(!$app){
            throw new Exception("Application not found");
        }
        //check if requires signature
        //check for pending consultations
        $data = $pdo->prepare("UPDATE applications SET status='rejected' WHERE id=?");
        $data->execute([$appId]);
        $logId = logAction($appId, $from, null, 'rejected', $actId, $reason);
        // add signature 
        $pdo->commit();
        return true;
    } catch(Exception $e){
        $pdo->rollBack();
        throw $e;
    }
}


?>