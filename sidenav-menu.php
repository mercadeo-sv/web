<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">
                <?php if ($nivel_acceso == 'administrador' || $nivel_acceso == 'adquisicion'): ?>
                    <a class="nav-link" href="smartphone.php">
                        <div class="sb-nav-link-icon">
                            <img src="img/catalogo.png" alt="Catalogo" style="width: 24px; height: 24px;">
                        </div>
                        Smartphone
                    </a>
                <?php endif; ?>
                <?php if ($nivel_acceso == 'administrador' || $nivel_acceso == 'consumo' || $nivel_acceso == 'callcenter' || $nivel_acceso == 'administradorjr'): ?>
                    <a class="nav-link" href="consulta_cliente.php">
                        <div class="sb-nav-link-icon">
                            <img src="img/cliente-360.png" alt="Cliente360" style="width: 24px; height: 24px;">
                        </div>
                        Clientes Prepago
                    </a>
                    <a class="nav-link" href="listas_negras.php">
                        <div class="sb-nav-link-icon">
                            <img src="img/bloquear-usuario.png" alt="BlackList" style="width: 24px; height: 24px;">
                        </div>
                        Listas Negras SMS
                    </a>
					<a class="nav-link" href="consulta_reintegros.php">
                        <div class="sb-nav-link-icon">
                            <img src="img/devolucion.png" alt="Reintegros" style="width: 24px; height: 24px;">
                        </div>
                        Reintegros de Saldo
                    </a>
                <?php endif; ?>
                <?php if ($nivel_acceso == 'administrador' || $nivel_acceso == 'consumo'): ?>
                    <a class="nav-link" href="consulta_voucher.php">
                        <div class="sb-nav-link-icon">
                            <img src="img/tarjeta-deb.png" alt="Proyecciones" style="width: 24px; height: 24px;">
                        </div>
                        Tarjetas Raspables
                    </a>
                <?php endif; ?>
                <?php if ($nivel_acceso == 'administrador'): ?>
                    <a class="nav-link" href="forecast.php">
                        <div class="sb-nav-link-icon">
                            <img src="img/pronostico.png" alt="Proyecciones" style="width: 24px; height: 24px;">
                        </div>
                        Proy. Consumo
                    </a>
                <?php endif; ?>
                <?php if ($nivel_acceso == 'administrador' || $nivel_acceso == 'adquisicion'): ?>
                    <a class="nav-link" href="forecast_pp.php">
                        <div class="sb-nav-link-icon">
                            <img src="img/proy_telefono.png" alt="Icono de activaciones" style="width: 24px; height: 24px;">
                        </div>
                        Proy. Activaciones
                    </a>
                <?php endif; ?>
                <?php if ($nivel_acceso == 'administrador'): ?>
                    <a class="nav-link" href="forecast_pr.php">
                        <div class="sb-nav-link-icon">
                            <img src="img/parque.png" alt="Icono de parque" style="width: 24px; height: 24px;">
                        </div>
                        Proy. Parques
                    </a>
                <?php endif; ?>
                <?php if ($nivel_acceso == 'administrador' || $nivel_acceso == 'adquisicion'): ?>
                    <a class="nav-link" href="proy_mes.php">
                        <div class="sb-nav-link-icon">
                            <img src="img/carga_doc.png" alt="Icono mensual" style="width: 24px; height: 24px;">
                        </div>
                        Proy. Mensual
                    </a>
                <?php endif; ?>
                <?php if ($nivel_acceso == 'administrador'): ?>
                    <a class="nav-link" href="reportes.php">
                        <div class="sb-nav-link-icon">
                            <img src="img/base-datos.png" alt="Icono de SQL" style="width: 24px; height: 24px;">
                        </div>
                        Reportes Prepago
                    </a>
                <?php endif; ?>
                <?php if ($nivel_acceso == 'administrador'|| $nivel_acceso == 'adquisicion'|| $nivel_acceso == 'consumo'): ?>
                    <a class="nav-link" href="mesa_trabajo.php">
                        <div class="sb-nav-link-icon">
                            <img src="img/check-list.png" alt="Icono de SQL" style="width: 24px; height: 24px;">
                        </div>
                        Mesa de Trabajo
                    </a>
                <?php endif; ?>
                <?php if ($nivel_acceso == 'administrador' || $nivel_acceso == 'adquisicion'): ?>
                    <a class="nav-link" href="marketshare.php">
                        <div class="sb-nav-link-icon">
                            <img src="img/marketshare.png" alt="Marketshare" style="width: 24px; height: 24px;">
                        </div>
                        Marketshare
                    </a>
                    <a class="nav-link" href="bench_sv.php">
                        <div class="sb-nav-link-icon">
                            <img src="img/bench.png" alt="Marketshare" style="width: 24px; height: 24px;">
                        </div>
                        Bench Prepago
                    </a>
                <?php endif; ?>
                <?php if ($nivel_acceso == 'administrador' || $nivel_acceso == 'planeacion' ): ?>
                    <a class="nav-link" href="existencias.php">
                        <div class="sb-nav-link-icon">
                            <img src="img/inventario.png" alt="Existencias SAP" style="width: 24px; height: 24px;">
                        </div>
                        Existencias SAP
                    </a>
                <?php endif; ?>
                <?php if ($nivel_acceso == 'administrador' || $nivel_acceso == 'publicidad' ): ?>
                    <a class="nav-link" href="extractor_bases.php">
                        <div class="sb-nav-link-icon">
                            <img src="img/descarga-base.png" alt="Descarga" style="width: 24px; height: 24px;">
                        </div>
                        Bases de Publicidad
                    </a>
                <?php endif; ?>
                <?php if ($nivel_acceso == 'administrador'): ?>
                    <a class="nav-link" href="clientes_cav.php">
                        <div class="sb-nav-link-icon">
                            <img src="img/alto-valor.png" alt="Carga CAV" style="width: 24px; height: 24px;">
                        </div>
                        Cargas CAV
                    </a>
                <?php endif; ?>
                <?php if ($nivel_acceso == 'administrador'|| $nivel_acceso == 'administradorjr' ): ?>
                    <a class="nav-link" href="maintenance.php">
                        <div class="sb-nav-link-icon">
                            <img src="img/mantenimiento-pc.png" alt="Mantenimiento" style="width: 24px; height: 24px;">
                        </div>
                        Mantenimiento
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="sb-sidenav-footer">
            <div class="small">Logged IP:</div>
            <?php include 'F_IP.php'; ?>
        </div>
    </nav>
</div>
