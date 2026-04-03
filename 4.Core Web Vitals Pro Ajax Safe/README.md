# Core Web Vitals Pro (AJAX Safe)

Optimizes image-heavy posts by progressively loading images via AJAX, improving Core Web Vitals and overall performance.

## 🚀 Features

- Progressive image loading (AJAX-based)
- Initial render: **6 images**
- Batch loading: **10 images per request**
- Tag-based activation (fully configurable)
- Secure AJAX with nonce validation
- Lightweight and performance-focused

## ⚙️ How It Works

1. Detects if the current post matches configured tags
2. Parses post content and extracts `<img>` elements
3. Renders only the first **6 images**
4. Adds a **"Load more images"** button
5. Loads additional images via AJAX in batches of **10**
6. Updates offset dynamically until all images are loaded

## 🏷️ Tag-Based Control

Activation is controlled via tags set in the admin panel:

**WP Admin → CWV Pro**

Example:  

march-2026, april-2026, may-2026  


## 🔐 Security

- Uses `wp_create_nonce` + `check_ajax_referer`
- Validates post ID and tag conditions before loading

## 📦 Installation

1. Upload plugin to:

/wp-content/plugins/core-web-vitals-pro/  

2. Activate in WordPress
3. Configure tags in:

WP Admin → CWV Pro  


## 📁 Included Files

- `plugin.php` → Core logic (AJAX + DOM parsing)
- `cwv.js` → Frontend AJAX loader
- `cwv.css` → Styles (button, layout)

## 🧠 Notes

- Works only on `post` post type
- Uses `DOMDocument` for precise image parsing
- Prevents unnecessary loading on non-matching posts
- Designed to improve:
- LCP (Largest Contentful Paint)
- CLS (Cumulative Layout Shift)
- TTFB perception

## 👨‍💻 Author

**Emmanuel - Fran Rival**

---

Performance control at content level.

*** We gonna need improve this plugin. UX. necesitamos escalar la calidad del codigo. El nivel de inteligencia es muy bajo comparado con su rendimiento.

Aun no probado luego de las mejoras.

Canibalizacion: Esta compitiendo este plugin con Load More Content... 