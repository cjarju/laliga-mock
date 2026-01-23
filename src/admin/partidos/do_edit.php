<?php
require_once __DIR__ . '/../../config.php'; 
require_once BASE_PATH . '/phps/php_functions.php';
require_once BASE_PATH . '/admin/_restrict_subdir.php';

// Start output buffering so headers can still be sent
ob_start();

// Debug flag: set to true to see debug output instead of redirect
$debug = false;

if (isset($_COOKIE['token'], $_SESSION['token']) && $_COOKIE['token'] === $_SESSION['token']) {
    // Valid form token
    require_once BASE_PATH . '/config/db/_database.php';

    $id = trim($_POST['rec_id'] ?? '');
    $equipo_local_id = trim($_POST['equipo_local'] ?? '');
    $equipo_away_id = trim($_POST['equipo_away'] ?? '');
    $goles_equipo_local = trim($_POST['goles_equipo_local'] ?? '');
    $goles_equipo_away = trim($_POST['goles_equipo_away'] ?? '');
    $fecha_partido = trim($_POST['fecha_partido'] ?? '');

    if ($goles_equipo_local === '' || $goles_equipo_away === '') {
        $goles_equipo_local = null;
        $goles_equipo_away = null;
    }

    // Prepare SQL
    $sql = "UPDATE partidos SET equipo_local_id = ?, equipo_away_id = ?, goles_equipo_local = ?, goles_equipo_away = ?, fecha_partido = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param(
            "iiiisi",
            $el_local_id,
            $el_away_id,
            $goles_eq_local,
            $goles_eq_away,
            $la_fecha,
            $el_id
        );

        $el_local_id = $equipo_local_id;
        $el_away_id = $equipo_away_id;
        $goles_eq_local = $goles_equipo_local;
        $goles_eq_away = $goles_equipo_away;
        $la_fecha = $fecha_partido;
        $el_id = $id;

        $stmt->execute();

        if (!$stmt->error) {
            array_push($_SESSION['flash'], "Partido editado.");
        } else {
            array_push($_SESSION['flash'], "Partido no editado. Error: " . $stmt->error);
        }

        $stmt->close();
        include '_update_clasificacion.php';
        $conn->close();

        if ($debug) {
            echo "<pre>POST Data:\n";
            print_r($_POST);
            echo "\nSession Flash:\n";
            print_r($_SESSION['flash']);
            echo "</pre>";
        } else {
            redirect("/admin/partidos");
        }

    } else {
        array_push($_SESSION['flash'], "Partido no editado. Statement prepare failed: " . $conn->error);
        $conn->close();

        if ($debug) {
            echo "<pre>POST Data:\n";
            print_r($_POST);
            echo "</pre>";
        } else {
            redirect("edit?id=$id");
        }
    }

} else {
    // Bad request or expired form token
    resetFormToken();

    if ($debug) {
        echo "<pre>Invalid token or session expired.</pre>";
    } else {
        redirect('/signin');
    }
}

// Flush output buffer (send content if any)
ob_end_flush();
?>
