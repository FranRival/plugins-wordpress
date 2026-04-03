# Auto Last 24 Hours Tag

Automatically adds a **"last 24 hours"** tag to newly published posts and removes it after 24 hours using WordPress cron.

## ⚡ Features

- Auto-adds tag on post publish
- Automatically removes tag after 24 hours
- Runs via WordPress cron (hourly check)
- Fully automated (no configuration needed)
- Lightweight and efficient

## ⚙️ How It Works

1. When a post is published:
   - Adds the tag: `last 24 hours`

2. Every hour (WP-Cron):
   - Scans posts with the tag
   - Checks post publish time
   - Removes tag if older than **24 hours (86400s)**

## ⏱️ Automation Logic

- Hook: `publish_post`
- Cron event: `hourly`
- Time comparison:

current_time - post_time > 86400  


## 📦 Installation

1. Upload plugin to:

/wp-content/plugins/auto-last-24-hours-tag/  

2. Activate in WordPress
3. Done ✅

## 🧠 Notes

- Only applies to `post` post type
- Uses tag slug: `last-24-hours`
- Uses WordPress **WP-Cron** (depends on site traffic)
- No UI or settings required

## 👨‍💻 Author

**Emmanuel - Fran Rival**

---

Simple automation. Zero maintenance.