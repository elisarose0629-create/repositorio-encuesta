<?php
// Conexión a BD
$host = 'localhost';
$user = 'root';
$password = '062900';
$dbname = 'encuestas_db';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) die("Error de conexión: " . $conn->connect_error);

// Crear tabla si no existe
$conn->query("CREATE TABLE IF NOT EXISTS encuestas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    lenguajes VARCHAR(100) DEFAULT '',
    facultad VARCHAR(50) NOT NULL,
    sexo ENUM('Masculino','Femenino') NOT NULL,
    observacion TEXT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// --- Lógica CRUD ---
$errores = [];
$exito = false;
$modo = 'crear'; // crear o editar
$edit_id = null;

// Cargar datos para edición
if (isset($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $stmt = $conn->prepare("SELECT * FROM encuestas WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $modo = 'editar';
        $edit_id = $row['id'];
        $nombre = $row['nombre'];
        $apellido = $row['apellido'];
        $email = $row['email'];
        $lenguajesSeleccionados = explode(',', $row['lenguajes']);
        $facultad = $row['facultad'];
        $sexo = $row['sexo'];
        $observacion = $row['observacion'];
    }
    $stmt->close();
}

// Eliminar registro
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $conn->query("DELETE FROM encuestas WHERE id = $id");
    header("Location: index.php?msg=eliminado");
    exit;
}

// Procesar envío del formulario (Crear o Actualizar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_encuesta'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $facultad = $_POST['facultad'] ?? '';
    $sexo = $_POST['sexo'] ?? '';
    $observacion = trim($_POST['observacion'] ?? '');
    $lenguajes = $_POST['lenguajes'] ?? [];
    $lenguajesStr = implode(',', $lenguajes);
    $edit_id_post = $_POST['edit_id'] ?? '';

    // Validaciones
    if ($nombre === '') $errores['nombre'] = 'Requerido';
    if ($apellido === '') $errores['apellido'] = 'Requerido';
    if ($email === '') $errores['email'] = 'Requerido';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores['email'] = 'Email inválido';
    if ($facultad === '') $errores['facultad'] = 'Seleccione una facultad';
    if ($sexo === '') $errores['sexo'] = 'Seleccione sexo';

    if (empty($errores)) {
        if ($edit_id_post) {
            // Actualizar
            $stmt = $conn->prepare("UPDATE encuestas SET nombre=?, apellido=?, email=?, lenguajes=?, facultad=?, sexo=?, observacion=? WHERE id=?");
            $stmt->bind_param("sssssssi", $nombre, $apellido, $email, $lenguajesStr, $facultad, $sexo, $observacion, $edit_id_post);
            $stmt->execute();
            $stmt->close();
            $exito = "actualizado";
        } else {
            // Insertar
            $stmt = $conn->prepare("INSERT INTO encuestas (nombre, apellido, email, lenguajes, facultad, sexo, observacion) VALUES (?,?,?,?,?,?,?)");
            $stmt->bind_param("sssssss", $nombre, $apellido, $email, $lenguajesStr, $facultad, $sexo, $observacion);
            $stmt->execute();
            $stmt->close();
            $exito = "creado";
        }
        // Limpiar el formulario redirigiendo para evitar reenvío
        header("Location: index.php?msg=" . ($edit_id_post ? "actualizado" : "creado"));
        exit;
    }
}

// Botón "Borrar los datos" -> redirigir sin parámetros (formulario limpio)
if (isset($_POST['borrar_datos'])) {
    header("Location: index.php");
    exit;
}

// Obtener listado de encuestas
$listado = $conn->query("SELECT * FROM encuestas ORDER BY fecha DESC");

// Valores por defecto para el formulario (si no viene de edición)
if ($modo !== 'editar') {
    $nombre = $apellido = $email = $facultad = $sexo = $observacion = '';
    $lenguajesSeleccionados = [];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Encuesta Estudiantil - CRUD</title>
    <style>
        /* CSS Manteniendo el mismo formato de tabla, pero mejorando estética */
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            margin: 0;
            padding: 30px 20px;
        }
        .wrapper {
            max-width: 1000px;
            margin: 0 auto;
        }
        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 40px;
        }
        h2 {
            margin-top: 0;
            color: #1e466e;
            border-left: 6px solid #007bff;
            padding-left: 15px;
        }
        /* Tabla del formulario (igual a la imagen) */
        .form-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        .form-table td, .form-table th {
            border: 1px solid #ccc;
            padding: 12px;
            vertical-align: top;
        }
        .form-table th {
            background-color: #f8f9fa;
            width: 180px;
            text-align: left;
            font-weight: 600;
        }
        input[type="text"],
        input[type="email"],
        select,
        textarea {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #aaa;
            border-radius: 6px;
            font-size: 14px;
        }
        .check-group, .radio-group {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        .check-group label, .radio-group label {
            font-weight: normal;
            margin-right: 12px;
        }
        .error {
            color: #d9534f;
            font-size: 0.8em;
            margin-top: 4px;
        }
        .btn {
            padding: 8px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            margin-right: 10px;
            transition: 0.2s;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        /* Tabla de listado */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        .data-table th, .data-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .data-table th {
            background-color: #1e466e;
            color: white;
        }
        .data-table tr:hover {
            background-color: #f1f1f1;
        }
        .acciones a {
            display: inline-block;
            margin-right: 8px;
            text-decoration: none;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.8em;
        }
        .editar {
            background-color: #ffc107;
            color: #333;
        }
        .eliminar {
            background-color: #dc3545;
            color: white;
        }
        h3 {
            margin-top: 0;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">
        <h2>📋 Encuesta estudiantil</h2>
        <h3>Universidad Tecnológica Centro de Bocas del Toro</h3>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success">
                <?php if ($_GET['msg'] == 'creado') echo "✔ Encuesta creada correctamente."; ?>
                <?php if ($_GET['msg'] == 'actualizado') echo "✔ Encuesta actualizada."; ?>
                <?php if ($_GET['msg'] == 'eliminado') echo "✔ Encuesta eliminada."; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <?php if ($modo == 'editar'): ?>
                <input type="hidden" name="edit_id" value="<?= $edit_id ?>">
            <?php endif; ?>

            <table class="form-table">
                <tr>
                    <th>Nombre</th>
                    <td>
                        <input type="text" name="nombre" value="<?= htmlspecialchars($nombre) ?>" placeholder="ingrese su nombre">
                        <?php if (isset($errores['nombre'])): ?>
                            <div class="error"><?= $errores['nombre'] ?></div>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Apellido</th>
                    <td>
                        <input type="text" name="apellido" value="<?= htmlspecialchars($apellido) ?>" placeholder="ingrese su apellido">
                        <?php if (isset($errores['apellido'])): ?>
                            <div class="error"><?= $errores['apellido'] ?></div>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>
                        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="ingrese su email">
                        <?php if (isset($errores['email'])): ?>
                            <div class="error"><?= $errores['email'] ?></div>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Lenguajes que más le gustan</th>
                    <td>
                        <div class="check-group">
                            <?php $lengs = ['Java','PHP','C#','Python']; ?>
                            <?php foreach ($lengs as $l): ?>
                                <label>
                                    <input type="checkbox" name="lenguajes[]" value="<?= $l ?>"
                                        <?= in_array($l, $lenguajesSeleccionados) ? 'checked' : '' ?>>
                                    <?= $l ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>Seleccione su Facultad</th>
                    <td>
                        <select name="facultad">
                            <option value="Ingeniería en Sistemas" <?= $facultad == 'Ingeniería en Sistemas' ? 'selected' : '' ?>>Ingeniería en Sistemas</option>
                            <option value="Administración" <?= $facultad == 'Administración' ? 'selected' : '' ?>>Administración</option>
                            <option value="Turismo" <?= $facultad == 'Turismo' ? 'selected' : '' ?>>Turismo</option>
                        </select>
                        <?php if (isset($errores['facultad'])): ?>
                            <div class="error"><?= $errores['facultad'] ?></div>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Seleccione sexo:</th>
                    <td>
                        <div class="radio-group">
                            <label><input type="radio" name="sexo" value="Masculino" <?= $sexo == 'Masculino' ? 'checked' : '' ?>> Masculino</label>
                            <label><input type="radio" name="sexo" value="Femenino" <?= $sexo == 'Femenino' ? 'checked' : '' ?>> Femenino</label>
                        </div>
                        <?php if (isset($errores['sexo'])): ?>
                            <div class="error"><?= $errores['sexo'] ?></div>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Observación</th>
                    <td>
                        <textarea name="observacion" rows="3" placeholder="ingrese alguna observación"><?= htmlspecialchars($observacion) ?></textarea>
                    </td>
                </tr>
            </table>

            <div style="margin-top: 20px;">
                <button type="submit" name="enviar_encuesta" class="btn btn-primary">Enviar encuesta</button>
                <button type="submit" name="borrar_datos" class="btn btn-secondary">Borrar los datos</button>
                <?php if ($modo == 'editar'): ?>
                    <a href="index.php" class="btn btn-secondary">Cancelar edición</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Listado de encuestas (CRUD Read) -->
    <div class="card">
        <h2>📋 Listado de encuestas registradas</h2>
        <table class="data-table">
            <thead>
                <tr><th>ID</th><th>Nombre</th><th>Apellido</th><th>Email</th><th>Lenguajes</th><th>Facultad</th><th>Sexo</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                <?php if ($listado->num_rows > 0): ?>
                    <?php while($row = $listado->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['nombre']) ?></td>
                        <td><?= htmlspecialchars($row['apellido']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['lenguajes']) ?></td>
                        <td><?= htmlspecialchars($row['facultad']) ?></td>
                        <td><?= $row['sexo'] ?></td>
                        <td class="acciones">
                            <a href="index.php?editar=<?= $row['id'] ?>" class="editar">✏️ Editar</a>
                            <a href="index.php?eliminar=<?= $row['id'] ?>" onclick="return confirm('¿Eliminar esta encuesta?')" class="eliminar">🗑️ Eliminar</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8">No hay encuestas aún. Completa el formulario de arriba.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>