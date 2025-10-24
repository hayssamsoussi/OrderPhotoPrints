# Photo Order Prints Platform

A complete PHP-based platform for managing photo print orders with unique client URLs and a comprehensive admin portal.

## Features

### Client Portal
- **Unique URLs**: Each client gets a unique upload link
- **Photo Upload**: Drag & drop or click to upload photos
- **Image Optimization**: Automatic client-side resizing before upload
- **Upload Progress**: Real-time progress bar during upload
- **Quantity Management**: Set how many prints per photo
- **Photo Deletion**: Remove unwanted photos
- **Order Calculation**: Automatic total calculation
- **Status Tracking**: Visual status display (Received, Printing, Shipping, Done)
- **Responsive Design**: Works on all devices

### Admin Portal
- **Secure Login**: Password-protected admin access
- **Generate Links**: Create unique client upload links
- **Order Management**: View and manage all orders
- **Status Updates**: Update order status
- **Deposit Tracking**: Set deposit amounts
- **Notes**: Add order notes
- **Dashboard**: Statistics and overview
- **Client Management**: View all clients and their orders

## Installation

### Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- mod_rewrite enabled (for Apache)

### Setup Steps

1. **Clone or download the project**
   ```bash
   cd /path/to/your/web/directory
   ```

2. **Configure Database**
   - Create a MySQL database
   - Update `config.php` with your database credentials:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'photo_orders');
     define('DB_USER', 'your_username');
     define('DB_PASS', 'your_password');
     ```

3. **Import Database Schema**
   ```bash
   mysql -u your_username -p photo_orders < database.sql
   ```

4. **Set Permissions**
   ```bash
   chmod 755 uploads/
   ```

5. **Configure Base URL**
   Update `BASE_URL` in `config.php` to match your domain:
   ```php
   define('BASE_URL', 'http://yourdomain.com');
   ```

6. **Access the Platform**
   - Admin Portal: `http://yourdomain.com/admin_login.php`
   - Default credentials:
     - Username: `admin`
     - Password: `admin123`
     - **Change this password immediately after first login!**

## Usage

### Admin Portal

1. **Login** at `/admin_login.php`
2. **Generate Client Link**
   - Go to Dashboard
   - Fill in client information
   - Click "Generate Link"
   - Share the unique code with the client

3. **Manage Orders**
   - View all orders on Orders page
   - Click "Manage" to update status, deposit, and notes
   - Track client progress

### Client Portal

1. **Access Upload Page**
   - Client uses their unique link: `/upload.php?code=UNIQUECODE`
   - The link is provided by the admin

2. **Upload Photos**
   - Click or drag photos to upload area
   - Photos are automatically resized
   - Progress bar shows upload status

3. **Manage Order**
   - Set quantity for each photo
   - Delete unwanted photos
   - View order total
   - Track order status

## File Structure

```
OrderPhotoPrints/
├── admin_clients.php          # View all clients
├── admin_dashboard.php        # Admin dashboard
├── admin_login.php            # Admin login page
├── admin_logout.php           # Logout handler
├── admin_order_details.php    # Order management
├── admin_orders.php           # All orders list
├── api_photo.php              # Photo API (quantity, delete)
├── api_upload.php             # Upload API
├── config.php                 # Configuration file
├── database.sql               # Database schema
├── upload.php                 # Client upload page
├── assets/
│   ├── css/
│   │   ├── admin.css         # Admin styles
│   │   └── client.css        # Client styles
│   └── js/
│       └── client.js         # Client-side JavaScript
└── uploads/                   # Uploaded photos directory
```

## Security Notes

- Change default admin password immediately
- Set proper file permissions on uploads directory
- Consider adding CSRF protection for production
- Add rate limiting for uploads
- Implement proper input validation
- Use HTTPS in production
- Regularly backup database

## Customization

### Price per Photo
Edit `config.php`:
```php
define('PRICE_PER_PHOTO', 0.50); // Change price here
```

### Upload Settings
Edit `config.php`:
```php
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // Max file size
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
```

### Image Resizing
Edit `assets/js/client.js`:
```javascript
const MAX_WIDTH = 1920;   // Max width
const MAX_HEIGHT = 1920;  // Max height
const QUALITY = 0.85;     // Compression quality
```

## Troubleshooting

### Upload Errors
- Check `uploads/` directory permissions
- Verify PHP upload settings in php.ini
- Check disk space

### Database Errors
- Verify database credentials in `config.php`
- Ensure database is imported correctly
- Check MySQL connection

### Login Issues
- Verify admin credentials in database
- Check session configuration
- Clear browser cookies

## Support

For issues or questions, please refer to the documentation or contact support.

## License

This project is provided as-is for use in your organization.

