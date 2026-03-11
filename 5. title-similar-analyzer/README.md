#### title-similarity-analyzer
**cargar modulos
**registrar hooks
**activar plugin

#### install.php
**Creartablas SQL
**wp_title_word_index |word|post_id|

#### indexer.php
**leer todos los titulos
**limpiar palabras
**insertar palabras en tabla indice

#### similarity-engine.php
**encontrar coincidencias entre titulos

#### stopwords.php
**eliminar palabras inutiles

#### admin-page.php
**mostrar resultados de wordpress

#### Build index
** el plugin:
. lee los 40k titulos
. limpia palabras
. crea indices
. guarda datos


**** errores: la base de datos. 
*** SWFox no aparece Octo
*** En su listado en el top me aparecen las palabras clave que mas se repiten. Pero no me aparece la segunda KW. lo que significa que no tiene tanto poder. HAY QUE ARREGLAR EL CRUCE DE PALABRAS CLAVE.

**** Mejoras:
*** ya podemos implementar un sistema de calificacion. una vez hecho, se colocaran solo los post mas calificados en el related title similarity.  
