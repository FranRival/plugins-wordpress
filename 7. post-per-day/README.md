# PostPerDay 

## 📌 Description

PostPerDay is a lightweight WordPress admin plugin that displays how many posts are published or scheduled per day.

It provides a simple visual overview of your publishing activity, helping detect patterns, gaps, or excessive posting.

---

## 🚀 Features

* 📊 Line chart showing posts per day
* 📅 Table with daily post counts
* 🔮 Includes scheduled posts (`future`)
* 📈 Compares:

  * Current period
  * Last week
  * Last year
  * Fast queries using direct database access (`$wpdb`)

---

## 📁 Installation

1. Create a folder:

```
/wp-content/plugins/post-per-day/
```

2. Add the main file:

```
post-per-day.php
```

3. Paste the plugin code inside.

4. Go to WordPress Admin → Plugins

5. Activate **PostPerDay**

---

## 🧭 Usage

1. Go to the WordPress admin panel
2. Click **"Posts per Day"** in the sidebar
3. View:

   * Graph (top)
   * Table (bottom)

---

## 🧠 What It Shows

* Number of posts per day
* Future scheduled posts
* Publishing trends over time

---

## ⚙️ Technical Notes

* Uses `post_date_gmt` for accurate scheduling data
* Includes both:

  * `publish`
  * `future` post statuses
* Chart powered by Chart.js (CDN)

---

## ⚠️ Limitations

* Week comparison may not align perfectly with future dates
* No filtering options (yet)
* No timezone adjustments beyond GMT logic

---

## 🔧 Version

**1.3**

---

## 📄 License

GPL2 or later
