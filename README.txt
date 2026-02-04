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
- bot/src/BotService.php: lógica del bot (puedes editarla sin tocar proveedores).
- bot/src/TwilioProvider.php: integración Twilio.
- bot/src/MetaWhatsAppProvider.php: integración Meta WhatsApp.

Configuración por variables de entorno (en iPage puedes definirlas en el
archivo de configuración o en el panel):

Proveedor:
- BOT_PROVIDER=twilio | meta

Twilio (sandbox o número oficial):
- TWILIO_ACCOUNT_SID=
- TWILIO_AUTH_TOKEN=
- TWILIO_WHATSAPP_FROM=whatsapp:+14155238886 (o tu número habilitado)

Meta WhatsApp Cloud API:
- WHATSAPP_ACCESS_TOKEN=
- WHATSAPP_PHONE_NUMBER_ID=
- WHATSAPP_API_BASE=https://graph.facebook.com/v20.0

Uso rápido:
1) Apunta el webhook de Twilio a:
   https://tudominio.com/bot/webhook.php
2) Para enviar un mensaje manual de prueba:
   POST https://tudominio.com/bot/send.php
   body: to=whatsapp:+XXXXXXXXXXX&message=Hola

Cambiar de proveedor:
- Ajusta BOT_PROVIDER=meta cuando quieras usar la API oficial.
- La lógica del bot se mantiene en BotService.php.
