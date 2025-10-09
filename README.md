![](https://i.ibb.co/7dwZtNkt/Ads-z-Tasar-m-kopyas.png)
## 📄 Custom Profile Page
![License](https://img.shields.io/badge/license-MIT-blue.svg) [![Latest Stable Version](https://img.shields.io/packagist/v/huseyinfiliz/custom-profile-page.svg)](https://packagist.org/packages/huseyinfiliz/custom-profile-page) [![Total Downloads](https://img.shields.io/packagist/dt/huseyinfiliz/custom-profile-page.svg)](https://packagist.org/packages/huseyinfiliz/custom-profile-page)

### 📝 Description
Custom Profile Page allows users to create and customize their own profile pages with rich content. Perfect for portfolios, bios, or any personalized content on your Flarum forum.

Special thanks to [@Silvestrus](https://discuss.flarum.org/u/silvestrus) for the future support! ✨

### 📊 Profile Page View
![Demo](https://i.ibb.co/p6n2hX0T/image.png)

### ✏️ Edit Modal with Markdown Toolbar
![Demo](https://i.ibb.co/R4tVYmBN/image.png)

### ⚙️ Admin Settings
![Demo](https://i.ibb.co/sJRjRcBF/image.png)

### 🚀 Key Features
- ✅ **Rich Content Support** - Full Flarum Markdown with formatting toolbar
- 🎨 **Multiple Formats** - Markdown, HTML, or Plain Text options
- 📺 **Media Embeds** - Auto-convert YouTube links to embedded players (optional)
- 🛡️ **Smart Permissions** - View, edit own, and moderate permissions
- 🔒 **HTML Sanitization** - Admin-controlled allowed HTML tags for security
- ⚙️ **Fully Customizable** - Tab title, icon, URL slug all configurable
- 📱 **Responsive Design** - Looks great on all devices

### 🎯 Markdown Toolbar Features
- **Header** - Create section headers
- **Bold** / **Italic** / **Strikethrough** - Text formatting
- **Quote** / **Spoiler** - Special content blocks
- **Code** - Inline and block code
- **Link** / **Image** - Add links and images
- **Lists** - Bulleted and numbered lists
- **Mention** / **Emoji** - User mentions and emojis

### 📥 Installation
```sh
composer require huseyinfiliz/custom-profile-page
php flarum migrate
php flarum cache:clear
```
You can also install with Extension Manager: `huseyinfiliz/custom-profile-page`

### ⚙️ Configuration
After installation, go to **Admin Panel → Extensions → Custom Profile Page** to configure:
- **Tab Title** - Customize the profile tab name
- **Tab Icon** - Set a FontAwesome icon
- **URL Slug** - Define the URL path
- **Content Format** - Choose Markdown, HTML, or Plain Text
- **Allowed HTML Tags** - Control which HTML tags are permitted
- **Enable Media Embeds** - Toggle YouTube video embedding

### 🔐 Permissions
Configure permissions in **Admin Panel → Permissions**:
- **View custom profile pages** - Who can view pages (default: Everyone)
- **Edit own custom profile page** - Who can create/edit their own page (default: Members)
- **Moderate custom profile pages** - Who can edit any user's page (default: Moderators)

### ♻ Updating
```sh
composer update huseyinfiliz/custom-profile-page
php flarum cache:clear
```
To remove simply run `composer remove huseyinfiliz/custom-profile-page`

### 🔗 Links
- [Discuss](https://discuss.flarum.org/d/custom-profile-page)
- [Packagist](https://packagist.org/packages/huseyinfiliz/custom-profile-page)
- [Github](https://github.com/huseyinfiliz/flarum-custom-profile-page)

### 💡 Feedback & Suggestions
Feel free to share your ideas, report bugs, or request features in the discussion thread!

### 🌍 Translate
Help translate this extension into your language!
[![Translate](https://weblate.rob006.net/widgets/flarum/-/huseyinfiliz-custom-profile-page/multi-auto.svg)](https://weblate.rob006.net/projects/flarum/huseyinfiliz-custom-profile-page/)

### 🙏 Support
If you find this extension helpful, consider supporting its development:
- ⭐ Star the repository on GitHub
- 💬 Share feedback and suggestions
- 🌍 Help translate to other languages
- ☕ Donate (ERC20 USDT): 0x69da494fe8157ac730fcdd59c2c4a63314e3cb4f