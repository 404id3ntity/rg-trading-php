<?php
require_once __DIR__ . '/../includes/config.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /rg-trading-php/index.php');
    exit;
}

$product_id = $_POST['product_id'] ?? '';
$qty        = max(1, intval($_POST['qty'] ?? 1));

if (!$product_id) {
    set_flash('error', 'Invalid product.');
    header('Location: /rg-trading-php/index.php');
    exit;
}

/* Get product from API */
$res = api_request('GET', '/products/' . urlencode($product_id));
$product = $res['body']['data']['product'] ?? null;

if (!$product) {
    set_flash('error', 'Product not found.');
    header('Location: /rg-trading-php/index.php');
    exit;
}

/* INIT CART */
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/* ADD / UPDATE ITEM */
if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id]['qty'] += $qty;
} else {
    $_SESSION['cart'][$product_id] = [
        'product_id'   => $product_id,
        'name'         => $product['name'],
        'price'        => $product['price'],
        'image'        => $product['image_url'] ?? '',
        'qty'          => $qty
    ];
}

set_flash('success', 'Added to cart!');
header('Location: /rg-trading-php/pages/cart.php');
exit;