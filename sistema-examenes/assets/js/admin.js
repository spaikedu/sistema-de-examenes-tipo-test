jQuery(document).ready(function($) {
    'use strict';
    
    // Upload de imágenes
    $('#upload_image').on('click', function(e) {
        e.preventDefault();
        
        const inputField = $(this).prev('input[type="text"]');
        const button = $(this);
        
        // Crear frame de media si no existe
        if (typeof wp !== 'undefined' && wp.media) {
            const mediaFrame = wp.media({
                title: 'Seleccionar Imagen',
                button: {
                    text: 'Usar esta imagen'
                },
                multiple: false
            });
            
            mediaFrame.on('select', function() {
                const attachment = mediaFrame.state().get('selection').first().toJSON();
                inputField.val(attachment.url);
            });
            
            mediaFrame.open();
        } else {
            alert('El gestor de medios no está disponible');
        }
    });
    
    // Validación de formularios
    $('form').on('submit', function(e) {
        let isValid = true;
        const form = $(this);
        
        // Limpiar validaciones anteriores
        form.find('.form-invalid').removeClass('form-invalid');
        
        // Validar campos requeridos
        form.find('input[required], textarea[required], select[required]').each(function() {
            const field = $(this);
            const value = field.val().trim();
            
            if (!value) {
                field.closest('tr').addClass('form-invalid');
                isValid = false;
            }
        });
        
        // Validar respuesta correcta
        const respuestaCorrecta = form.find('#respuesta_correcta');
        if (respuestaCorrecta.length && respuestaCorrecta.val()) {
            const valor = respuestaCorrecta.val().toLowerCase();
            if (!['a', 'b', 'c', 'd'].includes(valor)) {
                respuestaCorrecta.closest('tr').addClass('form-invalid');
                isValid = false;
            }
        }
        
        if (!isValid) {
            e.preventDefault();
            alert('Por favor, completa todos los campos requeridos correctamente.');
            
            // Scroll al primer error
            $('html, body').animate({
                scrollTop: form.find('.form-invalid').first().offset().top - 100
            }, 500);
        }
    });
    
    // Confirmación de eliminación
    $('.delete-button').on('click', function(e) {
        if (!confirm('¿Estás seguro de que quieres eliminar este elemento? Esta acción no se puede deshacer.')) {
            e.preventDefault();
        }
    });
    
    // Mejoras en la experiencia de usuario
    $('.form-table input, .form-table textarea, .form-table select').on('focus', function() {
        $(this).closest('tr').addClass('focused');
    }).on('blur', function() {
        $(this).closest('tr').removeClass('focused');
    });
    
    // Auto-ajuste del height de los textareas
    $('textarea').each(function() {
        this.setAttribute('style', 'height:' + (this.scrollHeight) + 'px;overflow-y:hidden;');
    }).on('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
    
    // Contador de caracteres para campos con límite
    $('.char-counter').each(function() {
        const counter = $(this);
        const target = $('#' + counter.data('target'));
        const maxLength = parseInt(counter.data('max-length'));
        
        function updateCounter() {
            const currentLength = target.val().length;
            const remaining = maxLength - currentLength;
            
            counter.text(remaining + ' caracteres restantes');
            
            if (remaining < 0) {
                counter.addClass('error');
            } else if (remaining < 20) {
                counter.addClass('warning');
            } else {
                counter.removeClass('error warning');
            }
        }
        
        target.on('input', updateCounter);
        updateCounter();
    });
    
    // Tooltips
    $('.help-tip').on('mouseenter', function() {
        const tip = $(this);
        const tooltip = $('<div class="tooltip">' + tip.data('tooltip') + '</div>');
        
        $('body').append(tooltip);
        
        const position = tip.offset();
        tooltip.css({
            position: 'absolute',
            top: position.top - tooltip.outerHeight() - 10,
            left: position.left + (tip.outerWidth() / 2) - (tooltip.outerWidth() / 2)
        }).fadeIn(200);
    }).on('mouseleave', function() {
        $('.tooltip').fadeOut(200, function() {
            $(this).remove();
        });
    });
    
    // Filtrado en tablas
    $('#filter_bloque').on('click', function() {
        const bloque = $('#bloque_filter').val();
        const currentUrl = window.location.href;
        const newUrl = currentUrl.split('?')[0] + '?page=sistema-examenes-preguntas&bloque=' + bloque;
        window.location.href = newUrl;
    });
    
    // Búsqueda instantánea (si se implementa)
    let searchTimeout;
    $('.search-input').on('input', function() {
        const input = $(this);
        const searchTerm = input.val().trim();
        
        clearTimeout(searchTimeout);
        
        searchTimeout = setTimeout(function() {
            if (searchTerm.length >= 3 || searchTerm.length === 0) {
                // Aquí se podría implementar una búsqueda AJAX
                console.log('Buscar:', searchTerm);
            }
        }, 500);
    });
    
    // Mejoras en la accesibilidad
    $('button, a, input, select, textarea').on('keydown', function(e) {
        if (e.key === 'Enter' && e.ctrlKey) {
            $(this).trigger('click');
        }
    });
    
    // Indicadores de carga
    $('.loading-indicator').hide();
    
    // Función para mostrar carga
    window.showLoading = function(element) {
        const indicator = $('<div class="loading-indicator"><span class="spinner"></span> Cargando...</div>');
        element.append(indicator);
        indicator.fadeIn(200);
    };
    
    // Función para ocultar carga
    window.hideLoading = function() {
        $('.loading-indicator').fadeOut(200, function() {
            $(this).remove();
        });
    };
});
