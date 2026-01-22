# Klaviyo Product Feed Email Template Guide

This guide explains how to use the Klaviyo product feed email template for dynamic product recommendations.

## Overview

The `klaviyo-product-feed-template.html` file is a responsive email template that displays up to 4 products from a Klaviyo product feed. It uses Klaviyo's Django-based templating syntax to dynamically populate product information.

## Features

- **Responsive Design**: Works perfectly on desktop, tablet, and mobile devices
- **Dynamic Product Feed Integration**: Pulls products from your Klaviyo product feed
- **Product Information Display**:
  - Product image with clickable link
  - Product title
  - Product description (optional)
  - Current price
  - Compare-at price (original price, shown with strikethrough if different from current price)
  - "Shop Now" call-to-action button
- **Email Client Compatibility**: Optimized for all major email clients including Outlook, Gmail, Apple Mail, etc.

## Setup Instructions

### 1. Create a Product Feed in Klaviyo

1. Log in to your Klaviyo account
2. Navigate to **Integrations** > **Product Feeds**
3. Create a new product feed or use an existing one
4. Name your feed (e.g., "Muca_test" as used in this template)
5. Configure your feed settings:
   - Add product data source (e.g., Shopify, WooCommerce, CSV, API)
   - Map the required fields: title, price, regular_price, image_url, url, description

### 2. Upload the Template to Klaviyo

1. Go to **Email** > **Templates** in Klaviyo
2. Click **Create Template**
3. Choose **Custom HTML**
4. Copy the entire contents of `klaviyo-product-feed-template.html`
5. Paste it into the HTML editor
6. Save the template with a descriptive name

### 3. Update Feed Name

If your product feed has a different name than "Muca_test", you need to update the template:

Find and replace all instances of `feeds.Muca_test` with `feeds.YOUR_FEED_NAME`:

```django
{% if feeds.YOUR_FEED_NAME|index:1 %}
{% with item=feeds.YOUR_FEED_NAME|index:1 %}
```

### 4. Create a Campaign or Flow

**For a Campaign:**
1. Go to **Campaigns** > **Create Campaign** > **Email**
2. Select your uploaded template
3. Configure your campaign settings
4. Preview and send

**For a Flow:**
1. Go to **Flows** > Create or edit a flow
2. Add an email action
3. Select your uploaded template
4. Configure trigger and flow settings

## Template Variables

The template uses the following Klaviyo product feed variables:

| Variable | Description | Type |
|----------|-------------|------|
| `item.title` | Product name/title | String |
| `item.price` | Current/sale price | String/Number |
| `item.regular_price` | Original/compare-at price | String/Number |
| `item.image_url` | Product image URL | URL String |
| `item.url` | Product page URL | URL String |
| `item.description` | Product description | String (HTML safe) |

## Customization

### Change Number of Products

The template currently displays up to 4 products. To add more products:

1. Copy the product block starting from `{% if feeds.Muca_test|index:4 %}`
2. Change the index number (e.g., `|index:5` for the 5th product)
3. Paste it before the Email Footer section

To show fewer products, simply delete the unwanted product blocks.

### Customize Styling

Key style customization areas:

**Colors:**
- Primary CTA button: `.product-cta` - currently `#007bff` (blue)
- Product price: `.product-price` - currently `#2c5f2d` (green)
- Background: `body` - currently `#f4f4f4` (light gray)

**Button Text:**
Change "Shop Now" to your preferred text:
```html
<a href="{{ Link }}" class="product-cta">Your Text Here</a>
```

**Email Header:**
Edit the header text in the Email Header section:
```html
<h1 style="margin: 0; font-size: 24px; font-weight: 700; color: #333333;">
    Your Custom Header
</h1>
```

### Grid Layout (2 Columns)

To display products in a 2-column grid for larger screens, replace a product block with:

```html
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
    <tr>
        <td width="50%" style="padding: 10px;">
            <!-- Product 1 card here -->
        </td>
        <td width="50%" style="padding: 10px;">
            <!-- Product 2 card here -->
        </td>
    </tr>
</table>
```

## Best Practices

1. **Product Feed Quality**: Ensure your product feed has high-quality images (at least 600px wide)
2. **Testing**: Always send test emails to check rendering across different email clients
3. **Personalization**: Consider adding the recipient's name in the header using `{{ first_name|default:'there' }}`
4. **A/B Testing**: Test different product orders, CTA button colors, and subject lines
5. **Mobile Preview**: Always preview on mobile devices as 60%+ of emails are opened on mobile

## Klaviyo Template Syntax Quick Reference

```django
{# Comments #}
{% if condition %}...{% endif %}                 # Conditional
{% with var=value %}...{% endwith %}             # Variable assignment
{{ variable }}                                   # Output variable
{{ variable|default:"fallback" }}                # Default value
{{ variable|safe }}                              # Mark as HTML-safe
{% for item in list %}...{% endfor %}           # Loop
feeds.FEED_NAME|index:N                          # Get Nth item from feed (1-indexed)
```

## Troubleshooting

### Products Not Showing

1. Check that your product feed name matches in the template
2. Verify the product feed has data and is active
3. Ensure feed field mappings are correct

### Broken Images

1. Verify `image_url` field in your product feed contains valid URLs
2. Check that images are hosted on HTTPS (not HTTP)
3. Ensure image URLs are publicly accessible

### Prices Not Displaying

1. Check that `price` and `regular_price` fields exist in your feed
2. Verify the price format includes currency symbol if needed
3. Add currency formatting if required: `{{ Price|floatformat:2 }}`

### Layout Issues

1. Test in Klaviyo's email preview tool
2. Send test emails to multiple email clients
3. Check for unclosed HTML tags

## Support

For Klaviyo-specific issues, refer to:
- [Klaviyo Help Center](https://help.klaviyo.com/)
- [Klaviyo Product Feeds Documentation](https://help.klaviyo.com/hc/en-us/articles/115005082927)
- [Klaviyo Template Language Guide](https://help.klaviyo.com/hc/en-us/articles/360032803851)

## License

This template is provided as-is for use with Klaviyo email marketing campaigns.
