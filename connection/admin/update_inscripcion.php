<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../index.html");
    exit();
}

require_once '../../connection/conn.php';

if (isset($_POST['id_inscripcion'], $_POST['id_tipo_membresia'], $_POST['num_meses'], $_POST['pago'])) {
    $id_inscripcion = $_POST['id_inscripcion'];
    $id_tipo_membresia = $_POST['id_tipo_membresia'];
    $num_meses = $_POST['num_meses'];
    $pago = $_POST['pago'];
    
    // Calcular la fecha de inicio y fin basado en el número de meses
    $fecha_inicio = date('Y-m-d'); // Fecha actual como fecha de inicio
    $fecha_fin = date('Y-m-d', strtotime("+$num_meses months", strtotime($fecha_inicio)));
    $id_estatus = 1; // Estatus activo por defecto
    
    // Registrar la acción antes de actualizar
    $sql_log = "INSERT INTO tb_inscripciones_log (id_inscripcion, id_cliente, id_tipo_membresia, tipo_membresia, fecha_inicio, fecha_fin, id_estatus, pago, accion, usuario)
                SELECT id_inscripcion, id_cliente, id_tipo_membresia, tipo_membresia, fecha_inicio, fecha_fin, id_estatus, pago, 'UPDATE', ? 
                FROM tb_inscripciones WHERE id_inscripcion = ?";
    $stmt_log = $conn->prepare($sql_log);
    $stmt_log->bind_param("si", $_SESSION['admin_nombre'], $id_inscripcion);
    $stmt_log->execute();
    $stmt_log->close();
    
    // Actualizar la inscripción
    $sql = "UPDATE tb_inscripciones SET id_tipo_membresia = ?, fecha_inicio = ?, fecha_fin = ?, id_estatus = ?, pago = ? WHERE id_inscripcion = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issidi", $id_tipo_membresia, $fecha_inicio, $fecha_fin, $id_estatus, $pago, $id_inscripcion);
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
