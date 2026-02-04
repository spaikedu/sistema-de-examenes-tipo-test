<?php
/**
 * Clase para gestionar el shortcode del sistema de exámenes
 */

class SistemaExamenes_Shortcode {
    
    private $database;
    
    public function __construct() {
        $this->database = new SistemaExamenes_Database();
        add_shortcode('sistema_examenes', array($this, 'render_shortcode'));
        add_action('wp_ajax_get_preguntas_bloque', array($this, 'ajax_get_preguntas_bloque'));
        add_action('wp_ajax_nopriv_get_preguntas_bloque', array($this, 'ajax_get_preguntas_bloque'));
        add_action('wp_ajax_iniciar_simulacro', array($this, 'ajax_iniciar_simulacro'));
        add_action('wp_ajax_nopriv_iniciar_simulacro', array($this, 'ajax_iniciar_simulacro'));
        add_action('wp_ajax_corregir_examen', array($this, 'ajax_corregir_examen'));
        add_action('wp_ajax_nopriv_corregir_examen', array($this, 'ajax_corregir_examen'));
    }
    
    /**
     * Renderizar el shortcode principal
     */
    public function render_shortcode($atts) {
        wp_enqueue_style('sistema-examenes-style');
        wp_enqueue_script('sistema-examenes-script');
        
        ob_start();
        ?>
        <div class="sistema-examenes-container">
            <div class="se-header">
                <h2>Sistema de Exámenes</h2>
                <p>Selecciona un bloque para practicar o realiza un simulacro completo</p>
            </div>
            
            <div class="se-menu-principal" id="se-menu-principal">
                <div class="se-bloques-grid">
                    <div class="se-bloque-card" data-bloque="bloque1">
                        <div class="se-bloque-icon">📚</div>
                        <h3>Bloque 1</h3>
                        <p>Practicar preguntas del Bloque 1</p>
                        <button class="se-btn se-btn-primary">Comenzar</button>
                    </div>
                    
                    <div class="se-bloque-card" data-bloque="bloque2">
                        <div class="se-bloque-icon">📖</div>
                        <h3>Bloque 2</h3>
                        <p>Practicar preguntas del Bloque 2</p>
                        <button class="se-btn se-btn-primary">Comenzar</button>
                    </div>
                    
                    <div class="se-bloque-card" data-bloque="bloque3">
                        <div class="se-bloque-icon">📝</div>
                        <h3>Bloque 3</h3>
                        <p>Practicar preguntas del Bloque 3</p>
                        <button class="se-btn se-btn-primary">Comenzar</button>
                    </div>
                    
                    <div class="se-bloque-card" data-bloque="bloque4">
                        <div class="se-bloque-icon">🔍</div>
                        <h3>Bloque 4</h3>
                        <p>Practicar preguntas del Bloque 4</p>
                        <button class="se-btn se-btn-primary">Comenzar</button>
                    </div>
                </div>
                
                <div class="se-simulacro-section">
                    <div class="se-simulacro-card">
                        <div class="se-simulacro-icon">🎯</div>
                        <h3>Simulacro de Examen</h3>
                        <p>80 preguntas aleatorias de todos los bloques + 2 supuestos prácticos</p>
                        <button class="se-btn se-btn-success se-btn-large" id="iniciar-simulacro">Iniciar Simulacro</button>
                    </div>
                </div>
            </div>
            
            <div class="se-examen-container" id="se-examen-container" style="display: none;">
                <div class="se-examen-header">
                    <div class="se-examen-info">
                        <span class="se-examen-titulo" id="se-examen-titulo"></span>
                        <span class="se-examen-progreso">
                            Pregunta <span id="pregunta-actual">1</span> de <span id="total-preguntas">1</span>
                        </span>
                    </div>
                    <div class="se-examen-tiempo">
                        <span id="tiempo-transcurrido">00:00</span>
                    </div>
                </div>
                
                <div class="se-pregunta-container" id="se-pregunta-container">
                    <!-- Las preguntas se cargarán aquí dinámicamente -->
                </div>
                
                <div class="se-examen-nav">
                    <button class="se-btn se-btn-secondary" id="anterior-pregunta" disabled>Anterior</button>
                    <button class="se-btn se-btn-primary" id="siguiente-pregunta">Siguiente</button>
                    <button class="se-btn se-btn-success" id="finalizar-examen" style="display: none;">Finalizar Examen</button>
                </div>
            </div>
            
            <div class="se-resultados-container" id="se-resultados-container" style="display: none;">
                <div class="se-resultados-header">
                    <h2>Resultados del Examen</h2>
                </div>
                
                <div class="se-resultados-stats">
                    <div class="se-stat-card">
                        <div class="se-stat-number" id="respuestas-correctas">0</div>
                        <div class="se-stat-label">Respuestas Correctas</div>
                    </div>
                    <div class="se-stat-card">
                        <div class="se-stat-number" id="respuestas-incorrectas">0</div>
                        <div class="se-stat-label">Respuestas Incorrectas</div>
                    </div>
                    <div class="se-stat-card">
                        <div class="se-stat-number" id="nota-final">0.00</div>
                        <div class="se-stat-label">Nota Final</div>
                    </div>
                    <div class="se-stat-card">
                        <div class="se-stat-number" id="tiempo-total">00:00</div>
                        <div class="se-stat-label">Tiempo Total</div>
                    </div>
                </div>
                
                <div class="se-revision-container" id="se-revision-container">
                    <!-- Las respuestas se mostrarán aquí -->
                </div>
                
                <div class="se-resultados-actions">
                    <button class="se-btn se-btn-primary" id="nuevo-examen">Nuevo Examen</button>
                    <button class="se-btn se-btn-secondary" id="volver-menu">Volver al Menú</button>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * AJAX: Obtener preguntas de un bloque
     */
    public function ajax_get_preguntas_bloque() {
        check_ajax_referer('sistema_examenes_nonce', 'nonce');
        
        $bloque = sanitize_text_field($_POST['bloque']);
        $preguntas = $this->database->get_preguntas_por_bloque($bloque, 20);
        
        wp_send_json_success(array(
            'preguntas' => $preguntas,
            'titulo' => 'Examen - ' . ucfirst(str_replace('bloque', 'Bloque ', $bloque))
        ));
    }
    
    /**
     * AJAX: Iniciar simulacro
     */
    public function ajax_iniciar_simulacro() {
        check_ajax_referer('sistema_examenes_nonce', 'nonce');
        
        $preguntas = $this->database->get_preguntas_simulacro(20);
        $supuestos = $this->database->get_supuestos_practicos(2);
        
        // Añadir preguntas de los supuestos
        $preguntas_supuestos = array();
        foreach ($supuestos as $supuesto) {
            $preguntas_supuesto = $this->database->get_preguntas_supuesto($supuesto->id);
            if ($preguntas_supuesto) {
                $preguntas_supuestos[] = array(
                    'supuesto' => $supuesto,
                    'preguntas' => $preguntas_supuesto
                );
            }
        }
        
        wp_send_json_success(array(
            'preguntas' => $preguntas,
            'supuestos' => $preguntas_supuestos,
            'titulo' => 'Simulacro de Examen'
        ));
    }
    
    /**
     * AJAX: Corregir examen
     */
    public function ajax_corregir_examen() {
        check_ajax_referer('sistema_examenes_nonce', 'nonce');
        
        $respuestas_usuario = $_POST['respuestas'];
        $preguntas_data = json_decode(stripslashes($_POST['preguntas_data']), true);
        $tiempo_total = sanitize_text_field($_POST['tiempo_total']);
        
        $correctas = 0;
        $incorrectas = 0;
        $resultados = array();
        
        foreach ($preguntas_data as $index => $pregunta) {
            $respuesta_usuario = isset($respuestas_usuario[$pregunta['id']]) ? $respuestas_usuario[$pregunta['id']] : '';
            $es_correcta = ($respuesta_usuario === $pregunta['respuesta_correcta']);
            
            if ($es_correcta) {
                $correctas++;
            } else {
                $incorrectas++;
            }
            
            $resultados[] = array(
                'pregunta' => $pregunta,
                'respuesta_usuario' => $respuesta_usuario,
                'es_correcta' => $es_correcta
            );
        }
        
        $total_preguntas = count($preguntas_data);
        $nota = $total_preguntas > 0 ? ($correctas / $total_preguntas) * 10 : 0;
        
        wp_send_json_success(array(
            'correctas' => $correctas,
            'incorrectas' => $incorrectas,
            'nota' => number_format($nota, 2),
            'tiempo_total' => $tiempo_total,
            'resultados' => $resultados
        ));
    }
}
