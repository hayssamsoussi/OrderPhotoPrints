# Products and Photo Options Feature

## Overview
Added the ability for clients to:
1. Select and add products (photo albums, packaging, frames, etc.) to their orders
2. Apply additional options to specific photos (frame, woodboard, bigger size)

## Database Changes

### New Tables Created:
1. **`products`** - Stores available products (albums, boxes, frames, etc.)
2. **`order_products`** - Links products to specific orders
3. **`photo_options`** - Stores additional options for each photo

### Sample Products:
- Photo Album - Small ($15.00)
- Photo Album - Large ($25.00)
- Gift Box Packaging ($10.00)
- Premium Frame ($20.00)
- Wooden Display Board ($30.00)

### Photo Options:
- Frame (+$5.00)
- Woodboard (+$8.00)
- Bigger Size (+$3.00)

## New Files Created

1. **`api_products.php`** - Handles product operations:
   - Get all available products
   - Add product to order
   - Remove product from order
   - Get products in current order

2. **`api_photo_options.php`** - Handles photo option operations:
   - Update photo options (frame, woodboard, bigger size)
   - Get current photo options

## Updated Files

### `upload.php`
- Added Products section with grid of available products
- Added "Products in Order" section showing added products
- Added photo options checkboxes for each photo
- Updated order summary to show product costs separately

### `api_upload.php`
- Updated to recalculate order total including products and options

### `api_photo.php`
- Updated to recalculate order total including products and options
- Added `recalculateOrderTotal()` helper function

### `assets/js/client.js`
- Added `addProduct()` function
- Added `removeProduct()` function
- Added `updatePhotoOption()` function
- Updated `updateSummary()` to reload page for accurate totals

### `assets/css/client.css`
- Added styles for products grid and product cards
- Added styles for photo options
- Added styles for added products list
- Added responsive design for mobile devices

## Features

### Product Management
- Clients can browse and add products to their order
- Products are categorized (album, box, frame, other)
- Each product shows icon, name, description, and price
- Added products appear in a separate "Products in Order" section
- Products can be removed from order (unless order is in printing status)

### Photo Options
- Each photo has checkboxes for three options:
  - 🖼️ Frame (+$5)
  - 🪵 Woodboard (+$8)
  - 📏 Bigger Size (+$3)
- Options are disabled when order is in printing status
- Options update the order total immediately

### Order Cost Calculation
Order total now includes:
1. Base photo cost (quantity × price per photo)
2. Product costs (sum of all added products)
3. Photo options costs (sum of all option costs × photo quantity)

## Usage

### For Clients:
1. Browse products and click "Add to Order" to add them
2. Upload photos as usual
3. For each photo, check the desired options (frame, woodboard, bigger size)
4. Order summary automatically updates to show total cost

### For Admins:
- Products can be managed through the database
- Set `is_active = FALSE` to hide products from clients
- Modify product prices in the `products` table
- Photo option costs are defined in `api_photo_options.php`

## Database Setup

Run the updated `database.sql` to create the new tables and insert sample products.

