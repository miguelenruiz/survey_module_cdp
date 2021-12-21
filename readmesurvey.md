# Envio de encuestas CDP  

En este modulo de ejemplo, podra encontrar un formulario donde se hace un envio de preguntas a CDP  mezclando el post back api de envio de data del form con el postback Api de survey


### Pre-requisitos 📋

Modulo desarrollado para drupal 8 

### Funcionamiento 🔧
En la ruta de formulario /form/surveycdp  
Se podra encontrar un formulario donde envia informacion al CDP captura  purpose , data del usuario  y a su vez envia las respuestas de las encuestas al cdp  
esto permite tener las respuestas del usuario en una tabla y poderlas cruzar con otra tabla donde esta la informacion personal  


## Construido con 🛠️

Actualmente se desarrollo un modulo con la funcionalidad especifica que utiliza envios con  cURL  pero es posible implementar otras tecnologias (Este modulo es un ejemplo de envio de data)


## Link play book  
https://abi-martech-global.ue.r.appspot.com/en/cdp-do-not-delete/survey-behaviors



## Parametro de envio de data , los campos con asterisco son OBLIGATORIOS

            "abi_dateofbirth": "'.$dateofbirth.'",
            "abi_email": "'.$email.'",
            "abi_phone": "'.$phone.'",
            "abi_first_name": "'.$name.'",
            "abi_last_name": "'.$last_name.'",
            "abi_gender": "'.$gender.'",
            "abi_survey_id": "TEST_SURVEYCDP_11_2021",   // Nombre de la campaña  *
            "abi_survey_title": "TEST_SURVEYCDP_11_2021", // Nombre de la campaña  * 
            "abi_question": "¿Cual es tu cerveza favorita ?",
            "abi_response": "respuesta de la pregunta ",
            "abi_earned_points": 0, // siempre en 0  *
            "abi_program_name": "Brand"  // Marca a la que pertenece ejmple  : Aguila , poker etc *
 




