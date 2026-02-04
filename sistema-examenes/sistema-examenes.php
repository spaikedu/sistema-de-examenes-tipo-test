<?php
/**
 * Plugin Name: Sistema de Exámenes
 * Plugin URI: https://eduardomartinezmarin.es
 * Description: Sistema de exámenes tipo test con 5 bloques y supuestos prácticos
 * Version: 1.0.0
 * Author: Edu
 * Author URI: https://eduardomartinezmarin.es
 * License: GPL v2 or later
 * Text Domain: sistema-examenes
 * Domain Path: /languages
 * Requires at least: 6.9
 * Tested up to: 6.9
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes
define('SISTEMA_EXAMENES_VERSION', '1.0.0');
define('SISTEMA_EXAMENES_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SISTEMA_EXAMENES_PLUGIN_URL', plugin_dir_url(__FILE__));

// Clase principal del plugin
class SistemaExamenes {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Incluir archivos necesarios
        include_once(SISTEMA_EXAMENES_PLUGIN_DIR . 'includes/database.php');
        include_once(SISTEMA_EXAMENES_PLUGIN_DIR . 'includes/admin.php');
        include_once(SISTEMA_EXAMENES_PLUGIN_DIR . 'includes/shortcode.php');
        
        // Inicializar clases
        new SistemaExamenes_Database();
        new SistemaExamenes_Admin();
        new SistemaExamenes_Shortcode();
        
        // Cargar estilos y scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
    }
    
    public function activate() {
        // Crear tablas de la base de datos
        include_once(SISTEMA_EXAMENES_PLUGIN_DIR . 'includes/database.php');
        $db = new SistemaExamenes_Database();
        $db->create_tables();
        
        // Opciones por defecto
        add_option('sistema_examenes_version', SISTEMA_EXAMENES_VERSION);
    }
    
    public function deactivate() {
        // Limpiar si es necesario
        flush_rewrite_rules();
    }
    
    public function enqueue_scripts() {
        wp_enqueue_style(
            'sistema-examenes-style',
            SISTEMA_EXAMENES_PLUGIN_URL . 'assets/css/style.css',
            array(),
            SISTEMA_EXAMENES_VERSION . '.' . time()
        );
        
        wp_enqueue_script(
            'sistema-examenes-script',
            SISTEMA_EXAMENES_PLUGIN_URL . 'assets/js/script.js',
            array('jquery'),
            SISTEMA_EXAMENES_VERSION . '.' . time(),
            true
        );
        
        wp_localize_script('sistema-examenes-script', 'sistemaExamenesAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('sistema_examenes_nonce')
        ));
    }
    
    public function admin_enqueue_scripts($hook) {
        if (strpos($hook, 'sistema-examenes') !== false) {
            wp_enqueue_style(
                'sistema-examenes-admin-style',
                SISTEMA_EXAMENES_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                SISTEMA_EXAMENES_VERSION
            );
            
            wp_enqueue_script(
                'sistema-examenes-admin-script',
                SISTEMA_EXAMENES_PLUGIN_URL . 'assets/js/admin.js',
                array('jquery'),
                SISTEMA_EXAMENES_VERSION,
                true
            );
        }
    }
}

// Iniciar el plugin
new SistemaExamenes();
