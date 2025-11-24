# Drop-and-Go WordPress Site

A WordPress site built with the Bricks theme and various plugins.

## 🚀 Local Development

The site runs locally using Docker Compose.

### Prerequisites
- Docker Desktop installed and running

### Starting the Site

```bash
docker-compose up -d
```

The site will be available at: **http://localhost:8000**

### Admin Access
- **Admin URL**: http://localhost:8000/wp-admin
- **Email**: Sambomelo@gmail.com
- **Password**: 0102030405sam

### Stopping the Site

```bash
docker-compose down
```

## 📦 Deployment Options

⚠️ **Important**: This is a WordPress site (PHP + MySQL) and **cannot be deployed directly to Netlify**, as Netlify only supports static sites.

### Recommended Hosting Options:

#### 1. **WordPress-Specific Hosting** (Easiest)
- **WP Engine** - Premium WordPress hosting
- **Kinsta** - Managed WordPress hosting
- **SiteGround** - Affordable WordPress hosting
- **Hostinger** - Budget-friendly option

#### 2. **Docker/Container Hosting**
Since you already have Docker setup:
- **Railway** - Easy Docker deployment
- **Render** - Free tier available
- **DigitalOcean App Platform** - Scalable container hosting
- **Fly.io** - Global edge deployment

#### 3. **Convert to Static Site** (For Netlify)
Use a plugin to convert WordPress to static HTML:
- **Simply Static** plugin
- **WP2Static** plugin
- **Strattic** (headless WordPress)

#### 4. **Headless WordPress + Netlify**
Use WordPress as a backend CMS and build a frontend with:
- **Next.js** + WordPress API
- **Gatsby** + WordPress source plugin

## 🗂️ Project Structure

- `wp-content/` - WordPress content (themes, plugins, uploads)
- `wp-admin/` - WordPress admin interface
- `wp-includes/` - WordPress core files
- `docker-compose.yml` - Docker configuration

## 🔧 Environment

- **WordPress**: Latest version
- **PHP**: 8.2
- **MySQL**: 8.0
- **Theme**: Bricks + Bricks Child
- **Plugins**: Multiple (see wp-content/plugins/)

## 📝 Notes

- Database backups are excluded from git (*.sql)
- Uploads folder is excluded from git
- wp-config.php contains environment-specific configuration

