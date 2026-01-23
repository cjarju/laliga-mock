<?php

require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . '/phps/php_functions.php';
require_once BASE_PATH . '/admin/_restrict_subdir.php';

/**
 * Defensive checks
 * (avoid undefined variable notices in PHP 8+)
 */
if (
    !isset($goles_equipo_local, $goles_equipo_away, $equipo_local_id, $equipo_away_id)
) {
    return;
}

if ($goles_equipo_local === '' || $goles_equipo_away === '') {
    return;
}

// Update classification
$equipos_ids = [$equipo_local_id, $equipo_away_id];

foreach ($equipos_ids as $equipo_id) {

    $equipo_id = (int) $equipo_id; // hard cast for safety

    $sql_get = <<<SQL
SELECT 
    pg * 3 + pe AS pt,
    pj, gf, gc, pg, pp, pe
FROM
(
    SELECT 0 id, COUNT(*) pj
    FROM partidos
    WHERE (equipo_local_id = $equipo_id OR equipo_away_id = $equipo_id)
      AND goles_equipo_local IS NOT NULL
) jugados
INNER JOIN
(
    SELECT 0 id, SUM(gf_home) gf, SUM(gc_home) gc FROM
    (
        SELECT SUM(goles_equipo_local) gf_home, SUM(goles_equipo_away) gc_home
        FROM partidos
        WHERE equipo_local_id = $equipo_id
          AND goles_equipo_local IS NOT NULL
        UNION ALL
        SELECT SUM(goles_equipo_away), SUM(goles_equipo_local)
        FROM partidos
        WHERE equipo_away_id = $equipo_id
          AND goles_equipo_away IS NOT NULL
    ) go
) goles ON jugados.id = goles.id
INNER JOIN
(
    SELECT 0 id, SUM(pg_home) pg FROM
    (
        SELECT COUNT(*) pg_home
        FROM partidos
        WHERE equipo_local_id = $equipo_id
          AND goles_equipo_local > goles_equipo_away
        UNION ALL
        SELECT COUNT(*)
        FROM partidos
        WHERE equipo_away_id = $equipo_id
          AND goles_equipo_away > goles_equipo_local
    ) ga
) ganados ON jugados.id = ganados.id
INNER JOIN
(
    SELECT 0 id, SUM(pp_home) pp FROM
    (
        SELECT COUNT(*) pp_home
        FROM partidos
        WHERE equipo_local_id = $equipo_id
          AND goles_equipo_away > goles_equipo_local
        UNION ALL
        SELECT COUNT(*)
        FROM partidos
        WHERE equipo_away_id = $equipo_id
          AND goles_equipo_local > goles_equipo_away
    ) pe
) perdidos ON jugados.id = perdidos.id
INNER JOIN
(
    SELECT 0 id, COUNT(*) pe
    FROM partidos
    WHERE (equipo_local_id = $equipo_id OR equipo_away_id = $equipo_id)
      AND goles_equipo_local = goles_equipo_away
) empatados ON jugados.id = empatados.id
SQL;

    $result = $conn->query($sql_get);

    if (!$result) {
        error_log("Classification query failed for equipo_id={$equipo_id}: " . $conn->error);
        continue;
    }

    $row = $result->fetch_assoc();
    $result->free();

    if (!$row) {
        continue;
    }

    $pt = (int) $row['pt'];
    $pj = (int) $row['pj'];
    $pg = (int) $row['pg'];
    $pe = (int) $row['pe'];
    $pp = (int) $row['pp'];
    $gf = (int) $row['gf'];
    $gc = (int) $row['gc'];

    $sql_update = "
        UPDATE clasificacion
        SET
            puntos = $pt,
            jugados = $pj,
            ganados = $pg,
            empatados = $pe,
            perdidos = $pp,
            goles_a_favor = $gf,
            goles_en_contra = $gc
        WHERE equipo_id = $equipo_id
    ";

    if (!$conn->query($sql_update)) {
        error_log("Classification update failed for equipo_id={$equipo_id}: " . $conn->error);
    }
}
