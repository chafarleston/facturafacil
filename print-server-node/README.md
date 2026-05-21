FacturaFacil Print Server (Node.js)
Servidor de impresión REST para impresoras térmicas ESC/POS. Soluciona automáticamente el problema de la ñ y tildes.
Instalación
bash
Copy

npm install

Uso
bash
Copy

npm start

El servidor escucha en http://0.0.0.0:9100.
Requisitos por plataforma
Windows

    Colocar raw-print.ps1 en la misma carpeta que server.js
    PowerShell disponible

Linux / Mac

    lp o lpr instalados (CUPS)

Endpoints
GET /status
Health check.
GET /printers
Lista impresoras disponibles.
POST /print
Imprime datos en base64.
Body:
JSON
Copy

{
"mode": "escpos",
"printer": "CocinaLocal",
"data": "BASE64_DEL_PAYLOAD_ESC_POS"
}

Si el payload contiene UTF-8, el servidor auto-convierte a CP850 e inserta el comando ESC t 0x02.
POST /print-raw
Imprime texto plano con encoding configurable.
Body:
JSON
Copy

{
"printer": "CocinaLocal",
"text": "Señor José\nCafé: $2.50",
"encoding": "cp850"
}

POST /print-escpos-text
El servidor genera el ticket ESC/POS completo desde texto UTF-8.
Body:
JSON
Copy

{
"printer": "CocinaLocal",
"text": "Señor José\nCafé con leche: $2.50",
"bold": false,
"align": "left",
"cut": true
}

Este endpoint es el más recomendado si tu cliente solo maneja texto y no quiere ensuciarse con comandos binarios ESC/POS.
Solución del problema ñ
El servidor detecta automáticamente si los datos ESC/POS vienen en UTF-8 y:

    Inserta el comando ESC t 0x02 (seleccionar code page PC850)
    Convierte el texto de UTF-8 a CP850 usando iconv-lite

Si tu cliente ya genera ESC/POS correctamente codificado, la detección no hará daño (solo verifica si falta el comando de code page).
