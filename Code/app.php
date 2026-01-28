<?php
session_start();
require_once __DIR__ . "/path.php";
require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/bd/lec_bd.php";

$url = $_SERVER['PATH_INFO'] ?? "/";

switch($url) {
    case "/":
    case "/index":
        include_once ROOT . "index.php";
        break;

    case "/amis":
        include_once ROOTFORM . "form_amis.php";
        include_once ROOT . "amis.php";

    case "/creationRoadTrip":
        if($_SERVER['REQUEST_METHOD'] === 'GET') {
            include_once ROOT . "creationRoadTrip.php";
        } else {
            include_once ROOTFUNCTIONS . "jsonError.php";
            include_once ROOTFUNCTIONS . "sauvegarderVilleDansCache.php";
            include_once ROOTFORM . "saveRoadtrip.php";
        }
        break;

    case "/mesRoadTrips":
        include_once ROOT . "mesRoadTrips.php";
        break;

    case "/vuRoadTrip":
        include_once ROOTFUNCTIONS . "geocoderVilleEnDirect.php";
        include_once ROOTFUNCTIONS . "getCoordonneesDepuisFavoris.php";
        include_once ROOTFUNCTIONS . "InfoItineraire.php";
        include_once ROOTFUNCTIONS . "getTransportIcon.php";
        include_once ROOT . "vuRoadTrip.php";
        break;

    case "/profil":
        if($_SERVER['REQUEST_METHOD'] === 'GET') {
            include_once ROOT . "profil.php";
        } else {
            include_once ROOTFORM . "form_modif.php";
        }
        break;

    case "/login":
        if($_SERVER['REQUEST_METHOD'] === 'GET') {
            include_once ROOT . "login.php";
        } else {
            include_once ROOTFORM . "form_connect.php";
        }
        break;

    case "/register":
        if($_SERVER['REQUEST_METHOD'] === 'GET') {
            include_once ROOT . "register.php";
        } else {
            include_once ROOTFORM . "form_register.php";
        }
        break;

    case "/logout":
        include_once ROOT . "logout.php";
        break;

    case "/reset_password":
        if($_SERVER['REQUEST_METHOD'] === 'GET') {
            include_once ROOT . "reset_password.php";
        } else {
            include_once ROOTFORM . "traitement_reset.php";
        }
        break;

    case "/oublie":
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            include_once ROOTFORM . "traitement_oublie.php";
        } else {
            include_once ROOT . "reset_password.php";
        }
        break;

    case "/messagerie":
        include_once ROOT . "messagerie.php";
        break;
    case "/send_mess":
        include_once ROOT . "messagerie/send_mess.php";
        break;
    case "/debut_conv":
        include_once ROOT . "messagerie/debut_conv.php";
        break;
    case "/get_mess":
        include_once ROOT . "messagerie/get_mess.php";
        break;
    case "/api/get_unread_count":
        include_once ROOT . "messagerie/get_unread_count.php";
        break;
    case "/api/get_public_key":
        include_once ROOT . "messagerie/get_public_key.php";
        break;
    case "/api/save_public_key":
        include_once ROOT . "messagerie/save_public_key.php";
        break;
    case "/api/get_session_id":
        include_once ROOT . "messagerie/get_session_id.php";
        break;

    case "/api/search_city":
        include_once ROOTFUNCTIONS . "recherche_villes.php";
        break;

    case "/api/all_roadtrips":
        include_once ROOT . "bd/rech_bd.php";
        break;

    case "/get_lieux_favoris":
        include_once ROOT . "fonctions/get_lieux_favoris.php";
        break;

    case "/fav_lieu":
        include_once ROOTFORM . "fav_lieu.php";
        break;

    case "/delete_RoadTrip":
        include_once ROOTFORM . "delete_RoadTrip.php";
        break;

    case "/upload_image":
        include_once ROOTFORM . "traitementImageTiny.php";
        break;

    case "/generate_shared_link":
        include_once ROOTFUNCTIONS . "/generate_shared_link.php";
        break;

    case "/fonctions/oublie":
        include_once ROOTFUNCTIONS . "oublie.php";
        break;

    case "/accessibilite": include_once ROOT . "accessibilite.php"; break;
    case "/favoris": include_once ROOT . "favoris.php"; break;
    case "/historique": include_once ROOT . "historique.php"; break;
    case "/public_road": include_once ROOTFUNCTIONS . "geocoderVilleEnDirect.php"; include_once ROOTFUNCTIONS . "InfoItineraire.php"; include_once ROOTFUNCTIONS . "getTransportIcon.php"; include_once ROOT . "public_road.php"; break;
    case "/shared": include_once ROOTFUNCTIONS . "InfoItineraire.php"; include_once ROOTFUNCTIONS . "gettTransportIcon.php"; include_once ROOT . "shared.php"; break;
    case "/Roadtrip": include_once ROOT . "Roadtrip.php"; break;

    case "/page_link/cgu": include_once ROOTLINK . "cgu.php"; break;
    case "/page_link/contact": include_once ROOTLINK . "contact.php"; break;
    case "/page_link/cookie": include_once ROOTLINK . "cookie.php"; break;
    case "/page_link/politique": include_once ROOTLINK . "politique.php"; break;
    case "/page_link/faq":
        if($_SERVER['REQUEST_METHOD'] === 'GET') include_once ROOTLINK . "faq.php";
        else include_once ROOTFORM . "form_faq.php";
        break;

    default:
        http_response_code(404);
        include_once ROOT . "404.php";
        break;
}