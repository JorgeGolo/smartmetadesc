document.addEventListener("DOMContentLoaded", () => {
    async function showTextarea(button) {
        console.log("Botón clickeado");

        // Ocultar todos los contenedores abiertos
        const allContainers = document.querySelectorAll(".textarea-container");
        allContainers.forEach(container => {
            container.style.display = "none";
        });

        // Mostrar el contenedor relacionado con el botón
        const container = button.closest("li").querySelector(".textarea-container");
        if (container) {
            console.log("Contenedor encontrado:", container);
            container.style.display = "block";

            // Seleccionar el textarea dentro del contenedor
            const textarea = container.querySelector("textarea");
            if (textarea) {
                // Llamar a la API y mostrar el saludo en el textarea
                textarea.value = "Cargando saludo..."; // Mostrar mensaje temporal
                const saludo = await obtenerSaludo();
                textarea.value = saludo; // Mostrar el saludo en el textarea
            } else {
                console.error("No se encontró el textarea.");
            }
        } else {
            console.error("No se encontró el contenedor.");
        }
    }

    // Añadir el evento a los botones
    const buttons = document.querySelectorAll(".button-secondary");
    buttons.forEach(button => {
        button.addEventListener("click", () => showTextarea(button));
    });

    // Función para obtener el saludo desde la API
    async function obtenerSaludo() {
        const url = "https://api.groq.com/openai/v1/chat/completions"; // Endpoint de la API
        const apiKey = "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"; // Tu clave API proporcionada por Groq

        const payload = {
            messages: [
                {
                    role: "user",
                    content: "Write a friendly greeting message!", // Ajusta el contenido según lo que necesites
                },
            ],
            model: "llama3-8b-8192", // El modelo que deseas usar
        };

      try {
            const response = await fetch(url, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": `Bearer ${apiKey}`,
                },
                body: JSON.stringify(payload),
            });
        
            if (!response.ok) {
                throw new Error(`Error en la API: ${response.statusText} - ${response.status}`);
            }
        
            const data = await response.json();
            return data.choices[0].message.content;
        } catch (error) {
            console.error("Error al obtener el saludo de la API de Groq:", error);
            return "Error al obtener el saludo.";
        }
    }
});
