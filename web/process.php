<?php

require_once "config.php";
include_once "functions.php";
function customhash($str)
{
  return md5($str); // To help change the hashing for password saving if needed.
}

// $query = "select value from admin where variable='mode'";

// $judge = DB::findOneFromQuery($query);
// $query =
//   "insert into logs value ('" .
//   time() .
//   "', '$_SERVER[REMOTE_ADDR]', '" .
//   addslashes(print_r($_SESSION, true)) .
//   "', '" .
//   addslashes(print_r($_REQUEST, true)) .
//   "' )";
// DB::query($query);
$_SESSION["msg"] = "";

writeError("Processing request...");

// ------------------ LOGIN ------------------- //
if (isset($_POST["login"])) {
  if (!isset($_POST["username"]) || $_POST["username"] == "") {
    $_SESSION["msg"] = "Username missing";
    redirectTo(SITE_URL . $_POST["login"] . ".php");
  } elseif (!isset($_POST["password"]) || $_POST["password"] == "") {
    $_SESSION["msg"] = "Pssword missing";
    redirectTo(SITE_URL . $_POST["login"] . ".php");
  } else {
    $_POST["password"] = customhash($_POST["password"]);
    $query = "select * from Users where username  = '$_POST[username]' and pass = '$_POST[password]'";

    $res = DB::findOneFromQuery($query);

    if ($res && ($res["status"] == "Normal" || $res["status"] == "Admin")) {
      $save = $_SESSION;
      session_regenerate_id(true);
      $_SESSION = $save;
      $_SESSION["Users"]["id"] = $res["id"];
      $_SESSION["Users"]["username"] = $res["username"];
      $_SESSION["Users"]["name"] = $res["name"];
      $_SESSION["loggedin"] = "true";
      $_SESSION["Users"]["status"] = $res["status"];
      $_SESSION["Users"]["time"] = time();

      // writeError($_SESSION["Users"]["name"]);

      redirectTo(SITE_URL . "/");
    } elseif ($res) {
      $_SESSION["msg"] = "You can not log in as your current status is : $res[status]";
      redirectTo(SITE_URL . "/login.php");
    } else {
      writeError("Incorrect Username/Password");
      writeError(SITE_URL . $_SESSION["url"]);

      $_SESSION["msg"] = "Incorrect Username/Password";
      redirectTo(SITE_URL . "/login.php");
    }
  }
  // ---------------------- LOG OUT -------------------------- //
} elseif (isset($_GET["logout"])) {
  writeError(
    'Action::\n' . "User " . $_SESSION["Users"]["name"] . " logged out."
  );
  session_destroy();
  redirectTo(SITE_URL . "/");
} elseif (isset($_GET["problems"])) {
  writeError(
    'Action::\n' . "Get Problems"
  );
  $query = "select * from problems where status = 'Active' order by id desc limit 50";
  $res = DB::findAllFromQuery($query);
  $data = array();
  foreach ($res as $row) {
    $data[] = array(

      "name" => $row["name"],
      "type" => $row["type"],
      "pgroup" => $row["pgroup"],
    );
  }
} elseif (isset($_POST["register"])) {
  if (
    isset($_POST["name"]) &&
    $_POST["name"] != "" &&
    (isset($_POST["password"]) && $_POST["password"] != "") &&
    (isset($_POST["repassword"]) && $_POST["repassword"] != "") &&
    (isset($_POST["username"]) && $_POST["username"] != "") &&
    (isset($_POST["email"]) && $_POST["email"] != "") &&
    (isset($_POST["phone"]) && $_POST["phone"] != "")
  ) {
    if (
      preg_match("/^[a-zA-Z0-9_@]+$/", $_POST["username"], $match) &&
      $match[0] == $_POST["username"]
    ) {
      if ($_POST["password"] == $_POST["repassword"]) {
        $query =
          "select * from Users where username='" . $_POST["username"] . "'";
        $res = DB::findOneFromQuery($query);

        writeError(
          'DB Connection::\n' . $_POST["username"] . " + " . $_POST["password"]
        );

        if ($res == null) {
          $query =
            "Insert into Users (name, pass, username, email, phone) 
                        values ('" .
            $_POST["name"] .
            "', '" .
            customhash($_POST["password"]) .
            "', '" .
            $_POST["username"] .
            "', '" .
            $_POST["email"] .
            "','" .
            $_POST["phone"] .
            "')";

          $res = DB::query($query);
          $query =
            "select * from Users where username='" . $_POST["username"] . "'";
          $res = DB::findOneFromQuery($query);
          if ($res) {
            $_SESSION["msg"] = "User successfully registered.";
            redirectTo(SITE_URL . "/");
          } else {
            $_SESSION["reg"] = $_POST;
            $_SESSION["msg"] =
              "Some error occured. Try again. If the problem continues contact admin.";
            redirectTo(SITE_URL . "/register.php");
          }
        } else {
          $_SESSION["reg"] = $_POST;
          $_SESSION["msg"] = "This username is already registered.";
          redirectTo(SITE_URL . "/register.php");
        }
      } else {
        $_SESSION["reg"] = $_POST;
        $_SESSION["msg"] = "Password mismatch.";
        redirectTo(SITE_URL . "/register.php");
      }
    } else {
      $_SESSION["reg"] = $_POST;
      $_SESSION["msg"] =
        "username should contain only alphabets numbers @ and _";
      redirectTo(SITE_URL . "/register.php");
    }
  }
} elseif (isset($_POST["add_problem"])) {
  $query =
    "INSERT INTO problems (" .
    "name , code , score , type , pgroup , contest , timelimit , status , displayio , maxfilesize , statement , input_statement , output_statement , note , input , output , sampleinput , sampleoutput , image" .
    ") values ('" .
    $_POST["name"] .
    "', '" .
    $_POST["code"] .
    "', '" .
    $_POST["score"] .
    "', '" .
    $_POST["type"] .
    "', '" .
    $_POST["pgroup"] .
    "', '" .
    $_POST["contest"] .
    "', '" .
    $_POST["timelimit"] .
    "', '" .
    $_POST["status"] .
    "', '" .
    $_POST["displayio"] .
    "', '" .
    $_POST["maxfilesize"] .
    "', '" .
    $_POST["statement"] .
    "', '" .
    $_POST["input_statement"] .
    "', '" .
    $_POST["output_statement"] .
    "', '" .
    $_POST["note"] .
    "', '" .
    $_POST["sampleinput"] .
    "', '" .
    $_POST["sampleoutput"] .
    "', '" .
    addslashes(file_get_contents($_FILES["input"]["tmp_name"])) .
    "', '" .
    addslashes(file_get_contents($_FILES["output"]["tmp_name"])) .
    "', '" .
    addslashes(file_get_contents($_FILES["image"]["tmp_name"])) .
    "')";
    #addslashes(file_get_contents($_FILES["sampleinput"]["tmp_name"])) .
    #"', '" .
    #addslashes(file_get_contents($_FILES["sampleoutput"]["tmp_name"])) .
    #"')";

  DB::query($query);

  $problemId = DB::findOneFromQuery(
      "SELECT pid FROM problems WHERE code = '" . $_POST["code"] . "'"
    );

  echo "Problem ID: " . $problemId["pid"] . " Code : " . $_POST["code"] . "<br>";

  $problemIdInt = intval($problemId["pid"]);


  $categories = implode(', ', $_POST['category']);
  echo "Selected categories: " . $categories;

  // Insert category IDs into category_problem table
  $categoryIds = $_POST['category'];
  foreach ($categoryIds as $categoryId) {
    $categoryIdInt = intval($categoryId);
    writeError(
      '<br>Category ID: ' . $categoryIdInt . "    PROBLEM ID: " . $problemIdInt . " " . '<br>'
    );

    // echo '<br>Category ID: ' . $categoryIdInt . "    PROBLEM ID: " . $problemId . " " . '<br>';
    $query =
      "INSERT INTO category_problem (category_id, problem_id) VALUES (" .
      $categoryIdInt . ", " .
      $problemIdInt .
      ")";

    DB::query($query);

    // Increment count in category table
    $updateQuery = "UPDATE category SET count = count + 1 WHERE id = " . $categoryIdInt;
    DB::query($updateQuery);
  }

  //$_SESSION["msg"] = "Problem Added.";
  //redirectTo(SITE_URL . "/add_problem.php");
} elseif (isset($_POST['addcontest'])) {
  writeError("addcontest");

  $newcontest = [];

  $newcontest['code'] = $_POST['code'];
  $newcontest['name'] = $_POST['name'];
  $date = new DateTime($_POST['starttime']);
  $newcontest['starttime'] = $date->getTimestamp();
  $date = new DateTime($_POST['endtime']);
  $newcontest['endtime'] = $date->getTimestamp();
  $newcontest['announcement'] = $_POST['announcement'];

  $keys = '';
  $values = '';
  foreach ($newcontest as $key => $value) {
    $keys .= ($keys ? ',' : '') . $key;
    $values .= ($values ? "','" : '') . $value;
  }

  $query = "INSERT INTO contest ($keys) VALUES ('$values')";
  DB::query($query);

  $_SESSION['msg'] = "Contest Added.";

  redirectTo(SITE_URL . "/admin_contests.php");
} else if (isset($_POST['updatecontest'])) {
  $id = $_POST['id'];
  $newcontest['code'] = $_POST['code'];
  $newcontest['name'] = $_POST['name'];
  $date = new DateTime($_POST['starttime']);
  $newcontest['starttime'] = $date->getTimestamp();
  $date = new DateTime($_POST['endtime']);
  $newcontest['endtime'] = $date->getTimestamp();
  $newcontest['announcement'] = $_POST['announcement'];
  foreach ($newcontest as $key => $val) {
    $query = "update contest set $key = '$val' where id=$id";
    DB::query($query);
  }
  $_SESSION['msg'] = "Contest Updated.";
  redirectTo(SITE_URL . $_SESSION['url']);
}
