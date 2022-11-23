<?php
session_start();

function api_all_types($key = 'code')
{
    $all_types = api_call();
    $all_types_key = array();
    for ($i = 0; $i < count($all_types); $i++) {
        $all_types_key[] = $all_types[$i][$key];
    }
    return ($all_types_key);
}
function api_call($url = NULL, $country = NULL, $year = NULL, $types = array())
{
    $login = 'any-login';
    $password = 'HK2EOquTh6BUiN817Gb80R8ui9TDpJgM3F26i8sfSp2un496d8o';
    if ($url) {
        if (substr($url, 0, 4) != 'http') {
            $request = 'https://api.footprintnetwork.org/v1/' . $url;
        } else {
            $request = $url;
        }
    } elseif (!$country) {
        if (!$year && (!$types || (is_array($types) && count($types) == 0))) {
            $request = 'https://api.footprintnetwork.org/v1/types';
        } else {
            return null;
        }
    } else {
        $request = 'https://api.footprintnetwork.org/v1/data/' . (string)$country;
        if ($year) {
            $request .= '/' . (string)$year . '/';
            $all_types_code = api_all_types();
            if (is_string($types) && in_array($types, $all_types_code)) {
                $request .= '/' . $types;
            } elseif (is_array($types) && count($types) >= 1) {
                for ($i = 0; $i < count($types); $i++) {
                    if (in_array($types[$i], $all_types_code)) {
                        if (substr($request, -1) == '/') {
                            $request .= $types[$i];
                        } else {
                            $request .= ',' . $types[$i];
                        }
                    }
                }
            }
        }
    }

    // Initialize the session
    $session = curl_init();

    // Set curl options
    curl_setopt($session, CURLOPT_URL, $request);
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($session, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($session, CURLOPT_USERPWD, "$login:$password");
    curl_setopt($session, CURLOPT_HTTPHEADER, array('Accept: application/json'));
    curl_setopt($session, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);

    $response = json_decode(curl_exec($session), true);

    curl_close($session);

    return ($response);
}

function get_url()
{
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
        $url = "https://";
    else
        $url = "http://";
    // Append the host(domain name, ip) to the URL.   
    $url .= $_SERVER['HTTP_HOST'];

    // Append the requested resource location to the URL   
    $url .= $_SERVER['REQUEST_URI'];

    if (substr($url, -1, 1) == '/') {
        return substr($url, 0, strlen($url) - 1);
    } else {
        return $url;
    }
}

function array_to_html(array $liste){
    echo '<ul class="w3-ul w3-light-grey w3-center">';
    echo '<li id="'.$liste[0]['year'].'" class="w3-red w3-xlarge w3-padding-32">'.$liste[0]['year'].'</li>';
    echo '</ul>';
    foreach($liste as $element){
        $record=$element['record'];
        $keys = array_keys($element);
        echo '<ul class="w3-ul w3-light-grey w3-center">';
        echo '<li class="w3-padding-16"><h2>'.$record.'</h2></li>';
        echo '</ul>';
        echo '<ul class="ul-double w3-ul w3-light-grey w3-center">';
        foreach($keys as $key){
            if(!in_array($key,array('countryName','shortName','year','isoa2','countryCode','record','value'))){
                echo '<li class="w3-padding-16">'.$key.'</li>';
            }elseif($key=='value'){
                echo '<li class="w3-padding-16">Total value of '.$record.'</li>';
            }
        }
        foreach($keys as $key){
            if(!in_array($key,array('countryName','shortName','year','isoa2','countryCode','record'))){
                echo '<li class="w3-padding-16">'.$element[$key].'</li>';
            }
        }
        echo '</ul><br>';
    }
    
    echo'</ul>';
    echo '<br>';
}

$url_array=parse_url(get_url());
$query=NULL;
if(isset($url_array['query'])){
    $query = $url_array['query'];
}

if (!$query) {
    echo '<script>window.location.replace("https://sciencepoviedonnes2022.000webhostapp.com/#search_data");</script>';
}

$query = explode('&', $query);

$parameters = array();
foreach ($query as $parameter) {
    $matches = array();
    if (preg_match('/(?<==).*/', $parameter, $matches)) {
        $parameters[] = $matches[0];
    }
}

if (count($parameters) == 3) {
    if(preg_match('/,/',$parameters[2])){
        $parameters[2]=explode(',',$parameters[2]);
    }
    $country=$parameters[0];
    $year=$parameters[1];
    $types=$parameters[2];
    $input = api_call(NULL, $parameters[0], $parameters[1], $parameters[2]);
} elseif (count($parameters) == 2) {
    $country=$parameters[0];
    $year=$parameters[1];
    $types=NULL;
    $input = api_call(NULL, $parameters[0], $parameters[1], NULL);
} elseif (count($parameters) == 1) {
    $country=$parameters[0];
    $year=NULL;
    $types=NULL;
    $input = api_call(NULL, $parameters[0]);
} else {
    echo ('NULL');
}

    $all_spaces = api_call('countries');
    $all_countries_names=array();

    for($i=0;$i<count($all_spaces);$i++){
        $country_shortName = $all_spaces[$i]['shortName'];
        $country_code = $all_spaces[$i]['countryCode'];
        if(substr($country_shortName,0,2)!='·' && substr($country_shortName,0,2) != 'º'){
            $all_countries_names[$country_code]=$country_shortName;
        }
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Vie Sociale des Données: Recherche</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <!--This CSS stylesheet and html is a free of use template provided by W3School and adapted to fit our needs.-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins">
    <style>
        body,
        h1,
        h2,
        h3,
        h4,
        h5 {
            font-family: "Poppins", sans-serif
        }

        body {
            font-size: 16px;
        }

        .w3-half img {
            margin-bottom: -6px;
            margin-top: 16px;
            opacity: 0.8;
            cursor: pointer
        }

        .w3-half img:hover {
            opacity: 1
        }

        .ul-double {
            columns: 2;
            -webkit-columns: 2;
            -moz-columns: 2;
        }
    </style>
</head>

<body>
    <nav class="w3-sidebar w3-red w3-collapse w3-top w3-large w3-padding" style="z-index:3;width:300px;font-weight:bold;" id="mySidebar"><br>
        <a href="javascript:void(0)" onclick="w3_close()" class="w3-button w3-hide-large w3-display-topleft" style="width:100%;font-size:22px">Close Menu</a>
        <div class="w3-container">
            <h3 class="w3-padding-64"><b>Vie Sociale<br>des données<br>2022</b></h3>
        </div>
        <div class="w3-bar-block">
            <a href="index.php#search_data" onclick="w3_close()" class="w3-bar-item w3-button w3-hover-white">Accueil</a>
            <?php
                if(!empty($input) && !$year){
                    $liste_years=array();
                    foreach($input as $element_year){
                        if(!in_array($element_year['year'],$liste_years)){
                            $liste_years[]=$element_year['year'];
                        }
                    }
                    sort($liste_years);
                    foreach($liste_years as $active_year){
                        echo '<a href="#'.$active_year.'" onclick="w3_close()" class="w3-bar-item w3-button w3-hover-white">> '.$active_year.'</a>';
                    }
                    $perm_liste_years=$liste_years;
                }
            ?>
        </div>
    </nav>

    <!-- Top menu on small screens -->
    <header class="w3-container w3-top w3-hide-large w3-red w3-xlarge w3-padding">
        <a href="javascript:void(0)" class="w3-button w3-red w3-margin-right" onclick="w3_open()">☰</a>
        <span>Exploration des données :</span>
    </header>

    <!-- Overlay effect when opening sidebar on small screens -->
    <div class="w3-overlay w3-hide-large" onclick="w3_close()" style="cursor:pointer" title="close side menu" id="myOverlay"></div>

    <div class="w3-main" style="margin-left:340px;margin-right:40px">
        <br>
        <h1 class="w3-jumbo"><b>Exploration des données :</b></h1>
        <?php
        echo '<h1 class="w3-xxxlarge w3-text-red"><b><u>'.$all_countries_names[$country].'</u></b></h1>';
        ?>
        <h1 class="w3-xxxlarge w3-text-red"><b><u></u></b></h1>
        <hr style="width:50px;border:5px solid red" class="w3-round">
        <br>
        <?php
            if(empty($input)){
                echo '<h3 align=central>Aucune donnée disponible pour cette recherche.</h3>';
                exit();
            }else{
                if(!$year){
                    $array_years=array();
                    $liste_years=array();
                    foreach($input as $element){
                        $year_of_element = $element['year'];
                        if(!in_array($year_of_element,$liste_years)){
                            $liste_years[]=$year_of_element;
                            $array_years[$year_of_element]=array($element);
                        }else{
                            $array_years[$year_of_element][]=$element;
                            
                        }
                    }
                    ksort($array_years);
                    #print_r($array_years);
                    foreach($array_years as $array_active_year){
                        array_to_html($array_active_year);
                    }
                }else{
                    array_to_html($input);
                }
            }
        ?>
    </div>
</body>

</html>