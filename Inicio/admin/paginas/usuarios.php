<?php
// Las variables $conexion e $id_institucion ($idInstitucionActual) vienen de auth.php
$idInstitucionActual = $id_institucion;

function admin_limpiar_texto_local($valor): string
{
    return trim((string) $valor);
}

function admin_generar_contrasena_importada(string $rut, string $nombre): string
{
    $rutLimpio = preg_replace('/[^0-9kK]/', '', $rut);
    $inicial = mb_substr($nombre, 0, 1, 'UTF-8');
    $inicial = mb_strtoupper($inicial, 'UTF-8');

    if ($rutLimpio === '') {
        $rutLimpio = 'usuario';
    }

    return $rutLimpio . $inicial;
}

function admin_buscar_por_id(array $items, int $id, string $clave): ?array
{
    foreach ($items as $item) {
        if ((int) ($item[$clave] ?? 0) === $id) {
            return $item;
        }
    }
    return null;
}

function admin_normalizar_rol_local(string $nombreRol): string
{
    return strtolower(trim($nombreRol));
}

function admin_rol_es_directivo_local(string $nombreRol): bool
{
    return in_array(admin_normalizar_rol_local($nombreRol), ['directivo', 'director'], true);
}

function admin_rol_requiere_carrera_local(string $nombreRol): bool
{
    return in_array(admin_normalizar_rol_local($nombreRol), ['estudiante', 'coordinador'], true) || admin_rol_es_directivo_local($nombreRol);
}

function admin_rol_soportado_local(string $nombreRol): bool
{
    return in_array(admin_normalizar_rol_local($nombreRol), ['estudiante', 'coordinador'], true) || admin_rol_es_directivo_local($nombreRol);
}

function admin_normalizar_header_importacion(string $valor): string
{
    $valor = strtolower(trim($valor));
    $valor = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $valor);
    $valor = preg_replace('/[^a-z0-9]+/', '_', $valor);
    return trim((string) $valor, '_');
}

function admin_columna_a_indice(string $referencia): int
{
    $referencia = strtoupper(preg_replace('/[^A-Z0-9]/', '', $referencia));
    $indice = 0;
    for ($i = 0, $len = strlen($referencia); $i < $len; $i++) {
        $indice = $indice * 26 + (ord($referencia[$i]) - ord('A') + 1);
    }
    return $indice - 1;
}

function admin_resolver_rol_importado(string $valor, array $roles): ?array
{
    $valor = trim((string) $valor);
    if ($valor === '') {
        return null;
    }

    if (is_numeric($valor)) {
        foreach ($roles as $rol) {
            if ((int) ($rol['id_rol'] ?? 0) === (int) $valor) {
                return $rol;
            }
        }
    }

    $valorNormalizado = admin_normalizar_header_importacion($valor);
    foreach ($roles as $rol) {
        if (admin_normalizar_header_importacion((string) ($rol['nombre_rol'] ?? '')) === $valorNormalizado) {
            return $rol;
        }
    }

    return null;
}

function admin_resolver_carrera_importada(string $valor, array $carreras): ?array
{
    $valor = trim((string) $valor);
    if ($valor === '') {
        return null;
    }

    if (is_numeric($valor)) {
        foreach ($carreras as $carrera) {
            if ((int) ($carrera['id_carrera'] ?? 0) === (int) $valor) {
                return $carrera;
            }
        }
    }

    $valorNormalizado = admin_normalizar_header_importacion($valor);
    foreach ($carreras as $carrera) {
        $nombreNormalizado = admin_normalizar_header_importacion((string) ($carrera['nombre_carrera'] ?? ''));
        $codigoNormalizado = admin_normalizar_header_importacion((string) ($carrera['codigo'] ?? ''));
        if ($nombreNormalizado === $valorNormalizado || $codigoNormalizado === $valorNormalizado) {
            return $carrera;
        }
    }

    return null;
}

function admin_extraer_filas_desde_excel(string $rutaArchivo, string $extension): array
{
    if ($extension === 'csv') {
        $filas = [];
        if (($handle = fopen($rutaArchivo, 'r')) !== false) {
            while (($fila = fgetcsv($handle)) !== false) {
                $filas[] = $fila;
            }
            fclose($handle);
        }
        return $filas;
    }

    if ($extension === 'xlsx') {
        $zip = new ZipArchive();
        if ($zip->open($rutaArchivo) !== true) {
            throw new Exception('No se pudo leer el archivo .xlsx.');
        }

        $sharedStrings = [];
        if ($zip->locateName('xl/sharedStrings.xml') !== false) {
            $sharedXml = simplexml_load_string($zip->getFromName('xl/sharedStrings.xml'));
            if ($sharedXml !== false) {
                foreach ($sharedXml->si as $item) {
                    $texto = '';
                    foreach ($item->children('http://schemas.openxmlformats.org/spreadsheetml/2006/main')->t as $valor) {
                        $texto .= (string) $valor;
                    }
                    $sharedStrings[] = $texto;
                }
            }
        }

        $workbookXml = simplexml_load_string($zip->getFromName('xl/workbook.xml'));
        $relationshipsXml = simplexml_load_string($zip->getFromName('xl/_rels/workbook.xml.rels'));
        $sheetRId = (string) $workbookXml->sheets->sheet[0]['r:id'];
        $sheetTarget = '';
        foreach ($relationshipsXml->Relationship as $relationship) {
            if ((string) $relationship['Id'] === $sheetRId) {
                $sheetTarget = (string) $relationship['Target'];
                break;
            }
        }
        $zip->close();

        if ($sheetTarget === '') {
            throw new Exception('No se pudo localizar la hoja del archivo .xlsx.');
        }

        $sheetPath = 'xl/' . ltrim($sheetTarget, '/');
        $sheetXml = simplexml_load_string(file_get_contents($rutaArchivo));
        if ($sheetXml === false) {
            throw new Exception('No se pudo interpretar la hoja del archivo .xlsx.');
        }

        $filas = [];
        if ($zip->open($rutaArchivo) === true) {
            $sheetContent = $zip->getFromName($sheetPath);
            $sheetXml = simplexml_load_string($sheetContent);
            $zip->close();
            if ($sheetXml !== false && isset($sheetXml->sheetData->row)) {
                foreach ($sheetXml->sheetData->row as $row) {
                    $valores = [];
                    foreach ($row->c as $cell) {
                        $tipo = (string) $cell['t'];
                        $referencia = (string) $cell['r'];
                        $indice = admin_columna_a_indice($referencia);
                        $valor = '';
                        if (isset($cell->v)) {
                            $valor = (string) $cell->v;
                            if ($tipo === 's' && isset($sharedStrings[(int) $valor])) {
                                $valor = $sharedStrings[(int) $valor];
                            }
                        }
                        $valores[$indice] = $valor;
                    }
                    ksort($valores);
                    $filas[] = array_values($valores);
                }
            }
        }

        return $filas;
    }

    if ($extension === 'xls') {
        if (!class_exists('PhpOffice\\PhpSpreadsheet\\IOFactory')) {
            throw new Exception('La librería PhpSpreadsheet no está instalada para importar archivos .xls.');
        }
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($rutaArchivo);
        $sheet = $spreadsheet->getActiveSheet();
        $filas = $sheet->toArray(null, true, true, true);
        $resultado = [];
        foreach ($filas as $fila) {
            $resultado[] = array_values(array_map(static function ($valor) {
                return $valor === null ? '' : (string) $valor;
            }, $fila));
        }
        return $resultado;
    }

    throw new Exception('Formato de archivo no soportado.');
}

function admin_importar_usuarios_excel(mysqli $conexion, string $rutaArchivo, string $nombreArchivo, int $idInstitucionActual, array $roles, array $carreras): array
{
    $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
    $filas = admin_extraer_filas_desde_excel($rutaArchivo, $extension);

    if (empty($filas)) {
        return ['correctos' => 0, 'errores' => ['El archivo no contiene datos.'], 'total' => 0];
    }

    $encabezados = [];
    $primerFila = array_shift($filas);
    foreach ($primerFila as $valor) {
        $encabezados[] = admin_normalizar_header_importacion((string) $valor);
    }

    $indices = [];
    $aliasCampos = [
        'nombre' => ['nombre', 'nombres', 'first_name', 'primer_nombre'],
        'apellido' => ['apellido', 'apellidos', 'last_name', 'primer_apellido'],
        'rut' => ['rut', 'run'],
        'correo' => ['correo', 'email', 'mail'],
        'rol' => ['rol', 'role', 'tipo_usuario'],
        'carrera' => ['carrera', 'programa', 'career'],
        'estado' => ['estado', 'estado_cuenta', 'status'],
        'contrasena' => ['contrasena', 'password', 'clave', 'contrasena_hash'],
    ];

    foreach ($encabezados as $indice => $encabezado) {
        foreach ($aliasCampos as $campo => $aliases) {
            if (in_array($encabezado, $aliases, true)) {
                $indices[$campo] = $indice;
                break;
            }
        }
    }

    foreach (['nombre', 'apellido', 'rut', 'correo', 'rol'] as $campoObligatorio) {
        if (!array_key_exists($campoObligatorio, $indices)) {
            return ['correctos' => 0, 'errores' => ['Faltan columnas obligatorias. Asegúrate de incluir nombre, apellido, rut, correo y rol.'], 'total' => 0];
        }
    }

    $correctos = 0;
    $errores = [];
    $numeroFila = 2;

    foreach ($filas as $fila) {
        $datos = [];
        foreach ($indices as $campo => $indice) {
            $datos[$campo] = isset($fila[$indice]) ? trim((string) $fila[$indice]) : '';
        }

        if ($datos['nombre'] === '' && $datos['apellido'] === '' && $datos['correo'] === '') {
            $numeroFila++;
            continue;
        }

        $nombre = admin_limpiar_texto_local($datos['nombre'] ?? '');
        $apellido = admin_limpiar_texto_local($datos['apellido'] ?? '');
        $rut = admin_limpiar_texto_local($datos['rut'] ?? '');
        $correo = admin_limpiar_texto_local($datos['correo'] ?? '');
        $rolTexto = admin_limpiar_texto_local($datos['rol'] ?? '');
        $carreraTexto = admin_limpiar_texto_local($datos['carrera'] ?? '');
        $estadoTexto = strtolower(trim((string) ($datos['estado'] ?? '')));
        $contrasenaTexto = admin_limpiar_texto_local($datos['contrasena'] ?? '');
        $estado = in_array($estadoTexto, ['inactiva'], true) ? 'inactiva' : 'activa';
        $contrasena = $contrasenaTexto !== '' ? $contrasenaTexto : admin_generar_contrasena_importada($rut, $nombre);

        if ($nombre === '' || $apellido === '' || $rut === '' || $correo === '' || $rolTexto === '') {
            $errores[] = 'Fila ' . $numeroFila . ': faltan datos obligatorios.';
            $numeroFila++;
            continue;
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'Fila ' . $numeroFila . ': el correo no es válido.';
            $numeroFila++;
            continue;
        }

        $rolImportado = admin_resolver_rol_importado($rolTexto, $roles);
        if (!$rolImportado) {
            $errores[] = 'Fila ' . $numeroFila . ': no se encontró el rol indicado.';
            $numeroFila++;
            continue;
        }

        $nombreRol = (string) $rolImportado['nombre_rol'];
        if (admin_rol_requiere_carrera_local($nombreRol)) {
            $carreraImportada = admin_resolver_carrera_importada($carreraTexto, $carreras);
            if (!$carreraImportada) {
                $errores[] = 'Fila ' . $numeroFila . ': selecciona una carrera válida para este rol.';
                $numeroFila++;
                continue;
            }
            $idCarrera = (int) $carreraImportada['id_carrera'];
        } else {
            $idCarrera = 0;
        }

        $stmtExistente = mysqli_prepare($conexion, "SELECT id_usuario FROM usuario WHERE correo = ? AND id_institucion = ? LIMIT 1");
        if (!$stmtExistente) {
            $errores[] = 'Fila ' . $numeroFila . ': no se pudo validar el correo.';
            $numeroFila++;
            continue;
        }
        mysqli_stmt_bind_param($stmtExistente, 'si', $correo, $idInstitucionActual);
        mysqli_stmt_execute($stmtExistente);
        $resultadoExistente = mysqli_stmt_get_result($stmtExistente);
        $usuarioExistente = $resultadoExistente ? mysqli_fetch_assoc($resultadoExistente) : null;
        mysqli_stmt_close($stmtExistente);

        if ($usuarioExistente) {
            $errores[] = 'Fila ' . $numeroFila . ': el correo ya existe en esta institución.';
            $numeroFila++;
            continue;
        }

        try {
            mysqli_begin_transaction($conexion);
            $hash = password_hash($contrasena, PASSWORD_DEFAULT);
            $stmtUsuario = mysqli_prepare($conexion, "INSERT INTO usuario (id_rol, id_institucion, rut, correo, contrasena_hash, estado_cuenta) VALUES (?, ?, ?, ?, ?, ?)");
            if (!$stmtUsuario) {
                throw new Exception('No se pudo preparar la creación del usuario.');
            }
            mysqli_stmt_bind_param($stmtUsuario, 'iissss', $rolImportado['id_rol'], $idInstitucionActual, $rut, $correo, $hash, $estado);
            if (!mysqli_stmt_execute($stmtUsuario)) {
                throw new Exception(mysqli_stmt_error($stmtUsuario));
            }
            $idUsuario = mysqli_insert_id($conexion);
            mysqli_stmt_close($stmtUsuario);

            $nombreRolNormalizado = admin_normalizar_rol_local($nombreRol);
            if ($nombreRolNormalizado === 'estudiante') {
                $stmtPerfil = mysqli_prepare($conexion, "INSERT INTO estudiante (id_usuario, id_carrera, nombre, apellido, nivel_curricular, habilidades, ramos_aprobados) VALUES (?, ?, ?, ?, 1, '', 0)");
                mysqli_stmt_bind_param($stmtPerfil, 'iiss', $idUsuario, $idCarrera, $nombre, $apellido);
            } elseif ($nombreRolNormalizado === 'coordinador') {
                $stmtPerfil = mysqli_prepare($conexion, "INSERT INTO coordinador (id_usuario, id_carrera, nombre, apellido) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmtPerfil, 'iiss', $idUsuario, $idCarrera, $nombre, $apellido);
            } elseif (admin_rol_es_directivo_local($nombreRol)) {
                $stmtPerfil = mysqli_prepare($conexion, "INSERT INTO directivo (id_usuario, id_carrera, nombre, apellido) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmtPerfil, 'iiss', $idUsuario, $idCarrera, $nombre, $apellido);
            } else {
                throw new Exception('Rol no soportado para importación.');
            }

            if (!$stmtPerfil) {
                throw new Exception('No se pudo preparar el perfil del usuario.');
            }
            if (!mysqli_stmt_execute($stmtPerfil)) {
                throw new Exception(mysqli_stmt_error($stmtPerfil));
            }
            mysqli_stmt_close($stmtPerfil);

            mysqli_commit($conexion);
            $correctos++;
        } catch (Throwable $e) {
            mysqli_rollback($conexion);
            $errores[] = 'Fila ' . $numeroFila . ': ' . $e->getMessage();
        }

        $numeroFila++;
    }

    return [
        'correctos' => $correctos,
        'errores' => $errores,
        'total' => count($filas),
    ];
}

function admin_reiniciar_perfiles_usuario(mysqli $conexion, int $idUsuario): void
{
    $tablas = ['estudiante', 'coordinador', 'directivo', 'administrador', 'empresa', 'superadministrador'];
    foreach ($tablas as $tabla) {
        $stmt = mysqli_prepare($conexion, "DELETE FROM {$tabla} WHERE id_usuario = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
}

$mensaje = '';
$tipoMensaje = 'success';
$nombreInstitucionActual = 'Sin institución';
$usuarios = [];
$resultadoImportacion = null;

if ($idInstitucionActual > 0) {
    $stmtInstitucion = mysqli_prepare($conexion, "SELECT nombre FROM institucion WHERE id_institucion = ? LIMIT 1");
    if ($stmtInstitucion) {
        mysqli_stmt_bind_param($stmtInstitucion, 'i', $idInstitucionActual);
        mysqli_stmt_execute($stmtInstitucion);
        $resultadoInstitucion = mysqli_stmt_get_result($stmtInstitucion);
        $filaInstitucion = $resultadoInstitucion ? mysqli_fetch_assoc($resultadoInstitucion) : null;
        mysqli_stmt_close($stmtInstitucion);
        if ($filaInstitucion && !empty($filaInstitucion['nombre'])) {
            $nombreInstitucionActual = (string) $filaInstitucion['nombre'];
        }
    }
}

$roles = $idInstitucionActual > 0 ? admin_query_all(
    $conexion,
    "SELECT id_rol, nombre_rol
    FROM rol
    WHERE LOWER(TRIM(estado)) = 'activo'
            AND LOWER(TRIM(nombre_rol)) NOT IN ('administrador', 'superadministrador', 'super administrador')
    ORDER BY nombre_rol"
) : [];

$carreras = $idInstitucionActual > 0 ? admin_query_all(
    $conexion,
    "SELECT id_carrera, nombre_carrera, codigo
    FROM carrera
    WHERE id_institucion = " . (int) $idInstitucionActual . "
    ORDER BY nombre_carrera"
) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($idInstitucionActual <= 0) {
        $mensaje = 'No se pudo identificar la institución del administrador. Inicia sesión nuevamente.';
        $tipoMensaje = 'danger';
    } elseif ($accion === 'importar') {
        if (!isset($_FILES['archivo']) || empty($_FILES['archivo']['name'])) {
            $mensaje = 'Selecciona un archivo para importar.';
            $tipoMensaje = 'danger';
        } elseif ($_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            $mensaje = 'No se pudo subir el archivo. Intenta nuevamente.';
            $tipoMensaje = 'danger';
        } else {
            $nombreArchivo = admin_limpiar_texto_local($_FILES['archivo']['name']);
            $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
            if (!in_array($extension, ['csv', 'xlsx', 'xls'], true)) {
                $mensaje = 'Solo se permiten archivos .csv, .xlsx o .xls.';
                $tipoMensaje = 'danger';
            } else {
                $resultadoImportacion = admin_importar_usuarios_excel($conexion, $_FILES['archivo']['tmp_name'], $nombreArchivo, $idInstitucionActual, $roles, $carreras);
                if (($resultadoImportacion['correctos'] ?? 0) > 0) {
                    $mensaje = 'Importación completada. Se crearon ' . (int) $resultadoImportacion['correctos'] . ' usuarios.';
                    $tipoMensaje = 'success';
                } elseif (!empty($resultadoImportacion['errores'])) {
                    $mensaje = 'No se pudo importar ningún usuario.';
                    $tipoMensaje = 'warning';
                } else {
                    $mensaje = 'No se importó ningún usuario.';
                    $tipoMensaje = 'warning';
                }
            }
        }
    } elseif ($accion === 'crear') {
        $nombre = admin_limpiar_texto_local($_POST['nombre'] ?? '');
        $apellido = admin_limpiar_texto_local($_POST['apellido'] ?? '');
        $rut = admin_limpiar_texto_local($_POST['rut'] ?? '');
        $correo = admin_limpiar_texto_local($_POST['correo'] ?? '');
        $contrasena = (string) ($_POST['contrasena'] ?? '');
        $confirmarContrasena = (string) ($_POST['confirmar_contrasena'] ?? '');
        $idRol = (int) ($_POST['id_rol'] ?? 0);
        $idCarrera = (int) ($_POST['id_carrera'] ?? 0);
        $estado = 'activa';

        $rolSeleccionado = admin_buscar_por_id($roles, $idRol, 'id_rol');
        $carreraSeleccionada = $idCarrera > 0 ? admin_buscar_por_id($carreras, $idCarrera, 'id_carrera') : null;

        if ($nombre === '' || $apellido === '' || $rut === '' || $correo === '' || $contrasena === '' || $confirmarContrasena === '') {
            $mensaje = 'Completa todos los datos del usuario.';
            $tipoMensaje = 'danger';
        } elseif ($contrasena !== $confirmarContrasena) {
            $mensaje = 'Las contraseñas no coinciden.';
            $tipoMensaje = 'danger';
        } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $mensaje = 'El correo ingresado no es válido.';
            $tipoMensaje = 'danger';
        } elseif (!$rolSeleccionado) {
            $mensaje = 'Selecciona un rol válido.';
            $tipoMensaje = 'danger';
        } elseif (!admin_rol_soportado_local((string) $rolSeleccionado['nombre_rol'])) {
            $mensaje = 'El rol seleccionado no está habilitado en este módulo.';
            $tipoMensaje = 'danger';
        } elseif (admin_rol_requiere_carrera_local((string) $rolSeleccionado['nombre_rol']) && !$carreraSeleccionada) {
            $mensaje = 'Debes seleccionar una carrera válida para ese rol.';
            $tipoMensaje = 'danger';
        } elseif ($carreraSeleccionada && (int) $carreraSeleccionada['id_carrera'] <= 0) {
            $mensaje = 'La carrera seleccionada no pertenece a tu institución.';
            $tipoMensaje = 'danger';
        } else {
            mysqli_begin_transaction($conexion);
            try {
                $hash = password_hash($contrasena, PASSWORD_DEFAULT);
                $stmtUsuario = mysqli_prepare($conexion, "INSERT INTO usuario (id_rol, id_institucion, rut, correo, contrasena_hash, estado_cuenta) VALUES (?, ?, ?, ?, ?, ?)");
                if (!$stmtUsuario) {
                    throw new Exception('No se pudo preparar el alta del usuario.');
                }
                mysqli_stmt_bind_param($stmtUsuario, 'iissss', $idRol, $idInstitucionActual, $rut, $correo, $hash, $estado);
                if (!mysqli_stmt_execute($stmtUsuario)) {
                    throw new Exception(mysqli_stmt_error($stmtUsuario));
                }
                $idUsuario = mysqli_insert_id($conexion);
                mysqli_stmt_close($stmtUsuario);

                $nombreRol = (string) $rolSeleccionado['nombre_rol'];
                $nombreRolNormalizado = admin_normalizar_rol_local($nombreRol);
                if ($nombreRolNormalizado === 'administrador') {
                    $stmtPerfil = mysqli_prepare($conexion, "INSERT INTO administrador (id_usuario, nombre, apellido) VALUES (?, ?, ?)");
                    mysqli_stmt_bind_param($stmtPerfil, 'iss', $idUsuario, $nombre, $apellido);
                } elseif ($nombreRolNormalizado === 'estudiante') {
                    $stmtPerfil = mysqli_prepare($conexion, "INSERT INTO estudiante (id_usuario, id_carrera, nombre, apellido, nivel_curricular, habilidades, ramos_aprobados) VALUES (?, ?, ?, ?, 1, '', 0)");
                    mysqli_stmt_bind_param($stmtPerfil, 'iiss', $idUsuario, $idCarrera, $nombre, $apellido);
                } elseif ($nombreRolNormalizado === 'coordinador') {
                    $stmtPerfil = mysqli_prepare($conexion, "INSERT INTO coordinador (id_usuario, id_carrera, nombre, apellido) VALUES (?, ?, ?, ?)");
                    mysqli_stmt_bind_param($stmtPerfil, 'iiss', $idUsuario, $idCarrera, $nombre, $apellido);
                } elseif (admin_rol_es_directivo_local($nombreRol)) {
                    $stmtPerfil = mysqli_prepare($conexion, "INSERT INTO directivo (id_usuario, id_carrera, nombre, apellido) VALUES (?, ?, ?, ?)");
                    mysqli_stmt_bind_param($stmtPerfil, 'iiss', $idUsuario, $idCarrera, $nombre, $apellido);
                } else {
                    throw new Exception('Rol no soportado.');
                }

                if (!$stmtPerfil) {
                    throw new Exception('No se pudo preparar el perfil del usuario.');
                }
                if (!mysqli_stmt_execute($stmtPerfil)) {
                    throw new Exception(mysqli_stmt_error($stmtPerfil));
                }
                mysqli_stmt_close($stmtPerfil);

                if (admin_normalizar_rol_local($nombreRol) === 'administrador') {
                    $stmtInstitucion = mysqli_prepare($conexion, "UPDATE institucion SET id_administrador = ? WHERE id_institucion = ?");
                    if ($stmtInstitucion) {
                        mysqli_stmt_bind_param($stmtInstitucion, 'ii', $idUsuario, $idInstitucionActual);
                        mysqli_stmt_execute($stmtInstitucion);
                        mysqli_stmt_close($stmtInstitucion);
                    }
                }

                mysqli_commit($conexion);
                admin_registrar_auditoria($conexion, 'Creacion de usuario', 'usuarios', 'ID usuario: ' . $idUsuario . ' | Rol: ' . $nombreRol . ' | Institucion: ' . $idInstitucionActual);
                $mensaje = 'Usuario creado correctamente.';
            } catch (Throwable $e) {
                mysqli_rollback($conexion);
                $mensaje = 'No se pudo crear el usuario.';
                $tipoMensaje = 'danger';
            }
        }
    } elseif ($accion === 'editar') {
        $idUsuario = (int) ($_POST['id_usuario'] ?? 0);
        $nombre = admin_limpiar_texto_local($_POST['nombre'] ?? '');
        $apellido = admin_limpiar_texto_local($_POST['apellido'] ?? '');
        $rut = admin_limpiar_texto_local($_POST['rut'] ?? '');
        $correo = admin_limpiar_texto_local($_POST['correo'] ?? '');
        $idRol = (int) ($_POST['id_rol'] ?? 0);
        $idCarrera = (int) ($_POST['id_carrera'] ?? 0);
        $estado = ($_POST['estado_cuenta'] ?? 'activa') === 'inactiva' ? 'inactiva' : 'activa';
        $contrasena = (string) ($_POST['contrasena'] ?? '');
        $confirmarContrasena = (string) ($_POST['confirmar_contrasena'] ?? '');

        $usuarioActual = null;
        $stmtActual = mysqli_prepare($conexion, "SELECT u.id_usuario, u.id_rol, u.id_institucion
            FROM usuario u
            WHERE u.id_usuario = ? AND u.id_institucion = ?
            LIMIT 1");
        if ($stmtActual) {
            mysqli_stmt_bind_param($stmtActual, 'ii', $idUsuario, $idInstitucionActual);
            mysqli_stmt_execute($stmtActual);
            $resultadoActual = mysqli_stmt_get_result($stmtActual);
            $usuarioActual = $resultadoActual ? mysqli_fetch_assoc($resultadoActual) : null;
            mysqli_stmt_close($stmtActual);
        }

        $rolSeleccionado = admin_buscar_por_id($roles, $idRol, 'id_rol');
        $carreraSeleccionada = $idCarrera > 0 ? admin_buscar_por_id($carreras, $idCarrera, 'id_carrera') : null;

        if (!$usuarioActual) {
            $mensaje = 'El usuario no pertenece a tu institución o no existe.';
            $tipoMensaje = 'danger';
        } elseif ($nombre === '' || $apellido === '' || $rut === '' || $correo === '') {
            $mensaje = 'Completa los campos obligatorios para editar.';
            $tipoMensaje = 'danger';
        } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $mensaje = 'El correo ingresado no es válido.';
            $tipoMensaje = 'danger';
        } elseif (!$rolSeleccionado) {
            $mensaje = 'Selecciona un rol válido.';
            $tipoMensaje = 'danger';
        } elseif (!admin_rol_soportado_local((string) $rolSeleccionado['nombre_rol'])) {
            $mensaje = 'El rol seleccionado no está habilitado en este módulo.';
            $tipoMensaje = 'danger';
        } elseif (admin_rol_requiere_carrera_local((string) $rolSeleccionado['nombre_rol']) && !$carreraSeleccionada) {
            $mensaje = 'Debes seleccionar una carrera válida para ese rol.';
            $tipoMensaje = 'danger';
        } elseif ($contrasena !== '' && $contrasena !== $confirmarContrasena) {
            $mensaje = 'Las contraseñas no coinciden.';
            $tipoMensaje = 'danger';
        } else {
            mysqli_begin_transaction($conexion);
            try {
                if ($contrasena !== '') {
                    $hash = password_hash($contrasena, PASSWORD_DEFAULT);
                    $stmtUsuario = mysqli_prepare($conexion, "UPDATE usuario SET id_rol = ?, rut = ?, correo = ?, estado_cuenta = ?, contrasena_hash = ?, id_institucion = ? WHERE id_usuario = ?");
                    if (!$stmtUsuario) {
                        throw new Exception('No se pudo preparar la actualización del usuario.');
                    }
                    mysqli_stmt_bind_param($stmtUsuario, 'issssii', $idRol, $rut, $correo, $estado, $hash, $idInstitucionActual, $idUsuario);
                } else {
                    $stmtUsuario = mysqli_prepare($conexion, "UPDATE usuario SET id_rol = ?, rut = ?, correo = ?, estado_cuenta = ?, id_institucion = ? WHERE id_usuario = ?");
                    if (!$stmtUsuario) {
                        throw new Exception('No se pudo preparar la actualización del usuario.');
                    }
                    mysqli_stmt_bind_param($stmtUsuario, 'isssii', $idRol, $rut, $correo, $estado, $idInstitucionActual, $idUsuario);
                }
                if (!mysqli_stmt_execute($stmtUsuario)) {
                    throw new Exception(mysqli_stmt_error($stmtUsuario));
                }
                mysqli_stmt_close($stmtUsuario);

                admin_reiniciar_perfiles_usuario($conexion, $idUsuario);

                $nombreRol = (string) $rolSeleccionado['nombre_rol'];
                $nombreRolNormalizado = admin_normalizar_rol_local($nombreRol);
                if ($nombreRolNormalizado === 'administrador') {
                    $stmtPerfil = mysqli_prepare($conexion, "INSERT INTO administrador (id_usuario, nombre, apellido) VALUES (?, ?, ?)");
                    mysqli_stmt_bind_param($stmtPerfil, 'iss', $idUsuario, $nombre, $apellido);
                } elseif ($nombreRolNormalizado === 'estudiante') {
                    $stmtPerfil = mysqli_prepare($conexion, "INSERT INTO estudiante (id_usuario, id_carrera, nombre, apellido, nivel_curricular, habilidades, ramos_aprobados) VALUES (?, ?, ?, ?, 1, '', 0)");
                    mysqli_stmt_bind_param($stmtPerfil, 'iiss', $idUsuario, $idCarrera, $nombre, $apellido);
                } elseif ($nombreRolNormalizado === 'coordinador') {
                    $stmtPerfil = mysqli_prepare($conexion, "INSERT INTO coordinador (id_usuario, id_carrera, nombre, apellido) VALUES (?, ?, ?, ?)");
                    mysqli_stmt_bind_param($stmtPerfil, 'iiss', $idUsuario, $idCarrera, $nombre, $apellido);
                } elseif (admin_rol_es_directivo_local($nombreRol)) {
                    $stmtPerfil = mysqli_prepare($conexion, "INSERT INTO directivo (id_usuario, id_carrera, nombre, apellido) VALUES (?, ?, ?, ?)");
                    mysqli_stmt_bind_param($stmtPerfil, 'iiss', $idUsuario, $idCarrera, $nombre, $apellido);
                } else {
                    throw new Exception('Rol no soportado.');
                }

                if (!$stmtPerfil) {
                    throw new Exception('No se pudo preparar el perfil del usuario.');
                }
                if (!mysqli_stmt_execute($stmtPerfil)) {
                    throw new Exception(mysqli_stmt_error($stmtPerfil));
                }
                mysqli_stmt_close($stmtPerfil);

                $stmtClearAdmin = mysqli_prepare($conexion, "UPDATE institucion SET id_administrador = NULL WHERE id_institucion = ? AND id_administrador = ?");
                if ($stmtClearAdmin) {
                    mysqli_stmt_bind_param($stmtClearAdmin, 'ii', $idInstitucionActual, $idUsuario);
                    mysqli_stmt_execute($stmtClearAdmin);
                    mysqli_stmt_close($stmtClearAdmin);
                }

                if (admin_normalizar_rol_local($nombreRol) === 'administrador') {
                    $stmtSetAdmin = mysqli_prepare($conexion, "UPDATE institucion SET id_administrador = ? WHERE id_institucion = ?");
                    if ($stmtSetAdmin) {
                        mysqli_stmt_bind_param($stmtSetAdmin, 'ii', $idUsuario, $idInstitucionActual);
                        mysqli_stmt_execute($stmtSetAdmin);
                        mysqli_stmt_close($stmtSetAdmin);
                    }
                }

                mysqli_commit($conexion);
                admin_registrar_auditoria($conexion, 'Edicion de usuario', 'usuarios', 'ID usuario: ' . $idUsuario . ' | Rol: ' . $nombreRol . ' | Estado: ' . $estado);
                $mensaje = 'Usuario actualizado correctamente.';
            } catch (Throwable $e) {
                mysqli_rollback($conexion);
                $mensaje = 'No se pudo actualizar el usuario.';
                $tipoMensaje = 'danger';
            }
        }
    } elseif ($accion === 'estado') {
        $idUsuario = (int) ($_POST['id_usuario'] ?? 0);
        $estado = ($_POST['estado'] ?? 'activa') === 'inactiva' ? 'inactiva' : 'activa';

        $stmt = mysqli_prepare($conexion, "UPDATE usuario SET estado_cuenta = ? WHERE id_usuario = ? AND id_institucion = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'sii', $estado, $idUsuario, $idInstitucionActual);
            if (mysqli_stmt_execute($stmt)) {
                $mensaje = $estado === 'activa' ? 'Usuario activado correctamente.' : 'Usuario desactivado correctamente.';
                admin_registrar_auditoria($conexion, 'Cambio de estado de usuario', 'usuarios', 'ID usuario: ' . $idUsuario . ' | Estado: ' . $estado);
            } else {
                $mensaje = 'No se pudo cambiar el estado.';
                $tipoMensaje = 'danger';
            }
            mysqli_close($stmt);
        }
    } elseif ($accion === 'eliminar') {
        $idUsuario = (int) ($_POST['id_usuario'] ?? 0);

        mysqli_begin_transaction($conexion);
        try {
            $stmtValidar = mysqli_prepare($conexion, "SELECT id_usuario FROM usuario WHERE id_usuario = ? AND id_institucion = ? LIMIT 1");
            if (!$stmtValidar) {
                throw new Exception('No se pudo validar el usuario.');
            }
            mysqli_stmt_bind_param($stmtValidar, 'ii', $idUsuario, $idInstitucionActual);
            mysqli_stmt_execute($stmtValidar);
            $resultadoValidar = mysqli_stmt_get_result($stmtValidar);
            $usuarioValido = $resultadoValidar ? mysqli_fetch_assoc($resultadoValidar) : null;
            mysqli_stmt_close($stmtValidar);

            if (!$usuarioValido) {
                throw new Exception('El usuario no existe o no pertenece a tu institución.');
            }

            $stmtClearAdmin = mysqli_prepare($conexion, "UPDATE institucion SET id_administrador = NULL WHERE id_institucion = ? AND id_administrador = ?");
            if ($stmtClearAdmin) {
                mysqli_stmt_bind_param($stmtClearAdmin, 'ii', $idInstitucionActual, $idUsuario);
                mysqli_stmt_execute($stmtClearAdmin);
                mysqli_stmt_close($stmtClearAdmin);
            }

            admin_reiniciar_perfiles_usuario($conexion, $idUsuario);

            $stmtUsuario = mysqli_prepare($conexion, "DELETE FROM usuario WHERE id_usuario = ? AND id_institucion = ?");
            if (!$stmtUsuario) {
                throw new Exception('No se pudo preparar la eliminación.');
            }
            mysqli_stmt_bind_param($stmtUsuario, 'ii', $idUsuario, $idInstitucionActual);
            if (!mysqli_stmt_execute($stmtUsuario)) {
                throw new Exception(mysqli_stmt_error($stmtUsuario));
            }
            mysqli_stmt_close($stmtUsuario);

            mysqli_commit($conexion);
            admin_registrar_auditoria($conexion, 'Eliminacion de usuario', 'usuarios', 'ID usuario: ' . $idUsuario);
            $mensaje = 'Usuario eliminado correctamente.';
        } catch (Throwable $e) {
            mysqli_rollback($conexion);
            $mensaje = 'No se pudo eliminar el usuario.';
            $tipoMensaje = 'danger';
        }
    }
}

if ($idInstitucionActual > 0) {
    $usuarios = admin_query_all(
        $conexion,
        "SELECT u.id_usuario, u.id_rol, u.rut, u.correo, u.estado_cuenta, u.fecha_creacion, r.nombre_rol,
            COALESCE(e.nombre, c.nombre, d.nombre, a.nombre, em.nombre_empresa, '') AS nombre,
            COALESCE(e.apellido, c.apellido, d.apellido, a.apellido, '') AS apellido,
            COALESCE(CONCAT(e.nombre, ' ', e.apellido), CONCAT(c.nombre, ' ', c.apellido), CONCAT(d.nombre, ' ', d.apellido), CONCAT(a.nombre, ' ', a.apellido), em.nombre_empresa, 'Usuario') AS nombre_completo,
            COALESCE(e.id_carrera, c.id_carrera, d.id_carrera, 0) AS id_carrera,
            COALESCE(ec.nombre_carrera, cc.nombre_carrera, dc.nombre_carrera, 'Sin carrera') AS carrera_nombre
        FROM usuario u
        LEFT JOIN rol r ON r.id_rol = u.id_rol
        LEFT JOIN estudiante e ON e.id_usuario = u.id_usuario
        LEFT JOIN coordinador c ON c.id_usuario = u.id_usuario
        LEFT JOIN directivo d ON d.id_usuario = u.id_usuario
        LEFT JOIN administrador a ON a.id_usuario = u.id_usuario
        LEFT JOIN empresa em ON em.id_usuario = u.id_usuario
        LEFT JOIN carrera ec ON ec.id_carrera = e.id_carrera
        LEFT JOIN carrera cc ON cc.id_carrera = c.id_carrera
        LEFT JOIN carrera dc ON dc.id_carrera = d.id_carrera
        WHERE u.id_institucion = " . (int) $idInstitucionActual . "
                    AND LOWER(TRIM(r.nombre_rol)) NOT IN ('administrador', 'superadministrador', 'super administrador')
        ORDER BY u.id_usuario DESC"
    );
}
?>

<?php if ($mensaje !== ''): ?>
    <div class="alert alert-<?= admin_e($tipoMensaje) ?> alert-dismissible fade show" role="alert">
        <?= admin_e($mensaje) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
    <div>
        <p class="text-muted mb-1">Institución actual</p>
        <h5 class="mb-0 text-primary"><?= admin_e($nombreInstitucionActual) ?></h5>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrear" <?= $idInstitucionActual <= 0 ? 'disabled' : '' ?>>
        <i class="bi bi-person-plus me-2"></i>Nuevo usuario
    </button>
</div>

<?php if ($idInstitucionActual <= 0): ?>
    <div class="alert alert-warning">No se pudo identificar la institución del administrador. La creación y edición de usuarios está deshabilitada.</div>
<?php endif; ?>

<div class="row g-3 mb-3 align-items-start">
    <div class="col-lg-4">
        <div class="card card-custom h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                    <div>
                        <h6 class="fw-bold mb-1">Importar usuarios desde Excel</h6>
                        <p class="text-muted small mb-0">Arrastra un archivo .xlsx, .xls o .csv y crea usuarios en lote para la institución actual.</p>
                    </div>
                </div>


                <form method="post" enctype="multipart/form-data" class="row g-3 align-items-end">
                    <input type="hidden" name="accion" value="importar">
                    <div class="col-12">
                        <div id="dropZone" class="border border-2 border-primary border-dashed rounded-3 p-4 text-center">
                            <input type="file" name="archivo" id="archivoImportacion" class="d-none" accept=".xlsx,.xls,.csv" required>
                            <label for="archivoImportacion" class="d-block mb-2 text-primary" style="cursor: pointer;">
                                <i class="bi bi-cloud-arrow-up fs-2"></i>
                                <div class="fw-semibold mt-2">Arrastra y suelta tu archivo aquí</div>
                                <div class="small text-muted">o haz clic para examinar</div>
                            </label>
                            <div id="archivoSeleccionado" class="small text-muted">Sin archivo seleccionado</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-upload me-2"></i>Importar usuarios
                        </button>
                    </div>
                </form>

                <?php if ($resultadoImportacion !== null): ?>
                    <div class="alert alert-<?= ($resultadoImportacion['correctos'] ?? 0) > 0 ? 'success' : 'warning' ?> mt-3 mb-0" role="alert">
                        <div class="fw-semibold">Resumen de importación</div>
                        <div class="small">Usuarios procesados: <?= (int) ($resultadoImportacion['total'] ?? 0) ?></div>
                        <div class="small">Creados correctamente: <?= (int) ($resultadoImportacion['correctos'] ?? 0) ?></div>
                        <?php if (!empty($resultadoImportacion['errores'])): ?>
                            <ul class="mb-0 mt-2 small">
                                <?php foreach ($resultadoImportacion['errores'] as $error): ?>
                                    <li><?= admin_e((string) $error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card card-custom h-100">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Carrera</th>
                        <th>Fecha creación</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($usuarios)): ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?= (int) $usuario['id_usuario'] ?></td>
                                <td><?= admin_e($usuario['nombre_completo']) ?></td>
                                <td><?= admin_e($usuario['correo']) ?></td>
                                <td><span class="badge bg-light text-dark"><?= admin_e($usuario['nombre_rol'] ?? 'Sin rol') ?></span></td>
                                <td><?= admin_e($usuario['carrera_nombre'] ?? 'Sin carrera') ?></td>
                                <td><?= admin_e((string) ($usuario['fecha_creacion'] ?? 'No disponible')) ?></td>
                                <td><span class="badge bg-<?= admin_badge_estado($usuario['estado_cuenta']) ?>"><?= admin_e($usuario['estado_cuenta']) ?></span></td>
                                <td class="d-flex gap-2 flex-wrap">
                                    <button
                                        class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEditar"
                                        data-id="<?= (int) $usuario['id_usuario'] ?>"
                                        data-nombre="<?= htmlspecialchars($usuario['nombre_completo'], ENT_QUOTES) ?>"
                                        data-nombre-usuario="<?= htmlspecialchars($usuario['nombre'], ENT_QUOTES) ?>"
                                        data-apellido-usuario="<?= htmlspecialchars($usuario['apellido'], ENT_QUOTES) ?>"
                                        data-correo="<?= htmlspecialchars($usuario['correo'], ENT_QUOTES) ?>"
                                        data-rut="<?= htmlspecialchars($usuario['rut'], ENT_QUOTES) ?>"
                                        data-rol="<?= (int) (($usuario['id_rol'] ?? 0)) ?>"
                                        data-carrera="<?= (int) (($usuario['id_carrera'] ?? 0)) ?>"
                                        data-estado="<?= htmlspecialchars($usuario['estado_cuenta'], ENT_QUOTES) ?>"
                                    >
                                        Editar
                                    </button>

                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="accion" value="estado">
                                        <input type="hidden" name="id_usuario" value="<?= (int) $usuario['id_usuario'] ?>">
                                        <input type="hidden" name="estado" value="<?= $usuario['estado_cuenta'] === 'activa' ? 'inactiva' : 'activa' ?>">
                                        <button class="btn btn-sm btn-outline-<?= $usuario['estado_cuenta'] === 'activa' ? 'secondary' : 'success' ?>" type="submit">
                                            <?= $usuario['estado_cuenta'] === 'activa' ? 'Desactivar' : 'Activar' ?>
                                        </button>
                                    </form>

                                    <form method="post" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar este usuario?');">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id_usuario" value="<?= (int) $usuario['id_usuario'] ?>">
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No hay usuarios registrados para esta institución.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

<div class="modal fade" id="modalCrear" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="accion" value="crear">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apellido</label>
                            <input type="text" name="apellido" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">RUT</label>
                            <input type="text" name="rut" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Correo</label>
                            <input type="email" name="correo" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contraseña</label>
                            <input type="password" name="contrasena" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirmar contraseña</label>
                            <input type="password" name="confirmar_contrasena" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Rol</label>
                            <select name="id_rol" id="crear_id_rol" class="form-select" required>
                                <option value="">Seleccione</option>
                                <?php foreach ($roles as $rol): ?>
                                    <option value="<?= (int) $rol['id_rol'] ?>"><?= admin_e($rol['nombre_rol']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Carrera</label>
                            <select name="id_carrera" id="crear_id_carrera" class="form-select">
                                <option value="0">Sin carrera</option>
                                <?php foreach ($carreras as $carrera): ?>
                                    <option value="<?= (int) $carrera['id_carrera'] ?>"><?= admin_e($carrera['nombre_carrera'] . ' (' . $carrera['codigo'] . ')') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Institución</label>
                            <input type="text" class="form-control" value="<?= admin_e($nombreInstitucionActual) ?>" readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Editar usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="accion" value="editar">
                    <input type="hidden" name="id_usuario" id="editar_id_usuario">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" id="editar_nombre" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apellido</label>
                            <input type="text" name="apellido" id="editar_apellido" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">RUT</label>
                            <input type="text" name="rut" id="editar_rut" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Correo</label>
                            <input type="email" name="correo" id="editar_correo" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nueva contraseña</label>
                            <input type="password" name="contrasena" id="editar_contrasena" class="form-control" placeholder="Dejar en blanco para mantener">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirmar contraseña</label>
                            <input type="password" name="confirmar_contrasena" id="editar_confirmar_contrasena" class="form-control" placeholder="Solo si cambias la contraseña">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Rol</label>
                            <select name="id_rol" id="editar_id_rol" class="form-select" required>
                                <option value="">Seleccione</option>
                                <?php foreach ($roles as $rol): ?>
                                    <option value="<?= (int) $rol['id_rol'] ?>"><?= admin_e($rol['nombre_rol']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Carrera</label>
                            <select name="id_carrera" id="editar_id_carrera" class="form-select">
                                <option value="0">Sin carrera</option>
                                <?php foreach ($carreras as $carrera): ?>
                                    <option value="<?= (int) $carrera['id_carrera'] ?>"><?= admin_e($carrera['nombre_carrera'] . ' (' . $carrera['codigo'] . ')') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estado</label>
                            <select name="estado_cuenta" id="editar_estado_cuenta" class="form-select">
                                <option value="activa">Activa</option>
                                <option value="inactiva">Inactiva</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Institución</label>
                            <input type="text" class="form-control" value="<?= admin_e($nombreInstitucionActual) ?>" readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const dropZone = document.getElementById('dropZone');
    const inputArchivo = document.getElementById('archivoImportacion');
    const archivoSeleccionado = document.getElementById('archivoSeleccionado');

    if (dropZone && inputArchivo && archivoSeleccionado) {
        ['dragenter', 'dragover'].forEach(function (evento) {
            dropZone.addEventListener(evento, function (e) {
                e.preventDefault();
                dropZone.classList.add('bg-light');
            });
        });

        ['dragleave', 'drop'].forEach(function (evento) {
            dropZone.addEventListener(evento, function (e) {
                e.preventDefault();
                dropZone.classList.remove('bg-light');
            });
        });

        dropZone.addEventListener('drop', function (e) {
            const archivos = e.dataTransfer?.files;
            if (archivos && archivos.length > 0) {
                inputArchivo.files = archivos;
                archivoSeleccionado.textContent = archivos[0].name;
            }
        });

        inputArchivo.addEventListener('change', function () {
            archivoSeleccionado.textContent = this.files && this.files[0] ? this.files[0].name : 'Sin archivo seleccionado';
        });
    }

    function ajustarCarreraSegunRol(selectRolId, selectCarreraId) {
        const rolSelect = document.getElementById(selectRolId);
        const carreraSelect = document.getElementById(selectCarreraId);
        if (!rolSelect || !carreraSelect) {
            return;
        }

        const rolTexto = rolSelect.options[rolSelect.selectedIndex]?.text || '';
        const requiereCarrera = ['estudiante', 'coordinador', 'directivo', 'director'].includes(rolTexto.trim().toLowerCase());
        carreraSelect.required = requiereCarrera;
        carreraSelect.disabled = false;
    }

    document.getElementById('crear_id_rol')?.addEventListener('change', function () {
        ajustarCarreraSegunRol('crear_id_rol', 'crear_id_carrera');
    });

    document.getElementById('editar_id_rol')?.addEventListener('change', function () {
        ajustarCarreraSegunRol('editar_id_rol', 'editar_id_carrera');
    });

    const modalEditar = document.getElementById('modalEditar');
    modalEditar.addEventListener('show.bs.modal', function (event) {
        const boton = event.relatedTarget;
        document.getElementById('editar_id_usuario').value = boton.getAttribute('data-id') || '';
        document.getElementById('editar_nombre').value = boton.getAttribute('data-nombre-usuario') || '';
        document.getElementById('editar_apellido').value = boton.getAttribute('data-apellido-usuario') || '';
        document.getElementById('editar_correo').value = boton.getAttribute('data-correo') || '';
        document.getElementById('editar_rut').value = boton.getAttribute('data-rut') || '';
        document.getElementById('editar_id_rol').value = boton.getAttribute('data-rol') || '';
        document.getElementById('editar_id_carrera').value = boton.getAttribute('data-carrera') || '0';
        document.getElementById('editar_estado_cuenta').value = boton.getAttribute('data-estado') || 'activa';
        document.getElementById('editar_contrasena').value = '';
        document.getElementById('editar_confirmar_contrasena').value = '';
        ajustarCarreraSegunRol('editar_id_rol', 'editar_id_carrera');
    });

    ajustarCarreraSegunRol('crear_id_rol', 'crear_id_carrera');
</script>
