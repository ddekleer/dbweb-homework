<?php
session_start();
//session_set_cookie_params(7*24*3600, "", "", 1); 

if(isset($_SESSION["usr"])) 
    header("Location: https://siegfried.webhosting.rug.nl/~s2229730/dbweb-homework/personalpage.php");

    // there is still some login session active: redirect to personal page
    //header("Location: https://siegfried.webhosting.rug.nl/~s2229730/dbweb-homework/personalpage.php");
/* Add a large session timeout. Looks quite dangerous in terms of
 * session ID hijacking (if someone steals your session ID somewhere
 * in the meantime, he/she can continue where you left off).
 */

error_reporting(-1);
ini_set("display_errors", 1); /* Debugging: uncomment if needed */

function validInput() // check if user input is not empty
{
    /* This may look a bit silly, but more code could be added if 
     * users have to input more than only a username and password! 
     */
    if(isset($_POST["username"], $_POST["password"]))
    {
        if($_POST["username"] != "" && $_POST["password"] != "")
            return array(1, "");
        return array(0,'<p style="color:red"><b>Please enter a username and a password.</b></p>');
    }
}

function validLogin($db_handle) // check if user has entered valid login data
{ 
    $q_handle = $db_handle->prepare("select name, password from user where name = ?");
    $q_handle->bindParam(1, $_POST["username"]);
    $q_handle->execute();
    $arr = $q_handle->fetch(PDO::FETCH_ASSOC);
    
    require_once("PasswordHash.php"); // PHPass hashing algorithm
    $hasher = new PasswordHash(12, false);
    $correct = $hasher->CheckPassword($_POST["password"], $arr["password"]);
        // compare entered password and hash
    
    if (!empty($arr["name"]) && $correct) // login data valid!
        return 1;
    return 0; 
}

$validarr = validInput();
$validinput = $validarr[0]; // input valid(1) or not(0)?
$error = $validarr[1]; // error message returned by validInput

if ($validinput) 
{
    //// CONNECT TO THE DATABASE
    require_once("db-config.php");
    try
    { 
        $db_handle = new PDO("mysql:host=$host;dbname=$dbname;", $username, $password);
        $db_handle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); /* useful for debugging */
    }
    catch (PDOException $e)
    {
        echo "Connection failed, something's wrong: " . $e->getMessage();
        exit;
    }

    if (validLogin($db_handle)) // fire up a new session and move user to their personal page
    {
        session_regenerate_id();
        $_SESSION["usr"] = $_POST["username"];
        header("Location: https://siegfried.webhosting.rug.nl/~s2229730/dbweb-homework/personalpage.php");
    }
    else
        $error = '<p style="color:red"><b>Username/password incorrect or user does not exist.</b></p>';
} 

?>

<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta charset="UTF-8"/>
<title>M-Choice Registration Page</title>
</head>

<body>
 
<h2>Login</h2>

<form action=login.php method=post>
<p>Please enter your username and password.</p>
<?php echo $error; ?>
<p>Username</p>
<input type="text" name="username" maxlength="15"/>
<br/>
<p>Password</p>
<input type="password" name="password" maxlength="30"/>
<br/><br/>
<input type="submit" name="sub" value="Submit"/>
</form>

</body>
</html>