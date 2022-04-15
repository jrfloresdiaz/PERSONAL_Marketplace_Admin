<?php

    require_once "../controllers/curl.controller.php";
    require_once "../controllers/template.controller.php";

    class DatatableController{

        public function data(){

            if(!empty($_POST)){

                /*=====================================================================
                    TODO: Capturando y organizando las variables POST de DataTable
                =====================================================================*/

                $draw = $_POST["draw"];//Contador utilizado por DataTables para garantizar que los retornos de Ajax de las solicitudes de procesamiento del lado del servidor sean dibujados en secuencia por DataTables

                $orderByColumnIndex = $_POST['order'][0]['column']; //Índice de la columna de clasificación (0 basado en el índice, es decir, 0 es el primer registro)

                $orderBy = $_POST['columns'][$orderByColumnIndex]["data"];//Obtener el nombre de la columna de clasificación de su índice

                $orderType = $_POST['order'][0]['dir'];// Obtener el orden ASC o DESC

                $start  = $_POST["start"];//Indicador de primer registro de paginación.

                $length = $_POST['length'];//Indicador de la longitud de la paginación.

                /*=====================================================================
                    TODO: El total de registros de la data
                =====================================================================*/

                $url = "users?select=id_user&linkTo=date_created_user&between1=".$_GET["between1"]."&between2=".$_GET["between2"]."&filterTo=rol_user&inTo='admin'";

                $method = "GET";
                $fields = array();

                $response = CurlController::request($url, $method, $fields);

                if($response->status == 200){

                    $totalData = $response->total;

                }else{

                    echo '{"data": []}';
                    return;

                }

                /*=====================================================================
                    TODO: Busqueda de datos
                =====================================================================*/

                $select = "id_user,picture_user,displayname_user,username_user,email_user,country_user,city_user,date_created_user,method_user";

                if(!empty($_POST['search']['value'])){

                    if(preg_match('/^[0-9A-Za-zñÑáéíóú ]{1,}$/',$_POST['search']['value'])){

                        $linkTo = ["displayname_user","username_user","email_user","country_user","city_user","date_created_user"];

                        $search = str_replace(" ", "_", $_POST['search']['value']);

                        foreach($linkTo as $key => $value) {

                            //$url = "users?select=".$select."&linkTo=".$value."&search=".$search."&orderBy=".$orderBy."&orderMode=".$orderType."&startAt=".$start."&endAt=".$length;
                            $url = "users?select=".$select."&linkTo=".$value.",rol_user&search=".$search.",admin&orderBy=".$orderBy."&orderMode=".$orderType."&startAt=".$start."&endAt=".$length;

                            $data = CurlController::request($url, $method, $fields)->results;

                            if($data == "Not Found"){

                                $data = array();
                                $recordsFiltered = count($data);

                            }else{

                                $data = $data;
                                $recordsFiltered = count($data);

                                break;

                            }

                        }
                    }else{

                        echo '{"data": []}';
                        return;

                    }

                }else{

                    /*=====================================================================
                        TODO: Seleccionar datos
                    =====================================================================*/

                    $url = "users?select=".$select."&linkTo=date_created_user&between1=".$_GET["between1"]."&between2=".$_GET["between2"]."&filterTo=rol_user&inTo='admin'&orderBy=".$orderBy."&orderMode=".$orderType."&startAt=".$start."&endAt=".$length;

                    $data = CurlController::request($url, $method, $fields)->results;

                    $recordsFiltered = $totalData;

                }

                /*=====================================================================
                    TODO: Cuando la data viene vacía
                =====================================================================*/

                if(empty($data)){

                    echo '{"data": []}';
                    return;

                }

                /*=====================================================================
                    TODO: Construimos el dato JSON a regresar
                =====================================================================*/

                $dataJson = '{

                    "Draw": '.intval($draw).',
                    "recordsTotal": '.$totalData.',
                    "recordsFiltered": '.$recordsFiltered.',
                    "data": [';

                    /*=====================================================================
                        TODO: Recorremos la data
                    =====================================================================*/

                    foreach($data as $key => $value){

                        if($_GET["text"] == "flat"){

                            $picture_user = $value->picture_user;
                            $actions = "";

                        }else{

                            $picture_user = "<img src='".TemplateController::returnImg($value->id_user, $value->picture_user, $value->method_user)."' class='img-circle' style='width:70px'>";

                            $actions = "<a href='/administradores/editar/".base64_encode($value->id_user."~".$_GET["token"])."' class='btn btn-warning btn-sm mr-1 rounded-circle'>

                                        <i class='fas fa-pencil-alt'></i>

                                        </a>

                                        <a class='btn btn-danger btn-sm rounded-circle removeItem' idItem='".base64_encode($value->id_user."~".$_GET["token"])."' table='users' suffix='user' deleteFile='users/".$value->id_user."/".$value->picture_user."' page='administradores'>

                                        <i class='fas fa-trash'></i>

                                        </a>";

                                        $actions = TemplateController::htmlClean($actions);


                        }

                        $displayname_user = $value->displayname_user;
                        $username_user = $value->username_user;
                        $email_user = $value->email_user;
                        $country_user = $value->country_user;
                        $city_user = $value->city_user;
                        $date_created_user = $value->date_created_user;

                        $dataJson.='{

                            "id_user":"'.($start+$key+1).'",
                            "picture_user":"'.$picture_user.'",
                            "displayname_user":"'.$displayname_user.'",
                            "username_user":"'.$username_user.'",
                            "email_user":"'.$email_user.'",
                            "country_user":"'.$country_user.'",
                            "city_user":"'.$city_user.'",
                            "date_created_user":"'.$date_created_user.'",
                            "actions":"'.$actions.'"

                        },';

                    }

                $dataJson = substr($dataJson,0,-1); // este substr quita el último caracter de la cadena, que es una coma, para impedir que rompa la tabla

                $dataJson .= ']}';

                echo $dataJson;

            }
        }
    }


    /*=============================================
        TODO: Activar función DataTable
    =============================================*/

    $data = new DatatableController();
    $data -> data();

?>