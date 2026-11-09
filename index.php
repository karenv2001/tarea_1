<?php

header("Content-Type: application/json");
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/models/Cliente.php";
require_once __DIR__ . "/controllers/ClienteController.php";

function soloPost(){
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        http_response_code(405);
        echo json_encode(["error" => "El metodo no es permitido"]);
        return false;
    }
    return true;
}

try {
    $db = new Database();
    $conexion = $db->conectar();

    $controlador = new ClienteController($conexion);

    $accion = $_GET["accion"] ?? 'todos';

    switch ($accion) {
        case "todos":
            $controlador->todos();
            break;
        case "obtener":
            $controlador->obtener((int)($_GET["id"] ?? 0));
            break;
        case "guardar":
            if (soloPost()) { $controlador->guardar($_POST); }
            break;
        case "actualizar":
            if (soloPost()) { $controlador->actualizar($_POST); }
            break;
        case "eliminar":
            if (soloPost()) { $controlador->eliminar($_POST); }
            break;
        case "creartabla":
            if (soloPost()) { $controlador->creartabla(); }
            break;
        default:
            http_response_code(404);
            echo json_encode(["error" => "Accion no encontrada"]);
    }

} catch (mysqli_sql_exception $error) {
    error_log($error->getMessage());
    if ($error->getCode() == 1146) {
        http_response_code(500);
        $mensaje = "La tabla clientes no existe. Pulsa Crear tabla para comenzar.";
    } elseif ($error->getCode() == 1062) {
        http_response_code(409);
        $mensaje = "Ya existe un cliente con esa cédula.";
    } else {
        http_response_code(500);
        $mensaje = "No se pudo completar la operación en la base de datos.";
    }
    echo json_encode(["error" => $mensaje]);
} catch (RuntimeException $error) {
    http_response_code(500);
    error_log($error->getMessage());
    echo json_encode(["error" => "No se pudo conectar con la base de datos."]);
}
