# smartmetadesc

## Descripción
**smartmetadesc** es un plugin de WordPress que facilita la gestión de las entradas publicadas al mostrar en el escritorio de administración (dashboard) una lista de las entradas con información sobre el estado de su meta descripción, y permite generar una metadescripción a través de una IA basándose en el contenido de la entrada. Además, agrega un enlace dentro de la sección **Herramientas** del menú de administración para acceder a una página personalizada con esta misma información.

---

## Características
- Añade un submenú en **Herramientas** para acceder a una página personalizada con esta funcionalidad.
- Compatible con entradas estándar de WordPress.
- Lista las entradas publicadas con metadescripción vacía en el escritorio de administración.
- Permite generar una metadescripción para cada entrada
- Usa una API Key proporcionada por Groq
---

## Requisitos
- WordPress 5.0 o superior.
- Acceso a la administración de WordPress.

---

## Instalación
1. Descarga el archivo ZIP del plugin desde el repositorio.
2. Ve al panel de administración de WordPress y accede a **Plugins > Añadir nuevo**.
3. Haz clic en **Subir plugin** y selecciona el archivo ZIP.
4. Haz clic en **Instalar ahora** y activa el plugin.

---

## Uso
1. Activa el plugin desde el menú **Plugins** de WordPress.
2. Para ver la lista de entradas:
   - Accede a **Herramientas > Smart Meta Desc** desde el menú lateral.
3. Verás una lista de entradas publicadas sin metadescripción.
4. Cada entrada se mostrará junto a un botón "Generar Metadesc", que mostrará un textarea con una metadescricpción generada por IA

---

## Configuración
1. Puedes seleccionar el número de entradas por defecto que se muestra
2. Puedes seleccionar qué modelo de IA generará las metadescripciones - AÚN NO FUNCIONA
3. Puedes poner una API Key - aún no funciona

---

## Créditos
- **Autor:** [Jorge GL - likonet.es]
- **Versión:** 1.1