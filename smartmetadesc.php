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





// Página de configuración del plugin
function smartmetadesc_config_page() {
    // Verificar si el usuario tiene permisos para administrar opciones
    if (!current_user_can('manage_options')) {
        return;
    }

    // Comprobar si el formulario de configuración fue enviado
    if (isset($_POST['submit'])) {
        // Guardar el número de entradas predeterminado
        update_option('smartmetadesc_num_posts', intval($_POST['num_posts']));
        echo '<div class="updated"><p>Configuración guardada.</p></div>';
    }

    // Obtener la configuración guardada (valor por defecto es 10)
    $num_posts = get_option('smartmetadesc_num_posts', 10);

    echo '<div class="wrap">';
    echo '<h1>Configuración de Smart Meta Desc</h1>';
    echo '<form method="post" action="">';
    echo '<table class="form-table">';
    echo '<tr>';
    echo '<th scope="row"><label for="num_posts">Número de entradas a mostrar por defecto:</label></th>';
    echo '<td><input type="number" id="num_posts" name="num_posts" value="' . esc_attr($num_posts) . '" min="1" /></td>';
    echo '</tr>';
    echo '</table>';
    echo '<p class="submit"><button type="submit" name="submit" class="button button-primary">Guardar cambios</button></p>';
    echo '</form>';
    echo '</div>';
}

// Página principal del plugin (mostrar las entradas con meta descripción vacía)
function smartmetadesc_render_page() {
    echo '<div class="wrap">';
    echo '<h1>Smart Meta Desc</h1>';

    // Obtener todas las entradas con meta descripción vacía
    $all_posts = get_posts(array(
        'post_type'   => 'post',   // Tipo de contenido (entradas)
        'post_status' => 'publish', // Solo entradas publicadas
        'numberposts' => -1        // Obtener todas las entradas (sin límite)
    ));

    // Filtrar las entradas con meta descripción vacía
    $empty_meta_posts = array();
    foreach ($all_posts as $post) {
        $meta_description = get_post_meta($post->ID, 'meta_description', true);
        if (empty($meta_description)) {
            $empty_meta_posts[] = $post; // Guardar solo las entradas con meta descripción vacía
        }
    }

    // Obtener el número de entradas a mostrar desde el formulario o usar un valor por defecto
    $num_posts = isset($_GET['num_posts']) ? intval($_GET['num_posts']) : 10;

    // Contar las entradas con meta descripción vacía
    $empty_meta_count = count($empty_meta_posts);

    // Mostrar el número de entradas con meta descripción vacía
    echo '<p><strong>' . $empty_meta_count . '</strong> entradas con meta descripción vacía.</p>';

    // Formulario para seleccionar el número de entradas
    echo '<form method="get" action="">';
    echo '<input type="hidden" name="page" value="smartmetadesc-lista" />';
    echo '<label for="num_posts">Número de entradas a mostrar:</label>';
    echo '<input type="number" id="num_posts" name="num_posts" value="' . esc_attr($num_posts) . '" min="1" max="' . count($empty_meta_posts) . '" />';
    echo '<button type="submit" class="button button-primary">Actualizar</button>';
    echo '</form>';

    // Mostrar las entradas con meta descripción vacía, según el número seleccionado
    if ($empty_meta_count > 0) {
        $posts_to_show = array_slice($empty_meta_posts, 0, $num_posts); // Mostrar solo el número seleccionado
        echo '<ul>';
        foreach ($posts_to_show as $post) {
            // Mostrar el título y el estado de la meta descripción
            echo '<li>' . esc_html($post->post_title) . ' <strong>(Sin meta descripción)</strong></li>';
        }
        echo '</ul>';
    } else {
        echo '<p>No hay entradas con meta descripción vacía.</p>';
    }

    echo '</div>';
}
