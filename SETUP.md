# Quick Setup Guide

## Option 1: Automated Installation (Recommended)

1. Place all files in your web directory
2. Access `install.php` in your browser
3. Fill in your database credentials
4. Click "Install Platform"
5. Log in with default credentials (admin/admin123)
6. **Delete install.php** for security

## Option 2: Manual Installation

### Step 1: Database Setup
```bash
mysql -u root -p
CREATE DATABASE photo_orders CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;
mysql -u root -p photo_orders < database.sql
```

### Step 2: Configure
Edit `config.php`:
- Update database credentials
- Set BASE_URL to your domain

### Step 3: Permissions
```bash
chmod 755 uploads/
```

### Step 4: First Login
- URL: `http://yourdomain.com/admin_login.php`
- Username: `admin`
- Password: `admin123`
- **Change password immediately!**

## Testing the Platform

### 1. Create a Client Link
- Login to admin portal
- Go to Dashboard
- Fill in client information
- Click "Generate Link"
- Copy the unique code

### 2. Client Upload Test
- Visit: `http://yourdomain.com/upload.php?code=UNIQUECODE`
- Upload some test photos
- Set quantities
- Verify totals calculate correctly

### 3. Manage Order
- Return to admin portal
- Go to Orders
- Find the test order
- Update status
- Add deposit amount
- Add notes

## Troubleshooting

### Upload Not Working
- Check `uploads/` folder permissions: `chmod 755 uploads/`
- Verify PHP settings: `upload_max_filesize` in php.ini
- Check error logs

### Database Connection Error
- Verify credentials in `config.php`
- Ensure MySQL is running
- Check user has proper permissions

### Images Not Displaying
- Check `uploads/` folder exists
- Verify file permissions
- Check BASE_URL in config.php

## File Structure Summary

```
OrderPhotoPrints/
├── Admin Portal
│   ├── admin_login.php          Login page
│   ├── admin_dashboard.php       Main dashboard
│   ├── admin_orders.php          View all orders
│   ├── admin_order_details.php   Manage orders
│   └── admin_clients.php         View clients
│
├── Client Portal
│   └── upload.php                Client upload page
│
├── API
│   ├── api_upload.php            Handle file uploads
│   └── api_photo.php             Photo management
│
├── Assets
│   ├── css/                      Stylesheets
│   └── js/                       JavaScript
│
├── Database
│   └── database.sql              Database schema
│
└── Config
    ├── config.php                Configuration
    ├── index.php                 Redirect to admin
    └── .htaccess                  Apache config
```

## Security Checklist

- [ ] Change default admin password
- [ ] Delete install.php after installation
- [ ] Set proper file permissions
- [ ] Use HTTPS in production
- [ ] Restrict uploads directory
- [ ] Regularly backup database
- [ ] Update PHP to latest version
- [ ] Review .htaccess security headers

## Next Steps

1. Customize pricing in `config.php`
2. Add your logo/branding
3. Set up email notifications (optional)
4. Configure SSL certificate
5. Set up regular backups
6. Train your team on the admin portal

## Support

If you encounter issues:
1. Check the error logs
2. Verify database connection
3. Test file permissions
4. Review README.md for detailed information

