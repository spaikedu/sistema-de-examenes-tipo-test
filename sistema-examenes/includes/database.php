<?php
/**
 * Clase para gestionar la base de datos del sistema de exámenes
 */

class SistemaExamenes_Database {
    
    private $table_preguntas;
    private $table_supuestos;
    
    public function __construct() {
        global $wpdb;
        $this->table_preguntas = $wpdb->prefix . 'se_preguntas';
        $this->table_supuestos = $wpdb->prefix . 'se_supuestos';
    }
    
    /**
     * Crear las tablas necesarias para el plugin
     */
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Tabla de preguntas
        $sql_preguntas = "CREATE TABLE $this->table_preguntas (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            bloque varchar(20) NOT NULL,
            enunciado text NOT NULL,
            imagen varchar(255) DEFAULT NULL,
            respuesta_a text NOT NULL,
            respuesta_b text NOT NULL,
            respuesta_c text NOT NULL,
            respuesta_d text NOT NULL,
            respuesta_correcta varchar(1) NOT NULL,
            activo tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_bloque (bloque),
            KEY idx_activo (activo)
        ) $charset_collate;";
        
        // Tabla de supuestos prácticos
        $sql_supuestos = "CREATE TABLE $this->table_supuestos (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            titulo varchar(255) NOT NULL,
            enunciado text NOT NULL,
            imagen varchar(255) DEFAULT NULL,
            bloque_origen varchar(20) NOT NULL,
            activo tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_bloque_origen (bloque_origen),
            KEY idx_activo (activo)
        ) $charset_collate;";
        
        // Crear tablas
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_preguntas);
        dbDelta($sql_supuestos);
        
        // Crear tabla de preguntas de supuestos
        $table_preguntas_supuestos = $wpdb->prefix . 'se_preguntas_supuestos';
        $sql_preguntas_supuestos = "CREATE TABLE $table_preguntas_supuestos (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            supuesto_id bigint(20) unsigned NOT NULL,
            pregunta text NOT NULL,
            respuesta_a text NOT NULL,
            respuesta_b text NOT NULL,
            respuesta_c text NOT NULL,
            respuesta_d text NOT NULL,
            respuesta_correcta varchar(1) NOT NULL,
            orden int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_supuesto_id (supuesto_id),
            FOREIGN KEY (supuesto_id) REFERENCES $this->table_supuestos(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        dbDelta($sql_preguntas_supuestos);
    }
    
    /**
     * Obtener preguntas por bloque
     */
    public function get_preguntas_por_bloque($bloque, $limit = null) {
        global $wpdb;
        
        $query = $wpdb->prepare(
            "SELECT * FROM $this->table_preguntas WHERE bloque = %s AND activo = 1 ORDER BY RAND()",
            $bloque
        );
        
        if ($limit) {
            $query .= " LIMIT " . intval($limit);
        }
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Obtener preguntas aleatorias para simulacro
     */
    public function get_preguntas_simulacro($cantidad_por_bloque = 20) {
        global $wpdb;
        $preguntas = array();
        
        $bloques = array('bloque1', 'bloque2', 'bloque3', 'bloque4');
        
        foreach ($bloques as $bloque) {
            $query = $wpdb->prepare(
                "SELECT * FROM $this->table_preguntas WHERE bloque = %s AND activo = 1 ORDER BY RAND() LIMIT %d",
                $bloque,
                $cantidad_por_bloque
            );
            $preguntas_bloque = $wpdb->get_results($query);
            $preguntas = array_merge($preguntas, $preguntas_bloque);
        }
        
        // Mezclar todas las preguntas
        shuffle($preguntas);
        
        return $preguntas;
    }
    
    /**
     * Obtener supuestos prácticos
     */
    public function get_supuestos_practicos($limit = 2) {
        global $wpdb;
        
        $query = $wpdb->prepare(
            "SELECT * FROM $this->table_supuestos WHERE activo = 1 ORDER BY RAND() LIMIT %d",
            $limit
        );
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Obtener preguntas de un supuesto práctico
     */
    public function get_preguntas_supuesto($supuesto_id) {
        global $wpdb;
        $table_preguntas_supuestos = $wpdb->prefix . 'se_preguntas_supuestos';
        
        $query = $wpdb->prepare(
            "SELECT * FROM $table_preguntas_supuestos WHERE supuesto_id = %d ORDER BY orden ASC",
            $supuesto_id
        );
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Insertar pregunta
     */
    public function insertar_pregunta($datos) {
        global $wpdb;
        
        $resultado = $wpdb->insert(
            $this->table_preguntas,
            array(
                'bloque' => $datos['bloque'],
                'enunciado' => $datos['enunciado'],
                'imagen' => isset($datos['imagen']) ? $datos['imagen'] : null,
                'respuesta_a' => $datos['respuesta_a'],
                'respuesta_b' => $datos['respuesta_b'],
                'respuesta_c' => $datos['respuesta_c'],
                'respuesta_d' => $datos['respuesta_d'],
                'respuesta_correcta' => $datos['respuesta_correcta'],
                'activo' => isset($datos['activo']) ? $datos['activo'] : 1
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d')
        );
        
        return $resultado ? $wpdb->insert_id : false;
    }
    
    /**
     * Insertar supuesto práctico
     */
    public function insertar_supuesto($datos) {
        global $wpdb;
        
        $resultado = $wpdb->insert(
            $this->table_supuestos,
            array(
                'titulo' => $datos['titulo'],
                'enunciado' => $datos['enunciado'],
                'imagen' => isset($datos['imagen']) ? $datos['imagen'] : null,
                'bloque_origen' => $datos['bloque_origen'],
                'activo' => isset($datos['activo']) ? $datos['activo'] : 1
            ),
            array('%s', '%s', '%s', '%s', '%d')
        );
        
        return $resultado ? $wpdb->insert_id : false;
    }
    
    /**
     * Insertar preguntas de supuesto práctico
     */
    public function insertar_preguntas_supuesto($supuesto_id, $preguntas) {
        global $wpdb;
        $table_preguntas_supuestos = $wpdb->prefix . 'se_preguntas_supuestos';
        
        foreach ($preguntas as $index => $pregunta) {
            $wpdb->insert(
                $table_preguntas_supuestos,
                array(
                    'supuesto_id' => $supuesto_id,
                    'pregunta' => $pregunta['pregunta'],
                    'respuesta_a' => $pregunta['respuesta_a'],
                    'respuesta_b' => $pregunta['respuesta_b'],
                    'respuesta_c' => $pregunta['respuesta_c'],
                    'respuesta_d' => $pregunta['respuesta_d'],
                    'respuesta_correcta' => $pregunta['respuesta_correcta'],
                    'orden' => $index + 1
                ),
                array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d')
            );
        }
        
        return true;
    }
    
    /**
     * Obtener todas las preguntas para administración
     */
    public function get_todas_preguntas($bloque = null, $limit = 50, $offset = 0) {
        global $wpdb;
        
        $where = "1=1";
        if ($bloque) {
            $where .= $wpdb->prepare(" AND bloque = %s", $bloque);
        }
        
        $query = "SELECT * FROM $this->table_preguntas WHERE $where ORDER BY created_at DESC LIMIT %d OFFSET %d";
        
        return $wpdb->get_results($wpdb->prepare($query, $limit, $offset));
    }
    
    /**
     * Obtener todos los supuestos para administración
     */
    public function get_todos_supuestos($limit = 50, $offset = 0) {
        global $wpdb;
        
        $query = "SELECT * FROM $this->table_supuestos ORDER BY created_at DESC LIMIT %d OFFSET %d";
        
        return $wpdb->get_results($wpdb->prepare($query, $limit, $offset));
    }
    
    /**
     * Contar preguntas
     */
    public function contar_preguntas($bloque = null) {
        global $wpdb;
        
        $where = "1=1";
        if ($bloque) {
            $where .= $wpdb->prepare(" AND bloque = %s", $bloque);
        }
        
        return $wpdb->get_var("SELECT COUNT(*) FROM $this->table_preguntas WHERE $where");
    }
    
    /**
     * Contar supuestos
     */
    public function contar_supuestos() {
        global $wpdb;
        
        return $wpdb->get_var("SELECT COUNT(*) FROM $this->table_supuestos");
    }
}
