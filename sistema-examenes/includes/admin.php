<?php
/**
 * Clase para gestionar la interfaz de administración
 */

class SistemaExamenes_Admin {
    
    private $database;
    
    public function __construct() {
        $this->database = new SistemaExamenes_Database();
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'handle_form_submissions'));
    }
    
    /**
     * Añadir menú de administración
     */
    public function add_admin_menu() {
        add_menu_page(
            'Sistema de Exámenes',
            'Sistema Exámenes',
            'manage_options',
            'sistema-examenes',
            array($this, 'dashboard_page'),
            'dashicons-clipboard',
            30
        );
        
        add_submenu_page(
            'sistema-examenes',
            'Gestionar Preguntas',
            'Preguntas',
            'manage_options',
            'sistema-examenes-preguntas',
            array($this, 'preguntas_page')
        );
        
        add_submenu_page(
            'sistema-examenes',
            'Gestionar Supuestos',
            'Supuestos Prácticos',
            'manage_options',
            'sistema-examenes-supuestos',
            array($this, 'supuestos_page')
        );
    }
    
    /**
     * Página principal del dashboard
     */
    public function dashboard_page() {
        ?>
        <div class="wrap">
            <h1>Sistema de Exámenes</h1>
            <div class="welcome-panel">
                <div class="welcome-panel-content">
                    <h2>Bienvenido al Sistema de Exámenes</h2>
                    <p class="about-description">Gestiona tus preguntas y supuestos prácticos para los exámenes tipo test.</p>
                    
                    <div class="welcome-panel-column-container">
                        <div class="welcome-panel-column">
                            <h3>Estadísticas</h3>
                            <ul>
                                <li>Total Preguntas: <strong><?php echo $this->database->contar_preguntas(); ?></strong></li>
                                <li>Total Supuestos: <strong><?php echo $this->database->contar_supuestos(); ?></strong></li>
                            </ul>
                        </div>
                        <div class="welcome-panel-column">
                            <h3>Preguntas por Bloque</h3>
                            <ul>
                                <li>Bloque 1: <strong><?php echo $this->database->contar_preguntas('bloque1'); ?></strong></li>
                                <li>Bloque 2: <strong><?php echo $this->database->contar_preguntas('bloque2'); ?></strong></li>
                                <li>Bloque 3: <strong><?php echo $this->database->contar_preguntas('bloque3'); ?></strong></li>
                                <li>Bloque 4: <strong><?php echo $this->database->contar_preguntas('bloque4'); ?></strong></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="se-shortcode-info">
                <h3>Usar el Sistema de Exámenes</h3>
                <p>Para mostrar el sistema de exámenes en cualquier página o entrada, utiliza el siguiente shortcode:</p>
                <code>[sistema_examenes]</code>
            </div>
        </div>
        <?php
    }
    
    /**
     * Página de gestión de preguntas
     */
    public function preguntas_page() {
        $bloque_actual = isset($_GET['bloque']) ? sanitize_text_field($_GET['bloque']) : 'bloque1';
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        
        if ($action === 'add') {
            $this->formulario_pregunta();
        } elseif ($action === 'edit' && isset($_GET['id'])) {
            $this->formulario_pregunta($_GET['id']);
        } else {
            $this->lista_preguntas($bloque_actual);
        }
    }
    
    /**
     * Lista de preguntas
     */
    private function lista_preguntas($bloque) {
        $preguntas = $this->database->get_todas_preguntas($bloque);
        ?>
        <div class="wrap">
            <h1>Gestionar Preguntas</h1>
            
            <div class="tablenav top">
                <div class="alignleft actions">
                    <select name="bloque_filter" id="bloque_filter">
                        <option value="bloque1" <?php selected($bloque, 'bloque1'); ?>>Bloque 1</option>
                        <option value="bloque2" <?php selected($bloque, 'bloque2'); ?>>Bloque 2</option>
                        <option value="bloque3" <?php selected($bloque, 'bloque3'); ?>>Bloque 3</option>
                        <option value="bloque4" <?php selected($bloque, 'bloque4'); ?>>Bloque 4</option>
                    </select>
                    <button type="button" id="filter_bloque" class="button">Filtrar</button>
                </div>
                <div class="alignright">
                    <a href="<?php echo admin_url('admin.php?page=sistema-examenes-preguntas&action=add&bloque=' . $bloque); ?>" class="button button-primary">Añadir Pregunta</a>
                </div>
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Bloque</th>
                        <th>Enunciado</th>
                        <th>Respuesta Correcta</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($preguntas): ?>
                        <?php foreach ($preguntas as $pregunta): ?>
                            <tr>
                                <td><?php echo $pregunta->id; ?></td>
                                <td><?php echo ucfirst($pregunta->bloque); ?></td>
                                <td><?php echo wp_trim_words($pregunta->enunciado, 10); ?></td>
                                <td><?php echo strtoupper($pregunta->respuesta_correcta); ?></td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=sistema-examenes-preguntas&action=edit&id=' . $pregunta->id); ?>" class="button">Editar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No hay preguntas en este bloque.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#filter_bloque').on('click', function() {
                var bloque = $('#bloque_filter').val();
                window.location.href = '<?php echo admin_url('admin.php?page=sistema-examenes-preguntas'); ?>&bloque=' + bloque;
            });
        });
        </script>
        <?php
    }
    
    /**
     * Formulario de pregunta
     */
    private function formulario_pregunta($id = null) {
        $pregunta = null;
        if ($id) {
            global $wpdb;
            $table = $wpdb->prefix . 'se_preguntas';
            $pregunta = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
        }
        
        $bloque_actual = isset($_GET['bloque']) ? sanitize_text_field($_GET['bloque']) : ($pregunta ? $pregunta->bloque : 'bloque1');
        ?>
        <div class="wrap">
            <h1><?php echo $id ? 'Editar Pregunta' : 'Añadir Pregunta'; ?></h1>
            
            <form method="post" action="<?php echo admin_url('admin.php?page=sistema-examenes-preguntas'); ?>">
                <input type="hidden" name="action" value="save_pregunta">
                <input type="hidden" name="pregunta_id" value="<?php echo $id ? $id : ''; ?>">
                
                <table class="form-table">
                    <tr>
                        <th><label for="bloque">Bloque</label></th>
                        <td>
                            <select name="bloque" id="bloque" required>
                                <option value="bloque1" <?php selected($bloque_actual, 'bloque1'); ?>>Bloque 1</option>
                                <option value="bloque2" <?php selected($bloque_actual, 'bloque2'); ?>>Bloque 2</option>
                                <option value="bloque3" <?php selected($bloque_actual, 'bloque3'); ?>>Bloque 3</option>
                                <option value="bloque4" <?php selected($bloque_actual, 'bloque4'); ?>>Bloque 4</option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="enunciado">Enunciado</label></th>
                        <td>
                            <textarea name="enunciado" id="enunciado" rows="4" class="large-text" required><?php echo $pregunta ? esc_textarea($pregunta->enunciado) : ''; ?></textarea>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="imagen">URL Imagen (opcional)</label></th>
                        <td>
                            <input type="text" name="imagen" id="imagen" class="regular-text" value="<?php echo $pregunta ? esc_attr($pregunta->imagen) : ''; ?>">
                            <button type="button" class="button" id="upload_image">Subir Imagen</button>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="respuesta_a">Respuesta A</label></th>
                        <td>
                            <textarea name="respuesta_a" id="respuesta_a" rows="2" class="large-text" required><?php echo $pregunta ? esc_textarea($pregunta->respuesta_a) : ''; ?></textarea>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="respuesta_b">Respuesta B</label></th>
                        <td>
                            <textarea name="respuesta_b" id="respuesta_b" rows="2" class="large-text" required><?php echo $pregunta ? esc_textarea($pregunta->respuesta_b) : ''; ?></textarea>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="respuesta_c">Respuesta C</label></th>
                        <td>
                            <textarea name="respuesta_c" id="respuesta_c" rows="2" class="large-text" required><?php echo $pregunta ? esc_textarea($pregunta->respuesta_c) : ''; ?></textarea>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="respuesta_d">Respuesta D</label></th>
                        <td>
                            <textarea name="respuesta_d" id="respuesta_d" rows="2" class="large-text" required><?php echo $pregunta ? esc_textarea($pregunta->respuesta_d) : ''; ?></textarea>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="respuesta_correcta">Respuesta Correcta</label></th>
                        <td>
                            <select name="respuesta_correcta" id="respuesta_correcta" required>
                                <option value="a" <?php echo $pregunta && $pregunta->respuesta_correcta === 'a' ? 'selected' : ''; ?>>A</option>
                                <option value="b" <?php echo $pregunta && $pregunta->respuesta_correcta === 'b' ? 'selected' : ''; ?>>B</option>
                                <option value="c" <?php echo $pregunta && $pregunta->respuesta_correcta === 'c' ? 'selected' : ''; ?>>C</option>
                                <option value="d" <?php echo $pregunta && $pregunta->respuesta_correcta === 'd' ? 'selected' : ''; ?>>D</option>
                            </select>
                        </td>
                    </tr>
                </table>
                
                <?php wp_nonce_field('save_pregunta', 'pregunta_nonce'); ?>
                
                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Guardar Pregunta">
                    <a href="<?php echo admin_url('admin.php?page=sistema-examenes-preguntas&bloque=' . $bloque_actual); ?>" class="button">Cancelar</a>
                </p>
            </form>
        </div>
        <?php
    }
    
    /**
     * Página de gestión de supuestos
     */
    public function supuestos_page() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        
        if ($action === 'add') {
            $this->formulario_supuesto();
        } elseif ($action === 'edit' && isset($_GET['id'])) {
            $this->formulario_supuesto($_GET['id']);
        } else {
            $this->lista_supuestos();
        }
    }
    
    /**
     * Lista de supuestos
     */
    private function lista_supuestos() {
        $supuestos = $this->database->get_todos_supuestos();
        ?>
        <div class="wrap">
            <h1>Gestionar Supuestos Prácticos</h1>
            
            <div class="tablenav top">
                <div class="alignright">
                    <a href="<?php echo admin_url('admin.php?page=sistema-examenes-supuestos&action=add'); ?>" class="button button-primary">Añadir Supuesto</a>
                </div>
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Bloque Origen</th>
                        <th>Enunciado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($supuestos): ?>
                        <?php foreach ($supuestos as $supuesto): ?>
                            <tr>
                                <td><?php echo $supuesto->id; ?></td>
                                <td><?php echo esc_html($supuesto->titulo); ?></td>
                                <td><?php echo ucfirst($supuesto->bloque_origen); ?></td>
                                <td><?php echo wp_trim_words($supuesto->enunciado, 10); ?></td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=sistema-examenes-supuestos&action=edit&id=' . $supuesto->id); ?>" class="button">Editar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No hay supuestos prácticos.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    /**
     * Formulario de supuesto
     */
    private function formulario_supuesto($id = null) {
        $supuesto = null;
        if ($id) {
            global $wpdb;
            $table = $wpdb->prefix . 'se_supuestos';
            $supuesto = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
        }
        ?>
        <div class="wrap">
            <h1><?php echo $id ? 'Editar Supuesto' : 'Añadir Supuesto'; ?></h1>
            
            <form method="post" action="<?php echo admin_url('admin.php?page=sistema-examenes-supuestos'); ?>">
                <input type="hidden" name="action" value="save_supuesto">
                <input type="hidden" name="supuesto_id" value="<?php echo $id ? $id : ''; ?>">
                
                <table class="form-table">
                    <tr>
                        <th><label for="titulo">Título</label></th>
                        <td>
                            <input type="text" name="titulo" id="titulo" class="regular-text" required value="<?php echo $supuesto ? esc_attr($supuesto->titulo) : ''; ?>">
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="bloque_origen">Bloque Origen</label></th>
                        <td>
                            <select name="bloque_origen" id="bloque_origen" required>
                                <option value="bloque3" <?php echo $supuesto && $supuesto->bloque_origen === 'bloque3' ? 'selected' : ''; ?>>Bloque 3</option>
                                <option value="bloque4" <?php echo $supuesto && $supuesto->bloque_origen === 'bloque4' ? 'selected' : ''; ?>>Bloque 4</option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="enunciado">Enunciado</label></th>
                        <td>
                            <textarea name="enunciado" id="enunciado" rows="6" class="large-text" required><?php echo $supuesto ? esc_textarea($supuesto->enunciado) : ''; ?></textarea>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><label for="imagen">URL Imagen (opcional)</label></th>
                        <td>
                            <input type="text" name="imagen" id="imagen" class="regular-text" value="<?php echo $supuesto ? esc_attr($supuesto->imagen) : ''; ?>">
                            <button type="button" class="button" id="upload_image">Subir Imagen</button>
                        </td>
                    </tr>
                </table>
                
                <?php wp_nonce_field('save_supuesto', 'supuesto_nonce'); ?>
                
                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Guardar Supuesto">
                    <a href="<?php echo admin_url('admin.php?page=sistema-examenes-supuestos'); ?>" class="button">Cancelar</a>
                </p>
            </form>
        </div>
        <?php
    }
    
    /**
     * Manejar envíos de formularios
     */
    public function handle_form_submissions() {
        if (isset($_POST['action']) && $_POST['action'] === 'save_pregunta') {
            if (!isset($_POST['pregunta_nonce']) || !wp_verify_nonce($_POST['pregunta_nonce'], 'save_pregunta')) {
                wp_die('Security check failed');
            }
            
            $datos = array(
                'bloque' => sanitize_text_field($_POST['bloque']),
                'enunciado' => wp_kses_post($_POST['enunciado']),
                'imagen' => esc_url_raw($_POST['imagen']),
                'respuesta_a' => wp_kses_post($_POST['respuesta_a']),
                'respuesta_b' => wp_kses_post($_POST['respuesta_b']),
                'respuesta_c' => wp_kses_post($_POST['respuesta_c']),
                'respuesta_d' => wp_kses_post($_POST['respuesta_d']),
                'respuesta_correcta' => sanitize_text_field($_POST['respuesta_correcta'])
            );
            
            if (!empty($_POST['pregunta_id'])) {
                // Actualizar pregunta
                global $wpdb;
                $table = $wpdb->prefix . 'se_preguntas';
                $wpdb->update($table, $datos, array('id' => intval($_POST['pregunta_id'])));
            } else {
                // Insertar nueva pregunta
                $this->database->insertar_pregunta($datos);
            }
            
            wp_redirect(admin_url('admin.php?page=sistema-examenes-preguntas&bloque=' . $datos['bloque']));
            exit;
        }
        
        if (isset($_POST['action']) && $_POST['action'] === 'save_supuesto') {
            if (!isset($_POST['supuesto_nonce']) || !wp_verify_nonce($_POST['supuesto_nonce'], 'save_supuesto')) {
                wp_die('Security check failed');
            }
            
            $datos = array(
                'titulo' => sanitize_text_field($_POST['titulo']),
                'enunciado' => wp_kses_post($_POST['enunciado']),
                'imagen' => esc_url_raw($_POST['imagen']),
                'bloque_origen' => sanitize_text_field($_POST['bloque_origen'])
            );
            
            if (!empty($_POST['supuesto_id'])) {
                // Actualizar supuesto
                global $wpdb;
                $table = $wpdb->prefix . 'se_supuestos';
                $wpdb->update($table, $datos, array('id' => intval($_POST['supuesto_id'])));
            } else {
                // Insertar nuevo supuesto
                $this->database->insertar_supuesto($datos);
            }
            
            wp_redirect(admin_url('admin.php?page=sistema-examenes-supuestos'));
            exit;
        }
    }
}
