<?php
  session_start();
  function api_all_types($key='code'){
    $all_types=api_call();
    $all_types_key=array();
    for($i=0;$i<count($all_types);$i++){
      $all_types_key[]=$all_types[$i][$key];
    }
    return($all_types_key);
  }
  function api_call($url=NULL,$country=NULL,$year=NULL,$types=array()){
    $login = 'any-login';
    $password = 'HK2EOquTh6BUiN817Gb80R8ui9TDpJgM3F26i8sfSp2un496d8o';
    if($url){
      if (substr($url,0,4)!='http'){
        $request='https://api.footprintnetwork.org/v1/'.$url;
      }else{
        $request=$url;
      }
      
    }
    elseif(!$country){
      if(!$year && (!$types || (is_array($types) && count($types)==0))){
        $request='https://api.footprintnetwork.org/v1/types';
      }else{
        return null;
      }
    }else{
      $request = 'https://api.footprintnetwork.org/v1/data/'.(string)$country;
      if($year){
        $request.= '/'.(string)$year.'/';
        $all_types_code = api_all_types();
        if(is_string($types) && in_array($types,$all_types_code)){
          $request.='/'.$types;
        }elseif(is_array($types) && count($types)>=1){
          for($i=0;$i<count($types);$i++){
            if(in_array($types[$i],$all_types_code)){
              if(substr($request,-1)=='/'){
                $request.=$types[$i];
              }else{
                $request.=','.$types[$i];
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
    curl_setopt($session, CURLOPT_HTTPHEADER,array('Accept: application/json'));
    curl_setopt($session, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);

    $response = json_decode(curl_exec($session),true);

    curl_close($session);

    return($response);
  }

  function deroulant(string $name,$options,$default=NULL,$sort=true,$codes=NULL){
    if(!$codes){
      $codes = array_flip($options);
    }
    if($sort){
      sort($options);
    }
    if(!$default){
      $default=$options[0];
      $i_start=1;
    }else{
      $i_start=0;
    }
    echo '<select name="'.$name.'">';
    if(in_array($default,$options)){
      echo '<option value="'.$codes[$default].'">'.$default.'</option>';
    }else{
      echo '<option value="0">'.$default.'</option>';
    }
    for($i=$i_start ; $i < count($options) ; $i++){
      $element=$options[$i];
      echo '<option value="'.$codes[$element].'">'.$element.'</option>';
    }
    echo '</select>';
  }

  function search($country,$year="",$types=array()){
    $url = 'search.php/?c='.urlencode($country);
    if($year!=""){
      $url.='&y='.urlencode($year);
      if(count($types)>0){
        $url.='&d=';
        foreach($types as $type_of_data){
          $url.=urlencode($type_of_data).',';
        }
      }
    }
    if(substr($url,-1,1)==','){
      $url=substr($url,0,strlen($url)-1);
    }
    echo '<script>window.location.replace("'.$url.'");</script>';
  }

  $all_types_code=api_all_types();
  $all_types_names=api_all_types('name');
  $all_types_code_and_names_associations = array();
  for($i=0;$i<count($all_types_code);$i++){
    $all_types_code_and_names_associations[$all_types_names[$i]]=$all_types_code[$i];
  }

  $all_spaces = api_call('countries');
  $all_countries_name=array();
  $all_countries_code=array();

  for($i=0;$i<count($all_spaces);$i++){
    $country_shortName = $all_spaces[$i]['shortName'];
    $country_code = $all_spaces[$i]['countryCode'];
    if(substr($country_shortName,0,2)!='·' && substr($country_shortName,0,2) != 'º'){
      $all_countries_name[]=$all_spaces[$i]['shortName'];
      $all_countries_code[$country_shortName]=$country_code;
    }
  }

  $all_years_raw = api_call('years');
  $all_years=array();
  for($i=0;$i<count($all_years_raw);$i++){
    $all_years[]=$all_years_raw[$i]['year'];
  }
  $a='stop';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Vie Sociale des Données</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<!--This CSS stylesheet and html is a free of use template provided by W3School and adapted to fit our needs.-->
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins">
<style>
body,h1,h2,h3,h4,h5 {font-family: "Poppins", sans-serif}
body {font-size:16px;}
.w3-half img{margin-bottom:-6px;margin-top:16px;opacity:0.8;cursor:pointer}
.w3-half img:hover{opacity:1}
.ul-double {
            columns: 2;
            -webkit-columns: 2;
            -moz-columns: 2;
        }
table, th, td {
    border: 1px solid;
    margin-left: auto;
    margin-right: auto;
    text-align:center;
    padding:0.1cm;
}
blockquote{
    margin-left: auto;
    margin-right: auto;
}
a{
    text-shadow: 0.5px 0.5px #f44336;
    -webkit-text-shadow: 0.5px 0.5px #ab545c;
}
a:hover{
    color:#f44336;
    text-shadow: 0.5px 0.5px #ab545c;
    -webkit-text-shadow: 0.5px 0.5px black;
}
</style>
</head>
<body>

<!-- Sidebar/menu -->
<nav class="w3-sidebar w3-red w3-collapse w3-top w3-large w3-padding" style="z-index:3;width:300px;font-weight:bold;" id="mySidebar"><br>
  <a href="javascript:void(0)" onclick="w3_close()" class="w3-button w3-hide-large w3-display-topleft" style="width:100%;font-size:22px">Close Menu</a>
  <div class="w3-container">
    <h3 class="w3-padding-64"><b>Vie Sociale<br>des données<br>2022</b></h3>
  </div>
  <div class="w3-bar-block">
    <a href="#" onclick="w3_close()" class="w3-bar-item w3-button w3-hover-white">Accueil</a> 
    <a href="#presentation" onclick="w3_close()" class="w3-bar-item w3-button w3-hover-white">> Le Global Footprint Network : Présentation</a> 
    <a href="#background" onclick="w3_close()" class="w3-bar-item w3-button w3-hover-white">> Historique</a> 
    <a href="#administrators" onclick="w3_close()" class="w3-bar-item w3-button w3-hover-white">> Gestionnaires actuels</a> 
    <a href="#finance" onclick="w3_close()" class="w3-bar-item w3-button w3-hover-white">> Financement</a>
    <a href="#vocabulary" onclick="w3_close()" class="w3-bar-item w3-button w3-hover-white">> Glossaire</a>
    <a href='#sources' onclick="w3_close()" class="w3-bar-item w3-button w3-hover-white">> Sources</a>
    <a href='#pertinence' onclick="w3_close()" class="w3-bar-item w3-button w3-hover-white">> Implications conceptuelles</a>
    <a href='#critics' onclick="w3_close()" class="w3-bar-item w3-button w3-hover-white">> Critiques</a>
    <a href="#coda" onclick="w3_close()" class="w3-bar-item w3-button w3-hover-white">> Conclusions</a>
    <a href="#search_data" onclick="w3_close()" class="w3-bar-item w3-button w3-hover-white">> Exploiter les données</a>
    <a href="#bio" onclick="w3_close()" class="w3-bar-item w3-button w3-hover-white">> Bibliographie</a>
  </div>
</nav>

<!-- Top menu on small screens -->
<header class="w3-container w3-top w3-hide-large w3-red w3-xlarge w3-padding">
  <a href="javascript:void(0)" class="w3-button w3-red w3-margin-right" onclick="w3_open()">☰</a>
  <span>Vie Sociale des données 2022</span>
</header>

<!-- Overlay effect when opening sidebar on small screens -->
<div class="w3-overlay w3-hide-large" onclick="w3_close()" style="cursor:pointer" title="close side menu" id="myOverlay"></div>

<!-- !PAGE CONTENT! -->
<div class="w3-main" style="margin-left:340px;margin-right:40px">

  <!-- Header -->
  <div class="w3-container" style="margin-top:80px" id="presentation">
    <h1 class="w3-jumbo"><b>Vie Sociale des données 2022</b></h1>
    <h1 class="w3-xxxlarge w3-text-red"><b><u>Le Global Footprint Network</u> : <u>Présentation</u></b></h1>
    <hr style="width:50px;border:5px solid red" class="w3-round">
    <p style="text-align:justify">
      Le Global Footprint Network est un organisme de bienfaisance non-lucratif dont l'objectif principal est de proposer des 
      outils de mesure et des données servant à encourager et faciliter les mesures liées au développement durable. 
      C'est à ces fins qu'ils rendent donc leurs outils et conclusions disponibles en open-source ; notamment, 
      <a href='https://data.footprintnetwork.org/#/'>leur interface en ligne de visualisation de données statistiques</a> a 
      pour vocation de permettre aux particuliers comme aux organisations de réutiliser les données représentées et de mieux 
      les comprendre.
    </p>
    <br>
    <p style="text-align:justify">
      Le présent site se veut un exemple basique de la façon dont lesdites données peuvent être mobilisées à l'extérieur des 
      plateformes du Global Footprint Network, après avoir contextualisé et présenté l'initiative. Pour accéder directement à la recherche, cliquez <a href='#search_data'>ici</a> ou sur "Exploiter les données" dans la barre de navigation.
    </p>
  </div>
  
  <!-- Background -->
  <div class="w3-container" id="background" style="margin-top:75px">
    <h1 class="w3-xxxlarge w3-text-red"><b><u>Historique du GPN</u></b></h1>
    <hr style="width:50px;border:5px solid red" class="w3-round">
    <p style="text-align:justify">
      L'empreinte écologique ("<i>ecological footprint</i>") est un index créé par <a href='https://en.wikipedia.org/wiki/Mathis_Wackernagel' title='https://en.wikipedia.org/wiki/Mathis_Wackernagel'>Mathis Wackernagel</a> et <a href='https://fr.wikipedia.org/wiki/William_E._Rees' title='https://fr.wikipedia.org/wiki/William_E._Rees'>William Rees</a> 
      <a href='https://www.footprintnetwork.org/about-us/our-history/' title='https://www.footprintnetwork.org/about-us/our-history/'>
      dans les années 90</a>. Wackernagel faisait sa thèse de PhD en “community and regional planning” à l’University of British Columbia et a développé le concept avec W. Rees, son superviseur, qui en était à l’origine. Ce concept est présenté dans l’ouvrage 
      <i><span title='Wackernagel, M. and W. Rees. 1996. Our Ecological Footprint: Reducing Human Impact on the Earth. New Society Publishers.'>
        Our Ecological Footprint: Reducing Human Impact on the Earth</span>
      </i>. Mathis Wackernagel est désormais le Président du Global Footprint Network, ainsi que lauréat du <a href='https://wsforum.org/instructions#awardees' title='https://wsforum.org/instructions#awardees'>World Sustainability Award</a> 
      en 2018. William Rees était son professeur à l’Université de la Colombie-Britannique et est spécalisé dans les recherches
      sur les politiques publiques et l’environnement. Il est à l’origine du concept d’empreinte écologique et co-devéloppeur 
      de la méthode de calcul.
    </p>
  </div>
  
  <!-- Administrators -->
  <div class="w3-container" id="administrators" style="margin-top:75px">
    <h1 class="w3-xxxlarge w3-text-red"><b><u>Gestionnaires actuels</u> :</b></h1>
    <hr style="width:50px;border:5px solid red" class="w3-round">
    <p style="text-align:justify"><b>Les données utilisées pour calculer l’empreinte écologique sont administrées par un consortium composé <a href='https://www.footprintnetwork.org/about-us/people/' title='https://www.footprintnetwork.org/about-us/people/'>du Global 
      Footprint Network</a>, de <a href='https://footprint.info.yorku.ca/people/' title='https://footprint.info.yorku.ca/people/'>l’Université de York</a> et de <a href='https://www.fodafo.org/board.html' title='https://www.fodafo.org/board.html'>la FODAFO (Footprint Data Foundation)</a>, organismes détaillés ci-dessous</b> :</p>
  </div>

  <!-- Administrators list -->
  <div class="w3-row-padding">
    <div class="w3-col m4 w3-margin-bottom">
      <div class="w3-light-grey">
        <p align='center'>
          <img src="/images/GFNlogo.png" alt="Logo of the Global Footing Network" style="width:90%">
        </p>
        <div class="w3-container">
          <h3>Global Footprint Network</h3>
          <span style="white-space: pre-line"><p class="w3-opacity">"International think tank working to drive informed, sustainable policy decisions in a world of limited resources. [...] Coordinates research, develops methodological standards, and provides decision-makers with a menu of tools to help the human economy operate within Earth’s ecological limits."
          – <a href='https://www.footprintnetwork.org/2015/09/23/eight-countries-meet-two-key-conditions-sustainable-development-united-nations-adopts-sustainable-development-goals/' title='https://www.footprintnetwork.org/2015/09/23/eight-countries-meet-two-key-conditions-sustainable-development-united-nations-adopts-sustainable-development-goals/'><i>Only eight countries meet two key conditions for sustainable development as United Nations adopts Sustainable Development Goals</i></a></p></span>
          <span style="white-space: pre-line"><p style="text-align:left">
              <u>Président</u> : Mathis Wackernagel, Ph.D
              <u>Direction scientifique</u> : David Lin, Ph.D
              <u>Co-fondatrice</u> : Susan Burns
              <u>Ancienne directrice générale</u> : Julia Marton-Lefèvre
            </p>
          </span>
        </div>
      </div>
    </div>
    <div class="w3-col m4 w3-margin-bottom">
      <div class="w3-light-grey">
        <p align='center'>
          <img src="/images/york_logo.png" alt="Logo of the York University" style="width:90%">
        </p>
        <div class="w3-container">
          <h3>York University Ecological Footprint Initiative</h3>
          <span style="white-space: pre-line"><p class="w3-opacity">"[...] scholars, students, researchers, and collaborating organizations working together to advance the measurement of Ecological Footprint and Biocapacity and the application of these measures around the world."
          – <a href='https://www.fodafo.org/why-fodafo.html' title='https://www.fodafo.org/why-fodafo.html'><i>Ecological Footprint Initiative</i></a></p></span>
          <span style="white-space: pre-line"><p style="text-align:left">
              <u>Doyenne de la Faculté des Changements Environnementaux et Urbains</u> : Alice Hovorka
              <u>Directeur de l'Initiative Empreinte Écologique</u> : Eric Miller
              <u>Chercheure adjointe</u> : Susan Burns
            </p>
          </span>
        </div>
      </div>
    </div>
    <div class="w3-col m4 w3-margin-bottom">
      <div class="w3-light-grey">
        <p align='center'>
          <img src="/images/fodafo_logo.png" alt="Logo of the Footprint Data Foundation" style="width:90%">
      </p>
        <div class="w3-container">
          <h3>Footprint Data Foundation</h3>
          <span style="white-space: pre-line"><p class="w3-opacity">"[...] a not-for-profit called the Footprint Data Foundation (FoDaFO) to be the stewards of these National Footprint & Biocapacity Accounts (NFAs), and to reproduce them with the support of York University and a broader academic network."
          – <a href='https://footprint.info.yorku.ca/' title='https://footprint.info.yorku.ca/'><i>Ecological Footprint Initiative</i></a></p></span>
          <span style="white-space: pre-line"><p style="text-align:left">
              <u>Doyenne de la Faculté des Changements Environnementaux et Urbains</u> : Alice Hovorka
              <u>Directeur de l'Initiative Empreinte Écologique</u> : Eric Miller
              <u>Chercheure adjointe</u> : Susan Burns
            </p>
          </span>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Finance-->
  <div class="w3-container" style="margin-top:80px" id="finance">
    <h1 class="w3-xxxlarge w3-text-red"><b><u>Financement en 2021</u> :</b></h1>
    <hr style="width:50px;border:5px solid red" class="w3-round">
    <p style="text-align:justify">
      Les finances engendrées par l'organisme Global Footprint Network sont publiées sur <a href='https://www.overshootday.org/annual-report-2021/' title='https://www.overshootday.org/annual-report-2021/'>leur site officiel</a> qui nous sert de sources. D'autres frais que ceux engendrés par la base de donnée et son maintien sont comprises dans les visualisations suivantes :
    </p>
    <p align='center'>
          <img src="/images/income-2021.jpg" alt="Income of the organism in 2021" style="width:80%;border-style:inset;border-color:#f44336;">
    </p>
    <p align='center'>
          <img src="/images/expenses-2021.jpg" alt="Expense of the organism in 2021" style="width:80%;border-style:inset;border-color:#f44336;">
    </p>
    <br>
    <p style="text-align:justify">
        L'organisme reçoit entre autres <a href='https://www.overshootday.org/content/uploads/2022/08/GFN-AR-2021-Support-FINAL.pdf' tile='https://www.overshootday.org/content/uploads/2022/08/GFN-AR-2021-Support-FINAL.pdf'>des dons d'entreprises privées</a> aux profils variés, dont voici la liste :
    </p>
    
    <ul class='ul-double'>
        <li><a href='https://www.joinatmos.com/impact' title='https://www.joinatmos.com/impact'>Atmos Financial</a> [Banque verte]</li>
        <li><a href='https://eberhard-ag.com/en/' title='https://eberhard-ag.com/en/'>Eberhard AG</a> [Industrie de robotique]</li>
        <li><a href='https://about.google/?utm_source=google-FR&utm_medium=referral&utm_campaign=hp-footer&fg=1' title='https://about.google/?utm_source=google-FR&utm_medium=referral&utm_campaign=hp-footer&fg=1'>Google</a>[Services technologiques]</li>
        <li>Heart Craft</li>
        <li><a href='https://www.hessnatur.com/en-DE/' title='https://www.hessnatur.com/en-DE/'>hessnatur</a> [Prêt-à-porter écologique]</li>
        <li><a href='https://populationcrisis.org/' title='https://populationcrisis.org/'>Population Crisis</a> [Documentaires sur la population mondiale]</li>
        <li><a href='https://www.se.com/fr/fr/' title='https://www.se.com/fr/fr/'>Schneider Electric</a> [Solutions énergétiques]</li>
        <li><a href='https://www.shirazcreative.com/' title='https://www.shirazcreative.com/'>Shiraz Creative of California, LLC</a> [Marketing]</li>
        <li>Winkler Benoit Consultancy [Cabinet de conseil en environnement]</li>
        <li><a href='https://zirkulit.ch/' title='https://zirkulit.ch/'>zirkulit AG</a> [Béton]</li>
    </ul>
  </div>
  
  <div class="w3-container" style="margin-top:80px" id="vocabulary">
    <h1 class="w3-xxxlarge w3-text-red"><b><u>Glossaire</u> :</b></h1>
    <hr style="width:50px;border:5px solid red" class="w3-round">
    <p style="text-align:justify">Toutes les définitions et formules appliquées par le Global Footprint Network et ses partenaires sont compilées dans l'ouvrage <i><a href='https://www.footprintnetwork.org/content/images/uploads/Ecological_Footprint_Standards_2009.pdf' title='https://www.footprintnetwork.org/content/images/uploads/Ecological_Footprint_Standards_2009.pdf'>Ecological Footprint Standards 2009</a></i>.
    </p>
    <br>
    <div class='w3-light-grey' style='border-style:solid;border-color:#f44336;padding:0.3cm;box-shadow: 4px 3px 8px 1px #969696;-webkit-box-shadow: 4px 3px 8px 1px #ab545c;'>
        <p align='center'>
            <span style='border-bottom:1px solid red;'><b>Empreinte écologique</b></span> : Superficie biologiquement productible nécessaire pour maintenir le rythme de fonctionnement actuel d'un secteur d'activité donné.
        </p>
        <p align='center' class="w3-opacity">
            L'empreinte écologique est calculée en "hectares globaux" à partir de la surface des terres cultivées, des pâturages, des terrains bâtis, des zônes de pêche, des produits forestiers et des terres exploitées qui absorbent le carbone terrestre.
        </p>
                <p align='center'>
            <img src="/images/EFprod.jpeg" alt="EF production equation" style="width:80%;border-style:inset;border-color:#f44336;">
            <br>
            <img src="/images/EFconsum.jpeg" alt="EF consommation equation" style="width:80%;border-style:inset;border-color:#f44336;">
        </p>
    </div>
    <br>
    <p align='center'>
          <img src="/images/data_EFformula.jpg " alt="Calculation of EF formula" style="width:80%;border-style:inset;border-color:#f44336;">
    </p>
    <br>
    <div class='w3-light-grey' style='border-style:solid;padding:0.3cm;box-shadow: 4px 3px 8px 1px #969696;-webkit-box-shadow: 4px 3px 8px 1px #ab545c;'>
        <p align='center'>
            <span style='border-bottom:1px solid black;'><b>Biocapacité</b></span> : Surfaces terrestres et maritimes biologiquement productives disponibles pour fournir les ressources qu'une population consomme et pour absorber ses déchets, compte tenu des technologies et des pratiques de gestion actuelles.
        </p>
        <p align='center' class="w3-opacity">
             Pour que les mesures de la biocapacité soient comparables entre deux époques ou deux espaces géographiques différents, les superficies sont ajustées proportionnellement selon leur productivité biologique.
        </p>
        <p align='center' class="w3-opacity">
            La biocapacité est calculée en "hectares globaux" à partir de la surface des terres cultivées, des pâturages, des terrains bâtis, des zônes de pêche et de la biocapacité forestière.
        </p>
        <p align='center'>
            <img src="/images/biocapacity.jpeg" alt="biocapacity equation" style="width:80%;border-style:inset;border-color:#f44336;">
        </p>
    </div>
    <br>
    <div class='w3-light-grey' style='border-style:solid;padding:0.3cm;box-shadow: 4px 3px 8px 1px #969696;-webkit-box-shadow: 4px 3px 8px 1px #ab545c;'>
        <p align='center'>
            <span style='border-bottom:1px solid;'><b>Réserve écologique</b></span> : désigne une empreinte écologique inférieure à la biocapacité.
        </p>
        <p align='center' class="w3-opacity">
            Les acteurs en situation de réserve écologique sont appelés des créditeurs écologiques.
        </p>
    </div>
    <br>
    <div class='w3-light-grey' style='border-style:solid;padding:0.3cm;box-shadow: 4px 3px 8px 1px #969696;-webkit-box-shadow: 4px 3px 8px 1px #ab545c;'>
        <p align='center'>
            <span style='border-bottom:1px solid;'><b>Déficit écologique</b></span> : désigne une empreinte écologique supérieure à la biocapacité.
        <p align='center' class="w3-opacity">
            Les acteurs en situation de déficit écologique sont appelés des débiteurs écologiques.
        </p>
        <p align='center' class="w3-opacity">
            Le jour du dépassement écologique, durant lequel les ressources de la planète pour l'année sont épuisées, est une situation de <b>déficit écologique international</b>.
        </p>
    </div>
    <br>
    <div class='w3-light-grey' style='border-style:solid;padding:0.3cm;box-shadow: 4px 3px 8px 1px #969696;-webkit-box-shadow: 4px 3px 8px 1px #ab545c;'>
        <p align='center'>
            <span style='border-bottom:1px solid;'><b>Hectare global</b></span> : hectare de terre qui fournit une quantité moyenne mondiale de régénération biologique chaque année.
        </p>
        <p align='center' class="w3-opacity">
            Les trois facteurs de conversion permettant d'obtenir l'hectare global sont ceux :
            <ul class="w3-opacity">
                <li>de rendement, qui relie le rendement national d'un type de terre spécifique par rapport au rendement moyen mondial ;</li>
                <li>d'équivalence, qui relie les éléments les uns aux autres en fonction de leur niveau de productivité biologique ;</li>
                <li>de rendement intertemporel, qui relie les changements de productivité biologique dans le temps.</li>
            </ul>
        </p>
    </div>
    <br>
    <div class='w3-light-grey' style='border-style:solid;padding:0.3cm;box-shadow: 4px 3px 8px 1px #969696;-webkit-box-shadow: 4px 3px 8px 1px #ab545c;'>
        <p align='center'>
            <span style='border-bottom:1px solid;'><b>Score de qualité des données</b></span> : indique si toutes, certaines ou aucune des données portant sur une nation précise sont incluses dans la plateforme de données ouverte. Le manque de données ou de qualité de ces données mène à un score de qualité bas.
        </p>
    </div>
    <br>
    <br>
    <h2><u>Pour aller plus loin</u> :</h2> 
    <br>
        <h3><a href='https://youtu.be/BX0FebbGEWc'>Global Footprint Network Declares Earth Overshoot Day</a></h3>
        <p align='center'>
        <iframe width="640" height="360" src="https://www.youtube.com/embed/BX0FebbGEWc" title="Global Footprint Network Declares Earth Overshoot Day" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen>
        </iframe>
        </p>
        <br>
        <h3><a href='https://youtu.be/jnkMg8tkZ1w'>Savez-vous quelle est l'Empreinte Ecologique de votre pays?</a></h3>
        <p align='center'>
        <iframe width="640" height="360" src="https://www.youtube.com/embed/jnkMg8tkZ1w" title="Savez-vous quelle est l'Empreinte Ecologique de votre pays?" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen>
        </iframe> 
        </p>
        <br>
        <h3><a href='https://youtu.be/3M29BY86bP4'>How much Nature do we have? How much do we use? | Mathis Wackernagel | TEDxSanFrancisco</a></h3>
        <p align='center'>
            <iframe width="640" height="360" src="https://www.youtube.com/embed/3M29BY86bP4" title="How much Nature do we have? How much do we use? | Mathis Wackernagel | TEDxSanFrancisco" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen>
            </iframe>
        </p>

  </div>
  
  <!-- Sources of data -->
  <div class="w3-container" style="margin-top:80px" id="sources">
    <h1 class="w3-xxxlarge w3-text-red"><b><u>Sources</u> :</b></h1>
    <hr style="width:50px;border:5px solid red" class="w3-round">
    <p style="text-align:justify">
        Les données sont gérées par la <i>York University’s Ecological Footprint Initiative</i> à travers la FODAFO. Le rapport mesure l’utilisation de resources écologiques et les capacités de resources des pays étudiés dans le temps.
    </p>
    <p style="text-align:justify">Les sources de ces données sont : </p>
    <ul>
        <li>l'Agence internationale de l’énergie ;</li>
        <li>l’Organisation des Nations unies pour l’alimentation et l’agriculture (plus spécifiquement ses bases de données : ProdStat, TradeStat, ResourceStat, FishStat) ;</li>
        <li>la Base de données UN - Comtrade ;</li>
        <li>la Base de données CORINE Land Cover ;</li>
        <li>la Base de données Global Agro-Ecological Zones ;</li>
        <li>le Global Land Cover ;</li>
        <li>le Global Carbon Budget ;</li>
        <li>la Banque Mondiale ;</li>
        <li>le Fond Monétaire International ;</li>
        <li>la Penn World Table.</li>
    </ul>
    <br>
    <p style="text-align:justify">L’édition 2022 de ce rapport présente les résultats des calculs pour des données allant de 1961 à 2018 et concernant 238 "entités internationales" dont 190 pays. Ce sont ces 190 pays qui composent le panel mondial de la base de données que nous étudions.
    </p>
    <p style="text-align:justify">Comparativement aux éditions précédentes, celle de 2022 a pour différences notables :</p>
    <ul>
        <li>de ne pas rendre compte des terres cultivées et du bétail pour plusieurs entités qui ne font plus l'objet de rapports de la FAO des Nations unies : Antilles néerlandaises (anciennes), Aruba, Bermudes, îles Vierges américaines, îles Caïmanes, îles Falkland (Malvinas), Groenland, Guam, Liechtenstein, Montserrat, île Norfolk, îles Wallis-et-Futuna, Sainte-Hélène, Ascension et Tristan da Cunha, Saint-Pierre-et-Miquelon, Sahara occidental et Samoa américaines ;</li>
        <br>
        <li>que les données pour le Soudan, le Sud-Soudan et le Soudan (ancien) en 2011 et 2012 reflètent les données déclarées alors que les éditions précédentes avaient estimé la plupart des données de ces années ;</li>
        <br>
        <li>que plusieurs pays ont des scores de qualité de données différents pour l'édition 2022, ce qui affecte la quantité de données nationales publiées sur la plateforme. Notamment :
        <ul>
            <li>
                L'Algérie, la Finlande, le Guatemala, le Sénégal, l'Eswatini, l'Uruguay et le Sud-Soudan ont désormais un score 3A, ce qui signifie que toutes les composantes du calcul de ce score sont présentées tout au long de la chronologie.
            </li>
            <li>Le Belize, les Îles Salomon, les Îles Féroé et le Vanuatu ont des scores de qualité des données plus élevés que dans la dernière édition, mais il y a encore beaucoup de données manquantes qui empêchent ces nations d'obtenir le score de 3A.</li>
            <li>Tous les pays n'ayant pas fait l'objet d'un rapport de la FAO dans cette édition se sont vus attribuer un score de 1D en raison de cette lacune.</li>
        </ul>
        </li>
    </ul>

    </div>
    
    <!-- Reusing data -->
  <div class="w3-container" style="margin-top:80px" id="pertinence">
    <h1 class="w3-xxxlarge w3-text-red"><b><u>Implications conceptuelles</u> :</b></h1>
    <hr style="width:50px;border:5px solid red" class="w3-round">
    <h3>La réutilisation des données : analyse géographique</h3>
    <br>
    <p align='center'>
          <img src="/images/origine_des_rapports.jpeg" alt="Origine géographique des rapports" style="width:80%;border-style:inset;border-color:#f44336;">
          <table>
              <tr>
                <th>Origine</th>
                <th>Pourcentage de rapport</th>
              </tr>
              <tr>
                <td>Europe</td>
                <td>45,8%</td>
              </tr>
              <tr>
                <td>Asie</td>
                <td>25,0%</td>
              </tr>
              <tr>
                <td>Amérique du Nord</td>
                <td>16,7%</td>
              </tr>
              <tr>
                <td>Afrique</td>
                <td>6,3%</td>
              </tr>
              <tr>
                <td>Amérique centrale</td>
                <td>2,1%</td>
              </tr>
              <tr>
                <td>Moyen-Orient</td>
                <td>2,1%</td>
              </tr>
              <tr>
                <td>Amérique latine</td>
                <td>2,1%</td>
              </tr>
            </table>
    </p>
    <br>
    <p style="text-align:justify">La majorité des rapports ayant été présentés par le Global Footprint Network et fondés sur l’Ecological Footprint ont été publiés en Europe, suivi de l’Asie et de l’Amérique du nord. Ces résultats interpellent, puisque le Global Footprint Network étant un index issu de l’environnement académique états-unien, l’on pourrait s’attendre à ce que la région soit plus active dans la réinterprétation de ces données. De cette observation l’on peut déduire plusieurs conclusions :</p><br>
<p style="text-align:justify">Tout d’abord, on observe que la majorité des rapports utilisant ces données pour produire de la connaissance viennent principalement des régions les plus représentées et reconnues en termes d’institutions académiques (Europe, Asie, Amérique du Nord).</p>
<p style="text-align:justify">Ensuite, à partir de ces résultats, on peut faire l’observation de l’influence intellectuelle des organismes nord-américains sur le reste du monde. Les sources de financements proviennent en grande partie du privé, ce qui permet d’offrir à ces organismes des avantages concurrentiels considérables pour la construction de leurs bases, et se font en grande partie avec la coopération des universitaires, ce qui légitime plus encore leurs travaux. Les connaissances numériques provenant des États-Unis sont donc très majoritaires au sein de la production de connaissances dans le reste du monde.</p>
<p style="text-align:justify"></p>Dernière conclusion – celle-ci pouvant n'être qu’une supposition qui mériterait une plus grande analyse afin d’être démontrée par un processus scientifique –, l’on peut émettre l’hypothèse que ces données sont tout simplement plus utilisées en Europe et en Asie en raison de leur nature de données climatiques. En effet, ces dernières années ont vu l’Europe prendre un rôle très important au sein du discours d’action climatique, et ce depuis que la chaise de leadership environnemental international a été laissée vacante par la présidence de Trump et son retrait des accords de Paris. L’Europe a ainsi formulé un Green New Deal et a nourri des travaux, au niveau étatique comme communautaire, très riches en termes de recherche et de politique climatique. Il en va de même pour l’Asie dont les pays sont soit directement touchés par les effets du réchauffement climatique et ont donc tout intérêt à contribuer à la dissémination des connaissances issues de ces données, soit affichent une volonté politique comme la Chine incitative de transition écologique de son tissu économique avec des promesses telles que la neutralité carbone en 2050.</p>
<br>
    <p align='center'>
        <img src="/images/nb_rapports_pays.jpeg" alt="Origine géographique des rapports" style="width:80%;border-style:inset;border-color:#f44336;">
    </p>
    <br>
    <p style="text-align:justify">
        La majorité des rapports ont été publiés aux Etats-Unis et en Suisse. La France, la Chine et l’Allemagne sont d’autres pays publiant le plus de rapports en lien avec le Global Footprint Network. Cette analyse par pays plutôt que par zone géographique étendue replace en perspective le rôle dominant des Etats-Unis dans l’utilisation des données issus de son milieu universitaire.
    </p>
    <br>
    <h3>La production de connaissances : nuances d’utilisations</h3>
    <br>
    <p style="text-align:justify">
        Avant la production de données climatiques mettant en évidence les effets invisibles mais latents des activités humaines sur notre écosystème, les politiques environnementales se focalisaient tout autant sur la protection de la biodiversité que sur la lutte contre les pollutions et accidents industriels. Aujourd'hui c’est sur la question du climat et de son réchauffement dû aux gaz à effet de serre que se focalisent les concertations internationales.

        Ainsi, ces données permettent...
    </p>
    <h5><pre>   De mesurer l’impact réel des États sur le climat :</pre></h5>
    <p style="text-align:justify">
        Un exemple de connaissance concrète de ce cas et qui découle directement de ces données est celui du “jour du dépassement”, qui permet de calculer la date à laquelle les sociétés ont épuisé les réserves naturelles théoriques qu’ils devaient utiliser pour veiller au bon renouvellement de celles futures. En effet, un croisement des différents indices de cette base nous permet de rapporter le calcul des impacts individuels à l’échelle de la planète, c’est à dire multiplier cette surface moyenne utilisée par chaque être humain pour sa subsistance par la population mondiale et comparer cette surface virtuelle à la biocapacité réelle de la terre. Ce chiffre est sans surprise supérieur à ce que la biosphère est en capacité d’absorber, et nous permet par la suite de calculer le jour du dépassement, qui tombait cette année le 28 juillet.
    </p>
    <br>
    <p align='center'>
        <img src="/images/twitter_ministe_climat.jpeg " alt="Tweet du ministère de l'écologie sur le jour du dépassement" style="width:80%;border-style:inset;border-color:#f44336;">
    </p>
    <br>
    <h5><pre>   De calculer l’empreinte écologique moyenne individuelle par État :</pre> </h5>
    <p style="text-align:justify">
        Cette interprétation des données comme production de connaissances permet notamment d'identifier les plus gros émetteurs de dioxyde de carbone ; elle est ainsi source d’instrumentalisation, et ce afin de porter le débat sur la responsabilité réelle de certains États. Les graphiques produits par cette base de données ne donnant pas d’échelle historique mais seulement géographique des émissions. Cela permet aux États de placer le curseur de la responsabilité sur des États considérés comme pollueurs alors même que leurs émissions sont récentes dans leur schéma de développement. Une instrumentalisation concrète cette fois-ci basée sur une notion plus historique, étendant donc l’analyse principalement géographique qui privilégie les Etats ayant les moyens de baisser leurs émissions, a notamment été observée lors de la COP27. En effet, la notion de dette carbone y a été source de tension entre États, certains réclamant que les pays s’étant enrichis de ces émissions visibles dans la base de données étudiée soit remboursée aux États qui aujourd’hui en payent le prix.
    </p>
    <br>
    <h5><pre>   De produire des systèmes interopérables d’utilisation de méthodes de calcul environnementales :</pre> </h5>
    <p style="text-align:justify">
Cette normalisation de la production de données et des méthodes d’évaluation environnementale sont particulièrement utiles aux entités qui formulent des politiques climatiques mutualisées et coordonnées. Un exemple concret est cette recommandation de la Commission européenne qui cite directement dans son annexe 1 page 16 les Ecological Footprint Standards parmi <a href='https://environment.ec.europa.eu/system/files/2021-12/Commission%20Recommendation%20on%20the%20use%20of%20the%20Environmental%20Footprint%20methods_0.pdf'>leurs guides méthodologiques fondatrices et normes ISO</a>.
    </p>
    <br>
    <h3>L’interprétation des données : multiplicité des nuances</h3>
    <br>
    <p style='text-align:justify'>
        La multiplication des indices écologiques, qui est illustrée par cette base puisqu’elle-même produit 5 indices différents (Déficit écologique/Réserve écologique ; Empreinte écologique totale ; Empreinte écologique par personne ; Biocapacité totale ; Biocapacité par personne) facilite l’instrumentalisation politique de cette base de données, puisqu’il multiplie les connaissances pouvant en être le produit.</p>
    <p style='text-align:justify'>Ces données peuvent en effet être interprétées de différentes manières en fonction des autres classements avec lesquels elles sont recoupées :
    <br>
    <ul>
        <li>Par exemple, le recoupement de cette base de données avec le classement du PIB nous permet d’observer que plus les pays sont riches et plus ils émettent, tandis que les pays les plus pauvres n’ont qu’un impact très limité sur les gaz à effet de serre. Ce croisement du classement PIB et du bilan carbone ne donne cependant qu’un aperçu partiel de la responsabilité des pays dans les émissions de gaz à effet de serre, parce qu’il ne prend pas en compte la délocalisation de certaines productions industrielles. Ainsi si le bilan carbone a sensiblement diminué depuis une trentaine d’années, l’empreinte carbone réelle à elle augmentée d’environ 20% ce qui s’explique par la désindustrialisation des pays occidentaux et l’augmentation de l’importation de produits manufacturés. </li>
        <br>
        <li>Selon l’indice de performance climatique, un indice qui met en avant les efforts pour la transition énergétique, c’est la Suède qui est la mieux classée dans le monde. Mais ce classement est ensuite recoupé avec la performance environnementale, un autre indice écologique qui prend en compte la vitalité des écosystèmes d’un pays. Là encore il faut nuancer ce classement : le lithium des batteries des véhicules électriques, très bénéfique pour l’environnement des pays qui les utilisent, provient de mines très polluantes situées essentiellement en Australie, au Chili ou en Chine, et qui n’est donc compter que dans les émissions de ces pays, réduisant leur performance environnementale au profit des pays qui eux en bénéficient. </li>
        <br>
        <li>Enfin, il faut considérer le croisement entre les deux indices de cette base de données : l’empreinte écologique par habitant et l’empreinte écologique totale. Le premier indice se distingue en ce qu’il mesure la surface terrestre nécessaire à la subsistance d’un individu selon son mode de vie : les surfaces agricoles utilisées pour son alimentation, les surfaces aquatiques, mais aussi les surfaces nécessaires pour compenser ses rejets et émissions de CO2. Cette surface moyenne permet de comparer l’empreinte écologique d’un qatari ou luxembourgeois, dont les États ne figurent pourtant pas à la première place des émissions, avec celle des érythréen ou haitien, et de remettre en perspective la responsabilité individuelle des membres du tissu social des États.</li>
    </ul></p>
    <br>
    <h3>L’application politique des connaissances : un exemple dans le discours climatique</h3>
    
    <p>
        <blockquote class="instagram-media" data-instgrm-permalink="https://www.instagram.com/reel/Ck8KbsQjVrC/?utm_source=ig_embed&amp;utm_campaign=loading" data-instgrm-version="14" style=" background:#FFF; border:0; border-radius:3px; box-shadow:0 0 1px 0 rgba(0,0,0,0.5),0 1px 10px 0 rgba(0,0,0,0.15); margin: 1px; max-width:540px; min-width:326px; padding:0; width:99.375%; width:-webkit-calc(100% - 2px); width:calc(100% - 2px);"><div style="padding:16px;"> <a href="https://www.instagram.com/reel/Ck8KbsQjVrC/?utm_source=ig_embed&amp;utm_campaign=loading" style="background:#FFFFFF; line-height:0; padding:0 0; text-align:center; text-decoration:none; width:100%;" target="_blank"> <div style=" display: flex; flex-direction: row; align-items: center;"> <div style="background-color: #F4F4F4; border-radius: 50%; flex-grow: 0; height: 40px; margin-right: 14px; width: 40px;"></div> <div style="display: flex; flex-direction: column; flex-grow: 1; justify-content: center;"> <div style=" background-color: #F4F4F4; border-radius: 4px; flex-grow: 0; height: 14px; margin-bottom: 6px; width: 100px;"></div> <div style=" background-color: #F4F4F4; border-radius: 4px; flex-grow: 0; height: 14px; width: 60px;"></div></div></div><div style="padding: 19% 0;"></div> <div style="display:block; height:50px; margin:0 auto 12px; width:50px;"><svg width="50px" height="50px" viewBox="0 0 60 60" version="1.1" xmlns="https://www.w3.org/2000/svg" xmlns:xlink="https://www.w3.org/1999/xlink"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g transform="translate(-511.000000, -20.000000)" fill="#000000"><g><path d="M556.869,30.41 C554.814,30.41 553.148,32.076 553.148,34.131 C553.148,36.186 554.814,37.852 556.869,37.852 C558.924,37.852 560.59,36.186 560.59,34.131 C560.59,32.076 558.924,30.41 556.869,30.41 M541,60.657 C535.114,60.657 530.342,55.887 530.342,50 C530.342,44.114 535.114,39.342 541,39.342 C546.887,39.342 551.658,44.114 551.658,50 C551.658,55.887 546.887,60.657 541,60.657 M541,33.886 C532.1,33.886 524.886,41.1 524.886,50 C524.886,58.899 532.1,66.113 541,66.113 C549.9,66.113 557.115,58.899 557.115,50 C557.115,41.1 549.9,33.886 541,33.886 M565.378,62.101 C565.244,65.022 564.756,66.606 564.346,67.663 C563.803,69.06 563.154,70.057 562.106,71.106 C561.058,72.155 560.06,72.803 558.662,73.347 C557.607,73.757 556.021,74.244 553.102,74.378 C549.944,74.521 548.997,74.552 541,74.552 C533.003,74.552 532.056,74.521 528.898,74.378 C525.979,74.244 524.393,73.757 523.338,73.347 C521.94,72.803 520.942,72.155 519.894,71.106 C518.846,70.057 518.197,69.06 517.654,67.663 C517.244,66.606 516.755,65.022 516.623,62.101 C516.479,58.943 516.448,57.996 516.448,50 C516.448,42.003 516.479,41.056 516.623,37.899 C516.755,34.978 517.244,33.391 517.654,32.338 C518.197,30.938 518.846,29.942 519.894,28.894 C520.942,27.846 521.94,27.196 523.338,26.654 C524.393,26.244 525.979,25.756 528.898,25.623 C532.057,25.479 533.004,25.448 541,25.448 C548.997,25.448 549.943,25.479 553.102,25.623 C556.021,25.756 557.607,26.244 558.662,26.654 C560.06,27.196 561.058,27.846 562.106,28.894 C563.154,29.942 563.803,30.938 564.346,32.338 C564.756,33.391 565.244,34.978 565.378,37.899 C565.522,41.056 565.552,42.003 565.552,50 C565.552,57.996 565.522,58.943 565.378,62.101 M570.82,37.631 C570.674,34.438 570.167,32.258 569.425,30.349 C568.659,28.377 567.633,26.702 565.965,25.035 C564.297,23.368 562.623,22.342 560.652,21.575 C558.743,20.834 556.562,20.326 553.369,20.18 C550.169,20.033 549.148,20 541,20 C532.853,20 531.831,20.033 528.631,20.18 C525.438,20.326 523.257,20.834 521.349,21.575 C519.376,22.342 517.703,23.368 516.035,25.035 C514.368,26.702 513.342,28.377 512.574,30.349 C511.834,32.258 511.326,34.438 511.181,37.631 C511.035,40.831 511,41.851 511,50 C511,58.147 511.035,59.17 511.181,62.369 C511.326,65.562 511.834,67.743 512.574,69.651 C513.342,71.625 514.368,73.296 516.035,74.965 C517.703,76.634 519.376,77.658 521.349,78.425 C523.257,79.167 525.438,79.673 528.631,79.82 C531.831,79.965 532.853,80.001 541,80.001 C549.148,80.001 550.169,79.965 553.369,79.82 C556.562,79.673 558.743,79.167 560.652,78.425 C562.623,77.658 564.297,76.634 565.965,74.965 C567.633,73.296 568.659,71.625 569.425,69.651 C570.167,67.743 570.674,65.562 570.82,62.369 C570.966,59.17 571,58.147 571,50 C571,41.851 570.966,40.831 570.82,37.631"></path></g></g></g></svg></div><div style="padding-top: 8px;"> <div style=" color:#3897f0; font-family:Arial,sans-serif; font-size:14px; font-style:normal; font-weight:550; line-height:18px;">Voir cette publication sur Instagram</div></div><div style="padding: 12.5% 0;"></div> <div style="display: flex; flex-direction: row; margin-bottom: 14px; align-items: center;"><div> <div style="background-color: #F4F4F4; border-radius: 50%; height: 12.5px; width: 12.5px; transform: translateX(0px) translateY(7px);"></div> <div style="background-color: #F4F4F4; height: 12.5px; transform: rotate(-45deg) translateX(3px) translateY(1px); width: 12.5px; flex-grow: 0; margin-right: 14px; margin-left: 2px;"></div> <div style="background-color: #F4F4F4; border-radius: 50%; height: 12.5px; width: 12.5px; transform: translateX(9px) translateY(-18px);"></div></div><div style="margin-left: 8px;"> <div style=" background-color: #F4F4F4; border-radius: 50%; flex-grow: 0; height: 20px; width: 20px;"></div> <div style=" width: 0; height: 0; border-top: 2px solid transparent; border-left: 6px solid #f4f4f4; border-bottom: 2px solid transparent; transform: translateX(16px) translateY(-4px) rotate(30deg)"></div></div><div style="margin-left: auto;"> <div style=" width: 0px; border-top: 8px solid #F4F4F4; border-right: 8px solid transparent; transform: translateY(16px);"></div> <div style=" background-color: #F4F4F4; flex-grow: 0; height: 12px; width: 16px; transform: translateY(-4px);"></div> <div style=" width: 0; height: 0; border-top: 8px solid #F4F4F4; border-left: 8px solid transparent; transform: translateY(-4px) translateX(8px);"></div></div></div> <div style="display: flex; flex-direction: column; flex-grow: 1; justify-content: center; margin-bottom: 24px;"> <div style=" background-color: #F4F4F4; border-radius: 4px; flex-grow: 0; height: 14px; margin-bottom: 6px; width: 224px;"></div> <div style=" background-color: #F4F4F4; border-radius: 4px; flex-grow: 0; height: 14px; width: 144px;"></div></div></a><p style=" color:#c9c8cd; font-family:Arial,sans-serif; font-size:14px; line-height:17px; margin-bottom:0; margin-top:8px; overflow:hidden; padding:8px 0 7px; text-align:center; text-overflow:ellipsis; white-space:nowrap;"><a href="https://www.instagram.com/reel/Ck8KbsQjVrC/?utm_source=ig_embed&amp;utm_campaign=loading" style=" color:#c9c8cd; font-family:Arial,sans-serif; font-size:14px; font-style:normal; font-weight:normal; line-height:17px; text-decoration:none;" target="_blank">Une publication partagée par Emmanuel Macron (@emmanuelmacron)</a></p></div></blockquote> <script async src="//www.instagram.com/embed.js"></script>
    </p>
    
    </div>
    
    <!-- Critic -->
  <div class="w3-container" style="margin-top:80px" id="critics">
    <h1 class="w3-xxxlarge w3-text-red"><b><u>Critiques</u> :</b></h1>
    <hr style="width:50px;border:5px solid red" class="w3-round">
    <p style="text-align:justify">
        Il existe une véritable querelle académique autour de l’empreinte écologique, en particulier sa pertinence en tant qu'indicateur pouvant informer l’élaboration de politiques publiques.
    </p>
    <br>
    <h5><pre>Commentaires introductifs :</pre></h5>
    
    <p style="text-align:justify">
        <a href='https://www.sciencedirect.com/science/article/abs/pii/S1470160X14002726?via%3Dihub'>Giampietro et Saltelli</a> (<a href='http://www.andreasaltelli.eu/file/repository/Footprints_to_nowhere_Giampietro_Saltelli_Ecol_ind_2014_PagesNumbers.pdf'>2014</a>) affirment que l'évaluation de l'empreinte écologique - prétendument utile comme argument contre l'idée de croissance perpétuelle - est truffée de contradictions internes. Leur évaluation critique est basée sur le manque de correspondance entre la sémantique - l'affirmation de ce que fait la comptabilité de l'empeinte écologique - et la syntaxe - le protocole de comptabilité de l’empeinte écologique qui devrait fournir le prétendu résultat. Pour ces derniers, l’empreinte écologique ne sert pas une discussion significative sur la modélisation de la durabilité, et le même récit favorable aux médias sur le jour du dépassement de la Terre est finalement rassurant et complaisant lorsqu'on considère d'autres aspects sur la pression de l'homme sur la planète et ses écosystèmes.
    </p>
    <p style='text-align:justify'>
        Pour Van den Bergh et Verbruggen (2014), les politiques visant la durabilité doivent tenir compte des dimensions spatiales des problèmes environnementaux et de leurs solutions. En particulier, les configurations spatiales des activités économiques méritent l'attention, ce qui signifie qu'il faut s'intéresser à l'utilisation des sols, aux infrastructures, au commerce et aux transports.
    </p>
    <br>
    <br>
    <h5><pre>Limites conceptuelles :</pre></h5>
    <br>
    <p style='text-align:justify'>
    <ul style='text-align:justify'>
        <li><b>Ce n’est pas une mesure exhaustive de la soutenabilité.</b> L’empreinte écologique mesure seulement si la demande globale humaine entre dans les capacités de régénération de la planète. D'autres dimensions importantes de la soutenabilité (bien-être humain, qualité de l'environnement, etc) ne sont pas prises en compte.</li>
        <br>
        <li><b>Ce n’est pas une prédiction pour le futur.</b>
Les comptes d'empreinte écologique ne font que suivre les activités réelles, comme le fait toute comptabilité. Ils enregistrent simplement les intrants et les extrants tels quels et ne fournissent aucune extrapolation quant à la quantité de biocapacité qui pourrait être épuisée par les activités humaines à l'avenir.</li>
    <br>
    <li><b>Elle emploie un pragmatisme discutable.</b><ul>
        <li>L’empreinte écologique mesure quelque chose d'irréel : en effet, la superficie terrestre n'est pas littéralement utilisée pour de telles activités. L'empreinte écologique implique de convertir les flux d'énergie et de matière vers et depuis les activités économiques dans une superficie terrestre hypothétique qui serait nécessaire pour maintenir ces flux. La neutralisation des externalités environnementales négatives nécessite un ensemble de hypothèses, donc lorsque cette neutralisation est traduite en superficie de terres utilisées, le résultat n'est qu'hypothétique par nature.</li><br>
        <li>L'utilisation hypothétique des terres et la disponibilité réelle des terres sont comparées. Par exemple, sur la base d'une interprétation logique de la méthodologie de l’empeinte écologique, moins de la moitié de la superficie des États-Unis plantée d'eucalyptus pourrait essentiellement nous donner un EF égal à une Terre. Fiala (2008) ajoute que l'empreinte ne peut pas non plus prendre en compte la production intensive, et donc les comparaisons avec la biocapacité sont erronées.</li>
    </ul>
    </li>
    <br>
    <li><b>C’est une mesure agrégée de la régénération et de la demande.</b> Ce sont des mesures de résultats; en d'autres termes, la régénération d'un écosystème est le résultat de l'état actuel des sols, de la disponibilité de l'eau, de la biodiversité et de nombreux autres facteurs. La mesure ne fournit pas de mesures spécifiques pour ces facteurs de soutenabilité, qui sont pourtant des éléments clés pour résoudre des problèmes à multiples facettes. En d’autres termes, l'empreinte écologique regroupe des problèmes environnementaux distincts en utilisant des pondérations arbitraires et non fondées (Van den Bergh et Verbruggen 2014). Ces pondérations ne correspondent pas à une logique physique ou chimique, ni à des valeurs sociales ou économiques (utilité ou bien-être).</li>
    <br>
    <li><b>L'utilisation de la notion d’hectares globaux contribue au caractère hypothétique de l'empreinte écologique.</b> C’est une productivité moyenne à l'échelle mondiale, destinée à simplifier et refléter la productivité variable entre les endroits du monde. Elle varie donc à la fois au fil du temps et entre les endroits du monde. Bien que très simpliste, la clarté de l’approche est discutable.</li>
    <br>
    <li>
        <b>La comptabilité de l'empreinte implique que l'utilisation des terres est la variable de durabilité la plus importante.</b> Il s'agit de "théoriser" la valeur des terres. L'utilisation des terres est considérée comme proxy de la pression environnementale. Ainsi, la réduction des externalités négatives environnementales à la superficie des terres se résume à une théorie implicite de la valeur des terres, qui fait de la rareté des terres une préoccupation antérieure, et l'emporte sur tous les autres problèmes. Selon Van den Bergh et Verbruggen (2014), supposer que les modèles de production et de consommation sont limités uniquement par la disponibilité des terres revient à suggérer que la politique foncière est la principale réponse publique à la non-durabilité". Utiliser les terres comme proxy des pressions environnementales a du sens pour l'agriculture (à l'exception des pesticides et des engrais concentrés), mais pas pour d'autres secteurs, comme l'industrie ou les services.
    </li>
    <br>
    <li>
        <b>Le calcul de la composante de l'empreinte carbone est basé sur un "scénario énergétique durable" arbitraire.</b> Pour la plupart des pays développés, environ la moitié de la valeur de l'empreinte écologique est le résultat de la transformation de la question du réchauffement climatique d'origine humaine par les émissions de CO2 en surface terrestre - en réalité, des terres purement hypothétiques. Est ici émise l’hypothèse d'un scénario énergétique dit durable dans lequel le CO2 est capté par la plantation d'arbres ou le reboisement.
        <ul>
            <li>Prolongeant ces conclusions, Blomqvist et al. (<a href='https://www.google.com/url?q=https://www.ncbi.nlm.nih.gov/pmc/articles/PMC3818165/&sa=D&source=docs&ust=1669239179620093&usg=AOvVaw0ijf8MGFQxZpN0jWtm_T8x'>2013</a>) confirme que l'ensemble du dépassement écologique mondial (empreinte de la consommation dépassant la biocapacité) résulte des émissions de dioxyde de carbone, recadrées comme la surface forestière hypothétique nécessaire pour compenser ces émissions. Les plantations d'arbres à croissance rapide permettraient, selon les chiffres, d'éliminer le dépassement global.</li>
            <br>
            <li>Ils en concluent que nous ferions mieux de discuter des émissions de gaz à effet de serre directement en termes de tonnes d'équivalent CO2 (et donc de nous concentrer sur les solutions aux émissions), et de développer un cadre plus écologique et de processus écosystémiques pour saisir les impacts que les humains ont actuellement sur les systèmes naturels de la planète. L'échelle appropriée pour ces indicateurs sera, dans de nombreux cas, locale et régionale. À ce niveau, l’empreinte écologique est une mesure des exportations ou importations nettes de biomasse et de la capacité d'absorption du carbone.</li><br>
            <li>Toute ville, par exemple, présenterait un déficit, car elle dépend de la nourriture et des matériaux provenant de l'extérieur. En soi, comme l'a noté Robert Costanza, "cela ne nous dit pas grand-chose sur la durabilité de cet apport [de l'extérieur de la région] dans le temps".</li><br>
        </ul>
    </li>
    <br>
    <li><b>Les applications de l'empreinte se concentrent sur les pays plutôt que sur les “biorégions”.</b> Ainsi, les empreintes écologiques nationales n'ont pas beaucoup de sens, car les frontières des pays sont déterminées par des facteurs historico-politiques qui ne reflètent pas nécessairement la pertinence écologique. Fiala (2008) confirme que l'empreinte suppose arbitrairement des frontières nationales, ce qui “rend problématique l'extrapolation à partir de l'empreinte écologique moyenne”.</li><br>
    <li>
        <b>La mesure des déficits écologiques nationaux soutient les sentiments anti-commerce.</b> L'application de l’empreinte écologique à des régions ou des pays donne lieu à la notion de déficit écologique, qui est facilement mal interprétée et soutient les sentiments anti-commerce.
    </li>
    </ul>
    </p>
    <h5><pre>Limites méthodiques :</pre></h5>
    <br>
    <p style='text-align:justify'>
        Au-delà d’utiliser une notion contestée en soi, les méthodes de calcul utilisées par le Global Footprint Network ont également fait l’objet de critiques.
        <br>
    <ul style='text-align:justify'>
        <li><b>L'empreinte ne capture pas toutes les pressions environnementales pertinentes.</b> Les omissions de pressions environnementales font que l’empreinte écologique sous-estime la “pression environnementale globale réelle”, c'est-à-dire l'impact humain sur la biosphère. Par exemple, la pollution de l'eau, les émissions de substances toxiques (y compris les métaux lourds), la pollution sonore, l'appauvrissement de la couche d'ozone, les pluies acides, la fragmentation des écosystèmes résultant de l'utilisation des sols et des infrastructures routières et, plus généralement la biodiversité, ne sont pas pris en compte par l'approche. De même, Fiala (2008) et Blomqvist et al. (2013) identifient que les évaluations de l'empreinte écologique pour les terres cultivées, les pâturages et les terrains bâtis ne permettent pas de prendre en compte la dégradation des sols ou l'utilisation non durable de quelque nature que ce soit.</li>
        <br>
        <li>
            <b>Elle sous-estime très probablement le dépassement global.</b> Les comptes d'empreintes écologiques des pays sont strictement basés sur les statistiques des Nations Unies. Ces statistiques peuvent ne pas inclure tous les postes de consommation, et les données de biocapacité basées sur ces statistiques peuvent surestimer la productivité à long terme, puisque l'impact de la déforestation, de l'épuisement des sols ou de la pénurie d'eau sur la productivité future n'est pas pris en compte.
        </li>
    </ul>
    </p>
    <br>
    <h5><pre>Le manque d’utilité pour informer les politiques publiques</pre></h5>
    <br>
    <p style='text-align:justify'>Excepté un très vague consensus selon lequel “nous devrions limiter la consommation”, la mesure souffre de son manque de caractère prescriptif. L’empreinte n'est donc pas en mesure d'apporter beaucoup de lumière sur les choix politiques, y compris la sélection des stratégies (par exemple, les solutions technologiques ou la réorganisation spatiale de l'économie) ou des instruments politiques (par exemple, les instruments de commande et de contrôle par rapport aux instruments basés sur le marché). La question est de savoir si nous avons vraiment besoin de regrouper les informations sur les différents problèmes environnementaux dans des indicateurs uniques. La prise de décision en matière de politique publique est mieux éclairée par des indicateurs concrets pour des problèmes spécifiques. Le risque est que plupart des utilisateurs innocents, de manière compréhensible, interprètent l'empreinte écologique comme un indicateur global d'environnement ou de (non)durabilité</p>
    <br>
    <h5><pre>Des approches de calcul alternatives existent</pre></h5>
    <br>
    <p style='text-align:justify'>
        En voici un sommaire :
    </p><br>
    <p align='center'>
    <img src="/images/alternatives.jpeg" alt="Origine géographique des rapports" style="width:100%;border-style:inset;border-color:#f44336;">
    </p>
    
    </div>

    <!-- conclusions -->
  <div class="w3-container" style="margin-top:80px" id="coda">
    <h1 class="w3-xxxlarge w3-text-red"><b><u>Conclusions</u> :</b></h1>
    <hr style="width:50px;border:5px solid red" class="w3-round">
    <br>
    <p style="text-align:justify">
        <ul style="text-align:justify">
            <li>Les indicateurs écologiques doivent, dans la mesure du possible, inclure des estimations de l'incertitude.</li>
            <br>
            <li>Les indicateurs doivent tenir compte de l'échelle géographique des phénomènes qu'ils mesurent.</li>
            <br>
            <li>Un ensemble d'indicateurs, chacun se rapportant à une forme identifiable et quantifiable de capital naturel ou de service écosystémique, sera probablement plus compréhensible et plus utile qu'un seul indice global.</li>
            <br>
            <li>Les indicateurs de la durabilité de la consommation de capital naturel doivent pouvoir enregistrer l'épuisement ou les excédents. </li>
            <br>
            <li>Les indicateurs doivent mettre en évidence les voies à suivre pour atteindre les objectifs de durabilité qui sont à la fois écologiques et de bon sens.</li>
        </ul>
    </p>
    </div>
  
  <!-- API call -->
    <div class="w3-container" id="search_data" style="margin-top:75px">
      <h1 class="w3-xxxlarge w3-text-red"><b><u>Exploiter les données</u> :</b></h1>
      <hr style="width:50px;border:5px solid red" class="w3-round">
      <form action="" method="post">
        <p style="text-align:justify">La base de donnée du Global Footprint Network est en libre accès et permet à chacun·e de l'exploiter à des fins plus ou moins spécifiques. Nous avons voulu illustrer cette polyvalence en faisant de notre rendu une interface permettant d'accéder aux données de chaque pays inscrits dans la base de données.</p>

        <p align='center'>
          <b>Veuillez choisir un pays :</b> (Obligatoire)
        </p>
        <p align='center'>
          <br>
          <?php
            deroulant('Country',$all_countries_name,NULL,true,$all_countries_code);
          ?>
        </p>
        <br>
        <p align='center'>
          <b>Vous pouvez sélectionner une année précise :</b> (Facultatif)
        </p>
        <br>
        <p align='center'>
          <?php
            deroulant('Year',$all_years,'    ',true);
          ?>
        </p>
        <br>
        <p align='center'>
          <b>Vous pouvez sélectionner les données retournées <u>pour cette année</u> :</b> 
          (N'en cocher aucune retournera toutes les données)
        </p>
        <br>
        <div style="text-align:center;width: max-content;margin: 0 auto;border-style: solid;border-color: #f44336">
          <div class="w3-light-grey">
            <div class="w3-container">
              <?php
                $selected_options = array();
                foreach($all_types_names as $type_name){
                  $type_code=$all_types_code_and_names_associations[$type_name];
                  echo '<p align="center"><div style="text-align:left;border-style: dotted;border-color:#cc3c33;padding: 1em;">
                  <input type="checkbox" id="'.$type_code.'" name="types[]" value="'.$type_code.'">
                  <label for="'.$type_code.'">'.$type_name.'</label>
                  </div></p>';
                }
              ?>
            </div>
          </div>
        </div>
        <br>
        <p align='center'>
          <button class="w3-button w3-block w3-padding-large w3-red w3-margin-bottom" type="submit" name=submit onclick="w3_close()">Je valide ces critères de recherche</button>
        </p>
      </form>
      <br>
      <?php
          if(isset($_POST['submit'])){
            if($_POST['Year']=="0"){
              search($_POST['Country']);
            }else{
              if(isset($_POST['types'])){
                search($_POST['Country'],$all_years[$_POST['Year']],$_POST['types']);
              }else{
                search($_POST['Country'],$all_years[$_POST['Year']]);
              }
            }
          }
      ?>
    </div>
    <!-- Bibliography -->
    <div class="w3-container" style="margin-top:80px" id="bio">
    <h1 class="w3-xxxlarge w3-text-red"><b><u>Bibliographie</u> :</b></h1>
    <hr style="width:50px;border:5px solid red" class="w3-round">
    <br>
    <p style='text-align:justify'>
        <ul style='text-align:justify'>
            <li>Blomqvist, Linus, et al. “Does the Shoe Fit? Real versus Imagined Ecological Footprints.” <i>PLoS Biology</i>, vol. 11, no. 11, 2013, <a href='https://doi.org/10.1371/journal.pbio.1001700'>https://doi.org/10.1371/journal.pbio.1001700</a>.</li><br>
            <li>Fiala, Nathan. “Measuring Sustainability: Why the Ecological Footprint Is Bad Economics and Bad Environmental Science.” <i>Ecological Economics</i>, vol. 67, no. 4, 2008, pp. 519–525., <a href='https://doi.org/10.1016/j.ecolecon.2008.07.023'>https://doi.org/10.1016/j.ecolecon.2008.07.023</a>.</li><br>
            <li>
                Giampietro, Mario, and Andrea Saltelli. “Footprints to Nowhere.” <i>Ecological Indicators</i>, vol. 46, 2014, pp. 610–621., <a href='https://doi.org/10.1016/j.ecolind.2014.01.030'>https://doi.org/10.1016/j.ecolind.2014.01.030</a>. 
            </li><br>
            <li>
                Van den Bergh, Jeroen C.J.M, and Fabio Grazi. “Ecological Footprint Policy? Land Use as an Environmental Indicator.” <i>Journal of Industrial Ecology</i>, vol. 18, no. 1, 2014, pp. 10–19., <a href='https://doi.org/10.1111/jiec.12045'>https://doi.org/10.1111/jiec.12045</a>.
            </li><br>
            <li>Van den Bergh, Jeroen C.J.M., and Fabio Grazi. “Reply to the First Systematic Response by the Global Footprint Network to Criticism: A Real Debate Finally?” <i>Ecological Indicators</i>, vol. 58, 2015, pp. 458–463., <a href=''>https://doi.org/10.1016/j.ecolind.2015.05.007</a>.</li><br>
        </ul>
    </p> 

    </div>
    <br>
  </div>
</div>
<!-- End page content -->
</div>

<!-- W3.CSS Container -->
<div class="w3-light-grey w3-container w3-padding-32" style="margin-top:75px;padding-right:58px">
    <p class="w3-right">Ce site et l'ensemble de son contenu ont été produits par Marianne MAUREL, Ulysse RICHARD et Manoë Mévellec.</p><br>
    </div>

<script>
// Script to open and close sidebar
function w3_open() {
  document.getElementById("mySidebar").style.display = "block";
  document.getElementById("myOverlay").style.display = "block";
}
 
function w3_close() {
  document.getElementById("mySidebar").style.display = "none";
  document.getElementById("myOverlay").style.display = "none";
}
</script>

</body>
</html>
