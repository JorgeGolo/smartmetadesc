<?php
/*
Plugin Name: smartmetadesc
Description: Muestra una lista de los nombres de las entradas publicadas en el escritorio de administración e indica si la meta descripción está vacía.
Version: 1.1
Author: Tu Nombre
*/

add_action('admin_menu', 'smartmetadesc_add_menu');

function smartmetadesc_add_menu() {
    // Agregar un submenú bajo la sección "Herramientas"
    add_submenu_page(
        'tools.php',                     // Página padre (Herramientas)
        'Smart Meta Desc',             // Título de la página
        'Smart Meta Desc',             // Texto del menú
        'manage_options',                // Capacidad requerida
        'smartmetadesc-lista',           // Slug único del menú
        'smartmetadesc_render_page'      // Función que renderiza el contenido
    );
}

function smartmetadesc_render_page() {
    echo '<div class="wrap">';
    echo '<h1>Smart Meta Desc</h1>';

    // Obtener las entradas publicadas
    $posts = get_posts(array(
        'post_type'   => 'post',   // Tipo de contenido (entradas)
        'post_status' => 'publish', // Solo entradas publicadas
        'numberposts' => 10        // Número máximo de entradas a mostrar
    ));

    // Mostrar la lista de entradas
    if (!empty($posts)) {
        echo '<ul>';
        foreach ($posts as $post) {
            // Obtener la meta descripción
            $meta_description = get_post_meta($post->ID, 'meta_description', true);

            // Verificar si está vacía
            $meta_status = empty($meta_description)
                ? '<strong>(Sin meta descripción)</strong>'
                : '<strong>(Meta descripción presente)</strong>';

            // Mostrar el título y el estado de la meta descripción
            echo '<li>' . esc_html($post->post_title) . ' ' . $meta_status . '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>No hay entradas disponibles.</p>';
    }

    echo '</div>';
}
