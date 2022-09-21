<?php
//classes are used to enforce types and immutability
//to jump to execution part jump to 'comment_exec_begins_here'
declare(strict_types=1); //disables type coercion
date_default_timezone_set('UTC'); //using UTC time
error_reporting(E_ALL); //better to fail than to silently introduce bugs

//turn all warnings into errors
//better to fail than to silently introduce bugs
function exception_error_handler($severity, $message, $file, $line)
{
  throw new ErrorException($message, 0, $severity, $file, $line);
  //panick("unexpected error");
}
set_error_handler("exception_error_handler");

//according to the PHP documentation hrtime is faster than microtime
//especially so for the virtualized workloads
//seems to be doing a syscall?
define("script_entrance_time", hrtime(true));

//max total cookie size is 4096
//WARNING: (4096 per domain, not per one cookie)
//but that number does not include possible overheads
//  hence is not practical to use as browsers might reject that
//there does not seem to be an easy answer to what is a safe
//  cookie size to choose. would choose the 4000 as that has a good margin
//  but curl fails with a cookie_session_length of length 4000
//  perhaps an encoding with 2bytes per char is used?
//  hence 2000
//the number is going to be divided by 2 in the code
//  for use with bin2hex(random_bytes()) hence the asserts
define("cookie_session_length", 2000);
assert(gettype(cookie_session_length) === "integer");
assert(cookie_session_length > 0);
assert(cookie_session_length <= 2000);
assert((cookie_session_length & 2) === 0);


//init this to have session
//must be done before having any output due to the way cookies work
class SessionManager
{
  private string $cookie;

  function __construct()
  {
    if (isset($_COOKIE["session"])){
      if (strlen($_COOKIE["session"]) !== cookie_session_length)
      {
        $this->resetCookie();
      }
      else
      {
        $this->cookie = $_COOKIE["session"];
      }
    }
    else
    {
      $this->resetCookie();
    }
  }

  //exposed to implement session reset
  function resetCookie(): void
  {
    //using bin2hex increases the string size two times
    //each byte can be represented with 2 hex numbers
    //hence 1 byte -> 2 hexs
    $this->cookie = bin2hex(random_bytes(cookie_session_length / 2));
    assert(strlen($this->cookie) == cookie_session_length);
    setcookie("session", $this->cookie, array('samesite' => 'Strict'));
    $this->valid = true;
  }

  function getCookie(): string
  {
    return $this->cookie;
  }
}

class LocalDB
{
  private SQLite3 $db;

  function __construct(string $databasePath)
  {
    $this->db = new SQLite3($databasePath);
    $this->db->exec(
      "CREATE TABLE IF NOT EXISTS"
        . " web1table"
        . "("
        . "cookieID STRING"
        . ", resultJson STRING"
        . ")"
    );
  }

  function storeResult(string $cookieID, ResultHolder $result): void
  {
    $stmtStore = $this->db->prepare(
      "INSERT INTO"
        . " web1table(cookieID, resultJson)"
        . " VALUES"
        . " (:cookieID, :resultJson)"
    );
    $stmtStore->bindValue("cookieID", $cookieID, SQLITE3_TEXT);
    $stmtStore->bindValue("resultJson", $result->toJson(), SQLITE3_TEXT);
    $stmtStore->execute()->finalize();
  }

  function getResults(string $cookieID): SQLite3Result
  {
    $stmtGet = $this->db->prepare(
      "SELECT resultJson FROM web1table WHERE cookieID=:id"
    );
    $stmtGet->bindValue(":id", $cookieID, SQLITE3_TEXT);
    return $stmtGet->execute();
  }
}

//using this class to ensure uniform json output
class ResultHolder implements JsonSerializable
{
  private float $x;
  private float $y;
  private float $R;
  private bool $match;
  private bool $request_success;
  private string $error_message;
  
  function __construct(
    float $x, float $y, float $R,
    bool $match,
    bool $request_success,
    string $error_message)
  {
    $this->x = $x;
    $this->y = $y;
    $this->R = $R;
    $this->match = $match;
    $this->request_success = $request_success;
    $this->error_message = $error_message;
  }

  //needed to make json_encode work without declaring fields as public
  //without it would return empty json
  function jsonSerialize(): mixed
  {
    return [
      "x" => $this->x,
      "y" => $this->y,
      "R" => $this->R,
      "match" => $this->match,
      "request_success" => $this->request_success,
      "server_time" => date('e H:i:s', time()),
      "script_exec_time" => intval((hrtime(true) - script_entrance_time) / 1000000),
      "error_message" => $this->error_message,
    ];
  }

  function toJson(): string
  {
    return json_encode($this, JSON_THROW_ON_ERROR);
  }
}

class Values {
  private float $x;
  private float $y;
  private float $R;
  
  function __construct(float $x, float $y, float $R)
  {
    $this->x = $x;
    $this->y = $y;
    $this->R = $R;
  }

  function x(): float
  {
    return $this->x;
  }
  function y(): float
  {
    return $this->y;
  }
  function R(): float
  {
    return $this->R;
  }
}

//exiting function
function returnResult(ResultHolder $result): void
{
  exit($result->toJson());
}
//returnResult wrapper for ease of panicking
function panick(string $error_message): void
{
  $resultHolder = new ResultHolder(
    x: 0,
    y: 0,
    R: 0,
    match: false,
    request_success: false,
    error_message: $error_message
  );
  returnResult($resultHolder);
}

function truncate(float $number): float
{
  $multiplier = 1000; // number of zeros is the precision
  if ($number < 0)
  {
      return ceil(($number * $multiplier)) / $multiplier;
  }
  else
  {
      return floor(($number * $multiplier)) / $multiplier;
  }
}
function parseInput(string $number) : float
{
  if (!is_numeric($number))
  {
    panick("x, y and R are all required to be numbers");
  }
  if (!ctype_digit($number))
  {
    if ((float) $number !== truncate((float) $number))
    {
      panick("no more than 3 digits after decimal point are allowed");
    }
  }
  return (float) $number;
}

function verifyX(float $x_variable) : bool
{
  return $x_variable <= 3 && $x_variable >= -3;
}
function verifyY(float $y_variable) : bool
{
  return $y_variable <= 3 && $y_variable >= -5;
}
function verifyR(float $r_variable) : bool
{
  return
    $r_variable == 1 ||
    $r_variable == 1.5 ||
    $r_variable == 2 ||
    $r_variable == 2.5 ||
    $r_variable == 3;
}
function matchCheck(float $x, float $y, float $R) : bool
{
  return
    (($x <= 0 && $y >= 0
      && $x <= ($R / 2)
      && $y <= ($R / 2))
    ||
      ($x >= 0 && $y <= 0
        && ($x * $x + $y * $y) <= ($R * $R))
    ||
      ($x <= 0 && $y <= 0
        && ((-$x) <= ($R + $y))
        && ((-$y) <= ($R + $x))));
}
//verifies that the existance of variables is agreeble
function operableInput(bool $x, bool $y, bool $R, bool $restore) : bool
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

//comment_exec_begins_here
$session = new SessionManager();
$db = new LocalDB("/tmp/web1.sqlite");

if (!operableInput(
      isset($_GET['x']),
      isset($_GET['y']),
      isset($_GET['R']),
      isset($_GET['restore'])))
{
  panick("either full set of x, y, R variables or restore parameter is needed");
}

if (isset($_GET['restore']) === true)
{
  $result = $db->getResults($session->getCookie());
  while ($row = $result->fetchArray())
  {
    echo json_encode($row);
  }
}
else {
  $values = new Values(
    parseInput($_GET['x']),
    parseInput($_GET['y']),
    parseInput($_GET['R']));

  if (!
   (verifyX($values->x()) &&
    verifyY($values->y()) &&
    verifyR($values->r())))
  {
    panick("input data does not fit in the boundaries");
  }
  else {
    $result = new ResultHolder(
      x: $values->x(),
      y: $values->y(),
      R: $values->R(),
      match: matchCheck($values->x(), $values->y(), $values->R()),
      request_success: true,
      error_message: ""
    );
    $db->storeResult($session->getCookie(), $result);
    echo $result->toJson();
  }
}