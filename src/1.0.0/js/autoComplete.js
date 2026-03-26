// Example url: http://localhost/digital-trends?autocomplete=true&email=mroy&alias=true
// Función para generar un alias aleatorio que comienza con el símbolo '+'
const generateTimestampAlias = () => {
  const now = new Date();
  const year = now.getFullYear();
  const month = String(now.getMonth() + 1).padStart(2, "0");
  const day = String(now.getDate()).padStart(2, "0");
  const hours = String(now.getHours()).padStart(2, "0");
  const minutes = String(now.getMinutes()).padStart(2, "0");
  const seconds = String(now.getSeconds()).padStart(2, "0");

  return `+${year}${month}${day}${hours}${minutes}${seconds}`;
};

// Función para obtener el valor de email desde el parámetro en la URL o generar uno aleatorio
const getEmailFromURL = () => {
  const urlParams = new URLSearchParams(window.location.search);
  const emailParam = urlParams.get("email");
  if (emailParam) {
    const aliasParam = urlParams.get("alias");
    if (aliasParam === "true") {
      const alias = generateTimestampAlias();
      return emailParam + alias + "@makingsense.com"; // Combina el valor del parámetro con el alias basado en la fecha y hora
    }
    return emailParam + "@makingsense.com"; // Combina el valor del parámetro con @makingsense.com
  } else {
    const randomString = Math.random().toString(36).substring(2, 10);
    return randomString + "@makingsense.com";
  }
};

// Función para generar un número de teléfono aleatorio sin código de área
const generateRandomPhoneNumber = () => {
  // Genera un número de 7 dígitos aleatorio
  const phoneNumber = Math.floor(Math.random() * 9000000) + 1000000;
  return phoneNumber.toString();
};

// Función para completar los formularios
const completeForms = () => {
  const autocompleteParam = new URLSearchParams(window.location.search).get("autocomplete");
  // Verifica si el parámetro autocomplete está en "true" en la URL
  if (autocompleteParam === "true") {
    const emailPrefix = getEmailFromURL(); // Obtiene el valor del parámetro ?email= de la URL
    const forms = document.querySelectorAll("form"); // Selecciona todos los formularios en la página

    setTimeout(function () {
      forms.forEach((form) => {
        form.querySelectorAll("input").forEach((input) => {
          if (input.type === "email") {
            input.value = emailPrefix; // Completa el campo de email con el valor personalizado o aleatorio
          } else if (input.type === "tel") {
            input.value = generateRandomPhoneNumber(); // Completa el campo de teléfono
          } else if (input.type === "text" || input.type === "number") {
            // Completa campos de texto y número con valores aleatorios
            input.value = Math.random().toString(36).substring(2, 10);
          } else if (input.type === "checkbox") {
            input.checked = true; // Marca los campos de tipo checkbox
          }
        });

        // Completa los selectores
        form.querySelectorAll("select").forEach((select) => {
          const options = select.querySelectorAll("option");
          const randomIndex = Math.floor(Math.random() * options.length);
          options[randomIndex].selected = true;
        });
      });
    }, 2500); // Establece un delay para evitar que se rompa el intel input
  }
};

// Llama a la función para completar los formularios
completeForms();
// Example url: http://localhost/digital-trends?autocomplete=true&email=mroy&alias=true
