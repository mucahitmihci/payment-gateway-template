# Klaviyo Dynamic Product Integration

This document describes the Klaviyo marketing integration for the Ecwid payment gateway template, enabling automatic tracking of customer orders and product views.

## Overview

The Klaviyo integration tracks customer behavior and order data to enable powerful email marketing automation, including:

- **Customer Identification**: Automatically identify customers by email
- **Order Tracking**: Track "Started Checkout" and "Placed Order" events
- **Product Views**: Track individual product views from order items
- **Dynamic Product Data**: Include product IDs, SKUs, prices, quantities, and images

## Features

### Tracked Events

1. **Started Checkout** - Fired when customer reaches the payment page
   - Customer email, name, phone
   - Order total and currency
   - Complete product list with IDs, SKUs, names, quantities, prices
   - Product images and URLs

2. **Placed Order** - Fired when payment is successfully completed
   - Same data as Started Checkout
   - Confirms successful purchase for Klaviyo flows

3. **Viewed Product** - Fired for each product in the order
   - Individual product details
   - Enables product-specific recommendations

### Product Data Fields

Each product tracked includes:

| Field | Description |
|-------|-------------|
| `ProductID` | Ecwid product ID |
| `SKU` | Product SKU code |
| `ProductName` | Product title/name |
| `Quantity` | Number of items ordered |
| `ItemPrice` | Price per individual item |
| `RowTotal` | Total price for this line item (Quantity × ItemPrice) |
| `ProductURL` | Link to product page (if available) |
| `ImageURL` | Product image URL (if available) |
| `Categories` | Array of category IDs |

## Setup Instructions

### 1. Get Your Klaviyo API Key

1. Log in to your [Klaviyo account](https://www.klaviyo.com)
2. Go to **Account** → **Settings** → **API Keys**
3. Copy your **Public API Key** (also called Site ID)
   - It looks like: `AbCd1E` (6 characters)
   - **Do NOT use** your Private API Key

### 2. Configure in Ecwid Control Panel

1. Open your Ecwid Control Panel
2. Navigate to the payment gateway app settings
3. Scroll to **Advanced Settings**
4. Find the **Klaviyo Marketing Integration** section
5. Check "Enable Klaviyo tracking"
6. Paste your Klaviyo Public API Key
7. Save settings

### 3. Test the Integration

1. Make a test order in your Ecwid store
2. Check your browser's Developer Console (F12) for Klaviyo debug output:
   ```
   Klaviyo Customer Data: {email: "...", $first_name: "...", ...}
   Klaviyo Order Data: {...}
   Klaviyo Products: [...]
   ```
3. In Klaviyo, go to **Analytics** → **Metrics**
4. You should see:
   - "Started Checkout" events
   - "Placed Order" events (after successful payment)
   - "Viewed Product" events

## Files Modified

- **klaviyo-tracking.php** (NEW) - Core Klaviyo tracking code with dynamic product data
- **payment-request.php** - Integrated Klaviyo tracking into payment flow
- **index.html** - Added Klaviyo configuration UI
- **functions.js** - Added Klaviyo settings to app storage

## Technical Implementation

### Payment Flow Integration

```
Customer Checkout → payment-request.php
                    ↓
         Decrypt Ecwid order payload
                    ↓
         Extract customer & product data
                    ↓
         Include klaviyo-tracking.php
                    ↓
         Track "Started Checkout" event
                    ↓
         Redirect to payment gateway
                    ↓
         Payment completed → Callback
                    ↓
         Track "Placed Order" event
```

### Data Extraction

The integration extracts data from the Ecwid order payload:

```php
$order["cart"]["order"]["email"]              // Customer email
$order["cart"]["order"]["billingPerson"]      // Customer details
$order["cart"]["order"]["items"]              // Product line items
$order["cart"]["order"]["total"]              // Order total
$order["cart"]["currency"]                    // Currency code
```

### Event Tracking Code

The tracking is implemented using Klaviyo's JavaScript SDK:

```javascript
// Customer identification
klaviyo.push(['identify', {
    'email': 'customer@example.com',
    '$first_name': 'John',
    '$last_name': 'Doe',
    '$phone_number': '+1234567890'
}]);

// Order tracking
klaviyo.push(['track', 'Started Checkout', {
    '$value': 99.99,
    'OrderId': '12345',
    'Items': [
        {
            'ProductID': '123',
            'ProductName': 'Example Product',
            'Quantity': 2,
            'ItemPrice': 49.99
        }
    ]
}]);
```

## Klaviyo Flow Suggestions

Once integrated, you can create powerful email flows in Klaviyo:

### 1. Abandoned Checkout Recovery
- **Trigger**: "Started Checkout" but not "Placed Order" within 1 hour
- **Action**: Send reminder email with cart details
- **Include**: Dynamic product images and names from the checkout

### 2. Post-Purchase Thank You
- **Trigger**: "Placed Order"
- **Action**: Send thank you email immediately
- **Include**: Order summary, product recommendations

### 3. Product Recommendations
- **Trigger**: "Viewed Product" or "Placed Order"
- **Action**: Send follow-up with related products
- **Include**: Dynamic product recommendations based on purchase history

### 4. Win-Back Campaign
- **Trigger**: "Placed Order" more than 90 days ago
- **Action**: Send special offer email
- **Include**: Products similar to previous purchases

## Troubleshooting

### Events Not Appearing in Klaviyo

1. **Check API Key**
   - Verify you're using the Public API Key, not Private
   - Ensure no extra spaces when pasting

2. **Check Browser Console**
   - Open Developer Tools (F12)
   - Look for Klaviyo debug output
   - Check for JavaScript errors

3. **Verify Integration is Enabled**
   - In Ecwid settings, "Enable Klaviyo tracking" must be checked
   - Klaviyo API Key field must be filled

4. **Check Klaviyo Metrics**
   - Go to Klaviyo → Analytics → Metrics
   - Events may take 5-10 minutes to appear
   - Look for "Started Checkout", "Placed Order", "Viewed Product"

### Product Data Missing

1. **Verify Order Payload**
   - Check that Ecwid is sending product data in `$order["cart"]["order"]["items"]`
   - Some test orders may have incomplete product data

2. **Check Product Structure**
   - Product IDs should be in `$item["product"]["id"]`
   - Product names should be in `$item["name"]`

## Privacy Considerations

- The integration sends customer email addresses to Klaviyo
- Ensure your privacy policy covers email marketing
- Comply with GDPR, CCPA, and other privacy regulations
- Customers should have the option to opt out of marketing emails
- Klaviyo provides built-in unsubscribe functionality

## Support

For issues with:
- **Klaviyo Integration**: Check this documentation and Klaviyo's support
- **Ecwid Payment Gateway**: Refer to the main README.md
- **API Keys**: Contact Klaviyo support for API key issues

## Version History

- **v1.0** (2026-01-09)
  - Initial Klaviyo integration
  - Support for Started Checkout, Placed Order, and Viewed Product events
  - Dynamic product data extraction
  - Merchant settings UI

## License

This integration follows the same license as the main payment gateway template (Apache 2.0).
