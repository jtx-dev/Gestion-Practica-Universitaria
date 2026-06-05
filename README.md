# Gestion-Practica-Universitaria
Sistema de gestión de práctica y empleabilidad
Definición del problema:

El problema radica en la gestión ineficiente de las prácticas profesionales debido al uso de herramientas dispersas, lo que dificulta el seguimiento, la visibilidad de oportunidades y la obtención de datos. Por ello, se requiere desarrollar una plataforma web que centralice y optimice todo el proceso.

## Módulos del sistema:

### Módulo de gestión base.
1. Registro, autenticación e inicio de sesion de usuarios (Estudiantes, coordinadores, empresa, director).
2. Control de acceso según roles (permisos por alcance de uso entre los roles).
3. Gestión de perfiles (Eliminar, visualizar, actualizar).

### Módulo de gestión de personas. 
1. Asignación de coordinador y directivo a un estudiante (tomando en cuenta de que el directivo sería el jefe/a de la carrera respectiva).

### Módulo de asignación de estudiantes.
1. Visualización de estudiantes postulantes.
2. Control de asignación de estudiantes a una oferta de práctica (publicada por empresa y asignada por coordinador).

### Módulo de gestión de prácticas: 
1. Gestión de ofertas por empresa.
2. Publicación de ofertas por empresa.
3. Visualización de ofertas publicadas por cada empresa (Vista estudiante y vista coordinador).
4. Postulación de estudiante por CV.
5. Registrar avances de práctica (estudiante se encarga de subir el avance de practica)
6. Creación de acta de cierre de práctica.
7. Sistema de Notificaciones: Alertas vía correo electrónico (o WhatsApp) para avisar sobre cambios de estado o evaluaciones pendientes.

### Módulo de recomendación de estudiantes por perfil
1. Matching entre estudiantes y oferta laboral de la empresa.
2. Visualización de la recomendación de estudiantes para el coordinador de la práctica (estudiantes que tienen matching con la oferta de la empresa) según
3. habilidades, carrera y nivel de avance curricular.

## Roles del sistema y sus respectivos permisos:

- Estudiante: Buscar ofertas laborales, postular con CV, registrar, evaluar la empresa y visualizar progreso de la práctica.
- Empresa: Ofrecer ofertas laborales, proceso de selección y evaluación al desempeño
- Coordinador: Aprobar y asignar practicantes, realizar el seguimiento a la práctica, completar la evaluación académica
- Directivo: Visualización global y permiso de exportación de datos
- Super Administrador: Crear nuevas instituciones
- Administrador: Administrar su propia institución en conjunto con su configuración y personalización.
