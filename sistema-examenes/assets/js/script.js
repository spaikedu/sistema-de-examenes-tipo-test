jQuery(document).ready(function($) {
    'use strict';
    
    let preguntasActuales = [];
    let preguntaActualIndex = 0;
    let respuestasUsuario = {};
    let tiempoInicio = null;
    let timerInterval = null;
    let esSimulacro = false;
    
    // Event listeners para los bloques
    $('.se-bloque-card').on('click', function() {
        const bloque = $(this).data('bloque');
        iniciarExamenBloque(bloque);
    });
    
    // Event listener para el simulacro
    $('#iniciar-simulacro').on('click', function() {
        iniciarSimulacro();
    });
    
    // Navegación del examen
    $('#anterior-pregunta').on('click', function() {
        if (preguntaActualIndex > 0) {
            preguntaActualIndex--;
            mostrarPreguntaActual();
        }
    });
    
    $('#siguiente-pregunta').on('click', function() {
        if (preguntaActualIndex < preguntasActuales.length - 1) {
            preguntaActualIndex++;
            mostrarPreguntaActual();
        } else {
            $('#finalizar-examen').show();
            $(this).hide();
        }
    });
    
    $('#finalizar-examen').on('click', function() {
        finalizarExamen();
    });
    
    // Event listeners para los resultados
    $('#nuevo-examen').on('click', function() {
        location.reload();
    });
    
    $('#volver-menu').on('click', function() {
        mostrarMenuPrincipal();
    });
    
    /**
     * Iniciar examen por bloque
     */
    function iniciarExamenBloque(bloque) {
        $.ajax({
            url: sistemaExamenesAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'get_preguntas_bloque',
                bloque: bloque,
                nonce: sistemaExamenesAjax.nonce
            },
            beforeSend: function() {
                mostrarLoading();
            },
            success: function(response) {
                if (response.success) {
                    esSimulacro = false;
                    preguntasActuales = response.data.preguntas;
                    iniciarExamen(response.data.titulo);
                } else {
                    alert('Error al cargar las preguntas');
                }
            },
            error: function() {
                alert('Error de conexión');
            }
        });
    }
    
    /**
     * Iniciar simulacro
     */
    function iniciarSimulacro() {
        $.ajax({
            url: sistemaExamenesAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'iniciar_simulacro',
                nonce: sistemaExamenesAjax.nonce
            },
            beforeSend: function() {
                mostrarLoading();
            },
            success: function(response) {
                if (response.success) {
                    esSimulacro = true;
                    preguntasActuales = [];
                    
                    // Añadir preguntas normales
                    preguntasActuales = preguntasActuales.concat(response.data.preguntas);
                    
                    // Añadir preguntas de supuestos
                    response.data.supuestos.forEach(function(supuesto) {
                        // Añadir el supuesto como una pregunta especial
                        preguntasActuales.push({
                            id: 'supuesto_' + supuesto.supuesto.id,
                            tipo: 'supuesto',
                            titulo: supuesto.supuesto.titulo,
                            enunciado: supuesto.supuesto.enunciado,
                            imagen: supuesto.supuesto.imagen,
                            bloque_origen: supuesto.supuesto.bloque_origen,
                            preguntas: supuesto.preguntas
                        });
                    });
                    
                    iniciarExamen(response.data.titulo);
                } else {
                    alert('Error al cargar el simulacro');
                }
            },
            error: function() {
                alert('Error de conexión');
            }
        });
    }
    
    /**
     * Iniciar examen
     */
    function iniciarExamen(titulo) {
        // Resetear variables
        preguntaActualIndex = 0;
        respuestasUsuario = {};
        tiempoInicio = new Date();
        
        // Actualizar UI
        $('#se-examen-titulo').text(titulo);
        $('#total-preguntas').text(preguntasActuales.length);
        $('#pregunta-actual').text('1');
        
        // Ocultar menú principal y mostrar examen
        $('#se-menu-principal').hide();
        $('#se-examen-container').show();
        
        // Iniciar timer
        iniciarTimer();
        
        // Mostrar primera pregunta
        mostrarPreguntaActual();
    }
    
    /**
     * Mostrar pregunta actual
     */
    function mostrarPreguntaActual() {
        const pregunta = preguntasActuales[preguntaActualIndex];
        
        // Actualizar contador
        $('#pregunta-actual').text(preguntaActualIndex + 1);
        
        // Actualizar botones de navegación
        $('#anterior-pregunta').prop('disabled', preguntaActualIndex === 0);
        $('#siguiente-pregunta').toggle(preguntaActualIndex < preguntasActuales.length - 1);
        $('#finalizar-examen').toggle(preguntaActualIndex === preguntasActuales.length - 1);
        
        // Renderizar pregunta
        if (pregunta.tipo === 'supuesto') {
            renderizarSupuesto(pregunta);
        } else {
            renderizarPregunta(pregunta);
        }
    }
    
    /**
     * Renderizar pregunta normal
     */
    function renderizarPregunta(pregunta) {
        let html = '<div class="se-pregunta">';
        html += '<div class="se-pregunta-enunciado">' + (preguntaActualIndex + 1) + '. ' + pregunta.enunciado + '</div>';
        
        if (pregunta.imagen) {
            html += '<img src="' + pregunta.imagen + '" alt="Imagen de la pregunta" class="se-pregunta-imagen">';
        }
        
        html += '<ul class="se-opciones">';
        html += '<li class="se-opcion">';
        html += '<input type="radio" name="respuesta_' + pregunta.id + '" id="respuesta_a_' + pregunta.id + '" value="a">';
        html += '<label for="respuesta_a_' + pregunta.id + '">A) ' + pregunta.respuesta_a + '</label>';
        html += '</li>';
        html += '<li class="se-opcion">';
        html += '<input type="radio" name="respuesta_' + pregunta.id + '" id="respuesta_b_' + pregunta.id + '" value="b">';
        html += '<label for="respuesta_b_' + pregunta.id + '">B) ' + pregunta.respuesta_b + '</label>';
        html += '</li>';
        html += '<li class="se-opcion">';
        html += '<input type="radio" name="respuesta_' + pregunta.id + '" id="respuesta_c_' + pregunta.id + '" value="c">';
        html += '<label for="respuesta_c_' + pregunta.id + '">C) ' + pregunta.respuesta_c + '</label>';
        html += '</li>';
        html += '<li class="se-opcion">';
        html += '<input type="radio" name="respuesta_' + pregunta.id + '" id="respuesta_d_' + pregunta.id + '" value="d">';
        html += '<label for="respuesta_d_' + pregunta.id + '">D) ' + pregunta.respuesta_d + '</label>';
        html += '</li>';
        html += '</ul>';
        html += '</div>';
        
        $('#se-pregunta-container').html(html);
        
        // Restaurar respuesta guardada
        const respuestaGuardada = respuestasUsuario[pregunta.id];
        if (respuestaGuardada) {
            $('input[name="respuesta_' + pregunta.id + '"][value="' + respuestaGuardada + '"]').prop('checked', true);
        }
        
        // Event listeners para las opciones
        $('.se-opcion input').on('change', function() {
            const valor = $(this).val();
            respuestasUsuario[pregunta.id] = valor;
            
            // Actualizar UI
            $('.se-opcion').removeClass('selected');
            $(this).closest('.se-opcion').addClass('selected');
        });
    }
    
    /**
     * Renderizar supuesto práctico
     */
    function renderizarSupuesto(supuesto) {
        let html = '<div class="se-supuesto">';
        html += '<div class="se-supuesto-titulo">Supuesto Práctico</div>';
        html += '<div class="se-supuesto-enunciado">' + supuesto.enunciado + '</div>';
        
        if (supuesto.imagen) {
            html += '<img src="' + supuesto.imagen + '" alt="Imagen del supuesto" class="se-pregunta-imagen">';
        }
        
        html += '<h4>Preguntas del supuesto:</h4>';
        
        supuesto.preguntas.forEach(function(pregunta, index) {
            html += '<div class="se-pregunta">';
            html += '<div class="se-pregunta-enunciado">' + (index + 1) + '. ' + pregunta.pregunta + '</div>';
            html += '<ul class="se-opciones">';
            html += '<li class="se-opcion">';
            html += '<input type="radio" name="respuesta_' + pregunta.id + '" id="respuesta_a_' + pregunta.id + '" value="a">';
            html += '<label for="respuesta_a_' + pregunta.id + '">A) ' + pregunta.respuesta_a + '</label>';
            html += '</li>';
            html += '<li class="se-opcion">';
            html += '<input type="radio" name="respuesta_' + pregunta.id + '" id="respuesta_b_' + pregunta.id + '" value="b">';
            html += '<label for="respuesta_b_' + pregunta.id + '">B) ' + pregunta.respuesta_b + '</label>';
            html += '</li>';
            html += '<li class="se-opcion">';
            html += '<input type="radio" name="respuesta_' + pregunta.id + '" id="respuesta_c_' + pregunta.id + '" value="c">';
            html += '<label for="respuesta_c_' + pregunta.id + '">C) ' + pregunta.respuesta_c + '</label>';
            html += '</li>';
            html += '<li class="se-opcion">';
            html += '<input type="radio" name="respuesta_' + pregunta.id + '" id="respuesta_d_' + pregunta.id + '" value="d">';
            html += '<label for="respuesta_d_' + pregunta.id + '">D) ' + pregunta.respuesta_d + '</label>';
            html += '</li>';
            html += '</ul>';
            html += '</div>';
        });
        
        html += '</div>';
        
        $('#se-pregunta-container').html(html);
        
        // Restaurar respuestas guardadas
        supuesto.preguntas.forEach(function(pregunta) {
            const respuestaGuardada = respuestasUsuario[pregunta.id];
            if (respuestaGuardada) {
                $('input[name="respuesta_' + pregunta.id + '"][value="' + respuestaGuardada + '"]').prop('checked', true);
            }
        });
        
        // Event listeners para las opciones
        $('.se-opcion input').on('change', function() {
            const name = $(this).attr('name');
            const preguntaId = name.replace('respuesta_', '');
            const valor = $(this).val();
            respuestasUsuario[preguntaId] = valor;
            
            // Actualizar UI
            $('.se-opcion').removeClass('selected');
            $(this).closest('.se-opcion').addClass('selected');
        });
    }
    
    /**
     * Iniciar timer
     */
    function iniciarTimer() {
        timerInterval = setInterval(function() {
            const ahora = new Date();
            const diferencia = Math.floor((ahora - tiempoInicio) / 1000);
            const minutos = Math.floor(diferencia / 60);
            const segundos = diferencia % 60;
            const tiempoFormateado = 
                String(minutos).padStart(2, '0') + ':' + 
                String(segundos).padStart(2, '0');
            $('#tiempo-transcurrido').text(tiempoFormateado);
        }, 1000);
    }
    
    /**
     * Finalizar examen
     */
    function finalizarExamen() {
        clearInterval(timerInterval);
        
        const tiempoTotal = $('#tiempo-transcurrido').text();
        
        // Preparar datos para enviar
        const preguntasData = [];
        preguntasActuales.forEach(function(pregunta) {
            if (pregunta.tipo === 'supuesto') {
                pregunta.preguntas.forEach(function(subPregunta) {
                    preguntasData.push({
                        id: subPregunta.id,
                        respuesta_correcta: subPregunta.respuesta_correcta,
                        enunciado: subPregunta.pregunta,
                        respuesta_a: subPregunta.respuesta_a,
                        respuesta_b: subPregunta.respuesta_b,
                        respuesta_c: subPregunta.respuesta_c,
                        respuesta_d: subPregunta.respuesta_d
                    });
                });
            } else {
                preguntasData.push({
                    id: pregunta.id,
                    respuesta_correcta: pregunta.respuesta_correcta,
                    enunciado: pregunta.enunciado,
                    respuesta_a: pregunta.respuesta_a,
                    respuesta_b: pregunta.respuesta_b,
                    respuesta_c: pregunta.respuesta_c,
                    respuesta_d: pregunta.respuesta_d
                });
            }
        });
        
        $.ajax({
            url: sistemaExamenesAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'corregir_examen',
                respuestas: respuestasUsuario,
                preguntas_data: JSON.stringify(preguntasData),
                tiempo_total: tiempoTotal,
                nonce: sistemaExamenesAjax.nonce
            },
            beforeSend: function() {
                mostrarLoading();
            },
            success: function(response) {
                if (response.success) {
                    mostrarResultados(response.data);
                } else {
                    alert('Error al corregir el examen');
                }
            },
            error: function() {
                alert('Error de conexión');
            }
        });
    }
    
    /**
     * Mostrar resultados
     */
    function mostrarResultados(datos) {
        // Ocultar examen y mostrar resultados
        $('#se-examen-container').hide();
        $('#se-resultados-container').show();
        
        // Actualizar estadísticas
        $('#respuestas-correctas').text(datos.correctas);
        $('#respuestas-incorrectas').text(datos.incorrectas);
        $('#nota-final').text(datos.nota);
        $('#tiempo-total').text(datos.tiempo_total);
        
        // Mostrar revisión de respuestas
        let revisionHtml = '<h3>Revisión de Respuestas</h3>';
        
        datos.resultados.forEach(function(resultado, index) {
            const esCorrecta = resultado.es_correcta;
            const clase = esCorrecta ? 'correcta' : 'incorrecta';
            const indicador = esCorrecta ? 
                '<span class="se-revision-indicador correcto">✓ Correcto</span>' : 
                '<span class="se-revision-indicador incorrecto">✗ Incorrecto</span>';
            
            revisionHtml += '<div class="se-revision-pregunta ' + clase + '">';
            revisionHtml += '<div class="se-revision-enunciado">';
            revisionHtml += (index + 1) + '. ' + resultado.pregunta.enunciado;
            revisionHtml += indicador;
            revisionHtml += '</div>';
            
            if (resultado.respuesta_usuario) {
                const letraRespuesta = resultado.respuesta_usuario.toUpperCase();
                const textoRespuesta = resultado.pregunta['respuesta_' + resultado.respuesta_usuario];
                revisionHtml += '<div class="se-revision-respuesta usuario">';
                revisionHtml += '<strong>Tu respuesta:</strong> ' + letraRespuesta + ') ' + textoRespuesta;
                revisionHtml += '</div>';
            } else {
                revisionHtml += '<div class="se-revision-respuesta usuario">';
                revisionHtml += '<strong>Tu respuesta:</strong> No respondida';
                revisionHtml += '</div>';
            }
            
            if (!esCorrecta) {
                const letraCorrecta = resultado.pregunta.respuesta_correcta.toUpperCase();
                const textoCorrecto = resultado.pregunta['respuesta_' + resultado.pregunta.respuesta_correcta];
                revisionHtml += '<div class="se-revision-respuesta correcta">';
                revisionHtml += '<strong>Respuesta correcta:</strong> ' + letraCorrecta + ') ' + textoCorrecto;
                revisionHtml += '</div>';
            }
            
            revisionHtml += '</div>';
        });
        
        $('#se-revision-container').html(revisionHtml);
    }
    
    /**
     * Mostrar menú principal
     */
    function mostrarMenuPrincipal() {
        $('#se-resultados-container').hide();
        $('#se-menu-principal').show();
        
        // Resetear variables
        preguntasActuales = [];
        preguntaActualIndex = 0;
        respuestasUsuario = {};
        clearInterval(timerInterval);
    }
    
    /**
     * Mostrar loading
     */
    function mostrarLoading() {
        const loadingHtml = '<div class="se-loading"><div class="se-spinner"></div></div>';
        $('#se-pregunta-container').html(loadingHtml);
    }
});
