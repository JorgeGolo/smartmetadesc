<?php
/*
Plugin Name: smartmetadesc
Description: Muestra una lista de los nombres de las entradas publicadas en el escritorio de administración e indica si la meta descripción está vacía.
Version: 1.1
Author: Tu Nombre
*/

// Agregar un enlace de "Ajustes" en la página de plugins de WordPress
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'smartmetadesc_add_settings_link');
function smartmetadesc_add_settings_link($links) {
    // Crear el enlace de ajustes que apunta a la página de configuración del plugin
    $settings_link = '<a href="options-general.php?page=smartmetadesc-configuracion">Ajustes</a>';
    // Agregar el enlace al principio del array de enlaces
    array_unshift($links, $settings_link);
    return $links;
}


add_action('admin_enqueue_scripts', 'smartmetadesc_enqueue_scripts');
function smartmetadesc_enqueue_scripts($hook) {
    // Asegurarte de cargar el script solo en las páginas del plugin
    if ($hook !== 'tools_page_smartmetadesc-lista') {
        return;
    }

    wp_enqueue_script(
        'smartmetadesc-script', // Handle del script
        plugin_dir_url(__FILE__) . 'assets/js/smartmetadesc.js', // Ruta del archivo
        array(), // Dependencias (si hay)
        '1.0', // Versión
        true // Cargar en el footer
    );

        // Pasar la clave API al script
        wp_localize_script('smartmetadesc-script', 'smartMetaDescConfig', [
            'apiKey' => get_option('smartmetadesc_api_key', ''),
        ]);
}



// Agregar un enlace de "Ajustes" en el menú de WordPress
add_action('admin_menu', 'smartmetadesc_add_menu');
function smartmetadesc_add_menu() {
    // Agregar un enlace de "Ajustes" en la página de "Ajustes"
    add_options_page(
        'Configuración de Smart Meta Desc',   // Título de la página
        'Smart Meta Desc',                    // Nombre del menú
        'manage_options',                     // Capacidad requerida
        'smartmetadesc-configuracion',        // Slug único del menú
        'smartmetadesc_config_page'           // Función que renderiza la página
    );
    add_submenu_page(
        'tools.php',                   // Página padre (Herramientas)
        'Smart Meta Desc',              // Título de la página
        'Smart Meta Desc',              // Texto del menú
        'manage_options',               // Capacidad requerida
        'smartmetadesc-lista',          // Slug único del menú
        'smartmetadesc_render_page'     // Función que renderiza el contenido
    );


}



function smartmetadesc_config_page() {
    // Verificar si el usuario tiene permisos para administrar opciones
    if (!current_user_can('manage_options')) {
        return;
    }

    // Comprobar si el formulario de configuración fue enviado
    if (isset($_POST['submit'])) {
        // Guardar las configuraciones enviadas
        update_option('smartmetadesc_num_posts', intval($_POST['num_posts']));
        update_option('smartmetadesc_option', sanitize_text_field($_POST['option_select']));
        update_option('smartmetadesc_api_key', sanitize_text_field($_POST['api_key'])); // Guardar la clave de API
        echo '<div class="updated"><p>Configuración guardada.</p></div>';
    }

    // Obtener las configuraciones guardadas
    $num_posts = get_option('smartmetadesc_num_posts', 10); // Valor predeterminado: 10
    $selected_option = get_option('smartmetadesc_option', 'gemini-1.5-flash'); // Valor predeterminado
    $api_key = get_option('smartmetadesc_api_key', ''); // Clave de API por defecto vacía

    // Renderizar el formulario
    echo '<div class="wrap">';
    echo '<h1>Configuración de Smart Meta Desc</h1>';
    echo '<form method="post" action="">';

    // Campo para el número de entradas
    echo '<table class="form-table">';
    echo '<tr>';
    echo '<th scope="row"><label for="num_posts">Número de entradas a mostrar por defecto:</label></th>';
    echo '<td><input type="number" id="num_posts" name="num_posts" value="' . esc_attr($num_posts) . '" min="1" /></td>';
    echo '</tr>';

    // Campo para seleccionar modelo de IA
    echo '<tr>';
    echo '<th scope="row"><label for="option_select">Modelo de IA:</label></th>';
    echo '<td>';
    echo '<select id="option_select" name="option_select">';
    echo '<option value="gemini-1.5-flash"' . selected($selected_option, 'gemini-1.5-flash', false) . '>gemini-1.5-flash</option>';
    echo '<option value="Llama 3"' . selected($selected_option, 'Llama 3', false) . '>Llama 3</option>';
    echo '</select>';
    echo '</td>';
    echo '</tr>';

    // Campo para la clave de API
    echo '<tr>';
    echo '<th scope="row"><label for="api_key">Clave de API:</label></th>';
    echo '<td><input type="text" id="api_key" name="api_key" value="' . esc_attr($api_key) . '" placeholder="Introduce tu clave de API" /></td>';
    echo '</tr>';
    echo '</table>';

    // Botón para guardar
    echo '<p class="submit"><button type="submit" name="submit" class="button button-primary">Guardar cambios</button></p>';
    echo '</form>';
    echo '</div>';
}



// Página principal del plugin (mostrar las entradas con meta descripción vacía)
// Comprobar si un plugin está activo
if (!function_exists('is_plugin_active')) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

function smartmetadesc_render_page() {
    echo '<div class="wrap">';
    echo '<h1>Smart Meta Desc</h1>';

    // Comprobar si Yoast SEO está activo
    $yoast_active = is_plugin_active('wordpress-seo/wp-seo.php');

// Obtener el número de entradas a mostrar desde la configuración del plugin (o por defecto)
    $default_posts_limit = get_option('smartmetadesc_num_posts', 10); // Valor predeterminado: 10
    $num_posts_default = isset($_GET['num_posts']) ? intval($_GET['num_posts']) : $default_posts_limit;


    // Obtener todas las entradas
    $all_posts = get_posts(array(
        'post_type'   => 'post',   // Tipo de contenido (entradas)
        'post_status' => 'publish', // Solo entradas publicadas
        'numberposts' => -1        // Obtener todas las entradas (sin límite)
    ));

    // Filtrar las entradas con meta descripción vacía
    $empty_meta_posts = array();
    foreach ($all_posts as $post) {
        // Comprobar el meta campo según el estado de Yoast SEO
        $meta_description = $yoast_active
            ? get_post_meta($post->ID, '_yoast_wpseo_metadesc', true) // Campo de Yoast SEO
            : get_post_meta($post->ID, 'meta_description', true);    // Campo genérico

        // Agregar al array si la meta descripción está vacía
        if (empty($meta_description)) {
            $empty_meta_posts[] = $post;
        }
    }

    // Contar las entradas con meta descripción vacía
    $empty_meta_count = count($empty_meta_posts);

    // Mostrar el resultado
    echo '<p><strong>' . $empty_meta_count . '</strong> entradas con meta descripción vacía.</p>';

    // Campo para ingresar el número de entradas
    echo '<div style="margin-bottom: 20px;">';
    echo '<label for="num_posts_input">Número de entradas a mostrar:</label>';
    echo '<input type="number" id="num_posts_input" value="' . esc_attr($num_posts_default) . '" min="1" />';
    echo '<button id="apply_posts_limit" class="button button-primary">Aplicar</button>';
    echo '</div>';

    // Mostrar las entradas con meta descripción vacía (aplicando el límite)
    if ($empty_meta_count > 0) {
        echo '<ul class="smd_postslist">';
        $count = 0;
        foreach ($empty_meta_posts as $post) {
            if ($count >= $num_posts_default) break; // Detener al alcanzar el límite
            echo '<li>';
            echo '<h3 class="smd_spantitulo">' . esc_html($post->post_title) . '</h3>';
            echo '<button type="button" class="smd_buttongen button button-secondary" data-post-id="' . esc_attr($post->ID) . '">Generar Metadescripción</button>';
            echo '<div class="textarea-container smd_textarea" style="display: none;">';
            echo '<textarea rows="4" cols="50" id="textarea-' . esc_attr($post->ID) . '" placeholder="Escribe la meta descripción aquí..."></textarea>';
            echo '<button type="button" class="smd_buttonsave button button-secondary">Guardar</button>';
            echo '</div>';
            echo '</li>';
            $count++;
        }
        echo '</ul>';
    } else {
        echo '<p>No hay entradas con meta descripción vacía.</p>';
    }

    echo '</div>';


}

add_action('rest_api_init', function () {
    register_rest_route('smartmetadesc/v1', '/entrada/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'smartmetadesc_get_post_content',
        'permission_callback' => '__return_true'
    ));
});

function smartmetadesc_get_post_content($data) {
    $post_id = $data['id'];
    $post = get_post($post_id);

    if (!$post) {
        return new WP_Error('no_post', 'Entrada no encontrada', array('status' => 404));
    }

    return array(
        'content' => wp_strip_all_tags($post->post_content)
    );
}

function smartmetadesc_enqueue_styles() {
    // Obtener la URL del plugin
    $plugin_url = plugin_dir_url(__FILE__);
    
    // Registrar y encolar el archivo CSS
    wp_enqueue_style(
        'smartmetadesc-styles', // Identificador único del estilo
        $plugin_url . 'assets/css/smartmetadesc.css', // Ruta del archivo CSS
        array(), // Dependencias (vacío si no hay)
        '1.0.0', // Versión del archivo
        'all' // Tipo de medio (e.g., 'all', 'screen', 'print')
    );
}
// Hook para cargar los estilos en el frontend o backend
add_action('admin_enqueue_scripts', 'smartmetadesc_enqueue_styles');

add_action('rest_api_init', function () {
    register_rest_route('smartmetadesc/v1', '/guardar/(?P<id>\d+)', array(
        'methods' => 'POST',
        'callback' => 'guardar_meta_descripcion',
        'permission_callback' => '__return_true',

    ));
});

function guardar_meta_descripcion($request) {
    $post_id = $request->get_param('id');
    $meta_descripcion = sanitize_text_field($request->get_param('metaDescripcion'));

    if (!$post_id || empty($meta_descripcion)) {
        return new WP_Error('invalid_request', 'Faltan parámetros o son inválidos.', array('status' => 400));
    }

    // Verificar si Yoast SEO está activo
    if (is_plugin_active('wordpress-seo/wp-seo.php')) {
        // Guardar la metadescripción en el campo de Yoast
        update_post_meta($post_id, '_yoast_wpseo_metadesc', $meta_descripcion);
    } else {
        // Guardar en el campo genérico
        update_post_meta($post_id, 'meta_description', $meta_descripcion);
    }

    return rest_ensure_response(array(
        'success' => true,
        'message' => 'Metadescripción guardada correctamente.',
    ));
}