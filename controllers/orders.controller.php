<?php

    class OrdersController{

        /*================================================================
            TODO: Actualizar la orden
        ================================================================*/

        public function orderUpdate(){

            if(isset($_POST["stage"])){

                $process = json_decode(base64_decode($_POST["processOrder"]),true);
                $changeProcess = [];

                foreach ($process as $key => $value) {

                    if($value["stage"] == $_POST["stage"]){

                        $value["date"] = $_POST["date"];
                        $value["status"] = $_POST["status"];
                        $value["comment"] = $_POST["comment"];

                    }

                    array_push($changeProcess, $value);

                }

                $url = "orders?id=".$_POST["idOrder"]."&nameId=id_order&token=".$_SESSION["admin"]->token_user."&table=users&suffix=user";
                $method = "PUT";

                /*================================================================
                    TODO: Cambiar estado de la orden y la venta
                ================================================================*/

                if($_POST["stage"] == "delivered" && $_POST["status"] == "ok"){

                    $fields = "status_order=ok&process_order=".json_encode($changeProcess);

                    /*================================================================
                        TODO: Actualizar la venta
                    ================================================================*/

                    $url2 = "sales?id=".$_POST["idOrder"]."&nameId=id_order_sale&token=".$_SESSION["admin"]->token_user."&table=users&suffix=user";
                    $fields2 = "status_sale=ok";

                    $saleUpdate = CurlController::request($url2, $method, $fields2);

                }else{

                    $fields = "process_order=".json_encode($changeProcess);

                }

                $orderUpdate = CurlController::request($url, $method, $fields);

                if($orderUpdate->status == 200){

                    $name = $_POST["clientOrder"];
                    $subject = "Se ha producido un cambio en su orden de compra.";
                    $email = $_POST["emailOrder"];
                    $message = "Se ha producido un cambio en el pedido de su producto. ".$_POST["productOrder"];
                    $url = TemplateController::srcImg()."account&my-shopping";

                    $sendEmail = TemplateController::sendEmail($name, $subject, $email, $message, $url);

                    if($sendEmail == "ok"){

                        echo '<script>

                                fncFormatInputs();

                                fncNotie(1, "La órden a sido actualizada.");

                            </script>
                        ';

                    }

                }

            }

        }

    }

?>