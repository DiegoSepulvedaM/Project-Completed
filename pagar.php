<?php
include "./php/conexion.php";
if(!isset($_GET['id_venta'])){
    header("Location: ./");
}
$datos = $conexion->query("select 
        ventas.*,  
        usuario.nombre,usuario.telefono,usuario.email
        from ventas 
        inner join usuario on ventas.id_usuario = usuario.id
        where ventas.id=".$_GET['id_venta'])or die($conexion->error);
$datosUsuario = mysqli_fetch_row($datos);
$datos2 = $conexion->query("select * from envios where id_venta=".$_GET['id_venta'])or die($conexion->error);
$datosEnvio= mysqli_fetch_row($datos2);

$datos3= $conexion->query("select productos_venta.*,    
                productos.nombre as nombre_producto, productos.imagen 
                from productos_venta inner join productos on productos_venta.id_producto = productos.id 
                where id_venta =".$_GET['id_venta'])or die($conexion->error);

$total = $datosUsuario[2];
$descuento = "0";
$banderadescuento = false;

if($datosUsuario[6] != 0){
  $banderadescuento = true;
  $cupon= $conexion->query("select  * from cupones where id =".$datosUsuario[6]);
  $filaCupon  = mysqli_fetch_row($cupon);
  if($filaCupon[3] == "moneda"){
    $total = $total - $filaCupon[4];
    $descuento =$filaCupon[4]."MXN";
  }else{
    $total = $total - ($total * ( $filaCupon[4] / 100 ));
    $descuento =$filaCupon[4]."%";
  }
 
}

require __DIR__ .  '/vendor/autoload.php';

MercadoPago\SDK::setAccessToken('TEST-2689557980115245-020817-8267e33d0ec9a66a47b38b27ecd6f267-159290397');


$preference = new MercadoPago\Preference();
$preference->back_urls = array(
    "success" => "http://localhost:8080/Carrito-De-Compras/thankyou.php?id_venta=".$_GET['id_venta']."&metodo=mercado_pago",
    "failure" => "http://localhost:8080/Carrito-De-Compras/errorpago.php?error=failure",
    "pending" => "http://localhost:8080/Carrito-De-Compras/errorpago.php?error=pending"
);
$preference->auto_return = "approved";

// Agrega los productos o servicios que se están comprando a la preferencia
$datos = array();
if($banderadescuento){
    $item = new MercadoPago\Item();
    $item->title = "Productos de mi tienda.com menos el descuento";
    $item->quantity = 1;
    $item->unit_price = $total;
    $datos[] = $item;
} else {
    while($f = mysqli_fetch_array($datos3)){
        $item = new MercadoPago\Item();
        $item->title = $f['nombre_producto'];
        $item->quantity = $f['cantidad'];
        $item->unit_price = $f['precio'];
        $datos[] = $item;
    }
}

$preference->items = $datos;
$preference->save();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    <title>Elige metodo de pago</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Mukta:300,400,700"> 
        <link rel="stylesheet" href="fonts/icomoon/style.css">

        <link rel="stylesheet" href="css/bootstrap.min.css">
        <link rel="stylesheet" href="css/magnific-popup.css">
        <link rel="stylesheet" href="css/jquery-ui.css">
        <link rel="stylesheet" href="css/owl.carousel.min.css">
        <link rel="stylesheet" href="css/owl.theme.default.min.css">


        <link rel="stylesheet" href="css/aos.css">

        <link rel="stylesheet" href="css/style.css">
</head>
<body>

    
  <div class="site-wrap">
  <?php include("./layouts/header.php"); ?> 

    <div class="site-section">
      <div class="container">
        <div class="row">
          <div class="col-md-12">
            <h2 class="h3 mb-3 text-black">Elige metodo de pago</h2>
          </div>
          <div class="col-md-7">

            <form action="#" method="post">
              
              <div class="p-3 p-lg-5 border">
                
                <div class="form-group row">
                  <div class="col-md-12">
                    <label for="c_fname" class="text-black">Venta #<?php echo $_GET['id_venta'];?></label>
                  </div>
                </div>
                <div class="form-group row">
                  <div class="col-md-12">
                    <label for="c_fname" class="text-black">Nombre <?php echo $datosUsuario[4];?></label>
                  </div>
                </div>
                <div class="form-group row">
                  <div class="col-md-12">
                    <label for="c_fname" class="text-black">Email <?php echo $datosUsuario[6];?></label>
                  </div>
                </div>
                <div class="form-group row">
                  <div class="col-md-12">
                    <label for="c_fname" class="text-black">Telefono <?php echo $datosUsuario[5];?></label>
                  </div>
                </div>
                <div class="form-group row">
                  <div class="col-md-12">
                    <label for="c_fname" class="text-black">Company <?php echo $datosEnvio[2];?></label>
                  </div>
                </div>
                <div class="form-group row">
                  <div class="col-md-12">
                    <label for="c_fname" class="text-black">Direccion <?php echo $datosEnvio[3];?></label>
                  </div>
                </div>
                <div class="form-group row">
                  <div class="col-md-12">
                    <label for="c_fname" class="text-black">Direccion <?php echo $datosEnvio[4];?></label>
                  </div>
                </div>
                
              </div>
            </form>
          </div>
          <div class="col-md-5 ml-auto">
            <h4 class="h1">Total: <?php echo $datosUsuario[2];?></h4>
            <h5>Descuento: <?php  echo $descuento;  ?></h5>
            <h5>Total Final <?php  echo $total;  ?></h5>
                <h2>Mercado pago</h2>
                <script
                src="https://sdk.mercadopago.com/js/v2"
                
                data-preference-id="<?php echo $preference->id; ?>">
                </script>



<div id="wallet_container"></div>

<script>

  
// Configura MercadoPago con tu clave pública
const mp = new MercadoPago('TEST-83761181-f2a5-4280-a9ad-48159949a286');
const bricksBuilder = mp.bricks();

// Inicializa el checkout desde la preferencia previamente creada
bricksBuilder.create('wallet', 'wallet_container', {
  initialization: {
    preferenceId: '<?php echo $preference->id; ?>', // Reemplaza con la ID de tu preferencia
  },
});
</script>

      </div>
    </div>
    <?php include("./layouts/footer.php"); ?> 
    
  </div>

  <script src="js/jquery-3.3.1.min.js"></script>
  <script src="js/jquery-ui.js"></script>
  <script src="js/popper.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/owl.carousel.min.js"></script>
  <script src="js/jquery.magnific-popup.min.js"></script>
  <script src="js/aos.js"></script>

  <script src="js/main.js"></script>
    
  </body>
</html>