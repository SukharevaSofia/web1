<?php
    $time_enter = hrtime(true);

    //saving users' sessions
    if (isset($_COOKIE["session"]))
    {
        define("session", $_COOKIE["session"]);
    }
    else
    {
        define("session", bin2hex(random_bytes(1024)));
        setcookie("session", session, array('samesite' => 'Strict'));
    }
    
    function operableInput($x, $y,  $R, $restore)
    {
        if ($x && $y && $R && (!$restore))
        {
            return true;
        }
        if ((!$x) && (!$y) && (!$R) && $restore)
        {
            return true;
        }
        return false;
    }

    if (!operableInput(isset($_GET['x']), isset($_GET['y']), isset($_GET['R']), isset($_GET['restore'])))
    {
        exit("Неправильный ввод :(");
    }
    
    $restore = isset($_GET['restore']);

    $db = new SQLite3("/tmp/sukhareva.db");
    $db->exec(
    "CREATE TABLE IF NOT EXISTS"
        . " web1table"
        . "("
        . "cookieID STRING"
        . ", resultJson STRING"
        . ")"
    );


    function checkInput(string $number)
    {
    if (!is_numeric($number))
    {
        exit(json_encode(["valid" => false]));
    }
    if (!ctype_digit($number))
    {
        if ((float)$number !== (float)(round($number*1000) / 1000))
        {
            exit(json_encode(["valid" => false]));
        }
    }
    return (float)$number;
    }

    
    if ($restore == false)
    {
        $x = checkInput($_GET['x']);
        $y = checkInput($_GET['y']);
        $R = checkInput($_GET['R']);
            if (!(($y < 5 && $y >-3)
            && ($x == 4 || $x == -4 || $x == -3
            || $x == -2 || $x == -1 || $x == 0
            || $x == 1 || $x == 2 || $x == 3)
            && ($R == 1 || $R == 1.5 || $R == 2 || $R == 2.5
            || $R == 3)))
        {
                $server_answer = json_encode(array("valid" => "false"));
                echo $server_answer;
        }
        else{
            if ((abs($x) <= $R && abs($y)<= $R) && (($x >= 0 && $y <=0)
                || ($x <= 0 && $y >= 0 && $x * $x + $y * $y <= ($R)*($R))
                || ($x <= 0 && $y <= 0 && $x + $y >= -$R))){
                    $res = true;
            }
            else $res = false;

            
            $working_time = (int)((hrtime(true) - $time_enter) / 1000);

            $stmtStore = $db->prepare(
                "INSERT INTO"
                  . " web1table(cookieID, resultJson)"
                  . " VALUES"
                  . " (:cookieID, :resultJson)"
              );
              $stmtStore->bindValue("cookieID", session, SQLITE3_TEXT);
              $stmtStore->bindValue("resultJson", json_encode(array("x" => $x, "y" => $y,"R" => $R,"res" => $res,
              "working_time" => $working_time, "valid" => "true"), SQLITE3_TEXT));
              $stmtStore->execute()->finalize();

            $server_answer = json_encode(array("x" => $x, "y" => $y,"R" => $R,"res" => $res,
             "working_time" => $working_time, "valid" => "true"));

            echo $server_answer;
        }
    }
    else
    {
        $stmtGet = $db->prepare(
        "SELECT resultJson FROM web1table WHERE cookieID=:id"
        );
        $stmtGet->bindValue(":id", session, SQLITE3_TEXT);
        $result = $stmtGet->execute();
        $response = [];
        while ($row = $result->fetchArray())
        {
        array_push($response, $row['resultJson']);
        }
        echo json_encode($response);
    }
          
?>