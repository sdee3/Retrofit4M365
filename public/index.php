<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';
require_once '../includes/dbOperation.php';

    //New app with the config to show errors
    $app = new \Slim\App(['settings' => ['displayErrorDetails' => true]]);

    function checkAvailableParameters($required_fields){

        $error = false;
        $error_fields = "";
        $request_params = $_REQUEST;

        foreach ($required_fields as $field){
            if(!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0){
                $error = true;
                $error_fields .= $field . ', ';
            }
        }

        if($error){
            $response = array();
            $response['error'] = true;
            $response["message"] = 'Nedostaju polja: ' . substr($error_fields, 0, -2);

            echo json_encode($response);
            return false;
        }
        return true;

    }

    //User Registration
    $app->post('/register', function (Request $request, Response $response){
        if(checkAvailableParameters(array('username', 'password', 'email', 'full_name'))){

            $requestData = $request->getParsedBody();

            $username = $requestData['username'];
            $password = $requestData['password'];
            $email = $requestData['email'];
            $full_name = $requestData['full_name'];

            $db = new dbOperation();

            $responseData = array();

            $result = $db->registerUser($username, $password, $email, $full_name);

            if($result){
                $responseData['message'] = 'Korisnik registrovan.';
                $responseData['error'] = false;
                $responseData['user'] = $db->getUserByEmail($email);
            }else{
                $responseData['message'] = 'Korisnik vec postoji.';
                $responseData['error'] = true;
            }

            $response->getBody()->write(json_encode($responseData));
        }
    });

    //User Login
    $app->post('/login', function(Request $request, Response $response){
       if(checkAvailableParameters(array('email', 'password'))){
           $requestData = $request->getParsedBody();

           $email = $requestData['email'];
           $password = $requestData['password'];

           $db = new dbOperation();

           $responseData = array();

           $result = $db->userLogin($email, $password);

           if($result){
               $responseData['error'] = false;
               $responseData['user'] = $db->getUserByEmail($email);
           }else{
               $responseData['error'] = true;
               $responseData['message'] = 'Neispravni podaci.';
           }

           $response->getBody()->write(json_encode($responseData));
       }
    });

    //Admin Login
    $app->post('/admin', function(Request $request, Response $response){
        if(checkAvailableParameters(array('email', 'password'))){
            $requestData = $request->getParsedBody();

            $email = $requestData['email'];
            $password = $requestData['password'];

            $db = new dbOperation();

            $responseData = array();

            $result = $db->adminLogin($email, $password);

            if($result){
                $responseData['error'] = false;
                $responseData['user'] = $db->getUserByEmail($email);
            }else{
                $responseData['error'] = true;
                $responseData['message'] = 'Neispravni podaci.';
            }

            $response->getBody()->write(json_encode($responseData));
        }
    });

    //Update Credit - potrebno uneti ID i DOPUNU
    $app->post('/update_credit', function(Request $request, Response $response){
        if(checkAvailableParameters(array('id', 'credit'))){

            $requestData = $request->getParsedBody();

            $id = $requestData['id'];
            $credit = $requestData['credit'];

            $db = new dbOperation();

            $responseData = array();

            $db->updateCredit($id, $credit);
            $responseData['error'] = false;
            $responseData['message'] = 'Uspesno azuriran preostali kredit.';

            $response->getBody()->write(json_encode($responseData));

        }
    });

    $app->post('/insert_utakmica', function (Request $request, Response $response){

        if(checkAvailableParameters(array('id_lige', 'naziv_utakmice', 'datum_utakmice', 'vreme_utakmice', 'ki1', 'kix', 'ki2', 'ug02', 'ug3p', 'specijal'))){

            $requestData = $request->getParsedBody();

            $id_lige = $requestData['id_lige'];
            $naziv_utakmice = $requestData['naziv_utakmice'];
            $datum_utakmice = $requestData['datum_utakmice'];
            $vreme_utakmice = $requestData['vreme_utakmice'];
            $ki1 = $requestData['ki1'];
            $kix = $requestData['kix'];
            $ki2 = $requestData['ki2'];
            $ug02 = $requestData['ug02'];
            $ug3p = $requestData['ug3p'];
            $specijal = $requestData['specijal'];

            $db = new dbOperation();

            $responseData = array();

            $db->insertUtakmica($id_lige, $naziv_utakmice, $datum_utakmice, $vreme_utakmice, $ki1, $kix, $ki2, $ug02, $ug3p, $specijal);
            $responseData['error'] = false;
            $responseData['message'] = 'Uspesno dodata utakmica ' . $naziv_utakmice;

            $response->getBody()->write(json_encode($responseData));

        }

    });

    $app->post('/insert_tiket', function (Request $request, Response $response){

        if(checkAvailableParameters(array('id', 'uplata', 'ukupna_kvota', 'dobitak', 'utakmice'))){

            $requestData = $request->getParsedBody();

            $id = $requestData['id'];
            $uplata = $requestData['uplata'];
            $ukupna_kvota = $requestData['ukupna_kvota'];
            $dobitak = $requestData['dobitak'];
            $utakmice = $requestData['utakmice'];

            $db = new dbOperation();

            $responseData = array();

            $db->insertTiket($id, $uplata, $ukupna_kvota, $dobitak, $utakmice);
            $responseData['error'] = false;
            $responseData['message'] = 'Uspesno kreiran tiket sa potencijalnim dobitkom od: ' . $dobitak . ' dinara';

            $response->getBody()->write(json_encode($responseData));

        }

    });

    //Change Password
    $app->post('/change_pass', function(Request $request, Response $response){
        if(checkAvailableParameters(array('new', 'email'))){
            $requestData = $request->getParsedBody();

            $email = $requestData['email'];
            $new = $requestData['new'];

            $db = new dbOperation();

            $db->changePassword($new, $email);
        }
    });

    $app->post('/delete_user', function (Request $request, Response $response){

        if(checkAvailableParameters(array('email'))){

            $requestData = $request->getParsedBody();

            $email = $requestData['email'];

            $db = new dbOperation();

            $responseData = array();

            $db->deleteUser($email);
            $responseData['error'] = false;
            $responseData['message'] = 'Uspesno obrisan korisnik.';

            $response->getBody()->write(json_encode($responseData));

        }

    });

    //Vraca JSON listu: sport
    $app->get('/sportovi', function (Request $request, Response $response) {
        $db = new DbOperation();
        $sportList = $db->getSportList();
        $response->getBody()->write(json_encode(array('sport' => $sportList)));
    });

    //Vraca JSON listu: lista_fudbal
    $app->get('/fudbal', function (Request $request, Response $response) {
        $db = new DbOperation();
        $fudbalList = $db->getListaFudbal();
        $response->getBody()->write(json_encode(array('lista_fudbal' => $fudbalList)));
    });

    //Vraca JSON listu: lista_kosarka
    $app->get('/kosarka', function (Request $request, Response $response) {
        $db = new DbOperation();
        $kosarkaList = $db->getListaKosarka();
        $response->getBody()->write(json_encode(array('lista_kosarka' => $kosarkaList)));
    });

    //Vraca JSON listu: lista_kosarka
    $app->get('/tenis', function (Request $request, Response $response) {
        $db = new DbOperation();
        $tenisList = $db->getListaTenis();
        $response->getBody()->write(json_encode(array('lista_tenis' => $tenisList)));
    });

    //Vraca JSON listu: lista_grcki_kino
    $app->get('/grckikino', function (Request $request, Response $response) {
        $db = new DbOperation();
        $grKinoList = $db->getListaGrckiKino();
        $response->getBody()->write(json_encode(array('lista_grcki_kino' => $grKinoList)));
    });

    //Vraca JSON listu: lista_badza
    $app->get('/badza', function (Request $request, Response $response) {
        $db = new DbOperation();
        $badzaList = $db->getListaBadza();
        $response->getBody()->write(json_encode(array('lista_badza' => $badzaList)));
    });

    //Vraca JSON listu: lista_trke_prasica
    $app->get('/trkeprasica', function (Request $request, Response $response) {
        $db = new DbOperation();
        $prasiciList = $db->getListaTrkaPrasica();
        $response->getBody()->write(json_encode(array('lista_trke_prasica' => $prasiciList)));
    });

    //Update tiketa
    $app->post('/update_status', function (Request $request, Response $response) {

        if(checkAvailableParameters(array('idTiket', 'idKorisnik', 'status'))){

            $requestData = $request->getParsedBody();

            $id_tiket = $requestData['idTiket'];
            $id_korisnik = $requestData['idKorisnik'];
            $status= $requestData['status'];

            $db = new dbOperation();

            $responseData = array();

            if($db->statusTiketa($id_tiket, $id_korisnik, $status)){
                $responseData['error'] = false;
                $responseData['message'] = 'Uspesno promenjen status tiketa!';

                $response->getBody()->write(json_encode($responseData));
            }
        }

    });

    //Get Tiket
    $app->get('/tiket', function (Request $request, Response $response) {

        if(checkAvailableParameters(array('idTiket', 'idKorisnik'))) {

            $requestData = $request->getParsedBody();

            $idTiket = $requestData['idTiket'];
            $idKorisnik = $requestData['idKorisnik'];

            $db = new dbOperation();

            $tiket = $db->getTiket($idTiket, $idKorisnik);
            $response->getBody()->write(json_encode(array('tiket' => $tiket)));
        }
    });

$app->run();