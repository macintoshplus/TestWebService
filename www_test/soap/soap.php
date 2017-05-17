<?php


header("Site: FRSRV3");
/**
 * Déclaration d'une classe qui contiendra les méthodes de Service WEB, et instanciation de la classe SoapServer
 * pour rendre notre Service disponible
 *
 */

//Cette classe pourra contenir d'autres méthodes accessibles via le SoapServer
class DateServer
{
    
    //On déclare notre méthode qui renverra la date et la signature du serveur dans un tableau associatif...
    public function retourDate()
    {

        $tab = array(
            'serveur' => isset($_SERVER['SERVER_SIGNATURE']) ? $_SERVER['SERVER_SIGNATURE']:'Server Sign',
            'date' => date("d/m/Y")
        );

        return $tab;
    }
}

//Cette option du fichier php.ini permet de ne pas stocker en cache le fichier WSDL, afin de pouvoir faire nos tests
//Car le cache se renouvelle toutes les 24 heures, ce qui n'est pas idéal pour le développement
//ini_set('soap.wsdl_cache_enabled', 0);

//L'instanciation du SoapServer se déroule de la même manière que pour le client : voir la doc pour plus d'informations sur les 
//Différentes options disponibles 
$serversoap=new SoapServer("http://127.0.0.1:8001/soap/exemple.wsdl", ['cache_wsdl' => 0, 'trace' => 1, 'soap_version' => SOAP_1_1]);

//Ici nous déclarons la classe qui sera servie par le Serveur SOAP, c'est cette déclaration qui fera le coeur de notre Servie WEB.
//Je déclare que je sers une classe contenant des méthodes accessibles, on peut aussi déclarer plus simplement des fonctions
//par l'instruction addFunction() : $serversoap->addFunction("retourDate"); à ce moment-là nous ne faisons pas de classe.

//Noter le style employé pour la déclaration : le nom de la classe est passé en argument de type String, et non pas de variable...
$serversoap->setClass("DateServer");


//Ici, on dit très simplement que maintenant c'est à PHP de prendre la main pour servir le Service WEB : il s'occupera de l'encodage XML, des
//Enveloppes SOAP, de gérer les demandes clientes, etc. Bref, on en a fini avec le serveur SOAP !!!!
$serversoap->handle();
