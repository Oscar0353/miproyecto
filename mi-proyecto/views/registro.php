<?php
session_start();
require_once '../inc/conexion.php';
require_once '../inc/funciones.php';

$errores = [
    'nombre' => '',
    'email' => '',
    'password' => '',
    'rol' => '',
    'exito' => ''
];

$nombre = '';
$email = '';
$password = '';
$rol = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = limpiar_dato($_POST['nombre']);
    $email = limpiar_dato($_POST['email']);
    $password = $_POST['password'];
    $rol = limpiar_dato($_POST['rol']); // Procesa el rol enviado desde el formulario

    // Validaciones
    if (empty($nombre)) {
        $errores['nombre'] = 'El nombre es obligatorio.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores['email'] = 'El email no es válido.';
    }
    if (strlen($password) < 6) {
        $errores['password'] = 'La contraseña debe tener al menos 6 caracteres.';
    }
    if (!in_array($rol, ['admin', 'viewer'])) { // Verifica si el rol es válido
        $errores['rol'] = 'El rol seleccionado no es válido.';
    }

    // Verificar si el email ya existe en la base de datos
    $sqlVerificacion = "SELECT COUNT(*) FROM usuarios WHERE email = :email";
    $stmtVerificacion = $conexion->prepare($sqlVerificacion);
    $stmtVerificacion->bindParam(':email', $email);
    $stmtVerificacion->execute();
    $emailExiste = $stmtVerificacion->fetchColumn();

    if ($emailExiste) {
        $errores['email'] = 'El correo electrónico ya está registrado.';
    }

    // Si no hay errores, proceder con el registro
    if (empty(array_filter($errores))) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios (nombre, email, password, rol) VALUES (:nombre, :email, :password, :rol)";
        $stmt = $conexion->prepare($sql);

        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $passwordHash);
        $stmt->bindParam(':rol', $rol);

        if ($stmt->execute()) {
            // echo "Usuario registrado exitosamente.";
            $errores['exito'] = 'Usuario registrado exitosamente.';
        } else {
            echo "Error al registrar el usuario.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        body {
            margin: 0;
        }
        .caja {
            display: grid;
            place-items: center;
            min-height: 100vh;
            background-color: #f0f0f0;
        }
        header {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            height: 50px;
        }
        a {
            padding-right: 20px;
            text-decoration: none;
            color: black;
            font-size: 27px;
        }
        form {
            width: 100%;
        }
        h2 {
            text-align: center;
        }
        .exito {
            text-align: center;
            color: green;
            font-weight: bold;
        }
        input, select {
            width: -webkit-fill-available;
            padding: 10px;
            font-size: 16px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .error {
            color: red;
            font-size: 0.9em;
        }
        button {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }

        /* Estilos personalizados para la parte de Rol */
        .rol-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            max-width: 400px;
            margin: 0 auto 20px;
        }

        /* Caja de selección más angosta con fondo blanco */
        
        
        .rol-container label {
            text-align: center;
            font-size: 16px;
            border: 1px solid #ccc; /* Borde alrededor de la etiqueta */
            padding: 8px; /* Añade relleno alrededor de la palabra "Rol" */
            max-width: 50px;
            background-color: #f0f0f0; /* Fondo gris claro para la etiqueta */
            display: inline-block; /* Mantiene el tamaño adecuado del cuadro */
            margin-right: 10px; /* Espacio entre la etiqueta y el select */
        }

        
        
        /* Estilo para el cuadro de selección encerrado en un cuadro */
        .rol-container select {
            font-size: 13px;
            border: 1px solid #ccc; /* Añade un borde al cuadro de selección */
            padding: 1%; /* Relleno dentro del cuadro de selección */
            background-color: #fff; /* Fondo blanco dentro de la selección */
        }

        .rol-container .select-box {
            border: 1px solid #ccc; /* Borde alrededor de la etiqueta */
            padding: 6px; /* Añade relleno alrededor de la palabra "Rol" */
            margin-bottom: 6px;
            background-color: #f0f0f0; /* Fondo gris claro para la etiqueta */
            display: inline-block; /* Mantiene el tamaño adecuado del cuadro */
            margin-right: 10px; /* Espacio entre la etiqueta y el select */
        }


</style>
</head>
<body>
    <header>
        <a href="../index.php">Index</a>
        <a href="login.php">Login</a>
    </header>

    <div class="caja">
        <form method="post">
            <h2>Registro de Usuario</h2>
            <?php if (!empty($errores['exito'])): ?>
                <p class="exito"><?php echo $errores['exito']; ?></p>
            <?php endif; ?>

            <label for="nombre">Nombre:</label>
            <input type="text" name="nombre" id="nombre" value="<?php echo htmlspecialchars($nombre); ?>">

            <?php if (!empty($errores['nombre'])): ?>
                <p class="error"><?php echo $errores['nombre']; ?></p>
            <?php endif; ?>

            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>">
            <?php if (!empty($errores['email'])): ?>
                <p class="error"><?php echo $errores['email']; ?></p>
            <?php endif; ?>

            <label for="password">Contraseña:</label>
            <input type="password" name="password" id="password">
            <?php if (!empty($errores['password'])): ?>
                <p class="error"><?php echo $errores['password']; ?></p>
            <?php endif; ?>

            <!-- Sección de selección de Rol -->
            <div class="rol-container">
                <label for="rol">Rol:</label>
                <div class="select-box">
                    <select name="rol" id="rol">
                        <option value="viewer" <?php echo $rol === 'viewer' ? 'selected' : ''; ?>>Invitado</option>
                        <option value="admin" <?php echo $rol === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                    </select>
                </div>
            </div>

            <button type="submit">Registrar</button>
        </form>
    </div>
</body>
</html>