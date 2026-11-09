<?php

class ClienteController{
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // Arma un objeto Cliente con los datos recibidos
    private function armarCliente($datos){
        return new Cliente(
            $this->conexion,
            trim($datos["cedula"] ?? ""),
            trim($datos["nombre"] ?? ""),
            trim($datos["apellido"] ?? ""),
            trim($datos["correo"] ?? ""),
            trim($datos["telefono"] ?? ""),
            trim($datos["direccion"] ?? ""),
            isset($datos["id"]) ? (int)$datos["id"] : null
        );
    }

    // Valida los atributos del cliente, devuelve el mensaje de error o null
    private function validar($cliente){
        if ($cliente->cedula === "" || !preg_match('/^[0-9]{10}$/', $cliente->cedula)) {
            return "La cédula es requerida y debe tener 10 dígitos";
        }
        if ($cliente->nombre === "" || mb_strlen($cliente->nombre) > 100) {
            return "El nombre es requerido y debe tener máximo 100 caracteres";
        }
        if ($cliente->apellido === "" || mb_strlen($cliente->apellido) > 100) {
            return "El apellido es requerido y debe tener máximo 100 caracteres";
        }
        if ($cliente->correo !== "" && !filter_var($cliente->correo, FILTER_VALIDATE_EMAIL)) {
            return "El correo no tiene un formato válido";
        }
        if (mb_strlen($cliente->correo) > 150 || mb_strlen($cliente->telefono) > 15 || mb_strlen($cliente->direccion) > 200) {
            return "Hay un campo que supera el máximo de caracteres";
        }
        return null;
    }

    public function todos(){
        $cliente = new Cliente($this->conexion);
        echo json_encode($cliente->todos());
    }

    public function obtener($id){
        $cliente = new Cliente($this->conexion);
        $datos = $cliente->obtener($id);
        if ($datos) {
            echo json_encode($datos);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Cliente no encontrado"]);
        }
    }

    public function guardar($datos){
        $cliente = $this->armarCliente($datos);
        $error = $this->validar($cliente);
        if ($error) {
            http_response_code(400);
            echo json_encode(["error" => $error]);
            return;
        }
        $resultado = $cliente->guardar();
        http_response_code(201);
        echo json_encode(["guardado" => $resultado]);
    }

    public function actualizar($datos){
        $cliente = $this->armarCliente($datos);
        if (!$cliente->id) {
            http_response_code(400);
            echo json_encode(["error" => "El id del cliente es requerido"]);
            return;
        }
        $error = $this->validar($cliente);
        if ($error) {
            http_response_code(400);
            echo json_encode(["error" => $error]);
            return;
        }
        $resultado = $cliente->actualizar();
        echo json_encode(["actualizado" => $resultado]);
    }

    public function eliminar($datos){
        $cliente = $this->armarCliente($datos);
        if (!$cliente->id) {
            http_response_code(400);
            echo json_encode(["error" => "El id del cliente es requerido"]);
            return;
        }
        $resultado = $cliente->eliminar();
        echo json_encode(["eliminado" => $resultado]);
    }

    public function creartabla(){
        $cliente = new Cliente($this->conexion);
        echo json_encode(["tabla_creada" => $cliente->crearTabla()]);
    }
}
