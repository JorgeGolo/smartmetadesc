document.addEventListener("DOMContentLoaded", () => {
    const GROQ_API_URL = "https://api.groq.com/openai/v1/chat/completions";
    const API_KEY = smartMetaDescConfig.apiKey; // Obtiene la clave API desde la configuración

    if (!API_KEY) {
        console.error("La clave API no está configurada.");
        return;
    }

    // Función para manejar el botón "Generar Metadescripción"
    function handleGenerateButtonClick(button) {
        
        // Mostrar el contenedor del botón clickeado
        const container = button.closest("li")?.querySelector(".textarea-container");
        if (!container) {
            console.error("No se encontró el contenedor.");
            return;
        }

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

        obtenerContenidoEntrada(postId)
            .then(contenidoEntrada => obtenerSaludo(contenidoEntrada))
            .then(saludo => {
                textarea.value = saludo;
            })
            .catch(error => {
                textarea.value = "Error al cargar los datos.";
                console.error(error);
            });
    }


    // Añadir eventos a los botones "Generar Metadescripción"
    document.querySelectorAll(".smd_buttongen").forEach(button => {
        button.addEventListener("click", () => handleGenerateButtonClick(button));
    });

    // Añadir eventos a los botones "Guardar"
    document.querySelectorAll(".smd_buttonsave").forEach(button => {
        button.addEventListener("click", () => guardarMetaDescripcion(button));
    });

    // Función para guardar la metadescripción
    async function guardarMetaDescripcion(button) {
        const container = button.closest(".textarea-container");
        const textarea = container.querySelector("textarea");

        if (!textarea) {
            console.error("No se encontró el textarea asociado.");
            return;
        }

        const metaDescripcion = textarea.value;
        const postId = button.closest("li").querySelector(".smd_buttongen").getAttribute("data-post-id");

        if (!postId) {
            console.error("No se encontró el ID de la publicación.");
            return;
        }

        if (!metaDescripcion.trim()) {
            alert("La metadescripción no puede estar vacía.");
            return;
        }

        try {
            // Enviar la metadescripción a la API
            const response = await fetch(`/wp-json/smartmetadesc/v1/guardar/${postId}`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ metaDescripcion }),
            });

            if (!response.ok) {
                throw new Error(`Error en la API: ${response.statusText}`);
            }

            const data = await response.json();
            console.log("Metadescripción guardada exitosamente:", data);
            alert("Metadescripción guardada correctamente.");
        } catch (error) {
            console.error("Error al guardar la metadescripción:", error);
            alert("Hubo un problema al guardar la metadescripción.");
        }
    }

    // Funciones auxiliares
    async function llamarApiGroq(mensaje, modelo) {
        try {
            const response = await fetch(GROQ_API_URL, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": `Bearer ${API_KEY}`,
                },
                body: JSON.stringify({
                    messages: [{ role: "user", content: mensaje }],
                    model: modelo,
                }),
            });

            if (!response.ok) {
                throw new Error(`Error en la API: ${response.statusText}`);
            }

            const data = await response.json();
            return data.choices[0].message.content.trim().replace(/^"|"$/g, '');
        } catch (error) {
            console.error("Error al comunicarse con la API de Groq:", error);
            throw error;
        }
    }

    async function obtenerSaludo(contenido) {
        const mensaje = `Devuelve una metadescripción de longitud entre 150 y 160 caracteres, en una sola frase, sin mensajes introductorios, para la entrada con este contenido: ${contenido}`;
        return await llamarApiGroq(mensaje, "llama3-8b-8192");
    }

    async function obtenerContenidoEntrada(postId) {
        try {
            const response = await fetch(`/wp-json/smartmetadesc/v1/entrada/${postId}`);
            if (!response.ok) {
                throw new Error(`Error en la API de WordPress: ${response.statusText}`);
            }

            const data = await response.json();
            const titulo = data.title || "Sin título";
            const contenido = data.content || "Contenido no disponible.";
            return `${titulo}. ${contenido}`;
        } catch (error) {
            console.error("Error al obtener el contenido de la entrada:", error);
            throw error;
        }
    }
});