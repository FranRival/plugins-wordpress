# 🚀 WordPress Custom Plugins – Content & Performance Suite

Este proyecto contiene **dos plugins personalizados para WordPress**, diseñados para mejorar **SEO, UX y performance**, sin añadir complejidad innecesaria ni dependencias externas.

Ambos plugins son **ligeros, automáticos y desacoplados**, pensados para sitios con alto volumen de contenido e imágenes.

---

## 📦 Plugins incluidos

### 1️⃣ 🎨 Display Tags

**Descripción:**  
Muestra automáticamente las **etiquetas (tags) del post** en dos ubicaciones estratégicas:

- 📌 Debajo del **título del post**
- 📎 Al **final del contenido**

Las etiquetas se renderizan con **colores variables**, mejorando la experiencia visual, la navegación interna y las señales semánticas para SEO.

#### 📂 Carpeta

/wp-content/plugins/display-tags/


#### ✨ Características
- ✅ Inserción automática (no modifica el editor)
- ✅ Compatible con posts estándar
- 🎨 Colores dinámicos por etiqueta
- 🔗 Mejora el enlazado interno
- ⚡ Plugin liviano y rápido
- 🔒 Sin dependencias externas

#### 🎯 Casos de uso
- Blogs con muchas etiquetas
- Sitios orientados a SEO semántico
- Mejora visual sin hard-coding
- Navegación interna más clara para el usuario

---

### 2️⃣ 🖼️ ImgBox Load More

**Descripción:**  
Carga progresiva de imágenes **ImgBox** dentro del contenido del post.

📸 Muestra inicialmente **9 imágenes**  
➕ El resto se carga mediante un botón **“View X more images”**, usando **AJAX**, sin recargar la página.

⚠️ Este plugin **solo se activa en posts que tengan etiquetas específicas**, por diseño.

#### 📂 Carpeta


#### ✨ Características
- ✅ Inserción automática (no modifica el editor)
- ✅ Compatible con posts estándar
- 🎨 Colores dinámicos por etiqueta
- 🔗 Mejora el enlazado interno
- ⚡ Plugin liviano y rápido
- 🔒 Sin dependencias externas

#### 🎯 Casos de uso
- Blogs con muchas etiquetas
- Sitios orientados a SEO semántico
- Mejora visual sin hard-coding
- Navegación interna más clara para el usuario

---

### 2️⃣ 🖼️ ImgBox Load More

**Descripción:**  
Carga progresiva de imágenes **ImgBox** dentro del contenido del post.

📸 Muestra inicialmente **9 imágenes**  
➕ El resto se carga mediante un botón **“View X more images”**, usando **AJAX**, sin recargar la página.

⚠️ Este plugin **solo se activa en posts que tengan etiquetas específicas**, por diseño.

#### 📂 Carpeta

/wp-content/plugins/imgbox-load-more/



#### ✨ Características
- 👁️ Muestra solo las primeras 9 imágenes
- 🔘 Botón dinámico con contador de imágenes restantes
- ⚙️ Carga vía AJAX (sin reload)
- 🧠 Uso de `transients` por post
- 🎯 JS y CSS desacoplados
- 🚫 No afecta posts sin etiquetas activadoras

#### 🏷️ Etiquetas activadoras
El plugin se ejecuta **solo si el post contiene al menos una** de las siguientes etiquetas:

- diciembre-2025  
- january-2026  
- february-2026  
- march-2026  
- april-2026  
- may-2026  
- june-2026  
- july-2026  
- august-2026  
- september-2026  
- october-2026  
- november-2026  
- december-2026  

#### 🎯 Casos de uso
- Posts con **muchas imágenes ImgBox**
- Optimización de **LCP / CLS**
- Mejor experiencia móvil 📱
- Control visual sin paginación tradicional

---

## 🧩 Convivencia entre plugins

- ✅ Ambos plugins pueden estar activos simultáneamente
- ❌ No comparten hooks ni lógica interna
- 🎯 No hay conflictos de CSS ni JS
- 🧠 Cada uno cumple una función clara:
  - **Display Tags** → SEO + estructura
  - **ImgBox Load More** → Performance + UX

---



---

### 3️⃣ ⏱️ Auto Last 24 Hours Tag

**Descripción:**  
Este plugin agrega automáticamente la etiqueta **`last 24 hours`** a cada post **en el momento exacto de su publicación** y la elimina de forma automática una vez que han pasado **24 horas**.

Está pensado como un **mecanismo temporal**, útil para destacar contenido reciente sin intervención manual.

---

#### 📂 Carpeta
/wp-content/plugins/auto-last-24-hours-tag/


---

#### ⚙️ Cómo funciona internamente

1. 📝 **Al publicar un post**
   - Se añade automáticamente la etiqueta:
     ```
     last 24 hours
     ```
   - Solo aplica a posts estándar (`post`)

2. ⏰ **Cron automático**
   - Se programa un evento **cada hora** usando WP-Cron
   - No requiere tráfico constante ni configuración manual

3. 🧹 **Limpieza automática**
   - El plugin revisa todos los posts con la etiqueta `last-24-hours`
   - Si el post tiene más de **24 horas desde su publicación**:
     - ❌ Se elimina la etiqueta
     - ✅ El post queda limpio sin intervención humana

---

#### ✨ Características
- ⚡ 100% automático
- 🧠 Basado en tiempo real (timestamp del post)
- 🏷️ Gestión dinámica de etiquetas
- 🔄 Limpieza periódica vía cron
- 🔒 No modifica contenido ni editor
- 🚫 No afecta páginas ni CPTs

---

#### 🎯 Casos de uso
- 🆕 Sección “contenido reciente”
- 🔥 Destacar posts nuevos durante 24h
- 📊 Filtros dinámicos por frescura
- 🧩 Activador temporal para otros plugins
- 🤖 Automatización editorial sin esfuerzo

---

#### 🧩 Integración con otros plugins

Este plugin **no actúa visualmente por sí solo**, pero es ideal como **trigger lógico** para otros sistemas:

- Puede usarse para:
  - mostrar banners
  - activar estilos especiales
  - habilitar features temporales
  - priorizar contenido reciente
- Compatible con:
  - **Display Tags** → visibilidad automática
  - **ImgBox Load More** → activación por etiquetas
  - Cualquier lógica basada en `has_tag()`

---

#### ⚠️ Notas técnicas
- Usa `WP-Cron` (requiere que WordPress ejecute cron)
- El intervalo real depende del tráfico del sitio
- La etiqueta se elimina aunque el post no vuelva a editarse
- No crea tablas ni opciones en la base de datos

---

#### 🔮 Mejoras futuras posibles
- ⏱️ Ventana configurable (12h, 48h, 7 días)
- ⚙️ Panel de ajustes en el admin
- 🧩 Compatibilidad con Custom Post Types
- 🏷️ Etiqueta configurable desde settings

---

---

### 4️⃣ ⚡ Core Web Vitals Pro (AJAX Safe)

**Descripción:**  
Plugin encargado de la **carga progresiva de imágenes** en posts etiquetados por **mes/año**, con foco en **mejorar Core Web Vitals** (LCP, CLS y TBT) mediante reducción del contenido inicial renderizado.

Este plugin **intercepta el contenido del post** y muestra únicamente un subconjunto inicial de imágenes, cargando el resto bajo demanda vía **AJAX seguro con nonce**.

---

#### 📂 Carpeta
/wp-content/plugins/core-web-vitals-pro/


---

#### ⚙️ Funcionamiento general

- 🔍 Detecta posts individuales (`is_single`)
- 🏷️ Se activa **solo si el post contiene etiquetas específicas**
- 🖼️ Renderiza inicialmente **6 imágenes**
- ➕ Inserta un botón **“Load more images”**
- 🔁 Carga el resto de imágenes en bloques vía AJAX
- 🔐 Protege las peticiones con `nonce`

---

#### ✨ Características
- ⚡ Optimización directa de Core Web Vitals
- 🧠 Uso de `DOMDocument` para parsing seguro de HTML
- 🔘 Carga progresiva por lotes (`batch`)
- 🔄 AJAX sin recarga de página
- 🔒 Seguridad mediante `nonce`
- 🚫 No modifica el editor ni el contenido guardado

---

#### 🏷️ Etiquetas activadoras
El plugin se ejecuta **solo si el post contiene al menos una** de las siguientes etiquetas:

- diciembre-2025  
- february-2026  
- march-2026  
- april-2026  
- may-2026  
- june-2026  
- july-2026  
- august-2026  
- september-2026  
- october-2026  
- november-2026  
- december-2026  

---

#### 🎯 Casos de uso
- Posts con **gran volumen de imágenes**
- Mejora de métricas **LCP / CLS**
- Control estricto del contenido inicial
- Sitios con enfoque en performance técnico
- Gestión de contenido visual por temporadas

---

#### ⚠️ Advertencia importante

- 🚨 Este plugin **reemplaza completamente el contenido renderizado**
- 🚨 Tiene **prioridad más alta** que otros plugins similares
- 🚨 En caso de coexistir con plugins que hacen lo mismo:
  - **Este plugin tiene precedencia**

👉 Actualmente entra en **conflicto directo** con *ImgBox Load More* (ver sección de conflicto).

---

#### 🔮 Plan futuro
- 🔁 Fusión con ImgBox Load More
- 🧩 Unificación de lógica por etiquetas
- ⚙️ Control dinámico de límites (imágenes iniciales / batch)
- 🧠 Arquitectura única sin duplicidades

---


---

## ⚠️ Conflicto entre plugins de carga progresiva

Actualmente existen **dos plugins activos que realizan la misma función base**:  
👉 **carga progresiva de imágenes mediante botón (“Load more”) en posts etiquetados**.

### 🔴 Plugins en conflicto
- **Core Web Vitals Pro (AJAX Safe)**
- **ImgBox Load More (Content-Based)**

Ambos:
- Interceptan `the_content`
- Ocultan imágenes
- Insertan un botón de carga
- Se activan por **etiquetas (tags)**

⚠️ **Esto provoca un conflicto funcional**, ya que **no están diseñados para convivir**.

---

## 🧠 ¿Cuál plugin controla la etiqueta `diciembre-2025`?

👉 **Core Web Vitals Pro (AJAX Safe)**

### Motivos técnicos:
- Escucha explícitamente la etiqueta `diciembre-2025`
- Se ejecuta con **mayor prioridad**:
  ```php
  add_filter('the_content', ..., 9);




## 🔧 Instalación

1. Subir cada plugin (ZIP o carpeta) a:

/wp-content/plugins/

2. Activarlos desde **WordPress → Plugins**
3. 🎉 Listo, no requieren configuración adicional

---

## 📝 Notas técnicas

- 🔒 Plugins diseñados como **plugins cerrados**, no genéricos
- 🚫 No exponen panel de configuración (por intención)
- 🧱 Arquitectura simple y mantenible

### 🔮 Mejoras futuras previstas
- 📅 Rango automático por año
- ⚙️ Página de settings opcional
- 🧩 Compatibilidad con Custom Post Types (CPTs)

---

## 👨‍💻 Autor / Uso interno
Plugins desarrollados para **infraestructura de contenido personalizada**, orientados a sitios con alta carga visual y enfoque SEO técnico.

---
