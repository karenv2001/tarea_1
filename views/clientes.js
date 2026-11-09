const API = "../index.php";

const formulario = document.getElementById("formCliente");
const tablahtml = document.getElementById("tablaClientes");
const mensaje = document.getElementById("mensaje");
const tituloForm = document.getElementById("tituloForm");
const btnCancelar = document.getElementById("btnCancelar");

const campos = ["id", "cedula", "nombre", "apellido", "correo", "telefono", "direccion"];

async function todos() {
    try {
        const respuesta = await fetch(API + "?accion=todos");
        const clientes = await respuesta.json();
        if (!respuesta.ok) {
            throw new Error(clientes.error || "Error al obtener los datos");
        }
        tablahtml.innerHTML = "";
        clientes.forEach(cliente => {
            const fila = document.createElement("tr");

            campos.forEach(campo => {
                const celda = document.createElement("td");
                celda.textContent = cliente[campo] ?? "";
                fila.appendChild(celda);
            });

            const celdaAcciones = document.createElement("td");

            const btnEditar = document.createElement("button");
            btnEditar.className = "btn btn-warning btn-sm me-1";
            btnEditar.textContent = "Editar";
            btnEditar.addEventListener("click", () => editar(cliente.id));

            const btnEliminar = document.createElement("button");
            btnEliminar.className = "btn btn-danger btn-sm";
            btnEliminar.textContent = "Eliminar";
            btnEliminar.addEventListener("click", () => eliminar(cliente.id));

            celdaAcciones.appendChild(btnEditar);
            celdaAcciones.appendChild(btnEliminar);
            fila.appendChild(celdaAcciones);

            tablahtml.appendChild(fila);
        });
    } catch (error) {
        mensaje.textContent = error.message;
    }
}

function datosFormulario() {
    const datos = new URLSearchParams();
    campos.forEach(campo => {
        datos.append(campo, document.getElementById(campo).value.trim());
    });
    return datos;
}

function limpiarFormulario() {
    formulario.reset();
    document.getElementById("id").value = "";
    tituloForm.textContent = "Registrar Cliente";
    btnCancelar.classList.add("d-none");
}

// CREATE y UPDATE: si hay id oculto se actualiza, si no se guarda uno nuevo
formulario.addEventListener("submit", async function (event) {
    event.preventDefault();

    const editando = document.getElementById("id").value !== "";
    const accion = editando ? "actualizar" : "guardar";

    try {
        const respuesta = await fetch(API + "?accion=" + accion, {
            method: "POST",
            body: datosFormulario()
        });
        const resultado = await respuesta.json();
        if (!respuesta.ok) {
            throw new Error(resultado.error || "Error al guardar");
        }

        if (resultado.guardado || resultado.actualizado) {
            mensaje.textContent = editando
                ? "Cliente actualizado correctamente"
                : "Cliente guardado correctamente";
            limpiarFormulario();
            await todos();
        } else {
            mensaje.textContent = "No se pudo guardar";
        }
    } catch (error) {
        mensaje.textContent = error.message;
    }
});

// Carga el cliente en el formulario para editarlo
async function editar(id) {
    try {
        const respuesta = await fetch(API + "?accion=obtener&id=" + id);
        const cliente = await respuesta.json();
        if (!respuesta.ok) {
            throw new Error(cliente.error || "No se pudo obtener el cliente");
        }
        campos.forEach(campo => {
            document.getElementById(campo).value = cliente[campo] ?? "";
        });
        tituloForm.textContent = "Editar Cliente";
        btnCancelar.classList.remove("d-none");
        mensaje.textContent = "";
    } catch (error) {
        mensaje.textContent = error.message;
    }
}

// DELETE
async function eliminar(id) {
    if (!confirm("¿Seguro que quieres eliminar este cliente?")) {
        return;
    }
    try {
        const datos = new URLSearchParams();
        datos.append("id", id);
        const respuesta = await fetch(API + "?accion=eliminar", {
            method: "POST",
            body: datos
        });
        const resultado = await respuesta.json();
        if (!respuesta.ok || !resultado.eliminado) {
            throw new Error(resultado.error || "No se pudo eliminar");
        }
        mensaje.textContent = "Cliente eliminado correctamente";
        await todos();
    } catch (error) {
        mensaje.textContent = error.message;
    }
}

async function crearTabla() {
    try {
        const respuesta = await fetch(API + "?accion=creartabla", {
            method: "POST"
        });
        const resultado = await respuesta.json();
        if (!respuesta.ok || !resultado.tabla_creada) {
            throw new Error(resultado.error || "No se pudo crear la tabla");
        }
        mensaje.textContent = "Tabla creada correctamente. Si ya existía, se conservaron los datos.";
        await todos();
    } catch (error) {
        mensaje.textContent = error.message;
    }
}

btnCancelar.addEventListener("click", limpiarFormulario);
document.getElementById("crearTabla").addEventListener("click", crearTabla);

todos();
