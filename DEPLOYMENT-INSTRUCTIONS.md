# Deployment Instructions for Netlify

Your WordPress site has been successfully converted to static HTML!

## 📦 What Was Done

1. ✅ Removed placeholder `default.php` file
2. ✅ Installed Simply Static plugin
3. ✅ Generated static HTML version using `wget`
4. ✅ Created `netlify.toml` configuration
5. ✅ Set up proper caching and headers

## 🚀 Deploy to Netlify

### Option 1: Automatic Deployment (Git)

1. **Push to GitHub** (if not already done):
   ```bash
   git add .
   git commit -m "Convert to static site for Netlify"
   git push origin master
   ```

2. **Connect to Netlify**:
   - Go to https://app.netlify.com
   - Click "Add new site" → "Import an existing project"
   - Choose GitHub and select your repository
   - Netlify will auto-detect the `netlify.toml` configuration
   - Click "Deploy site"

### Option 2: Manual Deployment (Drag & Drop)

1. **Zip the static-html folder**:
   ```bash
   cd static-html
   zip -r ../drop-and-go-static.zip .
   ```

2. **Deploy to Netlify**:
   - Go to https://app.netlify.com/drop
   - Drag and drop the `drop-and-go-static.zip` file
   - Your site will be live in seconds!

## 📝 Important Notes

### ⚠️ Static Site Limitations

- ✅ **Works**: Your website will display perfectly
- ✅ **Works**: All images, styles, and content are preserved
- ❌ **Doesn't Work**: WordPress admin panel (not accessible on live site)
- ❌ **Doesn't Work**: Contact forms (need to add Netlify Forms or external service)
- ❌ **Doesn't Work**: Comments (static site doesn't support dynamic comments)

### 🔄 Updating Your Site

When you make changes to your WordPress site locally:

1. **Regenerate static files**:
   ```bash
   cd /path/to/drop-and-go
   rm -rf static-html
   wget --mirror --convert-links --adjust-extension --page-requisites --no-parent --no-host-directories --directory-prefix=static-html --quiet http://localhost:8000/
   ```

2. **Commit and push**:
   ```bash
   git add static-html
   git commit -m "Update static site"
   git push
   ```

3. Netlify will automatically redeploy!

### 📂 Project Structure

```
drop-and-go/
├── static-html/          ← Static version (deployed to Netlify)
│   ├── index.html
│   ├── wp-content/
│   └── ...
├── wp-content/           ← WordPress source (for local development)
├── netlify.toml          ← Netlify configuration
├── docker-compose.yml    ← Local development environment
└── README.md
```

### 🛠️ Local Development

You can still use WordPress locally for editing:

```bash
docker-compose up -d
# Visit http://localhost:8000
```

After making changes, regenerate the static files (see above).

## 🌐 Custom Domain

To use a custom domain on Netlify:

1. Go to Site settings → Domain management
2. Click "Add custom domain"
3. Follow the DNS configuration instructions
4. Netlify provides free HTTPS automatically!

## 💡 Alternative: Keep Dynamic WordPress

If you need dynamic features (admin panel, forms, comments), consider hosting on:
- **Railway**: https://railway.app (Docker support)
- **Render**: https://render.com (Free tier available)
- **DigitalOcean**: https://www.digitalocean.com/products/app-platform

These platforms support PHP + MySQL and your `docker-compose.yml` will work as-is.

---

**Need help?** Check out [Netlify's documentation](https://docs.netlify.com/)

