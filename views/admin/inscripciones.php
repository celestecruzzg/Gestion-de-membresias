<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../index.html");
    exit();
}

$nombre = $_SESSION['admin_nombre'];
$ap_paterno = $_SESSION['admin_ap_paterno'];
$nombre_completo = $nombre . " " . $ap_paterno;

// Conectar a la base de datos
require_once '../../connection/conn.php';

// Recuperar inscripciones
$sql = "SELECT i.id_inscripcion, i.id_cliente, CONCAT(c.nombre, ' ', c.ap_paterno, ' ', c.ap_materno) AS nombre_completo, 
        tm.tipo_membresia, tm.id_tipo_membresia, i.fecha_inicio, i.fecha_fin, e.estatus AS estatus, i.pago,
        TIMESTAMPDIFF(MONTH, i.fecha_inicio, i.fecha_fin) AS num_meses
        FROM tb_inscripciones i 
        JOIN tb_clientes c ON i.id_cliente = c.id_cliente 
        JOIN tb_tipo_membresias tm ON i.id_tipo_membresia = tm.id_tipo_membresia
        JOIN tb_estatus e ON i.id_estatus = e.id_estatus";

$result = $conn->query($sql);
$inscripciones = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $inscripciones[] = $row;
    }
}

// Recuperar tipos de membresías
$sql_membresias = "SELECT * FROM tb_tipo_membresias";
$result_membresias = $conn->query($sql_membresias);
$membresias = [];

if ($result_membresias->num_rows > 0) {
    while ($row = $result_membresias->fetch_assoc()) {
        $membresias[] = $row;
    }
}

$conn->close();
?>

<!doctype html>
<html lang="es">
<head>
    <title>Inscripciones | Syntax Strong</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" />
    <link rel="stylesheet" href="../../assets/css/admin_css/dashboard.css">
    <link rel="stylesheet" href="../../assets/css/admin_css/usuarios.css">
    <link rel="shortcut icon" href="../../assets/image/favicon.ico" type="image/x-icon">
    <link href="https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css" rel="stylesheet" />
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="//cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">
    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script> 
</head>
<script>
    $(document).ready(function() {
        $('.table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.12.1/i18n/es_es.json"
            }
        });

        // Eliminar inscripción
        $('.btn-eliminar').click(function() {
            var id = $(this).data('id');
            if (confirm('¿Estás seguro de que deseas eliminar esta inscripción?')) {
                $.post('../../connection/admin/delete_inscripcion.php', {id_inscripcion: id}, function(response) {
                    $('#responseModal .modal-body').text(response);
                    $('#responseModal').modal('show');
                    if (response.trim() === 'success') {
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    }
                });
            }
        });

        // Actualizar inscripción
        $('.btn-actualizar').click(function() {
            var id = $(this).data('id');
            var inscripcion = $(this).closest('tr');
            var id_cliente = inscripcion.find('.id_cliente').data('id');
            var id_tipo_membresia = inscripcion.find('.id_tipo_membresia').data('id');
            var fecha_fin = inscripcion.find('.fecha_fin').text();
            var num_meses = inscripcion.find('.num_meses').text();
            var pago = inscripcion.find('.pago').text();

            $('#updateModal input[name="id_cliente"]').val(id_cliente);
            $('#updateModal select[name="id_tipo_membresia"]').val(id_tipo_membresia);
            $('#updateModal input[name="num_meses"]').val(num_meses);
            $('#updateModal input[name="pago"]').val(pago);
            $('#updateModal input[name="fecha_inicio"]').val(fecha_fin);
            
            $('#updateModal').modal('show');
            updateCostAndDate();
        });

        $('#updateForm').submit(function(event) {
            event.preventDefault();
            var formData = $(this).serialize();

            $.post('../../connection/admin/insert_inscripcion.php', formData, function(response) {
                $('#responseModal .modal-body').text(response);
                $('#responseModal').modal('show');
                if (response.trim() === 'success') {
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                }
            });
        });
    });

    document.getElementById('tipo_membresia').addEventListener('change', updateCostAndDate);
    document.getElementById('num_meses').addEventListener('input', updateCostAndDate);

    function updateCostAndDate() {
        var selectedOption = document.getElementById('tipo_membresia').options[document.getElementById('tipo_membresia').selectedIndex];
        var precio = parseFloat(selectedOption.getAttribute('data-precio'));
        var numMeses = parseInt(document.getElementById('num_meses').value) || 1;
        var costoTotal = precio * numMeses;

        document.getElementById('pago').value = costoTotal.toFixed(2);

        // Fecha de Inicio como la fecha de fin anterior
        var fechaInicio = document.getElementById('fecha_inicio').value;

        // Calcular la Fecha de Fin
        var fecha = new Date(fechaInicio);
        fecha.setMonth(fecha.getMonth() + numMeses);
        var fechaFin = fecha.toISOString().split('T')[0];
        document.getElementById('fecha_fin').value = fechaFin;
    }
</script>

<body>
<header>
    <!-- Navbar -->
    <nav>
        <div class="logo">
            <i class="bx bx-menu menu-icon"></i>
            <img src="../../assets/image/logo.png" alt="Logo" width="30" height="30" class="d-inline-block align-text-top">
            <span class="logo-name">Syntax Strong</span>
        </div>
        <div class="saludo">
            <h5><?= $nombre_completo; ?> | Administrador</h5>
        </div>
        <div class="sidebar">
            <div class="logo">
                <i class="bx bx-menu menu-icon"></i>
                <img src="../../assets/image/logo.png" alt="Logo" width="30" height="30" class="d-inline-block align-text-top">
                <span class="logo-name">Syntax Strong</span>
            </div>
            <div class="sidebar-content">
                <ul class="lists">
                    <li class="list">
                        <a href="./dashboard.php" class="nav-link">
                            <i class="bx bx-home-alt icon"></i>
                            <span class="link">Dashboard</span>
                        </a>
                    </li>
                    <li class="list">
                        <a href="./ganancias.php" class="nav-link">
                            <i class="bx bx-bell icon"></i>
                            <span class="link">Ganancias al mes</span>
                        </a>
                    </li>
                    <li class="list">
                        <a href="./usuarios.php" class="nav-link">
                            <i class='bx bx-user-check icon'></i>
                            <span class="link">Usuarios</span>
                        </a>
                    </li>
                    <li class="list">
                        <a href="./inscripciones.php" class="nav-link">
                            <i class='bx bx-notepad icon'></i>
                            <span class="link">Inscripciones</span>
                        </a>
                    </li>
                    <li class="list">
                        <a href="../../connection/logout.php" class="nav-link">
                            <i class="bx bx-log-out icon"></i>
                            <span class="link">Cerrar sesión</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>
<main class="contenido-general">
    <h1 class="fw-bold text-center p-2">Inscripciones</h1>
    <div class="tabla_usuarios">
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre completo</th>
                    <th class="id_cliente">ID Cliente</th>
                    <th>Membresía</th>
                    <th class="fecha_inicio">Fecha de inicio</th>
                    <th class="fecha_fin">Fecha de fin</th>
                    <th class="estatus">Estatus</th>
                    <th class="pago">Pago</th>
                    <th class="num_meses">Número de Meses</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inscripciones as $inscripcion): ?>
                <tr>
                    <td><?= $inscripcion['id_inscripcion'] ?></td>
                    <td><?= $inscripcion['nombre_completo'] ?></td>
                    <td class="id_cliente" data-id="<?= $inscripcion['id_cliente'] ?>"><?= $inscripcion['id_cliente'] ?></td>
                    <td class="id_tipo_membresia" data-id="<?= $inscripcion['id_tipo_membresia'] ?>"><?= $inscripcion['tipo_membresia'] ?></td>
                    <td class="fecha_inicio"><?= $inscripcion['fecha_inicio'] ?></td>
                    <td class="fecha_fin"><?= $inscripcion['fecha_fin'] ?></td>
                    <td class="estatus"><?= $inscripcion['estatus'] ?></td>
                    <td class="pago"><?= $inscripcion['pago'] ?></td>
                    <td class="num_meses"><?= $inscripcion['num_meses'] ?></td>
                    <td>
                        <button class="btn btn-danger btn-eliminar" data-id="<?= $inscripcion['id_inscripcion'] ?>">Eliminar</button>
                        <button class="btn btn-success btn-actualizar" data-id="<?= $inscripcion['id_inscripcion'] ?>">Actualizar</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<footer>
    <!-- place footer here -->
</footer>

<!-- Modal de Respuesta -->
<div class="modal fade" id="responseModal" tabindex="-1" aria-labelledby="responseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="responseModalLabel">Respuesta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Aquí se mostrará el mensaje de respuesta -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Actualización -->
<div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateModalLabel">Nueva Membresía</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateForm" action="../../connection/admin/insert_inscripcion.php" method="post">
                    <input type="hidden" name="id_cliente">
                    <div class="mb-3">
                        <label for="tipo_membresia" class="form-label">Tipo de Membresía</label>
                        <select class="form-control" id="tipo_membresia" name="id_tipo_membresia" required>
                            <?php foreach ($membresias as $membresia): ?>
                                <option value="<?= $membresia['id_tipo_membresia'] ?>" data-precio="<?= $membresia['precio'] ?>">
                                    <?= $membresia['tipo_membresia'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="num_meses" class="form-label">Número de Meses</label>
                        <input type="number" class="form-control" name="num_meses" id="num_meses" value="1" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="pago" class="form-label">Pago</label>
                        <input type="text" class="form-control" name="pago" id="pago" readonly required>
                    </div>
                    <div class="mb-3">
                        <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                        <input type="text" class="form-control" name="fecha_inicio" id="fecha_inicio" readonly required>
                    </div>
                    <div class="mb-3">
                        <label for="fecha_fin" class="form-label">Fecha de Fin</label>
                        <input type="text" class="form-control" name="fecha_fin" id="fecha_fin" readonly>
                    </div>
                    <input type="hidden" name="id_estatus" id="id_estatus" value="1">
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Agregar Membresía</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
<script src="../../assets/js/admin/sidevar.js"></script>
</body>
</html>
