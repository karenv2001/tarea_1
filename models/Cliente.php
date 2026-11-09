<?php

class Cliente{
    // Atributos publicos del objeto Cliente
    public $id;
    public $cedula;
    public $nombre;
    public $apellido;
    public $correo;
    public $telefono;
    public $direccion;

    private $conexion;

    public function __construct($conexion, $cedula = "", $nombre = "", $apellido = "", $correo = "", $telefono = "", $direccion = "", $id = null) {
        $this->conexion = $conexion;
        $this->id = $id;
        $this->cedula = $cedula;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->correo = $correo;
        $this->telefono = $telefono;
        $this->direccion = $direccion;
    }

    // Crea la tabla si no existe
    public function crearTabla(){
        $cadena = "create table if not exists clientes(
            id INT AUTO_INCREMENT PRIMARY KEY,
            cedula VARCHAR(10) NOT NULL UNIQUE,
            nombre VARCHAR(100) NOT NULL,
            apellido VARCHAR(100) NOT NULL,
            correo VARCHAR(150) NULL,
            telefono VARCHAR(15) NULL,
            direccion VARCHAR(200) NULL)";

        return $this->conexion->query($cadena);
    }

    // READ: todos los clientes
    public function todos(){
        $resultado = $this->conexion->query("Select * from clientes order by id");
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    // READ: un cliente por id
    public function obtener($id){
        $ejecutar = $this->conexion->prepare("Select * from clientes where id = ?");
        $ejecutar->bind_param('i', $id);
        $ejecutar->execute();
        $resultado = $ejecutar->get_result();
        return $resultado->fetch_assoc();
    }

    // CREATE: guarda con los valores de los atributos del objeto
    public function guardar(){
        $cadena = "insert into clientes(cedula, nombre, apellido, correo, telefono, direccion) values(?,?,?,?,?,?)";
        $ejecutar = $this->conexion->prepare($cadena);
        $ejecutar->bind_param('ssssss', $this->cedula, $this->nombre, $this->apellido, $this->correo, $this->telefono, $this->direccion);
        return $ejecutar->execute();
    }

    // UPDATE: actualiza el cliente cuyo id esta en el objeto
    public function actualizar(){
        $cadena = "update clientes set cedula = ?, nombre = ?, apellido = ?, correo = ?, telefono = ?, direccion = ? where id = ?";
        $ejecutar = $this->conexion->prepare($cadena);
        $ejecutar->bind_param('ssssssi', $this->cedula, $this->nombre, $this->apellido, $this->correo, $this->telefono, $this->direccion, $this->id);
        return $ejecutar->execute();
    }

    // DELETE: elimina el cliente cuyo id esta en el objeto
    public function eliminar(){
        $ejecutar = $this->conexion->prepare("delete from clientes where id = ?");
        $ejecutar->bind_param('i', $this->id);
        return $ejecutar->execute();
    }
}
