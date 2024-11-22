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
