# ImgBox Load More (Content-Based)

WordPress plugin that progressively loads images inside post content. Displays the first images and loads the rest on demand via AJAX.

## 🚀 Features

- Shows first **9 images** by default
- Loads remaining images via **"Load More" button**
- Works only on posts with specific tags
- AJAX-based (no page reload)
- Improves **performance & UX**
- Lightweight and automatic

## ⚙️ How It Works

1. Detects if the current post has specific tags
2. Extracts all `<img>` elements from content
3. Displays the first **9 images**
4. Stores remaining images in a **transient**
5. Adds a **Load More button**
6. Loads hidden images via AJAX on click

## 🏷️ Tag-Based Activation

The plugin only activates on posts with defined tags.

Default tags include:

diciembre-2025, january-2026, february-2026, march-2026, ...  

You can customize tags in:

**WordPress Admin → Tools → ImgBox Load More**

## 📦 Installation

1. Upload plugin to:  

/wp-content/plugins/imgbox-load-more/  

2. Activate it in WordPress
3. Done ✅

## 🧠 Notes

- Only affects `post` post type
- Uses WordPress **transients** for temporary storage
- Removes original `<img>` tags and rebuilds output
- Requires jQuery (included in WordPress)

## 📁 Included Files

- `plugin.php` → Core logic
- `load-more.js` → AJAX handler
- `style.css` → Button styles

## 👨‍💻 Author

**Emmanuel - Fran Rival**

---

Performance first. UX always.