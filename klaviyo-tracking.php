<!--
  Klaviyo Dynamic Product Tracking Integration
  This file demonstrates how to integrate Klaviyo tracking into the payment flow
  with dynamic product IDs and customer information from Ecwid orders
-->

<?php
// Example usage: include this after decrypting the order payload in payment-request.php
// Assumes $order variable is available with the Ecwid order data

// Configuration - Replace with your Klaviyo Public API Key
$klaviyo_public_key = "YOUR_KLAVIYO_PUBLIC_API_KEY_HERE";

// Extract customer data
$customer_email = isset($order["cart"]["order"]["email"]) ? $order["cart"]["order"]["email"] : "";
$customer_name = isset($order["cart"]["order"]["billingPerson"]["name"]) ? $order["cart"]["order"]["billingPerson"]["name"] : "";
$customer_phone = isset($order["cart"]["order"]["billingPerson"]["phone"]) ? $order["cart"]["order"]["billingPerson"]["phone"] : "";

// Split name into first and last
$name_parts = explode(" ", $customer_name, 2);
$first_name = isset($name_parts[0]) ? $name_parts[0] : "";
$last_name = isset($name_parts[1]) ? $name_parts[1] : "";

// Extract order data
$order_total = isset($order["cart"]["order"]["total"]) ? $order["cart"]["order"]["total"] : 0;
$order_currency = isset($order["cart"]["currency"]) ? $order["cart"]["currency"] : "USD";
$order_number = isset($order["cart"]["order"]["orderNumber"]) ? $order["cart"]["order"]["orderNumber"] : "";

// Extract product line items
$products_json = array();
if (isset($order["cart"]["order"]["items"]) && is_array($order["cart"]["order"]["items"])) {
    foreach ($order["cart"]["order"]["items"] as $item) {
        $product = array(
            "ProductID" => isset($item["product"]["id"]) ? strval($item["product"]["id"]) : "",
            "SKU" => isset($item["product"]["sku"]) ? $item["product"]["sku"] : "",
            "ProductName" => isset($item["name"]) ? $item["name"] : "",
            "Quantity" => isset($item["quantity"]) ? $item["quantity"] : 1,
            "ItemPrice" => isset($item["price"]) ? $item["price"] : 0,
            "RowTotal" => isset($item["quantity"]) && isset($item["price"]) ? ($item["quantity"] * $item["price"]) : 0,
            "ProductURL" => isset($item["product"]["url"]) ? $item["product"]["url"] : "",
            "ImageURL" => isset($item["product"]["imageUrl"]) ? $item["product"]["imageUrl"] : "",
            "Categories" => isset($item["product"]["categoryIds"]) ? $item["product"]["categoryIds"] : array()
        );
        $products_json[] = $product;
    }
}

// Encode data for JavaScript
$customer_data_json = json_encode(array(
    'email' => $customer_email,
    '$first_name' => $first_name,
    '$last_name' => $last_name,
    '$phone_number' => $customer_phone
));

$event_data_json = json_encode(array(
    '$event_id' => 'order_' . $order_number,
    '$value' => $order_total,
    'OrderId' => $order_number,
    'Categories' => array(),
    'ItemNames' => array_column($products_json, 'ProductName'),
    'Items' => $products_json,
    'Currency' => $order_currency
));
?>

<!-- Klaviyo Tracking Script -->
<script type="text/javascript">
    // Initialize Klaviyo
    !function(){if(!window.klaviyo){window._klOnsite=window._klOnsite||[];try{window.klaviyo=new Proxy({},{get:function(n,i){return"push"===i?function(){var n;(n=window._klOnsite).push.apply(n,arguments)}:function(){for(var n=arguments.length,o=new Array(n),w=0;w<n;w++)o[w]=arguments[w];var t="function"==typeof o[o.length-1]?o.pop():void 0,e=new Promise((function(n){window._klOnsite.push([i].concat(o,[function(i){t&&t(i),n(i)}]))}));return e}}})}catch(n){window.klaviyo=window.klaviyo||[],window.klaviyo.push=function(){var n;(n=window._klOnsite).push.apply(n,arguments)}}}}();

    // Set your Klaviyo Public API Key
    var klaviyoPublicKey = '<?php echo $klaviyo_public_key; ?>';
</script>

<!-- Load Klaviyo SDK -->
<script async type="text/javascript" src="https://static.klaviyo.com/onsite/js/klaviyo.js?company_id=<?php echo $klaviyo_public_key; ?>"></script>

<!-- Klaviyo Event Tracking -->
<script type="text/javascript">
    // Customer identification
    var customerProperties = <?php echo $customer_data_json; ?>;

    // Identify the customer in Klaviyo
    if (customerProperties.email) {
        klaviyo.push(['identify', customerProperties]);
    }

    // Track "Started Checkout" event
    var checkoutEventData = <?php echo $event_data_json; ?>;
    klaviyo.push(['track', 'Started Checkout', checkoutEventData]);

    // Function to track completed order (call this after successful payment)
    function trackKlaviyoPlacedOrder() {
        var orderEventData = <?php echo $event_data_json; ?>;
        klaviyo.push(['track', 'Placed Order', orderEventData]);
    }

    // If you want to track immediately on page load, uncomment below:
    // trackKlaviyoPlacedOrder();
</script>

<!-- Optional: Klaviyo Product Viewed Event (if you want to track individual product views) -->
<script type="text/javascript">
    // Track each product as "Viewed Product"
    var products = <?php echo json_encode($products_json); ?>;

    products.forEach(function(product) {
        klaviyo.push(['track', 'Viewed Product', {
            'ProductName': product.ProductName,
            'ProductID': product.ProductID,
            'SKU': product.SKU,
            'ImageURL': product.ImageURL,
            'URL': product.ProductURL,
            'Price': product.ItemPrice,
            'Currency': '<?php echo $order_currency; ?>'
        }]);
    });
</script>

<!-- Debug Console Output (remove in production) -->
<script type="text/javascript">
    console.log('Klaviyo Customer Data:', <?php echo $customer_data_json; ?>);
    console.log('Klaviyo Order Data:', <?php echo $event_data_json; ?>);
    console.log('Klaviyo Products:', <?php echo json_encode($products_json); ?>);
</script>

<!--
  INTEGRATION INSTRUCTIONS:

  1. Replace 'YOUR_KLAVIYO_PUBLIC_API_KEY_HERE' with your actual Klaviyo Public API Key
     (Find it in Klaviyo: Account → Settings → API Keys → Public API Key)

  2. Include this file in payment-request.php after the order payload is decrypted:

     Example integration in payment-request.php (after line 52):

     $order = getEcwidPayload($client_secret, $ecwid_payload);
     include('klaviyo-tracking.php');  // Add this line

  3. Standard Klaviyo Events Tracked:
     - "Identify" - Links email to customer profile
     - "Started Checkout" - Tracked when payment page loads
     - "Placed Order" - Call trackKlaviyoPlacedOrder() after successful payment
     - "Viewed Product" - Tracked for each product in the order

  4. Product Data Fields Available:
     - ProductID: Unique product identifier
     - SKU: Product SKU code
     - ProductName: Product title/name
     - Quantity: Items ordered
     - ItemPrice: Price per item
     - RowTotal: Total for this line item
     - ProductURL: Link to product page
     - ImageURL: Product image
     - Categories: Product category IDs

  5. For successful payment callback, add this to the callback section (around line 164):

     echo "<script src='klaviyo-tracking.php'></script>";
     echo "<script>trackKlaviyoPlacedOrder();</script>";

  6. Klaviyo Flow Ideas:
     - Abandoned checkout emails (if Started Checkout but not Placed Order)
     - Post-purchase thank you emails
     - Product recommendations based on purchase history
     - Win-back campaigns for customers who haven't ordered recently
-->
