# Sistema de Exámenes WordPress Plugin

Un plugin completo de WordPress para crear y gestionar sistemas de exámenes tipo test con 5 bloques de preguntas y supuestos prácticos.

## 🚀 Características

- ✅ **5 Bloques de preguntas**: Bloque 1, 2, 3, 4 y Supuestos Prácticos
- ✅ **Gestión completa**: Panel de administración intuitivo para añadir/editar preguntas
- ✅ **Supuestos prácticos**: Sistema con enunciado, imagen y 20 preguntas por supuesto
- ✅ **Simulacro de examen**: 80 preguntas aleatorias + 2 supuestos prácticos
- ✅ **Corrección automática**: Sistema de evaluación instantánea
- ✅ **Resultados detallados**: Visualización con indicadores visuales (verde/rojo)
- ✅ **Diseño responsive**: Compatible con móviles y tablets
- ✅ **WordPress 6.9+**: Totalmente compatible con las últimas versiones

## 📋 Requisitos

- WordPress 6.9 o superior
- PHP 7.4 o superior
- MySQL 5.6 o superior

## 🛠️ Instalación

1. Descarga el plugin
2. Sube la carpeta `sistema-examenes` a `/wp-content/plugins/`
3. Activa el plugin desde el panel de WordPress
4. Accede a "Sistema Exámenes" en el menú de administración

## 📖 Uso

### Administración
1. Ve a **Sistema Exámenes** → **Preguntas** para añadir preguntas por bloques
2. Ve a **Sistema Exámenes** → **Supuestos** para gestionar supuestos prácticos
3. Cada pregunta debe tener 4 opciones con solo 1 respuesta correcta

### Front-end
Añade el shortcode `[sistema_examenes]` en cualquier página o entrada para mostrar el sistema de exámenes.

Los usuarios podrán:
- Practicar por bloques individuales
- Realizar simulacros completos
- Ver resultados detallados con corrección

## 🗂️ Estructura del Plugin

```
sistema-examenes/
├── sistema-examenes.php          # Archivo principal
├── includes/
│   ├── database.php              # Gestión de base de datos
│   ├── admin.php                 # Interfaz de administración
│   └── shortcode.php             # Sistema de front-end
├── assets/
│   ├── css/
│   │   ├── style.css            # Estilos front-end
│   │   └── admin.css            # Estilos administración
│   └── js/
│       ├── script.js            # JavaScript front-end
│       └── admin.js             # JavaScript administración
├── readme.txt                    # Documentación WordPress
└── README.md                     # Este archivo
```

## 🗄️ Base de Datos

El plugin crea las siguientes tablas:
- `wp_se_preguntas` - Almacena las preguntas de los bloques 1-4
- `wp_se_supuestos` - Almacena los supuestos prácticos
- `wp_se_preguntas_supuestos` - Almacena las preguntas de los supuestos

## 🎨 Personalización

### CSS
Puedes personalizar los estilos modificando los archivos:
- `assets/css/style.css` - Estilos del front-end
- `assets/css/admin.css` - Estilos del panel de administración

### Funcionalidades
El plugin está desarrollado con código limpio y bien documentado, facilitando la extensión de funcionalidades.

## 🔧 Hooks y Filtros

El plugin utiliza los siguientes hooks de WordPress:
- `init` - Inicialización del plugin
- `admin_menu` - Creación del menú de administración
- `wp_enqueue_scripts` - Carga de scripts y estilos
- `wp_ajax_*` - Manejo de peticiones AJAX

## 🛡️ Seguridad

- Todas las entradas de usuario son validadas y sanitizadas
- Uso de nonces para seguridad en peticiones AJAX
- Permisos adecuados para funciones de administración
- Escapado de salida para prevenir XSS

## 📝 Licencia

Este plugin se distribuye bajo la licencia GPLv2 o posterior.

## 👤 Autor

- **Desarrollado por**: Edu
- **Web**: https://eduardomartinezmarin.es
- **Versión**: 1.0.0

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Por favor, sigue los estándares de codificación de WordPress.

## 📞 Soporte

Para soporte técnico, visita: https://eduardomartinezmarin.es

## 🔄 Actualizaciones

La versión 1.0.0 incluye:
- Sistema completo de exámenes tipo test
- 5 bloques de preguntas
- Supuestos prácticos
- Simulacro de examen
- Sistema de corrección automática
- Interfaz de administración
- Diseño responsive

---

**Nota**: Este plugin fue desarrollado específicamente para satisfacer las necesidades de un sistema de exámenes educativo con bloques temáticos y supuestos prácticos.
