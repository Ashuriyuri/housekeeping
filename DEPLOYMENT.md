# 🚀 Deployment Guide

## ✅ Pre-Deployment Checklist

- [x] All migrations applied
- [x] All 25 database triggers created
- [x] Sessions table configured
- [x] npm build tested and working
- [x] Composer dependencies installed
- [x] GitHub repo set up

## 📋 Deployment Steps

### Local Testing

```bash
# Build assets
npm run build

# Clear cache
php artisan optimize:clear

# Regenerate cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Deploy to Heroku

```bash
# 1. Login to Heroku
heroku login

# 2. Create Heroku app
heroku create your-app-name

# 3. Set environment variables
heroku config:set APP_ENV=production
heroku config:set APP_DEBUG=false
heroku config:set DB_HOST=your-database-host
heroku config:set DB_USERNAME=root
heroku config:set DB_PASSWORD=your-password
heroku config:set DB_DATABASE=hk_db

# 4. Push to Heroku
git push heroku main

# 5. Run migrations
heroku run php artisan migrate
```

### Deploy to GitHub Pages / Vercel (Frontend only)

This is a Laravel backend - NOT suitable for GitHub Pages/Vercel.
Deploy to traditional hosting or cloud platforms:

- Heroku
- AWS EC2
- DigitalOcean
- Linode
- Shared hosting (cPanel)

### Deploy to Traditional Hosting (cPanel, Plesk)

1. Upload all files to server (via FTP/SFTP)
2. Run composer install: `composer install --no-dev`
3. Run npm install: `npm install`
4. Build assets: `npm run build`
5. Generate APP_KEY: `php artisan key:generate`
6. Run migrations: `php artisan migrate`
7. Set storage/bootstrap permissions to 755
8. Set .env file with production values

## 🔧 Common Issues

### "Exit status 1" during build

**Solution**: Ensure these buildpacks are installed (if using Heroku):

```bash
heroku buildpacks:add heroku/php
heroku buildpacks:add heroku/nodejs
```

### npm: command not found

**Solution**: Add Node buildpack

```bash
heroku buildpacks:add heroku/nodejs
```

### Asset files not loading

**Solution**: Run vite build

```bash
npm run build
php artisan storage:link
```

### Database connection errors

**Solution**: Set database environment variables

```bash
heroku config:set DB_HOST=your-db-host
heroku config:set DB_USERNAME=your-username
heroku config:set DB_PASSWORD=your-password
```

## 📝 Environment Variables Required

```env
APP_NAME=Housekeeping
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_KEY_HERE
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=your-database-host
DB_PORT=3306
DB_DATABASE=hk_db
DB_USERNAME=root
DB_PASSWORD=your-password

SESSION_DRIVER=database
```

## ✅ After Deployment

1. Verify database migrations: `heroku run php artisan migrate:status`
2. Check database triggers: `heroku run php artisan tinker` then query `DB::select('SELECT TRIGGER_NAME FROM INFORMATION_SCHEMA.TRIGGERS WHERE TRIGGER_SCHEMA = "hk_db"')`
3. Test appointment creation workflow
4. Monitor logs: `heroku logs --tail`

## 🛠️ Troubleshooting

Run these commands to debug:

```bash
# Check logs
heroku logs --tail

# Check buildpacks
heroku buildpacks

# Verify environment
heroku run env

# SSH into server
heroku ps:exec

# Run artisan commands
heroku run php artisan <command>
```

## 📊 Deployment Checklist for Production

- [ ] .env configured with production values
- [ ] APP_DEBUG=false set
- [ ] Database backup created
- [ ] APP_KEY generated and set
- [ ] npm run build executed
- [ ] Composer install --no-dev executed
- [ ] Cache cleared and regenerated
- [ ] Migrations run on production
- [ ] Storage/bootstrap directories writable (755)
- [ ] SSL certificate installed
- [ ] Error logging configured
- [ ] Backups scheduled
- [ ] Monitoring set up

---

**Ready to deploy!** Choose your hosting platform and follow the steps above.
