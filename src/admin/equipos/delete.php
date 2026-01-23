<?php

require_once __DIR__ . '/../../config.php'; 
require_once BASE_PATH . '/phps/php_functions.php';
require_once BASE_PATH . '/admin/_restrict_subdir.php';
require_once BASE_PATH . '/config/db/_database.php';

$id = $_GET['id'];

$sql = "SELECT ruta_logo FROM equipos  WHERE id = ?";

$stmt = $conn->prepare($sql);

if ($stmt) {

    // bind parameters for markers
    $stmt->bind_param("i", $el_id);
    // set parameters and execute

    // delete
    $el_id = $id;
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($ruta_foto);
    $stmt->fetch();
    $filename = pathinfo($ruta_foto, PATHINFO_FILENAME);
    $filename_pattern = BASE_PATH . "/images/logos/" . $filename . "*";

}


$sql = "DELETE FROM equipos  WHERE id = ?";

$stmt = $conn->prepare($sql);

if ($stmt) {


    // bind parameters for markers
    $stmt->bind_param("i", $el_id);
    // set parameters and execute

    // delete
    $el_id = $id;
    $stmt->execute();

    array_push($_SESSION['flash'], "Equipo borrado.");

    if (!empty($filename_pattern)) {
        array_map('unlink', glob($filename_pattern));
    }

}
else {
    array_push($_SESSION['flash'], "Equipo no borrado.");
    $stmt->error;
}

// close statement
$stmt->close();

// close connection
$conn->close();

redirect("/admin/equipos");

?>

