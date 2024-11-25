// groqApi.js
import Groq from "groq-sdk";

const groq = new Groq({ apiKey: process.env.GROQ_API_KEY });

/**
 * Envía un mensaje a la API de Groq y devuelve la respuesta.
 * @param {string} userMessage - El mensaje del usuario.
 * @returns {Promise<string>} - La respuesta generada por la API.
 */
export async function getApiResponse(userMessage) {
  try {
    const completion = await groq.chat.completions.create({
      messages: [
        {
          role: "user",
          content: userMessage,
        },
      ],
      model: "llama3-8b-8192",
    });
    return completion.choices[0].message.content;
  } catch (error) {
    console.error("Error al comunicarse con la API de Groq:", error);
    throw new Error("Error al obtener respuesta de la API");
  }
}
