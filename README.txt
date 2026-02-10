Tanto el bot como la Landing Page estaran hosteados en ipage.
Debido a que ya contamos con este servicio de host.

Las credenciales de ipage estan en el servidor de la empresa:
    Sistemas\Pagina Web Ipage

========================
Capa de bot WhatsApp (PHP)
========================

La carpeta "bot" contiene una capa intermedia para usar Twilio en pruebas y
luego cambiar a la API oficial de WhatsApp (Meta) sin tocar la lógica del bot.

Archivos principales:
- bot/webhook.php: endpoint para recibir mensajes entrantes.
- bot/send.php: endpoint sencillo para enviar mensajes de prueba.
- bot/src/BotService.php: lógica del bot (flujo principal).
- bot/src/UserRepository.php: acceso a base de datos (usuarios, etiquetas, planes, suscripciones).
- bot/src/TwilioProvider.php: integración Twilio.
- bot/src/MetaWhatsAppProvider.php: integración Meta WhatsApp.

Configuración por variables de entorno (en iPage puedes definirlas en el
archivo de configuración o en el panel):

Proveedor:
- BOT_PROVIDER=twilio | meta

Base de datos MySQL (finanzas60s):
- DB_HOST=127.0.0.1
- DB_PORT=3306
- DB_NAME=finanzas60s
- DB_USER=tu_usuario_mysql
- DB_PASSWORD=tu_password_mysql
- DB_CHARSET=utf8mb4

Twilio (sandbox o número oficial):
- TWILIO_ACCOUNT_SID=
- TWILIO_AUTH_TOKEN=
- TWILIO_WHATSAPP_FROM=whatsapp:+14155238886 (o tu número habilitado)

Meta WhatsApp Cloud API:
- WHATSAPP_ACCESS_TOKEN=
- WHATSAPP_PHONE_NUMBER_ID=
- WHATSAPP_API_BASE=https://graph.facebook.com/v20.0

Flujo implementado para pruebas:
1) Usuario nuevo recibe bienvenida y suscripción Gratuita (7 días).
2) Se pide nombre.
3) Se pide correo.
4) Se pide nivel (1/2/3). Si responde mal, se vuelve a pedir.
5) Se asigna etiqueta según nivel: frio / tibio / caliente.
6) Se envían Tip1 ... Tip7 (controlados con `paso_tip`).
7) Al terminar Tip7 se pregunta Confirmar/Cancelar para compra.
8) Confirmar => plan Mensual (30 días), Cancelar => suscripción cancelada.

Uso rápido:
1) Apunta el webhook de Twilio a:
   https://tudominio.com/bot/webhook.php
2) Para enviar un mensaje manual de prueba:
   POST https://tudominio.com/bot/send.php
   body: to=whatsapp:+XXXXXXXXXXX&message=Hola

Cambiar de proveedor:
- Ajusta BOT_PROVIDER=meta cuando quieras usar la API oficial.
- La lógica del bot se mantiene en BotService.php.

Ejecución local (para pruebas):
1) Exporta variables de entorno en tu terminal (ejemplo):
   export BOT_PROVIDER=twilio
   export DB_HOST=127.0.0.1
   export DB_PORT=3306
   export DB_NAME=finanzas60s
   export DB_USER=root
   export DB_PASSWORD=secret
   export TWILIO_ACCOUNT_SID=ACxxxxxxxx
   export TWILIO_AUTH_TOKEN=xxxxxxxx
   export TWILIO_WHATSAPP_FROM=whatsapp:+14155238886
2) Levanta un servidor PHP local desde la raíz del repo:
   php -S 0.0.0.0:8080 -t .
3) Si necesitas recibir webhooks de Twilio en local, usa un túnel como ngrok:
   ngrok http 8080
   Luego apunta el webhook de Twilio a:
   https://<tu-subdominio>.ngrok.io/bot/webhook.php

Twilio (Sandbox de WhatsApp):
- En la consola de Twilio, configura el campo "When a message comes in" con:
  https://tudominio.com/bot/webhook.php (o la URL pública de ngrok si estás en local).
- Método HTTP recomendado: POST.
- Para enviar mensajes de prueba desde tu propio endpoint:
  POST https://tudominio.com/bot/send.php con body:
  to=whatsapp:+XXXXXXXXXXX&message=Hola

Nota de espera entre tips:
- La espera de 5 minutos está en `bot/src/BotService.php` en el comentario:
  "AQUÍ se realiza la espera entre tips (5 minutos para pruebas)".
