<?php

    require_once __DIR__ . '/../config.php'; 
    require_once BASE_PATH . '/phps/php_functions.php';

/*
 * Ensure the request is a POST request.
 * Prevents direct access to this script via URL.
 */
error_log("DEBUG: REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("DEBUG: Not POST, redirecting...");
    redirect('/signin');
    exit;
}

/*
 * Ensure required POST fields exist before accessing them.
 * Prevents undefined index and deprecated warnings.
 */
if (!isset($_POST['username'], $_POST['password'])) {
    redirect('/signin');
    exit;
}

/*
 * CSRF protection:
 * request is valid only if client cookie token matches server session token
 */
if (
    !isset($_COOKIE['token']) ||
    !isset($_SESSION['token']) ||
    $_COOKIE['token'] !== $_SESSION['token']
) {
    // bad request (expired or invalid form token)
    resetFormToken();
    redirect('/signin');
    exit;
}

// request from server - ok

require_once BASE_PATH . '/config/db/_database.php';

$username = strtolower($_POST['username']);
$password = $_POST['password'];
$password_hash = md5($password);

error_log("DEBUG: POST received: " . print_r($_POST, true));
error_log("DEBUG: SESSION: " . print_r($_SESSION, true));

/*
 * Fallback validation on server side (bulletproof):
 * complements the client-side validation
 */
if (
    isEmpty($username) ||
    isEmpty($password) ||
    isSafeAlphaNum1($username) ||
    isSafeAlphaNum2($password)
) {
    array_push($_SESSION['flash'], "<span class='error-color'>Inicio de sesión sin éxito.</span>");
    redirect('/signin');
    exit;
}

$sql = "SELECT * FROM usuarios WHERE nombre_usuario = ?";

/*
 * Prepared statement:
 *  - string is escaped (quoted) implicitly; real_escape_string() not required
 *  - prevents 1st order injection (injection from external source; e.g. user input)
 */
$stmt = $conn->prepare($sql);

if ($stmt) {

    // bind parameters for markers
    $stmt->bind_param("s", $el_nombre);

    // set parameters and execute
    $el_nombre = $username;
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $nombre, $hash, $admin);

    if ($stmt->num_rows > 0) {
        // username exists already: check if password is correct

        $stmt->fetch();

        if ($username === $nombre && $password_hash === $hash) {

            // signin successful

            $_SESSION['user_id']   = $id;
            $_SESSION['username']  = $nombre;
            $_SESSION['signed_in'] = true;
            $_SESSION['is_admin']  = $admin;

            array_push($_SESSION['flash'], "Inicio de sesión con éxito.");

            // free result
            $stmt->free_result();

            // close statement
            $stmt->close();

            // close connection
            $conn->close();

            /*
             * Redirect user to originally requested URL (if any),
             * otherwise redirect to homepage
             */
            if (isset($_SESSION['request_url'])) {
                redirect($_SESSION['request_url']);
            } else {
                redirect('/index.php');
            }

        } else {

            // signin unsuccessful: password incorrect

            array_push(
                $_SESSION['flash'],
                "<span class='error-color'>Inicio de sesión sin éxito. ID del usuario y/o contraseña no válido.</span>"
            );

            // free result
            $stmt->free_result();

            // close statement
            $stmt->close();

            // close connection
            $conn->close();

            redirect('/signin');
        }

    } else {
        // username does not exist

        array_push(
            $_SESSION['flash'],
            "<span class='error-color'>Inicio de sesión sin éxito. ID del usuario y/o contraseña no válido.</span>"
        );

        // close statement
        $stmt->close();

        // close connection
        $conn->close();

        redirect('/signin');
    }

} else {
    // select statement not valid

    array_push(
        $_SESSION['flash'],
        "<span class='error-color'>Inicio de sesión sin éxito. ID del usuario y/o contraseña no válido.</span>"
    );

    // close connection
    $conn->close();

    redirect('/signin');
}

?>
