=== Sistema de Exámenes ===
Contributors: Edu
Tags: examenes, test, educacion, wordpress, plugin
Requires at least: 6.9
Tested up to: 6.9
Stable tag: 4.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Sistema completo de exámenes tipo test para WordPress con 5 bloques de preguntas y supuestos prácticos.

== Description ==

Sistema de Exámenes es un plugin completo para WordPress que permite crear y gestionar exámenes tipo test. Desarrollado por Edu para eduardomartinezmarin.es, este plugin ofrece una solución robusta para evaluaciones educativas.

Características principales:
* 5 bloques de preguntas (Bloque 1, 2, 3, 4 y Supuestos Prácticos)
* Sistema de supuestos prácticos con preguntas de los bloques 3 y 4
* Simulacro de examen con 80 preguntas aleatorias + 2 supuestos
* Interfaz de administración intuitiva
* Sistema de corrección automática
* Visualización de resultados con indicadores visuales
* Diseño responsive y moderno
* Compatible con WordPress 6.9+

== Installation ==

1. Sube la carpeta `sistema-examenes` al directorio `/wp-content/plugins/` de tu WordPress
2. Activa el plugin desde la página 'Plugins' en WordPress
3. El plugin creará automáticamente las tablas necesarias en la base de datos
4. Aparecerá un nuevo menú 'Sistema Exámenes' en el panel de administración

== Usage ==

### Administración
1. Accede a 'Sistema Exámenes' en el menú de administración
2. Añade preguntas usando el formulario correspondiente
3. Organiza las preguntas por bloques (1, 2, 3, 4)
4. Crea supuestos prácticos con sus preguntas correspondientes

### Front-end
1. Crea una nueva página o entrada en WordPress
2. Añade el shortcode `[sistema_examenes]`
3. Los usuarios podrán:
   - Practicar por bloques individuales
   - Realizar simulacros completos
   - Ver resultados detallados con corrección

### Shortcode
Usa `[sistema_examenes]` para mostrar el sistema de exámenes en cualquier página o entrada.

== Frequently Asked Questions ==

= ¿Cuántas preguntas puedo añadir? =
No hay límite en el número de preguntas que puedes añadir a cada bloque.

= ¿Puedo personalizar el diseño? =
Sí, el plugin incluye estilos CSS personalizados que puedes modificar según tus necesidades.

= ¿Funciona con el último WordPress? =
Sí, el plugin es compatible con WordPress 6.9 y versiones superiores.

= ¿Los exámenes se guardan en la base de datos? =
Las preguntas y supuestos se guardan, pero los resultados de los usuarios no se almacenan permanentemente.

== Screenshots ==

1. Panel de administración principal
2. Formulario de añadir pregunta
3. Interfaz del examen en el front-end
4. Página de resultados

== Changelog ==

= 1.0.0 =
* Versión inicial del plugin
* Sistema completo de exámenes tipo test
* 5 bloques de preguntas
* Supuestos prácticos
* Simulacro de examen
* Sistema de corrección automática
* Interfaz de administración
* Diseño responsive

== Upgrade Notice ==

= 1.0.0 =
Versión inicial del plugin.

== A brief Markdown Example ==

Uso básico del shortcode:

```
[sistema_examenes]
```

Esto mostrará el sistema completo de exámenes en tu página.

== Developer Information ==

* **Author:** Edu
* **Author URI:** https://eduardomartinezmarin.es
* **Plugin URI:** https://eduardomartinezmarin.es
* **WordPress Version:** 6.9+
* **PHP Version:** 7.4+

== Database Tables ==

El plugin crea las siguientes tablas:
* `wp_se_preguntas` - Almacena las preguntas de los bloques 1-4
* `wp_se_supuestos` - Almacena los supuestos prácticos
* `wp_se_preguntas_supuestos` - Almacena las preguntas de los supuestos

== Hooks & Filters ==

El plugin utiliza los siguientes hooks:
* `init` - Inicialización del plugin
* `admin_menu` - Creación del menú de administración
* `wp_enqueue_scripts` - Carga de scripts y estilos
* `wp_ajax_*` - Manejo de peticiones AJAX

== Security ==

* Todas las entradas de usuario son validadas y sanitizadas
* Uso de nonces para seguridad en peticiones AJAX
* Permisos adecuados para funciones de administración
* Escapado de salida para prevenir XSS

== License ==

Este plugin se distribuye bajo la licencia GPLv2 o posterior.

== Support ==

Para soporte técnico, visita: https://eduardomartinezmarin.es

== Donations ==

Si te gusta este plugin, considera hacer una donación en https://eduardomartinezmarin.es
