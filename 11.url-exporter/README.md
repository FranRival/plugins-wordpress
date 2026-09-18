# URL Exporter (multi-idioma)

Plugin de WordPress para exportar todas las URLs publicadas del sitio a un
CSV, sin los límites de exportación de Google Search Console (1,000 filas)
ni depender de que Google ya haya indexado todo. Opcionalmente multiplica
cada URL por los códigos de idioma/país que uses (ej. los de GTranslate),
para reconstruir el mapa completo de URLs esperadas.

## Instalación

1. Sube la carpeta o el .zip en **Plugins → Añadir nuevo → Subir plugin**.
2. Actívalo.
3. Ve a **Herramientas → URL Exporter**.

## Uso

1. **Elige qué exportar**: marca los post types que quieras incluir
   (posts, páginas, tu custom post type de videos, etc.). Se muestran
   solo los públicos, y verás cuántos posts publicados tiene cada uno.
2. **Códigos de idioma (opcional)**: pega uno por línea, tal como
   aparecen en tus URLs actuales (ej. `es`, `en`, `fr`...). Si los dejas
   vacíos, el CSV solo trae la URL original de cada post/página, sin
   variantes.
   - Si tienes las 14 casillas marcadas en GTranslate (según tu
     captura: fr, ar, zh-CN, de, cs, nl, en, it, ja, pt, ru, es, uk, vi),
     pega esos 14 códigos aquí y el CSV te va a dar prácticamente el
     mismo total que las 82 mil páginas indexadas en Search Console —
     (posts publicados) × (14 idiomas + 1 original).
3. Click en **Generar CSV**. Procesa en lotes de 1,000 posts vía AJAX
   para no saturar el servidor ni provocar timeouts, y muestra una
   barra de progreso.
4. Al terminar aparece el botón **Descargar CSV**.

## Columnas del CSV

| Columna | Contenido |
|---|---|
| ID | ID del post en WordPress |
| post_type | Tipo de contenido |
| title | Título del post |
| url | URL completa (original o con prefijo de idioma) |
| lang_code | `(original)` o el código de idioma de esa fila |

## Notas

- El archivo se guarda en `wp-content/uploads/url-exporter/`. Bórralo
  cuando termines de usarlo — no es información sensible (son URLs
  públicas de tu propio sitio), pero no hay razón para dejarlo ahí.
- Este exportador te da las URLs que **deberían** existir según tu
  contenido y tu configuración de idiomas — no confirma que Google las
  tenga indexadas. Para cruzar ambas listas (qué se exportó aquí vs. qué
  aparece realmente en Search Console) lo ideal es comparar contra el
  sitemap.xml del sitio.
- Si tu custom post type de videos usa un slug distinto al de la URL
  visible (por ejemplo, por un rewrite personalizado), `get_permalink()`
  igual devuelve la URL final correcta, así que no debería haber
  problema.
