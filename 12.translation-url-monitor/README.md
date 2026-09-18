# Translation URL Monitor

Revisa periódicamente (vía WP-Cron) si las URLs traducidas de tu sitio
(`/idioma/categoría/título`) siguen funcionando, y te avisa por correo
apenas detecta **fallas seguidas** — no una sola, para evitar falsas
alarmas por un error de red pasajero.

Funciona sin importar qué plugin de traducción tengas activo (GTranslate
o el nuevo "Local Translate URLs") — solo prueba las URLs desde afuera,
como lo haría un visitante.

## Instalación

1. Sube el .zip en **Plugins → Añadir nuevo → Subir plugin**.
2. Actívalo.
3. Ve a **Herramientas → Translation Monitor**.

## Cómo funciona

1. Elige 2-3 posts al azar (fijos entre corridas, para comparar lo mismo
   cada vez) y los usa como muestra.
2. En cada revisión:
   - Primero prueba esos posts **sin prefijo** (idioma original). Si
     fallan, asume que el sitio completo está caído — no un problema de
     traducción — y te manda un solo aviso de "el sitio no responde",
     sin generar una alerta por cada idioma.
   - Si el sitio base responde bien, prueba cada código de idioma
     configurado (`/fr/`, `/es/`, etc.) contra esos mismos posts.
3. Si un idioma falla varias veces seguidas (configurable, por defecto
   2), te llega un correo. No te vuelve a avisar del mismo problema
   hasta que se resuelva y vuelva a fallar después.

## Configuración

- **Correo para avisos**: a dónde llega la alerta.
- **Códigos a monitorear**: uno por línea, los mismos que uses en tus
  URLs actuales.
- **Posts de muestra**: cuántos posts al azar se usan por chequeo (más
  posts = detección más confiable, pero más peticiones por corrida).
- **Revisar cada**: cada cuánto se dispara el chequeo. Depende de que
  WP-Cron se active con tráfico real al sitio — con 200k de tráfico no
  debería ser un problema, pero si algún día quieres precisión exacta
  al minuto, se puede desactivar WP-Cron y apuntar un cron real del
  hosting a `wp-cron.php`.
- **Fallas seguidas antes de avisar**: para no alarmarte por un solo
  timeout ocasional.

## Botones útiles

- **Revisar ahora**: corre el chequeo en el momento, sin esperar al
  cron — útil para probar que todo esté bien configurado.
- **Elegir otros posts al azar**: cambia la muestra de posts usada.

## Nota

Este plugin no arregla nada por sí solo — solo te avisa. Cuando llegue
el correo de que un idioma dejó de funcionar, ahí es cuando entra el
plugin "Local Translate URLs": lo activas (y desactivas GTranslate) para
recuperar esas URLs.
