<?php

require __DIR__  . '/vendor/autoload.php';

//REPLACE WITH YOUR ACCESS TOKEN AVAILABLE IN: https://developers.mercadopago.com/panel/credentials
MercadoPago\SDK::setAccessToken("TEST-2689557980115245-020817-8267e33d0ec9a66a47b38b27ecd6f267-159290397");

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

switch($path){
    case '':
    case '/':
        require __DIR__ . '/../../client/html-js/index.html';
        break;
    case '/create_preference':
        $json = file_get_contents("php://input");
        $data = json_decode($json);

        $preference = new MercadoPago\Preference();

        $item = new MercadoPago\Item();
        $item->title = $data->description;
        $item->quantity = $data->quantity;
        $item->unit_price = $data->price;

        $preference->items = array($item);

        $preference->back_urls = array(
            "success" => "https://localhost:8080/carrito/thankyou.php?id_venta=",
            "failure" => "https://localhost:8080/carrito/errorpago.php?error=failure",
            "pending" => "https://localhost:8080/carrito/errorpago.php?error=pending"
        );
        $preference->auto_return = "approved";
        // Crea un ítem en la preferencia
        

        $preference->save();

        $response = array(
            'id' => $preference->id,
        ); 
        echo json_encode($response);
        break;        
    case '/feedback':
        $respuesta = array(
            'Payment' => $_GET['payment_id'],
            'Status' => $_GET['status'],
            'MerchantOrder' => $_GET['merchant_order_id']        
        ); 
        echo json_encode($respuesta);
        break;
    //Server static resources
    default:
        $file = __DIR__ . '/../../client' . $path;
        $extension = end(explode('.', $path));
        $content = 'text/html';
        switch($extension){
            case 'js': $content = 'application/javascript'; break;
            case 'css': $content = 'text/css'; break;
            case 'png': $content = 'image/png'; break;
        }
        header('Content-Type: '.$content);
        readfile($file);          
}