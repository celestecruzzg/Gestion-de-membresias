<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../index.html");
    exit();
}

require_once '../../connection/conn.php';

if (isset($_POST['id_cliente'], $_POST['id_tipo_membresia'], $_POST['num_meses'], $_POST['pago'], $_POST['fecha_inicio'])) {
    $id_cliente = $_POST['id_cliente'];
    $id_tipo_membresia = $_POST['id_tipo_membresia'];
    $num_meses = $_POST['num_meses'];
    $pago = $_POST['pago'];
    
    // Obtener la fecha de inicio de la inscripción anterior
    $fecha_inicio = $_POST['fecha_inicio'];
    // Calcular la fecha de fin basado en la fecha de inicio y el número de meses
    $fecha_fin = date('Y-m-d', strtotime("+$num_meses months", strtotime($fecha_inicio)));
    $id_estatus = 1; // Estatus activo por defecto
    
    // Registrar la nueva inscripción
    $sql = "INSERT INTO tb_inscripciones (id_cliente, id_tipo_membresia, fecha_inicio, fecha_fin, id_estatus, pago)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissid", $id_cliente, $id_tipo_membresia, $fecha_inicio, $fecha_fin, $id_estatus, $pago);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        echo "success";
    } else {
        echo "error";
    }
    
    $stmt->close();
    $conn->close();
}
?>
