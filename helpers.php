<?php

/**
 * Calcula un porcentaje de afinidad entre los requisitos de una oferta
 * y las habilidades de un estudiante basado en palabras clave.
 * 
 * @param string $requisitos Texto de requisitos de la oferta
 * @param string $habilidades Texto de habilidades del estudiante
 * @return int Porcentaje de 0 a 100
 */
/**
 * Algoritmo de Matching Profesional por Etiquetas (Tags)
 * Compara las IDs de competencias requeridas vs las que tiene el estudiante.
 */
function calcular_afinidad_tags($conexion, $id_estudiante, $id_oferta) {
    // 1. Obtener IDs de competencias que pide la oferta
    $sql_req = "SELECT id_competencia FROM oferta_competencias WHERE id_oferta = " . (int)$id_oferta;
    $res_req = mysqli_query($conexion, $sql_req);
    
    $requisitos = [];
    if ($res_req) {
        while($f = mysqli_fetch_assoc($res_req)) {
            $requisitos[] = $f['id_competencia'];
        }
    }

    // Si la oferta no tiene etiquetas definidas, devolvemos 0 (o podrías usar el fallback de texto)
    if (empty($requisitos)) {
        return 0;
    }

    // 2. Contar cuántas de esas tiene el alumno
    $ids_busqueda = implode(',', $requisitos);
    $sql_match = "SELECT COUNT(*) as total 
                  FROM estudiante_competencias 
                  WHERE id_estudiante = " . (int)$id_estudiante . " 
                  AND id_competencia IN ($ids_busqueda)";
    
    $res_match = mysqli_query($conexion, $sql_match);
    $coincidencias = 0;
    if ($res_match) {
        $data = mysqli_fetch_assoc($res_match);
        $coincidencias = (int)$data['total'];
    }

    // 3. Porcentaje simple: (coincidencias / total_requisitos) * 100
    return (int) round(($coincidencias / count($requisitos)) * 100);
}
