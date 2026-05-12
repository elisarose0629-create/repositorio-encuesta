<?php
// Procesar formulario
$errores = [];
$nombre = $apellido = $email = $facultad = $sexo = $observacion = '';
$lenguajes = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Botón borrar
    if (isset($_POST['borrar_datos'])) {
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    // Botón enviar
    if (isset($_POST['enviar_encuesta'])) {
        $nombre   = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $facultad = $_POST['facultad'] ?? '';
        $sexo     = $_POST['sexo'] ?? '';
        $observacion = trim($_POST['observacion'] ?? '');
        $lenguajes = $_POST['lenguajes'] ?? [];

        // Validaciones
        if ($nombre === '') $errores['nombre'] = 'Requerido';
        if ($apellido === '') $errores['apellido'] = 'Requerido';
        if ($email === '') $errores['email'] = 'Requerido';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores['email'] = 'Email inválido';
        if ($facultad === '') $errores['facultad'] = 'Seleccione una facultad';
        if ($sexo === '') $errores['sexo'] = 'Seleccione sexo';

        if (empty($errores)) {
            $exito = true;
            // Aquí puedes guardar en BD o mostrar mensaje
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Encuesta Estudiantil</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        table { border-collapse: collapse; width: 600px; }
        td, th { border: 1px solid #ccc; padding: 8px; vertical-align: top; }
        .error { color: red; font-size: 0.8em; }
        .exito { color: green; margin-bottom: 10px; }
        input, select, textarea { width: 100%; box-sizing: border-box; }
        .check-group, .radio-group { display: flex; gap: 15px; }
        .check-group label, .radio-group label { font-weight: normal; }
        button { margin-top: 10px; padding: 5px 15px; }
    </style>
</head>
<body>

<h2>Encuesta estudiantil</h2>
<h3>Universidad Tecnológica Centro de Bocas del Toro</h3>

<?php if (isset($exito) && $exito): ?>
    <div class="exito">✔ Encuesta enviada correctamente.</div>
<?php endif; ?>

<form method="post">
    <table>
        <tr>
            <td><strong>Nombre</strong></td>
            <td>
                <input type="text" name="nombre" value="<?= htmlspecialchars($nombre) ?>" placeholder="Ingrese su nombre">
                <?php if (isset($errores['nombre'])): ?>
                    <div class="error"><?= $errores['nombre'] ?></div>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td><strong>Apellido</strong></td>
            <td>
                <input type="text" name="apellido" value="<?= htmlspecialchars($apellido) ?>" placeholder="Ingrese su apellido">
                <?php if (isset($errores['apellido'])): ?>
                    <div class="error"><?= $errores['apellido'] ?></div>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td><strong>Email</strong></td>
            <td>
                <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="Ingrese su email">
                <?php if (isset($errores['email'])): ?>
                    <div class="error"><?= $errores['email'] ?></div>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td><strong>Lenguajes que más le gustan</strong></td>
            <td>
                <div class="check-group">
                    <label><input type="checkbox" name="lenguajes[]" value="Java" <?= in_array('Java', $lenguajes) ? 'checked' : '' ?>> Java</label>
                    <label><input type="checkbox" name="lenguajes[]" value="PHP" <?= in_array('PHP', $lenguajes) ? 'checked' : '' ?>> PHP</label>
                    <label><input type="checkbox" name="lenguajes[]" value="C#" <?= in_array('C#', $lenguajes) ? 'checked' : '' ?>> C#</label>
                    <label><input type="checkbox" name="lenguajes[]" value="Python" <?= in_array('Python', $lenguajes) ? 'checked' : '' ?>> Python</label>
                </div>
            </td>
        </tr>
        <tr>
            <td><strong>Seleccione su Facultad</strong></td>
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
            <td><strong>Seleccione sexo:</strong></td>
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
            <td><strong>Observación</strong></td>
            <td>
                <textarea name="observacion" rows="3" placeholder="Ingrese alguna observación"><?= htmlspecialchars($observacion) ?></textarea>
            </td>
        </tr>
    </table>

    <div>
        <button type="submit" name="enviar_encuesta">Enviar encuesta</button>
        <button type="submit" name="borrar_datos">Borrar los datos</button>
    </div>
</form>

</body>
</html>