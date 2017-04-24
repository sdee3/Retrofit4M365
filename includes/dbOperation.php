<?php

class dbOperation{

    private $conn;

    function __construct(){

        require_once dirname(__FILE__) . '/dbConnect.php';
        $db = new dbConnect();
        $this->conn = $db->connect();

    }

    function duplicateUser($email, $username)
    {
        $stmt = $this->conn->prepare("SELECT id FROM user WHERE email = ? OR username = ?");
        $stmt->bind_param('ss', $email, $username);
        $stmt->execute();
        $stmt->store_result();

        return $stmt->num_rows > 0;
    }

    function registerUser($username, $password, $email, $full_name){

        if(!$this->duplicateUser($email, $username)){
            $password = md5($password);

            $stmt = $this->conn->prepare("INSERT INTO user (username, password, email, full_name) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('ssss', $username, $password, $email, $full_name);
            if($stmt->execute())
                return true;
            else
                throw new Exception($this->conn->error);

        }

    }

    function userLogin($email, $password){

        $password = md5($password);
        $stmt = $this->conn->prepare("SELECT id FROM user WHERE email = ? AND password = ? AND is_admin = 0");
        $stmt->bind_param('ss', $email, $password);
        $stmt->execute();
        $stmt->store_result();

        return $stmt->num_rows > 0;
    }

    function adminLogin($email, $password){

        $password = md5($password);
        $stmt = $this->conn->prepare("SELECT id FROM user WHERE email = ? AND password = ? AND is_admin = 1");
        $stmt->bind_param("ss", $email, $password);
        $stmt->execute();
        $stmt->store_result();

        return $stmt->num_rows > 0;

    }

    function getUserByEmail($email){

        $stmt = $this->conn->prepare("SELECT id, username, email FROM user WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->bind_result($id, $username, $email);
        $stmt->fetch();

        $users = array();
        $users['id'] = $id;
        $users['username'] = $username;
        $users['email'] = $email;

        return $users;

    }

    function updateCredit($id, $credit){

        $stmt = $this->conn->prepare("UPDATE user SET credit = credit + ? WHERE id = ?");
        $stmt->bind_param('ii', $credit, $id);
        $stmt->execute();

    }

    function insertUtakmica($id_lige, $naziv_utakmice, $datum_utakmice, $vreme_utakmice, $ki1, $kix, $ki2, $ug02, $ug3p, $specijal){

        $stmt = $this->conn->prepare("INSERT INTO utakmica (id_lige, naziv_utakmice, datum_utakmice, vreme_utakmice, ki1, kix, ki2, ug02, ug3p, specijal) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('isssdddddd', $id_lige, $naziv_utakmice, $datum_utakmice, $vreme_utakmice, $ki1, $kix, $ki2, $ug02, $ug3p, $specijal);
        if($stmt->execute())
            return true;
        else
            throw new Exception($this->conn->error);

    }

    function insertTiket($id, $uplata, $ukupna_kvota, $dobitak, $utakmice){

        $stmt = $this->conn->prepare("INSERT INTO user_tiket (id_tiket, id, uplata, ukupna_kvota, dobitak, utakmice) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iiidds', rand(1,500), $id, $uplata, $ukupna_kvota, $dobitak, $utakmice);
        if($stmt->execute()) {

            //Promena stanja na racunu:
            $stmt1 = $this->conn->prepare("UPDATE user SET credit = credit - ? WHERE id = ?");
            $stmt1->bind_param('ii', $uplata, $id);
            if(!$stmt1->execute())
                throw new Exception($this->conn->error);

            return true;
        }
        else
            throw new Exception($this->conn->error);

    }

    function changePassword($new, $email){

        $new = md5($new);

        $stmt = $this->conn->prepare("UPDATE user SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $new, $email);

        if($stmt->execute())
            return true;
        else
            throw new Exception($this->conn->error);

    }

    function deleteUser($email){

        $stmt = $this->conn->prepare("DELETE FROM user WHERE email = ?");
        $stmt -> bind_param("s", $email);
        if($stmt->execute())
            return true;
        else
            throw new Exception($this->conn->error);

    }

    function getSportList(){

        $stmt = $this->conn->prepare("SELECT naziv_sporta FROM sport_list");
        $stmt->execute();
        $stmt->bind_result($naziv_sporta);
        $sportovi = array();

        while($stmt->fetch()){

            $temp = array();

            $temp['naziv_sporta'] = $naziv_sporta;

            array_push($sportovi, $temp);

        }

        return $sportovi;

    }

    function getListaFudbal(){

        $stmt = $this->conn->prepare("select u.id_utakmice, l.naziv_lige, u.naziv_utakmice, u.datum_utakmice, u.vreme_utakmice, u.ki1, u.kix, u.ki2, u.ug02, u.ug3p, u.specijal
              from utakmica u, liga l where (u.id_lige = l.id_lige) and l.id_sporta = 1;");
        $stmt->execute();
        $stmt->bind_result($id_utakmice, $naziv_lige, $naziv_utakmice, $datum_utakmice,
            $vreme_utakmice, $ki1, $kix, $ki2, $ug02, $ug3p, $specijal);

        $lista = array();

        while($stmt->fetch()){

            $temp = array();

            $temp['id_utakmice'] = $id_utakmice;
            $temp['naziv_lige'] = $naziv_lige;
            $temp['naziv_utakmice'] = $naziv_utakmice;
            $temp['datum_utakmice'] = $datum_utakmice;
            $temp['vreme_utakmice'] = $vreme_utakmice;
            $temp['ki1'] = $ki1;
            $temp['kix'] = $kix;
            $temp['ki2'] = $ki2;
            $temp['ug02'] = $ug02;
            $temp['ug3p'] = $ug3p;
            $temp['specijal'] = $specijal;

            array_push($lista, $temp);

        }

        return $lista;

    }

    function getListaKosarka(){

        $stmt = $this->conn->prepare("select u.id_utakmice, l.naziv_lige, u.naziv_utakmice, u.datum_utakmice, u.vreme_utakmice, u.ki1, u.kix, u.ki2
              from utakmica u, liga l where (u.id_lige = l.id_lige) and l.id_sporta = 2;");
        $stmt->execute();
        $stmt->bind_result($id_utakmice, $naziv_lige, $naziv_utakmice, $datum_utakmice,
            $vreme_utakmice, $ki1, $kix, $ki2);

        $lista = array();

        while($stmt->fetch()){

            $temp = array();

            $temp['id_utakmice'] = $id_utakmice;
            $temp['naziv_lige'] = $naziv_lige;
            $temp['naziv_utakmice'] = $naziv_utakmice;
            $temp['datum_utakmice'] = $datum_utakmice;
            $temp['vreme_utakmice'] = $vreme_utakmice;
            $temp['ki1'] = $ki1;
            $temp['kix'] = $kix;
            $temp['ki2'] = $ki2;

            array_push($lista, $temp);

        }

        return $lista;

    }

    function getListaTenis(){

        $stmt = $this->conn->prepare("select u.id_utakmice, l.naziv_lige, u.naziv_utakmice, u.datum_utakmice, u.vreme_utakmice, u.ki1, u.ki2
              from utakmica u, liga l where (u.id_lige = l.id_lige) and l.id_sporta = 3;");
        $stmt->execute();
        $stmt->bind_result($id_utakmice, $naziv_lige, $naziv_utakmice, $datum_utakmice,
            $vreme_utakmice, $ki1, $ki2);

        $lista = array();

        while($stmt->fetch()){

            $temp = array();

            $temp['id_utakmice'] = $id_utakmice;
            $temp['naziv_lige'] = $naziv_lige;
            $temp['naziv_utakmice'] = $naziv_utakmice;
            $temp['datum_utakmice'] = $datum_utakmice;
            $temp['vreme_utakmice'] = $vreme_utakmice;
            $temp['ki1'] = $ki1;
            $temp['ki2'] = $ki2;

            array_push($lista, $temp);

        }

        return $lista;

    }

    function getListaGrckiKino(){

        $stmt = $this->conn->prepare("select u.id_utakmice, l.naziv_lige, u.naziv_utakmice, u.datum_utakmice, u.vreme_utakmice, u.ki1, u.kix, u.ki2, u.ug02
              from utakmica u, liga l where (u.id_lige = l.id_lige) and l.id_sporta = 4;");
        $stmt->execute();
        $stmt->bind_result($id_utakmice, $naziv_lige, $naziv_utakmice, $datum_utakmice,
            $vreme_utakmice, $ki1, $kix, $ki2, $ug02);

        $lista = array();

        while($stmt->fetch()){

            $temp = array();

            $temp['id_utakmice'] = $id_utakmice;
            $temp['naziv_lige'] = $naziv_lige;
            $temp['naziv_utakmice'] = $naziv_utakmice;
            $temp['datum_utakmice'] = $datum_utakmice;
            $temp['vreme_utakmice'] = $vreme_utakmice;
            $temp['ki1'] = $ki1;
            $temp['kix'] = $kix;
            $temp['ki2'] = $ki2;
            $temp['ug02'] = $ug02;

            array_push($lista, $temp);

        }

        return $lista;


    }

    function getListaBadza(){

        $stmt = $this->conn->prepare("select u.id_utakmice, l.naziv_lige, u.naziv_utakmice, u.datum_utakmice, u.vreme_utakmice, u.ki1, u.kix, u.ki2
              from utakmica u, liga l where (u.id_lige = l.id_lige) and l.id_sporta = 5;");
        $stmt->execute();
        $stmt->bind_result($id_utakmice, $naziv_lige, $naziv_utakmice, $datum_utakmice,
            $vreme_utakmice, $ki1, $kix, $ki2);

        $lista = array();

        while($stmt->fetch()){

            $temp = array();

            $temp['id_utakmice'] = $id_utakmice;
            $temp['naziv_lige'] = $naziv_lige;
            $temp['naziv_utakmice'] = $naziv_utakmice;
            $temp['datum_utakmice'] = $datum_utakmice;
            $temp['vreme_utakmice'] = $vreme_utakmice;
            $temp['ki1'] = $ki1;    //Hipsterski Combo Pack
            $temp['kix'] = $kix;    //Casual, yet Stylish
            $temp['ki2'] = $ki2;    //Spring 2017 Throwback

            array_push($lista, $temp);

        }

        return $lista;

    }

    function getListaTrkaPrasica(){

        $stmt = $this->conn->prepare("select u.id_utakmice, l.naziv_lige, u.naziv_utakmice, u.datum_utakmice, u.vreme_utakmice, u.ki1, u.kix, u.ki2, u.ug02
              from utakmica u, liga l where (u.id_lige = l.id_lige) and l.id_sporta = 6;");
        $stmt->execute();
        $stmt->bind_result($id_utakmice, $naziv_lige, $naziv_utakmice, $datum_utakmice,
            $vreme_utakmice, $ki1, $kix, $ki2, $ug02);

        $lista = array();

        while($stmt->fetch()){

            $temp = array();

            $temp['id_utakmice'] = $id_utakmice;
            $temp['naziv_lige'] = $naziv_lige;
            $temp['naziv_utakmice'] = $naziv_utakmice;
            $temp['datum_utakmice'] = $datum_utakmice;
            $temp['vreme_utakmice'] = $vreme_utakmice;
            $temp['ki1'] = $ki1;
            $temp['kix'] = $kix;
            $temp['ki2'] = $ki2;
            $temp['ug02'] = $ug02;

            array_push($lista, $temp);

        }

        return $lista;

    }

    function getTiket($idTiket, $idKorisnik){

        $stmt = $this->conn->prepare("SELECT id_tiket, uplata, ukupna_kvota, dobitak, utakmice, dobitan FROM user_tiket WHERE id_tiket = ? AND id = ?");
        $stmt->bind_param("ii", $idTiket, $idKorisnik);

        if($stmt->execute()) {

            $stmt->bind_result($id_tiket, $uplata, $ukupna_kvota, $dobitak, $utakmice, $dobitan);
            $tiket = array();

            while($stmt->fetch()){

                $temp = array();

                $temp['id_tiket'] = $id_tiket;
                $temp['uplata'] = $uplata;
                $temp['ukupna_kvota'] = $ukupna_kvota;
                $temp['dobitak'] = $dobitak;
                $temp['utakmice'] = $utakmice;
                $temp['dobitan'] = $dobitan;

                array_push($tiket, $temp);

            }

            return $tiket;

        }else
            throw new Exception($this->conn->error);

    }

    function statusTiketa($idTiket, $idKorisnik, $status){

        $stmt = $this->conn->prepare("UPDATE user_tiket SET dobitan = ? WHERE id_tiket = ? AND id = ?");
        $stmt->bind_param("iii", $status, $idTiket, $idKorisnik);

        if($stmt->execute())
            return true;
        else
            throw new Exception($this->conn->error);

    }

}