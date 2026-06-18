# Gestion-Practica-Universitaria

Sistema web para centralizar la gestión de prácticas profesionales, asignaciones académicas y seguimiento institucional.

## Badges

[![Estado](https://img.shields.io/badge/status-en%20desarrollo-orange)](#)
[![Versión](https://img.shields.io/badge/version-1.0.0-blue)](#)

## Introducción

Gestion-Practica-Universitaria es una plataforma desarrollada en PHP y MySQL para resolver la fragmentación en la administración de prácticas profesionales. El sistema centraliza el ciclo completo: autenticación, administración institucional, asignación de personas clave, publicación de ofertas, postulación de estudiantes, seguimiento de prácticas y generación de reportes.

El objetivo principal es reducir el uso de herramientas dispersas y entregar trazabilidad, control por roles y una mejor visibilidad del proceso académico y administrativo.

## Características principales

- Autenticación y redirección por rol.
- Gestión de usuarios, roles, carreras e instituciones.
- Asignación de coordinador y directivo a estudiantes.
- Administración de ofertas de práctica por empresa.
- Postulación de estudiantes con CV.
- Seguimiento de avances, evaluaciones y cierre de práctica.
- Paneles diferenciados para administrador, coordinador, directivo, estudiante, empresa y superadministrador.
- Registro de auditoría para acciones clave del sistema.
- Estructura preparada para reportes y exportación de datos.
- Validación por institución para evitar cruces de información entre organizaciones.

## Tecnologías utilizadas

| Tecnología | Uso |
| --- | --- |
| PHP | Lógica de servidor y renderizado de vistas |
| MySQL / MariaDB | Persistencia de datos |
| HTML5 | Estructura de interfaces |
| CSS3 | Estilos visuales personalizados |
| JavaScript | Interacción en formularios y modales |
| Bootstrap 5 | Componentes UI y layout responsive |
| Bootstrap Icons | Iconografía del sistema |
| XAMPP | Entorno de desarrollo local recomendado |

## Instalación

### Requisitos previos

- PHP 8.x o superior.
- MySQL o MariaDB.
- Apache o un servidor web compatible.
- XAMPP, WAMP o Laragon para desarrollo local.
- Git para clonar el repositorio.

### Paso a paso

1. Clona el repositorio en tu entorno local.

```bash
git clone [URL_DEL_REPOSITORIO]
```

2. Copia el proyecto en el directorio web de tu servidor local.

```bash
htdocs/Gestion-Practica-Universitaria-1
```

3. Crea la base de datos `sgppe` en MySQL o MariaDB.

```sql
CREATE DATABASE sgppe CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
```

4. Importa el script SQL del proyecto.

```bash
mysql -u root -p sgppe < [ruta_al_archivo_sql]
```

Si prefieres hacerlo desde phpMyAdmin, selecciona la base de datos `sgppe` y usa la opción de importación para cargar el archivo `.sql`.

5. Verifica la configuración de conexión.

El archivo actual de conexión es [conexion.php](conexion.php). Ajusta los valores según tu entorno local o migra esos datos a variables de entorno si quieres endurecer la configuración.

```php
$conexion = mysqli_connect("localhost", "root", "", "sgppe");
```

6. Inicia Apache y MySQL desde XAMPP.

7. Abre el proyecto en tu navegador.

```text
http://localhost/Gestion-Practica-Universitaria-1/Inicio/inicio.php
```

### Dependencias de entorno

Actualmente el proyecto no depende de `npm`, `pip` ni contenedores Docker para funcionar. Si deseas modernizar el despliegue, puedes agregar un entorno Docker o un gestor de dependencias frontend más adelante.

## Uso

### Acceso al sistema

1. Inicia sesión desde la pantalla pública.
2. El sistema redirige al panel correspondiente según el rol del usuario.
3. Gestiona usuarios, ofertas, asignaciones y reportes desde el módulo asignado.

### Ejemplo de flujo de administración

```text
Administrador -> Usuarios -> Crear usuario
Administrador -> Asignaciones -> Crear o editar asignación
Administrador -> Roles -> Administrar catálogo de roles
```

### Ejemplo de operación sobre la base de datos

```sql
SELECT u.id_usuario, u.correo, r.nombre_rol
FROM usuario u
INNER JOIN rol r ON r.id_rol = u.id_rol
WHERE u.id_institucion = 5;
```

### Ejemplo de estructura de acceso por rol

```text
Estudiante: postular y revisar seguimiento de práctica
Coordinador: revisar estudiantes, ofertas y asignaciones
Directivo: visualizar indicadores y reportes
Empresa: publicar ofertas y revisar postulaciones
Superadministrador: crear instituciones
Administrador: gestionar su institución
```

## Configuración / Variables de entorno

El proyecto actualmente usa conexión directa en `conexion.php`. Si decides mover la configuración a un archivo `.env`, estas son las variables sugeridas:

```env
APP_NAME="Gestion-Practica-Universitaria"
APP_ENV=local
APP_URL=http://localhost/Gestion-Practica-Universitaria-1

DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=sgppe
DB_USERNAME=root
DB_PASSWORD=

SESSION_TIMEOUT_MINUTES=30
MAIL_HOST=[Insertar host SMTP aquí]
MAIL_PORT=[Insertar puerto SMTP aquí]
MAIL_USERNAME=[Insertar usuario SMTP aquí]
MAIL_PASSWORD=[Insertar contraseña SMTP aquí]
MAIL_FROM_ADDRESS=[Insertar correo remitente aquí]
MAIL_FROM_NAME="SGPPE"
```

Si tu instalación no usa `.env`, puedes mantener la configuración en [conexion.php](conexion.php) y documentar esos valores internamente.

## Roadmap

- Normalización completa de nombres de roles en todo el sistema.
- Exportación de reportes en PDF y Excel.
- Notificaciones automáticas por correo.
- Panel de indicadores con métricas por institución.
- Mejoras en la trazabilidad de asignaciones y evaluaciones.
- Implementación opcional de variables de entorno para la conexión a la base de datos.
- Contenerización con Docker para facilitar despliegue y portabilidad.

## Contribución

1. Crea una rama a partir de `main` o de la rama de integración acordada.
2. Mantén los cambios enfocados en un solo objetivo por pull request.
3. Usa mensajes de commit descriptivos.
4. Verifica que el cambio no rompa la lógica por institución ni el flujo de autenticación.
5. Abre un pull request con resumen, alcance y capturas si aplica.

Flujo sugerido:

```bash
git checkout -b feature/nombre-de-la-mejora
git add .
git commit -m "Describe el cambio"
git push origin feature/nombre-de-la-mejora
```

## Información del proyecto

- Lenguaje principal: PHP.
- Base de datos: MySQL / MariaDB.
- Frontend: HTML, CSS y JavaScript con Bootstrap 5.
- Tipo de aplicación: sistema web monolítico con vistas por rol.
- Contexto funcional: gestión de prácticas universitarias con control institucional.
