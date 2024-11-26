document.addEventListener("DOMContentLoaded", () => {
    const GROQ_API_URL = "https://api.groq.com/openai/v1/chat/completions";
    const API_KEY = "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";

    // Mostrar y manejar el contenedor de textarea relacionado con un botón
    async function showTextarea(button) {
        console.log("Botón clickeado");

        // Ocultar todos los contenedores
        document.querySelectorAll(".textarea-container").forEach(container => {
            container.style.display = "none";
        });

        // Mostrar el contenedor del botón clickeado
        const container = button.closest("li")?.querySelector(".textarea-container");
        if (!container) {
            console.error("No se encontró el contenedor.");
            return;
        }

        console.log("Contenedor encontrado:", container);
        container.style.display = "block";

        const textarea = container.querySelector("textarea");
        if (!textarea) {
            console.error("No se encontró el textarea.");
            return;
        }

        const postId = button.getAttribute("data-post-id");
        if (!postId) {
            console.error("ID de entrada no proporcionado.");
            textarea.value = "Error al obtener la entrada.";
            return;
        }

        textarea.value = "Cargando datos...";

        try {

            // Obtener el contenido de la entrada
            const contenidoEntrada = await obtenerContenidoEntrada(postId);

            // Obtener el saludo
            const saludo = await obtenerSaludo(contenidoEntrada);



            // Mostrar el saludo y el contenido en el textarea
            textarea.value = `${saludo}`;
        } catch (error) {
            textarea.value = "Error al cargar los datos.";
            console.error(error);
        }
    }

    // Realizar solicitud a la API de Groq
async function llamarApiGroq(mensaje, modelo) {
    try {
        const response = await fetch(GROQ_API_URL, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Authorization": `Bearer ${API_KEY}`,
            },
            body: JSON.stringify({
                messages: [
                    { role: "user", content: mensaje },
                    { role: "assistant", content: "" } // Prefilling con texto vacío
                ],
                model: modelo,
                stop: "\n", // Detenemos al final de la primera línea o al encontrar un salto de línea
                max_tokens: 30 // Limitamos el tamaño de la respuesta generada
            }),
        });

        if (!response.ok) {
            throw new Error(`Error en la API: ${response.statusText} - ${response.status}`);
        }

        const data = await response.json();
        return data.choices[0].message.content.trim();
    } catch (error) {
        console.error("Error al comunicarse con la API de Groq:", error);
        throw error;
    }
}

    // Obtener saludo
    async function obtenerSaludo(contenido) {
        
        mensaje="Devuelve una metadescripción de longitud entre 150 y 160 caracteres, sin ningun mensaje introductorio, para la entrada con este contenido:" + contenido;
        
        return await llamarApiGroq(mensaje, "llama3-8b-8192");
    }

    // Obtener contenido de la entrada desde la API de WordPress
    async function obtenerContenidoEntrada(postId) {
        try {
            const response = await fetch(`/wp-json/smartmetadesc/v1/entrada/${postId}`);
            if (!response.ok) {
                throw new Error(`Error en la API de WordPress: ${response.statusText}`);
            }
            const data = await response.json();
    
            // Combinar título y contenido
            const titulo = data.title || "Sin título";
            const contenido = data.content || "Contenido no disponible.";
    
            return `${titulo}. ${contenido}`; // Devuelve título seguido del contenido
        } catch (error) {
            console.error("Error al obtener el contenido de la entrada:", error);
            throw error;
        }
    }

    // Añadir eventos a los botones
    document.querySelectorAll(".button-secondary").forEach(button => {
        button.addEventListener("click", () => showTextarea(button));
    });
});