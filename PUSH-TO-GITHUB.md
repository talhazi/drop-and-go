# Push to GitHub and Deploy to Netlify

## Step 1: Create GitHub Repository

1. **Go to GitHub**: https://github.com/new
2. **Create a new repository**:
   - Name: `drop-and-go` (or any name you prefer)
   - Make it Public or Private
   - **DO NOT** initialize with README, .gitignore, or license
3. **Click "Create repository"**

## Step 2: Push Your Code

After creating the repository, run these commands:

```bash
cd /Users/talhazi/workspace/talhazi/drop-and-go

# Add the remote repository (replace USERNAME with your GitHub username)
git remote add origin https://github.com/USERNAME/drop-and-go.git

# Push to GitHub
git push -u origin master
```

## Step 3: Deploy to Netlify

### Option A: Automatic Deployment from GitHub

1. **Go to Netlify**: https://app.netlify.com
2. **Click "Add new site" → "Import an existing project"**
3. **Connect to GitHub**:
   - Authorize Netlify to access your GitHub
   - Select your `drop-and-go` repository
4. **Configure settings** (Netlify will auto-detect from `netlify.toml`):
   - Build command: `echo 'Static site already generated'`
   - Publish directory: `static-html`
5. **Click "Deploy site"**

🎉 Your site will be live in ~30 seconds!

### Option B: Manual Deployment (Faster, No GitHub Needed)

If you don't want to use GitHub:

1. **Go to**: https://app.netlify.com/drop
2. **Drag and drop** the `static-html` folder
3. **Done!** Your site is live!

## Step 4: Get Your Live URL

After deployment:
- Netlify will give you a URL like: `https://random-name-12345.netlify.app`
- You can change it to a custom domain in Site settings
- Free HTTPS is included automatically!

## Future Updates

When you make changes to your WordPress site:

1. **Update locally**:
   ```bash
   docker-compose up -d
   # Edit in WordPress at http://localhost:8000/wp-admin
   ```

2. **Regenerate static files**:
   ```bash
   rm -rf static-html
   wget --mirror --convert-links --adjust-extension --page-requisites --no-parent --no-host-directories --directory-prefix=static-html --quiet http://localhost:8000/
   ```

3. **Commit and push**:
   ```bash
   git add static-html
   git commit -m "Update static site"
   git push
   ```

4. **Netlify will auto-deploy** (if using GitHub method)

---

**Need help?** All your changes are committed locally and ready to push!

