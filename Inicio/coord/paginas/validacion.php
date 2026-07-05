<header class="mb-5">
    <h2 class="mb-1 fw-bold">Validación de Documentos</h2>
    <p class="text-muted">Revisa y aprueba la documentación obligatoria de tus estudiantes para autorizar sus prácticas.</p>
</header>

<!-- AVISO DE MOCKUP -->
<div class="alert alert-warning border-0 border-start border-warning border-4 shadow-sm mb-4">
    <i class="bi bi-tools me-2"></i> <strong>Modo Interfaz Gráfica (Mockup):</strong> Esta pantalla es solo visual para estructurar el diseño. Los datos mostrados son ejemplos y los botones no están conectados aún a la base de datos.
</div>

<div class="card card-custom p-4 bg-white shadow-sm">
    <h5 class="fw-bold mb-4 text-primary">Estudiantes Pendientes de Revisión</h5>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Estudiante</th>
                    <th class="text-center">Cédula Identidad</th>
                    <th class="text-center">Cert. Alumno Regular</th>
                    <th class="text-center">Curriculum Vitae</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                
                <!-- Ejemplo 1: Todo subido, listo para aprobar -->
                <tr>
                    <td>
                        <div class="fw-bold text-dark">Valentina Ramos</div>
                        <div class="text-muted small">Nivel 8</div>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-success mb-1"><i class="bi bi-check-circle me-1"></i> Subido</span><br>
                        <a href="#" class="small text-decoration-none"><i class="bi bi-search"></i> Ver PDF</a>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-success mb-1"><i class="bi bi-check-circle me-1"></i> Subido</span><br>
                        <a href="#" class="small text-decoration-none"><i class="bi bi-search"></i> Ver PDF</a>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-success mb-1"><i class="bi bi-check-circle me-1"></i> Subido</span><br>
                        <a href="#" class="small text-decoration-none"><i class="bi bi-search"></i> Ver PDF</a>
                    </td>
                    <td>
                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">Pendiente Revisión</span>
                    </td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-success shadow-sm" title="Aprobar documentos"><i class="bi bi-check-lg"></i> Aprobar</button>
                        <button class="btn btn-sm btn-outline-danger shadow-sm" title="Rechazar por documentos erróneos"><i class="bi bi-x-lg"></i> Rechazar</button>
                    </td>
                </tr>

                <!-- Ejemplo 2: Le falta un documento -->
                <tr>
                    <td>
                        <div class="fw-bold text-dark">Matías Silva</div>
                        <div class="text-muted small">Nivel 7</div>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-success mb-1"><i class="bi bi-check-circle me-1"></i> Subido</span><br>
                        <a href="#" class="small text-decoration-none text-muted">Ver PDF</a>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-danger mb-1"><i class="bi bi-exclamation-circle me-1"></i> Falta</span><br>
                        <span class="small text-muted">-</span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-success mb-1"><i class="bi bi-check-circle me-1"></i> Subido</span><br>
                        <a href="#" class="small text-decoration-none text-muted">Ver PDF</a>
                    </td>
                    <td>
                        <span class="badge bg-danger px-3 py-2 rounded-pill">Incompleto</span>
                    </td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-success opacity-50 shadow-sm" style="cursor:not-allowed;" title="No se puede aprobar si falta documentación"><i class="bi bi-check-lg"></i> Aprobar</button>
                        <button class="btn btn-sm btn-outline-secondary shadow-sm" title="Enviar recordatorio automático al alumno"><i class="bi bi-bell"></i> Recordar</button>
                    </td>
                </tr>

                <!-- Ejemplo 3: Documentos Aprobados (Historial rápido) -->
                <tr class="table-light">
                    <td>
                        <div class="fw-bold text-muted">Camila Fernández</div>
                        <div class="text-muted small">Nivel 9</div>
                    </td>
                    <td class="text-center"><span class="badge border border-success text-success"><i class="bi bi-check"></i></span></td>
                    <td class="text-center"><span class="badge border border-success text-success"><i class="bi bi-check"></i></span></td>
                    <td class="text-center"><span class="badge border border-success text-success"><i class="bi bi-check"></i></span></td>
                    <td>
                        <span class="badge bg-success px-3 py-2 rounded-pill"><i class="bi bi-shield-check me-1"></i>Validado</span>
                    </td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-secondary shadow-sm" title="Ver historial"><i class="bi bi-eye"></i> Detalles</button>
                    </td>
                </tr>

            </tbody>
        </table>
    </div>

</div>
