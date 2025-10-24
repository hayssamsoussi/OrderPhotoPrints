# Photo Order Prints Platform - Project Summary

## What Was Built

A complete PHP-based photo ordering platform with unique client URLs and a comprehensive admin portal.

## Key Features Implemented

### ✅ Client Portal (`upload.php`)
- **Unique URLs**: Each client gets a personalized upload link (`upload.php?code=UNIQUECODE`)
- **Drag & Drop Upload**: Intuitive photo upload interface
- **Client-Side Resizing**: Images automatically resized before upload (max 1920x1920px)
- **Progress Bar**: Real-time upload progress display
- **Quantity Management**: Set print quantity for each photo (1-100)
- **Photo Deletion**: Remove unwanted photos from order
- **Automatic Calculation**: Total cost calculated based on photo count and quantity
- **Status Tracking**: Visual status bar showing order progress
- **Responsive Design**: Mobile-friendly interface

### ✅ Admin Portal
**Dashboard** (`admin_dashboard.php`)
- Statistics overview (orders, clients, photos, revenue)
- Generate new client links
- Recent orders list

**Orders Management** (`admin_orders.php`, `admin_order_details.php`)
- View all orders with filters
- Update order status (received → printing → shipping → done)
- Set deposit amounts
- Add notes to orders
- View all photos for each order

**Client Management** (`admin_clients.php`)
- View all clients
- Access their upload links
- See order status and totals

**Security**
- Password-protected login (`admin_login.php`)
- Session management
- SQL injection protection (prepared statements)
- XSS protection (input sanitization)

### ✅ Technical Features
- **Database**: MySQL with normalized schema
- **File Upload**: Secure handling with validation
- **Image Processing**: Client-side compression using Canvas API
- **AJAX**: Real-time updates without page refresh
- **Security Headers**: XSS protection, content-type sniffing prevention
- **Responsive CSS**: Mobile-first design
- **Loading States**: User feedback during operations

## Project Structure

```
OrderPhotoPrints/
├── Frontend (Client Portal)
│   ├── upload.php                 Main upload page
│   ├── assets/css/client.css      Client styles
│   └── assets/js/client.js        Client JavaScript
│
├── Admin Portal
│   ├── admin_login.php            Login page
│   ├── admin_dashboard.php        Dashboard
│   ├── admin_orders.php           Orders list
│   ├── admin_order_details.php    Order management
│   ├── admin_clients.php          Clients list
│   ├── admin_logout.php           Logout handler
│   └── assets/css/admin.css       Admin styles
│
├── API Endpoints
│   ├── api_upload.php             File upload handler
│   └── api_photo.php              Photo CRUD operations
│
├── Configuration
│   ├── config.php                 Main configuration
│   ├── database.sql               Database schema
│   ├── .htaccess                  Apache configuration
│   └── .gitignore                 Git ignore rules
│
├── Installation
│   ├── install.php                Automated installer
│   ├── README.md                  Full documentation
│   └── SETUP.md                   Quick setup guide
│
└── Storage
    └── uploads/                   Photo storage directory
```

## Database Schema

**Tables:**
- `admins` - Admin users
- `clients` - Client information with unique codes
- `orders` - Order details, status, totals
- `photos` - Individual photos with quantities

**Relationships:**
- Clients → Orders (one-to-one)
- Orders → Photos (one-to-many)

## How It Works

### Client Flow
1. Admin generates unique link for client
2. Client receives link with unique code
3. Client uploads photos via drag & drop
4. Images are resized client-side before upload
5. Client sets quantities for each photo
6. System calculates total automatically
7. Client tracks order status

### Admin Flow
1. Login to admin portal
2. Generate new client links as needed
3. View all orders on dashboard
4. Manage individual orders:
   - Update status
   - Set deposit amount
   - Add notes
   - View photos
5. Track overall statistics

## Configuration Options

### Pricing (`config.php`)
```php
define('PRICE_PER_PHOTO', 0.50); // Adjust price here
```

### Upload Settings (`config.php`)
```php
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
```

### Image Resizing (`assets/js/client.js`)
```javascript
const MAX_WIDTH = 1920;   // Max width in pixels
const MAX_HEIGHT = 1920;  // Max height in pixels
const QUALITY = 0.85;     // Compression quality (0-1)
```

## Security Features

- ✅ SQL injection protection (PDO prepared statements)
- ✅ XSS protection (input sanitization)
- ✅ Password hashing (bcrypt)
- ✅ Session management
- ✅ File type validation
- ✅ File size limits
- ✅ Secure headers (.htaccess)
- ✅ Directory traversal protection
- ✅ PHP execution disabled in uploads folder

## Browser Compatibility

- ✅ Chrome/Edge (modern versions)
- ✅ Firefox (modern versions)
- ✅ Safari (modern versions)
- ✅ Mobile browsers

## Responsive Breakpoints

- Desktop: Full width layouts
- Tablet: Adjusted grids
- Mobile: Single column layouts

## Installation Requirements

- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx
- mod_rewrite (Apache)
- GD extension (optional, for server-side image processing)

## Quick Start

1. Run `install.php` in browser
2. Enter database credentials
3. Login with admin/admin123
4. Generate first client link
5. Start accepting orders!

## Default Credentials

- **Username**: `admin`
- **Password**: `admin123`
- ⚠️ **Change immediately after installation!**

## Customization Ideas

- Add email notifications
- Implement payment gateway integration
- Add bulk photo management
- Create order export functionality
- Add photo preview lightbox
- Implement search and filters
- Add user roles/permissions
- Create print-ready export
- Add invoice generation
- Implement order history tracking

## Support & Maintenance

- Regular database backups recommended
- Monitor uploads folder size
- Review error logs periodically
- Update PHP/MySQL regularly
- Test after PHP/MySQL updates

## License

Custom platform for internal use.

