<div class="header">
    <ul class="nav">
        <li><a href="/admin/equipos">Equipos</a></li>
        <li><a href="/admin/partidos">Partidos</a></li>
        <li><a href="/admin/noticias">Noticias</a></li>
        <?php
            if (isset($_SESSION['is_admin']) || $_SESSION['is_admin']) {
            echo '<li><a href="/signout">Signout</a></li>';
            }
        ?>

    </ul>
    <img src="/images/logos/lfp_transparent.png" alt="LogoLPF" />
    <!-- <h3 class="text-muted">Project name</h3> -->
</div>