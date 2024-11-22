document.addEventListener("DOMContentLoaded", () => {
    function showTextarea(button) {
        console.log("Botón clickeado");

        // Ocultar todos los contenedores abiertos
        const allContainers = document.querySelectorAll(".textarea-container");
        allContainers.forEach(container => {
            container.style.display = "none";
        });

        // Mostrar el contenedor relacionado con el botón
        var container = button.closest("li").querySelector(".textarea-container");
        if (container) {
            console.log("Contenedor encontrado:", container);
            container.style.display = "block";
        } else {
            console.error("No se encontró el contenedor.");
        }
    }

    // Añadir el evento a los botones
    const buttons = document.querySelectorAll(".button-secondary");
    buttons.forEach(button => {
        button.addEventListener("click", () => showTextarea(button));
    });
});
