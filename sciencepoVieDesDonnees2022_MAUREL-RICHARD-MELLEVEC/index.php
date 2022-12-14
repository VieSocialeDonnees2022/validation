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
    if(substr($country_shortName,0,2)!='??' && substr($country_shortName,0,2) != '??'){
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
<title>Vie Sociale des Donn??es</title>
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
    <h3 class="w3-padding-64"><b>Vie Sociale<br>des donn??es<br>2022</b></h3>
  </div>
  <div class="w3-bar-block">
    <a href="#" onclick="w3_close()" class="w3-bar-item w3-button w3-hover-white">Accueil</a> 
    <a href="#presentation" onclick="w3_close()" class="w3-bar-item w3-button w3-hover-white">> Le Global Footprint Network : Pr??sentation</a> 
    <a href="#background" onclick="w3_close()" class="w3-bar-item w3-button w3-hover-white">> Historique</a> 
    <a href="#administrators" onclick="w3_close()" class="w3-bar-item w3-button w3-hover-white">> Gestionnaires actuels</a> 
    <a href="#finance" onclick="w3_close()" class="w3-bar-item w3-button w3-hover-white">> Financement</a>
    <a href="#vocabulary" onclick="w3_close()" class="w3-bar-item w3-button w3-hover-white">> Glossaire</a>
    <a href='#sources' onclick="w3_close()" class="w3-bar-item w3-button w3-hover-white">> Sources</a>
    <a href='#pertinence' onclick="w3_close()" class="w3-bar-item w3-button w3-hover-white">> Implications conceptuelles</a>
    <a href='#critics' onclick="w3_close()" class="w3-bar-item w3-button w3-hover-white">> Critiques</a>
    <a href="#coda" onclick="w3_close()" class="w3-bar-item w3-button w3-hover-white">> Conclusions</a>
    <a href="#search_data" onclick="w3_close()" class="w3-bar-item w3-button w3-hover-white">> Exploiter les donn??es</a>
    <a href="#bio" onclick="w3_close()" class="w3-bar-item w3-button w3-hover-white">> Bibliographie</a>
  </div>
</nav>

<!-- Top menu on small screens -->
<header class="w3-container w3-top w3-hide-large w3-red w3-xlarge w3-padding">
  <a href="javascript:void(0)" class="w3-button w3-red w3-margin-right" onclick="w3_open()">???</a>
  <span>Vie Sociale des donn??es 2022</span>
</header>

<!-- Overlay effect when opening sidebar on small screens -->
<div class="w3-overlay w3-hide-large" onclick="w3_close()" style="cursor:pointer" title="close side menu" id="myOverlay"></div>

<!-- !PAGE CONTENT! -->
<div class="w3-main" style="margin-left:340px;margin-right:40px">

  <!-- Header -->
  <div class="w3-container" style="margin-top:80px" id="presentation">
    <h1 class="w3-jumbo"><b>Vie Sociale des donn??es 2022</b></h1>
    <h1 class="w3-xxxlarge w3-text-red"><b><u>Le Global Footprint Network</u> : <u>Pr??sentation</u></b></h1>
    <hr style="width:50px;border:5px solid red" class="w3-round">
    <p style="text-align:justify">
      Le Global Footprint Network est un organisme de bienfaisance non-lucratif dont l'objectif principal est de proposer des 
      outils de mesure et des donn??es servant ?? encourager et faciliter les mesures li??es au d??veloppement durable. 
      C'est ?? ces fins qu'ils rendent donc leurs outils et conclusions disponibles en open-source ; notamment, 
      <a href='https://data.footprintnetwork.org/#/'>leur interface en ligne de visualisation de donn??es statistiques</a> a 
      pour vocation de permettre aux particuliers comme aux organisations de r??utiliser les donn??es repr??sent??es et de mieux 
      les comprendre.
    </p>
    <br>
    <p style="text-align:justify">
      Le pr??sent site se veut un exemple basique de la fa??on dont lesdites donn??es peuvent ??tre mobilis??es ?? l'ext??rieur des 
      plateformes du Global Footprint Network, apr??s avoir contextualis?? et pr??sent?? l'initiative. Pour acc??der directement ?? la recherche, cliquez <a href='#search_data'>ici</a> ou sur "Exploiter les donn??es" dans la barre de navigation.
    </p>
  </div>
  
  <!-- Background -->
  <div class="w3-container" id="background" style="margin-top:75px">
    <h1 class="w3-xxxlarge w3-text-red"><b><u>Historique du GPN</u></b></h1>
    <hr style="width:50px;border:5px solid red" class="w3-round">
    <p style="text-align:justify">
      L'empreinte ??cologique ("<i>ecological footprint</i>") est un index cr???? par <a href='https://en.wikipedia.org/wiki/Mathis_Wackernagel' title='https://en.wikipedia.org/wiki/Mathis_Wackernagel'>Mathis Wackernagel</a> et <a href='https://fr.wikipedia.org/wiki/William_E._Rees' title='https://fr.wikipedia.org/wiki/William_E._Rees'>William Rees</a> 
      <a href='https://www.footprintnetwork.org/about-us/our-history/' title='https://www.footprintnetwork.org/about-us/our-history/'>
      dans les ann??es 90</a>. Wackernagel faisait sa th??se de PhD en ???community and regional planning??? ?? l???University of British Columbia et a d??velopp?? le concept avec W. Rees, son superviseur, qui en ??tait ?? l???origine. Ce concept est pr??sent?? dans l???ouvrage 
      <i><span title='Wackernagel, M. and W. Rees. 1996. Our Ecological Footprint: Reducing Human Impact on the Earth. New Society Publishers.'>
        Our Ecological Footprint: Reducing Human Impact on the Earth</span>
      </i>. Mathis Wackernagel est d??sormais le Pr??sident du Global Footprint Network, ainsi que laur??at du <a href='https://wsforum.org/instructions#awardees' title='https://wsforum.org/instructions#awardees'>World Sustainability Award</a> 
      en 2018. William Rees ??tait son professeur ?? l???Universit?? de la Colombie-Britannique et est sp??calis?? dans les recherches
      sur les politiques publiques et l???environnement. Il est ?? l???origine du concept d???empreinte ??cologique et co-dev??loppeur 
      de la m??thode de calcul.
    </p>
  </div>
  
  <!-- Administrators -->
  <div class="w3-container" id="administrators" style="margin-top:75px">
    <h1 class="w3-xxxlarge w3-text-red"><b><u>Gestionnaires actuels</u> :</b></h1>
    <hr style="width:50px;border:5px solid red" class="w3-round">
    <p style="text-align:justify"><b>Les donn??es utilis??es pour calculer l???empreinte ??cologique sont administr??es par un consortium compos?? <a href='https://www.footprintnetwork.org/about-us/people/' title='https://www.footprintnetwork.org/about-us/people/'>du Global 
      Footprint Network</a>, de <a href='https://footprint.info.yorku.ca/people/' title='https://footprint.info.yorku.ca/people/'>l???Universit?? de York</a> et de <a href='https://www.fodafo.org/board.html' title='https://www.fodafo.org/board.html'>la FODAFO (Footprint Data Foundation)</a>, organismes d??taill??s ci-dessous</b> :</p>
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
          <span style="white-space: pre-line"><p class="w3-opacity">"International think tank working to drive informed, sustainable policy decisions in a world of limited resources. [...] Coordinates research, develops methodological standards, and provides decision-makers with a menu of tools to help the human economy operate within Earth???s ecological limits."
          ??? <a href='https://www.footprintnetwork.org/2015/09/23/eight-countries-meet-two-key-conditions-sustainable-development-united-nations-adopts-sustainable-development-goals/' title='https://www.footprintnetwork.org/2015/09/23/eight-countries-meet-two-key-conditions-sustainable-development-united-nations-adopts-sustainable-development-goals/'><i>Only eight countries meet two key conditions for sustainable development as United Nations adopts Sustainable Development Goals</i></a></p></span>
          <span style="white-space: pre-line"><p style="text-align:left">
              <u>Pr??sident</u> : Mathis Wackernagel, Ph.D
              <u>Direction scientifique</u> : David Lin, Ph.D
              <u>Co-fondatrice</u> : Susan Burns
              <u>Ancienne directrice g??n??rale</u> : Julia Marton-Lef??vre
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
          ??? <a href='https://www.fodafo.org/why-fodafo.html' title='https://www.fodafo.org/why-fodafo.html'><i>Ecological Footprint Initiative</i></a></p></span>
          <span style="white-space: pre-line"><p style="text-align:left">
              <u>Doyenne de la Facult?? des Changements Environnementaux et Urbains</u> : Alice Hovorka
              <u>Directeur de l'Initiative Empreinte ??cologique</u> : Eric Miller
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
          ??? <a href='https://footprint.info.yorku.ca/' title='https://footprint.info.yorku.ca/'><i>Ecological Footprint Initiative</i></a></p></span>
          <span style="white-space: pre-line"><p style="text-align:left">
              <u>Doyenne de la Facult?? des Changements Environnementaux et Urbains</u> : Alice Hovorka
              <u>Directeur de l'Initiative Empreinte ??cologique</u> : Eric Miller
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
      Les finances engendr??es par l'organisme Global Footprint Network sont publi??es sur <a href='https://www.overshootday.org/annual-report-2021/' title='https://www.overshootday.org/annual-report-2021/'>leur site officiel</a> qui nous sert de sources. D'autres frais que ceux engendr??s par la base de donn??e et son maintien sont comprises dans les visualisations suivantes :
    </p>
    <p align='center'>
          <img src="/images/income-2021.jpg" alt="Income of the organism in 2021" style="width:80%;border-style:inset;border-color:#f44336;">
    </p>
    <p align='center'>
          <img src="/images/expenses-2021.jpg" alt="Expense of the organism in 2021" style="width:80%;border-style:inset;border-color:#f44336;">
    </p>
    <br>
    <p style="text-align:justify">
        L'organisme re??oit entre autres <a href='https://www.overshootday.org/content/uploads/2022/08/GFN-AR-2021-Support-FINAL.pdf' tile='https://www.overshootday.org/content/uploads/2022/08/GFN-AR-2021-Support-FINAL.pdf'>des dons d'entreprises priv??es</a> aux profils vari??s, dont voici la liste :
    </p>
    
    <ul class='ul-double'>
        <li><a href='https://www.joinatmos.com/impact' title='https://www.joinatmos.com/impact'>Atmos Financial</a> [Banque verte]</li>
        <li><a href='https://eberhard-ag.com/en/' title='https://eberhard-ag.com/en/'>Eberhard AG</a> [Industrie de robotique]</li>
        <li><a href='https://about.google/?utm_source=google-FR&utm_medium=referral&utm_campaign=hp-footer&fg=1' title='https://about.google/?utm_source=google-FR&utm_medium=referral&utm_campaign=hp-footer&fg=1'>Google</a>[Services technologiques]</li>
        <li>Heart Craft</li>
        <li><a href='https://www.hessnatur.com/en-DE/' title='https://www.hessnatur.com/en-DE/'>hessnatur</a> [Pr??t-??-porter ??cologique]</li>
        <li><a href='https://populationcrisis.org/' title='https://populationcrisis.org/'>Population Crisis</a> [Documentaires sur la population mondiale]</li>
        <li><a href='https://www.se.com/fr/fr/' title='https://www.se.com/fr/fr/'>Schneider Electric</a> [Solutions ??nerg??tiques]</li>
        <li><a href='https://www.shirazcreative.com/' title='https://www.shirazcreative.com/'>Shiraz Creative of California, LLC</a> [Marketing]</li>
        <li>Winkler Benoit Consultancy [Cabinet de conseil en environnement]</li>
        <li><a href='https://zirkulit.ch/' title='https://zirkulit.ch/'>zirkulit AG</a> [B??ton]</li>
    </ul>
  </div>
  
  <div class="w3-container" style="margin-top:80px" id="vocabulary">
    <h1 class="w3-xxxlarge w3-text-red"><b><u>Glossaire</u> :</b></h1>
    <hr style="width:50px;border:5px solid red" class="w3-round">
    <p style="text-align:justify">Toutes les d??finitions et formules appliqu??es par le Global Footprint Network et ses partenaires sont compil??es dans l'ouvrage <i><a href='https://www.footprintnetwork.org/content/images/uploads/Ecological_Footprint_Standards_2009.pdf' title='https://www.footprintnetwork.org/content/images/uploads/Ecological_Footprint_Standards_2009.pdf'>Ecological Footprint Standards 2009</a></i>.
    </p>
    <br>
    <div class='w3-light-grey' style='border-style:solid;border-color:#f44336;padding:0.3cm;box-shadow: 4px 3px 8px 1px #969696;-webkit-box-shadow: 4px 3px 8px 1px #ab545c;'>
        <p align='center'>
            <span style='border-bottom:1px solid red;'><b>Empreinte ??cologique</b></span> : Superficie biologiquement productible n??cessaire pour maintenir le rythme de fonctionnement actuel d'un secteur d'activit?? donn??.
        </p>
        <p align='center' class="w3-opacity">
            L'empreinte ??cologique est calcul??e en "hectares globaux" ?? partir de la surface des terres cultiv??es, des p??turages, des terrains b??tis, des z??nes de p??che, des produits forestiers et des terres exploit??es qui absorbent le carbone terrestre.
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
            <span style='border-bottom:1px solid black;'><b>Biocapacit??</b></span> : Surfaces terrestres et maritimes biologiquement productives disponibles pour fournir les ressources qu'une population consomme et pour absorber ses d??chets, compte tenu des technologies et des pratiques de gestion actuelles.
        </p>
        <p align='center' class="w3-opacity">
             Pour que les mesures de la biocapacit?? soient comparables entre deux ??poques ou deux espaces g??ographiques diff??rents, les superficies sont ajust??es proportionnellement selon leur productivit?? biologique.
        </p>
        <p align='center' class="w3-opacity">
            La biocapacit?? est calcul??e en "hectares globaux" ?? partir de la surface des terres cultiv??es, des p??turages, des terrains b??tis, des z??nes de p??che et de la biocapacit?? foresti??re.
        </p>
        <p align='center'>
            <img src="/images/biocapacity.jpeg" alt="biocapacity equation" style="width:80%;border-style:inset;border-color:#f44336;">
        </p>
    </div>
    <br>
    <div class='w3-light-grey' style='border-style:solid;padding:0.3cm;box-shadow: 4px 3px 8px 1px #969696;-webkit-box-shadow: 4px 3px 8px 1px #ab545c;'>
        <p align='center'>
            <span style='border-bottom:1px solid;'><b>R??serve ??cologique</b></span> : d??signe une empreinte ??cologique inf??rieure ?? la biocapacit??.
        </p>
        <p align='center' class="w3-opacity">
            Les acteurs en situation de r??serve ??cologique sont appel??s des cr??diteurs ??cologiques.
        </p>
    </div>
    <br>
    <div class='w3-light-grey' style='border-style:solid;padding:0.3cm;box-shadow: 4px 3px 8px 1px #969696;-webkit-box-shadow: 4px 3px 8px 1px #ab545c;'>
        <p align='center'>
            <span style='border-bottom:1px solid;'><b>D??ficit ??cologique</b></span> : d??signe une empreinte ??cologique sup??rieure ?? la biocapacit??.
        <p align='center' class="w3-opacity">
            Les acteurs en situation de d??ficit ??cologique sont appel??s des d??biteurs ??cologiques.
        </p>
        <p align='center' class="w3-opacity">
            Le jour du d??passement ??cologique, durant lequel les ressources de la plan??te pour l'ann??e sont ??puis??es, est une situation de <b>d??ficit ??cologique international</b>.
        </p>
    </div>
    <br>
    <div class='w3-light-grey' style='border-style:solid;padding:0.3cm;box-shadow: 4px 3px 8px 1px #969696;-webkit-box-shadow: 4px 3px 8px 1px #ab545c;'>
        <p align='center'>
            <span style='border-bottom:1px solid;'><b>Hectare global</b></span> : hectare de terre qui fournit une quantit?? moyenne mondiale de r??g??n??ration biologique chaque ann??e.
        </p>
        <p align='center' class="w3-opacity">
            Les trois facteurs de conversion permettant d'obtenir l'hectare global sont ceux :
            <ul class="w3-opacity">
                <li>de rendement, qui relie le rendement national d'un type de terre sp??cifique par rapport au rendement moyen mondial ;</li>
                <li>d'??quivalence, qui relie les ??l??ments les uns aux autres en fonction de leur niveau de productivit?? biologique ;</li>
                <li>de rendement intertemporel, qui relie les changements de productivit?? biologique dans le temps.</li>
            </ul>
        </p>
    </div>
    <br>
    <div class='w3-light-grey' style='border-style:solid;padding:0.3cm;box-shadow: 4px 3px 8px 1px #969696;-webkit-box-shadow: 4px 3px 8px 1px #ab545c;'>
        <p align='center'>
            <span style='border-bottom:1px solid;'><b>Score de qualit?? des donn??es</b></span> : indique si toutes, certaines ou aucune des donn??es portant sur une nation pr??cise sont incluses dans la plateforme de donn??es ouverte. Le manque de donn??es ou de qualit?? de ces donn??es m??ne ?? un score de qualit?? bas.
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
        Les donn??es sont g??r??es par la <i>York University???s Ecological Footprint Initiative</i> ?? travers la FODAFO. Le rapport mesure l???utilisation de resources ??cologiques et les capacit??s de resources des pays ??tudi??s dans le temps.
    </p>
    <p style="text-align:justify">Les sources de ces donn??es sont : </p>
    <ul>
        <li>l'Agence internationale de l?????nergie ;</li>
        <li>l???Organisation des Nations unies pour l???alimentation et l???agriculture (plus sp??cifiquement ses bases de donn??es : ProdStat, TradeStat, ResourceStat, FishStat) ;</li>
        <li>la Base de donn??es UN - Comtrade ;</li>
        <li>la Base de donn??es CORINE Land Cover ;</li>
        <li>la Base de donn??es Global Agro-Ecological Zones ;</li>
        <li>le Global Land Cover ;</li>
        <li>le Global Carbon Budget ;</li>
        <li>la Banque Mondiale ;</li>
        <li>le Fond Mon??taire International ;</li>
        <li>la Penn World Table.</li>
    </ul>
    <br>
    <p style="text-align:justify">L?????dition 2022 de ce rapport pr??sente les r??sultats des calculs pour des donn??es allant de 1961 ?? 2018 et concernant 238 "entit??s internationales" dont 190 pays. Ce sont ces 190 pays qui composent le panel mondial de la base de donn??es que nous ??tudions.
    </p>
    <p style="text-align:justify">Comparativement aux ??ditions pr??c??dentes, celle de 2022 a pour diff??rences notables :</p>
    <ul>
        <li>de ne pas rendre compte des terres cultiv??es et du b??tail pour plusieurs entit??s qui ne font plus l'objet de rapports de la FAO des Nations unies : Antilles n??erlandaises (anciennes), Aruba, Bermudes, ??les Vierges am??ricaines, ??les Ca??manes, ??les Falkland (Malvinas), Groenland, Guam, Liechtenstein, Montserrat, ??le Norfolk, ??les Wallis-et-Futuna, Sainte-H??l??ne, Ascension et Tristan da Cunha, Saint-Pierre-et-Miquelon, Sahara occidental et Samoa am??ricaines ;</li>
        <br>
        <li>que les donn??es pour le Soudan, le Sud-Soudan et le Soudan (ancien) en 2011 et 2012 refl??tent les donn??es d??clar??es alors que les ??ditions pr??c??dentes avaient estim?? la plupart des donn??es de ces ann??es ;</li>
        <br>
        <li>que plusieurs pays ont des scores de qualit?? de donn??es diff??rents pour l'??dition 2022, ce qui affecte la quantit?? de donn??es nationales publi??es sur la plateforme. Notamment :
        <ul>
            <li>
                L'Alg??rie, la Finlande, le Guatemala, le S??n??gal, l'Eswatini, l'Uruguay et le Sud-Soudan ont d??sormais un score 3A, ce qui signifie que toutes les composantes du calcul de ce score sont pr??sent??es tout au long de la chronologie.
            </li>
            <li>Le Belize, les ??les Salomon, les ??les F??ro?? et le Vanuatu ont des scores de qualit?? des donn??es plus ??lev??s que dans la derni??re ??dition, mais il y a encore beaucoup de donn??es manquantes qui emp??chent ces nations d'obtenir le score de 3A.</li>
            <li>Tous les pays n'ayant pas fait l'objet d'un rapport de la FAO dans cette ??dition se sont vus attribuer un score de 1D en raison de cette lacune.</li>
        </ul>
        </li>
    </ul>

    </div>
    
    <!-- Reusing data -->
  <div class="w3-container" style="margin-top:80px" id="pertinence">
    <h1 class="w3-xxxlarge w3-text-red"><b><u>Implications conceptuelles</u> :</b></h1>
    <hr style="width:50px;border:5px solid red" class="w3-round">
    <h3>La r??utilisation des donn??es : analyse g??ographique</h3>
    <br>
    <p align='center'>
          <img src="/images/origine_des_rapports.jpeg" alt="Origine g??ographique des rapports" style="width:80%;border-style:inset;border-color:#f44336;">
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
                <td>Am??rique du Nord</td>
                <td>16,7%</td>
              </tr>
              <tr>
                <td>Afrique</td>
                <td>6,3%</td>
              </tr>
              <tr>
                <td>Am??rique centrale</td>
                <td>2,1%</td>
              </tr>
              <tr>
                <td>Moyen-Orient</td>
                <td>2,1%</td>
              </tr>
              <tr>
                <td>Am??rique latine</td>
                <td>2,1%</td>
              </tr>
            </table>
    </p>
    <br>
    <p style="text-align:justify">La majorit?? des rapports ayant ??t?? pr??sent??s par le Global Footprint Network et fond??s sur l???Ecological Footprint ont ??t?? publi??s en Europe, suivi de l???Asie et de l???Am??rique du nord. Ces r??sultats interpellent, puisque le Global Footprint Network ??tant un index issu de l???environnement acad??mique ??tats-unien, l???on pourrait s???attendre ?? ce que la r??gion soit plus active dans la r??interpr??tation de ces donn??es. De cette observation l???on peut d??duire plusieurs conclusions :</p><br>
<p style="text-align:justify">Tout d???abord, on observe que la majorit?? des rapports utilisant ces donn??es pour produire de la connaissance viennent principalement des r??gions les plus repr??sent??es et reconnues en termes d???institutions acad??miques (Europe, Asie, Am??rique du Nord).</p>
<p style="text-align:justify">Ensuite, ?? partir de ces r??sultats, on peut faire l???observation de l???influence intellectuelle des organismes nord-am??ricains sur le reste du monde. Les sources de financements proviennent en grande partie du priv??, ce qui permet d???offrir ?? ces organismes des avantages concurrentiels consid??rables pour la construction de leurs bases, et se font en grande partie avec la coop??ration des universitaires, ce qui l??gitime plus encore leurs travaux. Les connaissances num??riques provenant des ??tats-Unis sont donc tr??s majoritaires au sein de la production de connaissances dans le reste du monde.</p>
<p style="text-align:justify"></p>Derni??re conclusion ??? celle-ci pouvant n'??tre qu???une supposition qui m??riterait une plus grande analyse afin d?????tre d??montr??e par un processus scientifique ???, l???on peut ??mettre l???hypoth??se que ces donn??es sont tout simplement plus utilis??es en Europe et en Asie en raison de leur nature de donn??es climatiques. En effet, ces derni??res ann??es ont vu l???Europe prendre un r??le tr??s important au sein du discours d???action climatique, et ce depuis que la chaise de leadership environnemental international a ??t?? laiss??e vacante par la pr??sidence de Trump et son retrait des accords de Paris. L???Europe a ainsi formul?? un Green New Deal et a nourri des travaux, au niveau ??tatique comme communautaire, tr??s riches en termes de recherche et de politique climatique. Il en va de m??me pour l???Asie dont les pays sont soit directement touch??s par les effets du r??chauffement climatique et ont donc tout int??r??t ?? contribuer ?? la diss??mination des connaissances issues de ces donn??es, soit affichent une volont?? politique comme la Chine incitative de transition ??cologique de son tissu ??conomique avec des promesses telles que la neutralit?? carbone en 2050.</p>
<br>
    <p align='center'>
        <img src="/images/nb_rapports_pays.jpeg" alt="Origine g??ographique des rapports" style="width:80%;border-style:inset;border-color:#f44336;">
    </p>
    <br>
    <p style="text-align:justify">
        La majorit?? des rapports ont ??t?? publi??s aux Etats-Unis et en Suisse. La France, la Chine et l???Allemagne sont d???autres pays publiant le plus de rapports en lien avec le Global Footprint Network. Cette analyse par pays plut??t que par zone g??ographique ??tendue replace en perspective le r??le dominant des Etats-Unis dans l???utilisation des donn??es issus de son milieu universitaire.
    </p>
    <br>
    <h3>La production de connaissances : nuances d???utilisations</h3>
    <br>
    <p style="text-align:justify">
        Avant la production de donn??es climatiques mettant en ??vidence les effets invisibles mais latents des activit??s humaines sur notre ??cosyst??me, les politiques environnementales se focalisaient tout autant sur la protection de la biodiversit?? que sur la lutte contre les pollutions et accidents industriels. Aujourd'hui c???est sur la question du climat et de son r??chauffement d?? aux gaz ?? effet de serre que se focalisent les concertations internationales.

        Ainsi, ces donn??es permettent...
    </p>
    <h5><pre>   De mesurer l???impact r??el des ??tats sur le climat :</pre></h5>
    <p style="text-align:justify">
        Un exemple de connaissance concr??te de ce cas et qui d??coule directement de ces donn??es est celui du ???jour du d??passement???, qui permet de calculer la date ?? laquelle les soci??t??s ont ??puis?? les r??serves naturelles th??oriques qu???ils devaient utiliser pour veiller au bon renouvellement de celles futures. En effet, un croisement des diff??rents indices de cette base nous permet de rapporter le calcul des impacts individuels ?? l?????chelle de la plan??te, c???est ?? dire multiplier cette surface moyenne utilis??e par chaque ??tre humain pour sa subsistance par la population mondiale et comparer cette surface virtuelle ?? la biocapacit?? r??elle de la terre. Ce chiffre est sans surprise sup??rieur ?? ce que la biosph??re est en capacit?? d???absorber, et nous permet par la suite de calculer le jour du d??passement, qui tombait cette ann??e le 28 juillet.
    </p>
    <br>
    <p align='center'>
        <img src="/images/twitter_ministe_climat.jpeg " alt="Tweet du minist??re de l'??cologie sur le jour du d??passement" style="width:80%;border-style:inset;border-color:#f44336;">
    </p>
    <br>
    <h5><pre>   De calculer l???empreinte ??cologique moyenne individuelle par ??tat :</pre> </h5>
    <p style="text-align:justify">
        Cette interpr??tation des donn??es comme production de connaissances permet notamment d'identifier les plus gros ??metteurs de dioxyde de carbone ; elle est ainsi source d???instrumentalisation, et ce afin de porter le d??bat sur la responsabilit?? r??elle de certains ??tats. Les graphiques produits par cette base de donn??es ne donnant pas d?????chelle historique mais seulement g??ographique des ??missions. Cela permet aux ??tats de placer le curseur de la responsabilit?? sur des ??tats consid??r??s comme pollueurs alors m??me que leurs ??missions sont r??centes dans leur sch??ma de d??veloppement. Une instrumentalisation concr??te cette fois-ci bas??e sur une notion plus historique, ??tendant donc l???analyse principalement g??ographique qui privil??gie les Etats ayant les moyens de baisser leurs ??missions, a notamment ??t?? observ??e lors de la COP27. En effet, la notion de dette carbone y a ??t?? source de tension entre ??tats, certains r??clamant que les pays s?????tant enrichis de ces ??missions visibles dans la base de donn??es ??tudi??e soit rembours??e aux ??tats qui aujourd???hui en payent le prix.
    </p>
    <br>
    <h5><pre>   De produire des syst??mes interop??rables d???utilisation de m??thodes de calcul environnementales :</pre> </h5>
    <p style="text-align:justify">
Cette normalisation de la production de donn??es et des m??thodes d?????valuation environnementale sont particuli??rement utiles aux entit??s qui formulent des politiques climatiques mutualis??es et coordonn??es. Un exemple concret est cette recommandation de la Commission europ??enne qui cite directement dans son annexe 1 page 16 les Ecological Footprint Standards parmi <a href='https://environment.ec.europa.eu/system/files/2021-12/Commission%20Recommendation%20on%20the%20use%20of%20the%20Environmental%20Footprint%20methods_0.pdf'>leurs guides m??thodologiques fondatrices et normes ISO</a>.
    </p>
    <br>
    <h3>L???interpr??tation des donn??es : multiplicit?? des nuances</h3>
    <br>
    <p style='text-align:justify'>
        La multiplication des indices ??cologiques, qui est illustr??e par cette base puisqu???elle-m??me produit 5 indices diff??rents (D??ficit ??cologique/R??serve ??cologique ; Empreinte ??cologique totale ; Empreinte ??cologique par personne ; Biocapacit?? totale ; Biocapacit?? par personne) facilite l???instrumentalisation politique de cette base de donn??es, puisqu???il multiplie les connaissances pouvant en ??tre le produit.</p>
    <p style='text-align:justify'>Ces donn??es peuvent en effet ??tre interpr??t??es de diff??rentes mani??res en fonction des autres classements avec lesquels elles sont recoup??es :
    <br>
    <ul>
        <li>Par exemple, le recoupement de cette base de donn??es avec le classement du PIB nous permet d???observer que plus les pays sont riches et plus ils ??mettent, tandis que les pays les plus pauvres n???ont qu???un impact tr??s limit?? sur les gaz ?? effet de serre. Ce croisement du classement PIB et du bilan carbone ne donne cependant qu???un aper??u partiel de la responsabilit?? des pays dans les ??missions de gaz ?? effet de serre, parce qu???il ne prend pas en compte la d??localisation de certaines productions industrielles. Ainsi si le bilan carbone a sensiblement diminu?? depuis une trentaine d???ann??es, l???empreinte carbone r??elle ?? elle augment??e d???environ 20% ce qui s???explique par la d??sindustrialisation des pays occidentaux et l???augmentation de l???importation de produits manufactur??s. </li>
        <br>
        <li>Selon l???indice de performance climatique, un indice qui met en avant les efforts pour la transition ??nerg??tique, c???est la Su??de qui est la mieux class??e dans le monde. Mais ce classement est ensuite recoup?? avec la performance environnementale, un autre indice ??cologique qui prend en compte la vitalit?? des ??cosyst??mes d???un pays. L?? encore il faut nuancer ce classement : le lithium des batteries des v??hicules ??lectriques, tr??s b??n??fique pour l???environnement des pays qui les utilisent, provient de mines tr??s polluantes situ??es essentiellement en Australie, au Chili ou en Chine, et qui n???est donc compter que dans les ??missions de ces pays, r??duisant leur performance environnementale au profit des pays qui eux en b??n??ficient. </li>
        <br>
        <li>Enfin, il faut consid??rer le croisement entre les deux indices de cette base de donn??es : l???empreinte ??cologique par habitant et l???empreinte ??cologique totale. Le premier indice se distingue en ce qu???il mesure la surface terrestre n??cessaire ?? la subsistance d???un individu selon son mode de vie : les surfaces agricoles utilis??es pour son alimentation, les surfaces aquatiques, mais aussi les surfaces n??cessaires pour compenser ses rejets et ??missions de CO2. Cette surface moyenne permet de comparer l???empreinte ??cologique d???un qatari ou luxembourgeois, dont les ??tats ne figurent pourtant pas ?? la premi??re place des ??missions, avec celle des ??rythr??en ou haitien, et de remettre en perspective la responsabilit?? individuelle des membres du tissu social des ??tats.</li>
    </ul></p>
    <br>
    <h3>L???application politique des connaissances : un exemple dans le discours climatique</h3>
    
    <p>
        <blockquote class="instagram-media" data-instgrm-permalink="https://www.instagram.com/reel/Ck8KbsQjVrC/?utm_source=ig_embed&amp;utm_campaign=loading" data-instgrm-version="14" style=" background:#FFF; border:0; border-radius:3px; box-shadow:0 0 1px 0 rgba(0,0,0,0.5),0 1px 10px 0 rgba(0,0,0,0.15); margin: 1px; max-width:540px; min-width:326px; padding:0; width:99.375%; width:-webkit-calc(100% - 2px); width:calc(100% - 2px);"><div style="padding:16px;"> <a href="https://www.instagram.com/reel/Ck8KbsQjVrC/?utm_source=ig_embed&amp;utm_campaign=loading" style="background:#FFFFFF; line-height:0; padding:0 0; text-align:center; text-decoration:none; width:100%;" target="_blank"> <div style=" display: flex; flex-direction: row; align-items: center;"> <div style="background-color: #F4F4F4; border-radius: 50%; flex-grow: 0; height: 40px; margin-right: 14px; width: 40px;"></div> <div style="display: flex; flex-direction: column; flex-grow: 1; justify-content: center;"> <div style=" background-color: #F4F4F4; border-radius: 4px; flex-grow: 0; height: 14px; margin-bottom: 6px; width: 100px;"></div> <div style=" background-color: #F4F4F4; border-radius: 4px; flex-grow: 0; height: 14px; width: 60px;"></div></div></div><div style="padding: 19% 0;"></div> <div style="display:block; height:50px; margin:0 auto 12px; width:50px;"><svg width="50px" height="50px" viewBox="0 0 60 60" version="1.1" xmlns="https://www.w3.org/2000/svg" xmlns:xlink="https://www.w3.org/1999/xlink"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g transform="translate(-511.000000, -20.000000)" fill="#000000"><g><path d="M556.869,30.41 C554.814,30.41 553.148,32.076 553.148,34.131 C553.148,36.186 554.814,37.852 556.869,37.852 C558.924,37.852 560.59,36.186 560.59,34.131 C560.59,32.076 558.924,30.41 556.869,30.41 M541,60.657 C535.114,60.657 530.342,55.887 530.342,50 C530.342,44.114 535.114,39.342 541,39.342 C546.887,39.342 551.658,44.114 551.658,50 C551.658,55.887 546.887,60.657 541,60.657 M541,33.886 C532.1,33.886 524.886,41.1 524.886,50 C524.886,58.899 532.1,66.113 541,66.113 C549.9,66.113 557.115,58.899 557.115,50 C557.115,41.1 549.9,33.886 541,33.886 M565.378,62.101 C565.244,65.022 564.756,66.606 564.346,67.663 C563.803,69.06 563.154,70.057 562.106,71.106 C561.058,72.155 560.06,72.803 558.662,73.347 C557.607,73.757 556.021,74.244 553.102,74.378 C549.944,74.521 548.997,74.552 541,74.552 C533.003,74.552 532.056,74.521 528.898,74.378 C525.979,74.244 524.393,73.757 523.338,73.347 C521.94,72.803 520.942,72.155 519.894,71.106 C518.846,70.057 518.197,69.06 517.654,67.663 C517.244,66.606 516.755,65.022 516.623,62.101 C516.479,58.943 516.448,57.996 516.448,50 C516.448,42.003 516.479,41.056 516.623,37.899 C516.755,34.978 517.244,33.391 517.654,32.338 C518.197,30.938 518.846,29.942 519.894,28.894 C520.942,27.846 521.94,27.196 523.338,26.654 C524.393,26.244 525.979,25.756 528.898,25.623 C532.057,25.479 533.004,25.448 541,25.448 C548.997,25.448 549.943,25.479 553.102,25.623 C556.021,25.756 557.607,26.244 558.662,26.654 C560.06,27.196 561.058,27.846 562.106,28.894 C563.154,29.942 563.803,30.938 564.346,32.338 C564.756,33.391 565.244,34.978 565.378,37.899 C565.522,41.056 565.552,42.003 565.552,50 C565.552,57.996 565.522,58.943 565.378,62.101 M570.82,37.631 C570.674,34.438 570.167,32.258 569.425,30.349 C568.659,28.377 567.633,26.702 565.965,25.035 C564.297,23.368 562.623,22.342 560.652,21.575 C558.743,20.834 556.562,20.326 553.369,20.18 C550.169,20.033 549.148,20 541,20 C532.853,20 531.831,20.033 528.631,20.18 C525.438,20.326 523.257,20.834 521.349,21.575 C519.376,22.342 517.703,23.368 516.035,25.035 C514.368,26.702 513.342,28.377 512.574,30.349 C511.834,32.258 511.326,34.438 511.181,37.631 C511.035,40.831 511,41.851 511,50 C511,58.147 511.035,59.17 511.181,62.369 C511.326,65.562 511.834,67.743 512.574,69.651 C513.342,71.625 514.368,73.296 516.035,74.965 C517.703,76.634 519.376,77.658 521.349,78.425 C523.257,79.167 525.438,79.673 528.631,79.82 C531.831,79.965 532.853,80.001 541,80.001 C549.148,80.001 550.169,79.965 553.369,79.82 C556.562,79.673 558.743,79.167 560.652,78.425 C562.623,77.658 564.297,76.634 565.965,74.965 C567.633,73.296 568.659,71.625 569.425,69.651 C570.167,67.743 570.674,65.562 570.82,62.369 C570.966,59.17 571,58.147 571,50 C571,41.851 570.966,40.831 570.82,37.631"></path></g></g></g></svg></div><div style="padding-top: 8px;"> <div style=" color:#3897f0; font-family:Arial,sans-serif; font-size:14px; font-style:normal; font-weight:550; line-height:18px;">Voir cette publication sur Instagram</div></div><div style="padding: 12.5% 0;"></div> <div style="display: flex; flex-direction: row; margin-bottom: 14px; align-items: center;"><div> <div style="background-color: #F4F4F4; border-radius: 50%; height: 12.5px; width: 12.5px; transform: translateX(0px) translateY(7px);"></div> <div style="background-color: #F4F4F4; height: 12.5px; transform: rotate(-45deg) translateX(3px) translateY(1px); width: 12.5px; flex-grow: 0; margin-right: 14px; margin-left: 2px;"></div> <div style="background-color: #F4F4F4; border-radius: 50%; height: 12.5px; width: 12.5px; transform: translateX(9px) translateY(-18px);"></div></div><div style="margin-left: 8px;"> <div style=" background-color: #F4F4F4; border-radius: 50%; flex-grow: 0; height: 20px; width: 20px;"></div> <div style=" width: 0; height: 0; border-top: 2px solid transparent; border-left: 6px solid #f4f4f4; border-bottom: 2px solid transparent; transform: translateX(16px) translateY(-4px) rotate(30deg)"></div></div><div style="margin-left: auto;"> <div style=" width: 0px; border-top: 8px solid #F4F4F4; border-right: 8px solid transparent; transform: translateY(16px);"></div> <div style=" background-color: #F4F4F4; flex-grow: 0; height: 12px; width: 16px; transform: translateY(-4px);"></div> <div style=" width: 0; height: 0; border-top: 8px solid #F4F4F4; border-left: 8px solid transparent; transform: translateY(-4px) translateX(8px);"></div></div></div> <div style="display: flex; flex-direction: column; flex-grow: 1; justify-content: center; margin-bottom: 24px;"> <div style=" background-color: #F4F4F4; border-radius: 4px; flex-grow: 0; height: 14px; margin-bottom: 6px; width: 224px;"></div> <div style=" background-color: #F4F4F4; border-radius: 4px; flex-grow: 0; height: 14px; width: 144px;"></div></div></a><p style=" color:#c9c8cd; font-family:Arial,sans-serif; font-size:14px; line-height:17px; margin-bottom:0; margin-top:8px; overflow:hidden; padding:8px 0 7px; text-align:center; text-overflow:ellipsis; white-space:nowrap;"><a href="https://www.instagram.com/reel/Ck8KbsQjVrC/?utm_source=ig_embed&amp;utm_campaign=loading" style=" color:#c9c8cd; font-family:Arial,sans-serif; font-size:14px; font-style:normal; font-weight:normal; line-height:17px; text-decoration:none;" target="_blank">Une publication partag??e par Emmanuel Macron (@emmanuelmacron)</a></p></div></blockquote> <script async src="//www.instagram.com/embed.js"></script>
    </p>
    
    </div>
    
    <!-- Critic -->
  <div class="w3-container" style="margin-top:80px" id="critics">
    <h1 class="w3-xxxlarge w3-text-red"><b><u>Critiques</u> :</b></h1>
    <hr style="width:50px;border:5px solid red" class="w3-round">
    <p style="text-align:justify">
        Il existe une v??ritable querelle acad??mique autour de l???empreinte ??cologique, en particulier sa pertinence en tant qu'indicateur pouvant informer l?????laboration de politiques publiques.
    </p>
    <br>
    <h5><pre>Commentaires introductifs :</pre></h5>
    
    <p style="text-align:justify">
        <a href='https://www.sciencedirect.com/science/article/abs/pii/S1470160X14002726?via%3Dihub'>Giampietro et Saltelli</a> (<a href='http://www.andreasaltelli.eu/file/repository/Footprints_to_nowhere_Giampietro_Saltelli_Ecol_ind_2014_PagesNumbers.pdf'>2014</a>) affirment que l'??valuation de l'empreinte ??cologique - pr??tendument utile comme argument contre l'id??e de croissance perp??tuelle - est truff??e de contradictions internes. Leur ??valuation critique est bas??e sur le manque de correspondance entre la s??mantique - l'affirmation de ce que fait la comptabilit?? de l'empeinte ??cologique - et la syntaxe - le protocole de comptabilit?? de l???empeinte ??cologique qui devrait fournir le pr??tendu r??sultat. Pour ces derniers, l???empreinte ??cologique ne sert pas une discussion significative sur la mod??lisation de la durabilit??, et le m??me r??cit favorable aux m??dias sur le jour du d??passement de la Terre est finalement rassurant et complaisant lorsqu'on consid??re d'autres aspects sur la pression de l'homme sur la plan??te et ses ??cosyst??mes.
    </p>
    <p style='text-align:justify'>
        Pour Van den Bergh et Verbruggen (2014), les politiques visant la durabilit?? doivent tenir compte des dimensions spatiales des probl??mes environnementaux et de leurs solutions. En particulier, les configurations spatiales des activit??s ??conomiques m??ritent l'attention, ce qui signifie qu'il faut s'int??resser ?? l'utilisation des sols, aux infrastructures, au commerce et aux transports.
    </p>
    <br>
    <br>
    <h5><pre>Limites conceptuelles :</pre></h5>
    <br>
    <p style='text-align:justify'>
    <ul style='text-align:justify'>
        <li><b>Ce n???est pas une mesure exhaustive de la soutenabilit??.</b> L???empreinte ??cologique mesure seulement si la demande globale humaine entre dans les capacit??s de r??g??n??ration de la plan??te. D'autres dimensions importantes de la soutenabilit?? (bien-??tre humain, qualit?? de l'environnement, etc) ne sont pas prises en compte.</li>
        <br>
        <li><b>Ce n???est pas une pr??diction pour le futur.</b>
Les comptes d'empreinte ??cologique ne font que suivre les activit??s r??elles, comme le fait toute comptabilit??. Ils enregistrent simplement les intrants et les extrants tels quels et ne fournissent aucune extrapolation quant ?? la quantit?? de biocapacit?? qui pourrait ??tre ??puis??e par les activit??s humaines ?? l'avenir.</li>
    <br>
    <li><b>Elle emploie un pragmatisme discutable.</b><ul>
        <li>L???empreinte ??cologique mesure quelque chose d'irr??el : en effet, la superficie terrestre n'est pas litt??ralement utilis??e pour de telles activit??s. L'empreinte ??cologique implique de convertir les flux d'??nergie et de mati??re vers et depuis les activit??s ??conomiques dans une superficie terrestre hypoth??tique qui serait n??cessaire pour maintenir ces flux. La neutralisation des externalit??s environnementales n??gatives n??cessite un ensemble de hypoth??ses, donc lorsque cette neutralisation est traduite en superficie de terres utilis??es, le r??sultat n'est qu'hypoth??tique par nature.</li><br>
        <li>L'utilisation hypoth??tique des terres et la disponibilit?? r??elle des terres sont compar??es. Par exemple, sur la base d'une interpr??tation logique de la m??thodologie de l???empeinte ??cologique, moins de la moiti?? de la superficie des ??tats-Unis plant??e d'eucalyptus pourrait essentiellement nous donner un EF ??gal ?? une Terre. Fiala (2008) ajoute que l'empreinte ne peut pas non plus prendre en compte la production intensive, et donc les comparaisons avec la biocapacit?? sont erron??es.</li>
    </ul>
    </li>
    <br>
    <li><b>C???est une mesure agr??g??e de la r??g??n??ration et de la demande.</b> Ce sont des mesures de r??sultats; en d'autres termes, la r??g??n??ration d'un ??cosyst??me est le r??sultat de l'??tat actuel des sols, de la disponibilit?? de l'eau, de la biodiversit?? et de nombreux autres facteurs. La mesure ne fournit pas de mesures sp??cifiques pour ces facteurs de soutenabilit??, qui sont pourtant des ??l??ments cl??s pour r??soudre des probl??mes ?? multiples facettes. En d???autres termes, l'empreinte ??cologique regroupe des probl??mes environnementaux distincts en utilisant des pond??rations arbitraires et non fond??es (Van den Bergh et Verbruggen 2014). Ces pond??rations ne correspondent pas ?? une logique physique ou chimique, ni ?? des valeurs sociales ou ??conomiques (utilit?? ou bien-??tre).</li>
    <br>
    <li><b>L'utilisation de la notion d???hectares globaux contribue au caract??re hypoth??tique de l'empreinte ??cologique.</b> C???est une productivit?? moyenne ?? l'??chelle mondiale, destin??e ?? simplifier et refl??ter la productivit?? variable entre les endroits du monde. Elle varie donc ?? la fois au fil du temps et entre les endroits du monde. Bien que tr??s simpliste, la clart?? de l???approche est discutable.</li>
    <br>
    <li>
        <b>La comptabilit?? de l'empreinte implique que l'utilisation des terres est la variable de durabilit?? la plus importante.</b> Il s'agit de "th??oriser" la valeur des terres. L'utilisation des terres est consid??r??e comme proxy de la pression environnementale. Ainsi, la r??duction des externalit??s n??gatives environnementales ?? la superficie des terres se r??sume ?? une th??orie implicite de la valeur des terres, qui fait de la raret?? des terres une pr??occupation ant??rieure, et l'emporte sur tous les autres probl??mes. Selon Van den Bergh et Verbruggen (2014), supposer que les mod??les de production et de consommation sont limit??s uniquement par la disponibilit?? des terres revient ?? sugg??rer que la politique fonci??re est la principale r??ponse publique ?? la non-durabilit??". Utiliser les terres comme proxy des pressions environnementales a du sens pour l'agriculture (?? l'exception des pesticides et des engrais concentr??s), mais pas pour d'autres secteurs, comme l'industrie ou les services.
    </li>
    <br>
    <li>
        <b>Le calcul de la composante de l'empreinte carbone est bas?? sur un "sc??nario ??nerg??tique durable" arbitraire.</b> Pour la plupart des pays d??velopp??s, environ la moiti?? de la valeur de l'empreinte ??cologique est le r??sultat de la transformation de la question du r??chauffement climatique d'origine humaine par les ??missions de CO2 en surface terrestre - en r??alit??, des terres purement hypoth??tiques. Est ici ??mise l???hypoth??se d'un sc??nario ??nerg??tique dit durable dans lequel le CO2 est capt?? par la plantation d'arbres ou le reboisement.
        <ul>
            <li>Prolongeant ces conclusions, Blomqvist et al. (<a href='https://www.google.com/url?q=https://www.ncbi.nlm.nih.gov/pmc/articles/PMC3818165/&sa=D&source=docs&ust=1669239179620093&usg=AOvVaw0ijf8MGFQxZpN0jWtm_T8x'>2013</a>) confirme que l'ensemble du d??passement ??cologique mondial (empreinte de la consommation d??passant la biocapacit??) r??sulte des ??missions de dioxyde de carbone, recadr??es comme la surface foresti??re hypoth??tique n??cessaire pour compenser ces ??missions. Les plantations d'arbres ?? croissance rapide permettraient, selon les chiffres, d'??liminer le d??passement global.</li>
            <br>
            <li>Ils en concluent que nous ferions mieux de discuter des ??missions de gaz ?? effet de serre directement en termes de tonnes d'??quivalent CO2 (et donc de nous concentrer sur les solutions aux ??missions), et de d??velopper un cadre plus ??cologique et de processus ??cosyst??miques pour saisir les impacts que les humains ont actuellement sur les syst??mes naturels de la plan??te. L'??chelle appropri??e pour ces indicateurs sera, dans de nombreux cas, locale et r??gionale. ?? ce niveau, l???empreinte ??cologique est une mesure des exportations ou importations nettes de biomasse et de la capacit?? d'absorption du carbone.</li><br>
            <li>Toute ville, par exemple, pr??senterait un d??ficit, car elle d??pend de la nourriture et des mat??riaux provenant de l'ext??rieur. En soi, comme l'a not?? Robert Costanza, "cela ne nous dit pas grand-chose sur la durabilit?? de cet apport [de l'ext??rieur de la r??gion] dans le temps".</li><br>
        </ul>
    </li>
    <br>
    <li><b>Les applications de l'empreinte se concentrent sur les pays plut??t que sur les ???bior??gions???.</b> Ainsi, les empreintes ??cologiques nationales n'ont pas beaucoup de sens, car les fronti??res des pays sont d??termin??es par des facteurs historico-politiques qui ne refl??tent pas n??cessairement la pertinence ??cologique. Fiala (2008) confirme que l'empreinte suppose arbitrairement des fronti??res nationales, ce qui ???rend probl??matique l'extrapolation ?? partir de l'empreinte ??cologique moyenne???.</li><br>
    <li>
        <b>La mesure des d??ficits ??cologiques nationaux soutient les sentiments anti-commerce.</b> L'application de l???empreinte ??cologique ?? des r??gions ou des pays donne lieu ?? la notion de d??ficit ??cologique, qui est facilement mal interpr??t??e et soutient les sentiments anti-commerce.
    </li>
    </ul>
    </p>
    <h5><pre>Limites m??thodiques :</pre></h5>
    <br>
    <p style='text-align:justify'>
        Au-del?? d???utiliser une notion contest??e en soi, les m??thodes de calcul utilis??es par le Global Footprint Network ont ??galement fait l???objet de critiques.
        <br>
    <ul style='text-align:justify'>
        <li><b>L'empreinte ne capture pas toutes les pressions environnementales pertinentes.</b> Les omissions de pressions environnementales font que l???empreinte ??cologique sous-estime la ???pression environnementale globale r??elle???, c'est-??-dire l'impact humain sur la biosph??re. Par exemple, la pollution de l'eau, les ??missions de substances toxiques (y compris les m??taux lourds), la pollution sonore, l'appauvrissement de la couche d'ozone, les pluies acides, la fragmentation des ??cosyst??mes r??sultant de l'utilisation des sols et des infrastructures routi??res et, plus g??n??ralement la biodiversit??, ne sont pas pris en compte par l'approche. De m??me, Fiala (2008) et Blomqvist et al. (2013) identifient que les ??valuations de l'empreinte ??cologique pour les terres cultiv??es, les p??turages et les terrains b??tis ne permettent pas de prendre en compte la d??gradation des sols ou l'utilisation non durable de quelque nature que ce soit.</li>
        <br>
        <li>
            <b>Elle sous-estime tr??s probablement le d??passement global.</b> Les comptes d'empreintes ??cologiques des pays sont strictement bas??s sur les statistiques des Nations Unies. Ces statistiques peuvent ne pas inclure tous les postes de consommation, et les donn??es de biocapacit?? bas??es sur ces statistiques peuvent surestimer la productivit?? ?? long terme, puisque l'impact de la d??forestation, de l'??puisement des sols ou de la p??nurie d'eau sur la productivit?? future n'est pas pris en compte.
        </li>
    </ul>
    </p>
    <br>
    <h5><pre>Le manque d???utilit?? pour informer les politiques publiques</pre></h5>
    <br>
    <p style='text-align:justify'>Except?? un tr??s vague consensus selon lequel ???nous devrions limiter la consommation???, la mesure souffre de son manque de caract??re prescriptif. L???empreinte n'est donc pas en mesure d'apporter beaucoup de lumi??re sur les choix politiques, y compris la s??lection des strat??gies (par exemple, les solutions technologiques ou la r??organisation spatiale de l'??conomie) ou des instruments politiques (par exemple, les instruments de commande et de contr??le par rapport aux instruments bas??s sur le march??). La question est de savoir si nous avons vraiment besoin de regrouper les informations sur les diff??rents probl??mes environnementaux dans des indicateurs uniques. La prise de d??cision en mati??re de politique publique est mieux ??clair??e par des indicateurs concrets pour des probl??mes sp??cifiques. Le risque est que plupart des utilisateurs innocents, de mani??re compr??hensible, interpr??tent l'empreinte ??cologique comme un indicateur global d'environnement ou de (non)durabilit??</p>
    <br>
    <h5><pre>Des approches de calcul alternatives existent</pre></h5>
    <br>
    <p style='text-align:justify'>
        En voici un sommaire :
    </p><br>
    <p align='center'>
    <img src="/images/alternatives.jpeg" alt="Origine g??ographique des rapports" style="width:100%;border-style:inset;border-color:#f44336;">
    </p>
    
    </div>

    <!-- conclusions -->
  <div class="w3-container" style="margin-top:80px" id="coda">
    <h1 class="w3-xxxlarge w3-text-red"><b><u>Conclusions</u> :</b></h1>
    <hr style="width:50px;border:5px solid red" class="w3-round">
    <br>
    <p style="text-align:justify">
        <ul style="text-align:justify">
            <li>Les indicateurs ??cologiques doivent, dans la mesure du possible, inclure des estimations de l'incertitude.</li>
            <br>
            <li>Les indicateurs doivent tenir compte de l'??chelle g??ographique des ph??nom??nes qu'ils mesurent.</li>
            <br>
            <li>Un ensemble d'indicateurs, chacun se rapportant ?? une forme identifiable et quantifiable de capital naturel ou de service ??cosyst??mique, sera probablement plus compr??hensible et plus utile qu'un seul indice global.</li>
            <br>
            <li>Les indicateurs de la durabilit?? de la consommation de capital naturel doivent pouvoir enregistrer l'??puisement ou les exc??dents. </li>
            <br>
            <li>Les indicateurs doivent mettre en ??vidence les voies ?? suivre pour atteindre les objectifs de durabilit?? qui sont ?? la fois ??cologiques et de bon sens.</li>
        </ul>
    </p>
    </div>
  
  <!-- API call -->
    <div class="w3-container" id="search_data" style="margin-top:75px">
      <h1 class="w3-xxxlarge w3-text-red"><b><u>Exploiter les donn??es</u> :</b></h1>
      <hr style="width:50px;border:5px solid red" class="w3-round">
      <form action="" method="post">
        <p style="text-align:justify">La base de donn??e du Global Footprint Network est en libre acc??s et permet ?? chacun??e de l'exploiter ?? des fins plus ou moins sp??cifiques. Nous avons voulu illustrer cette polyvalence en faisant de notre rendu une interface permettant d'acc??der aux donn??es de chaque pays inscrits dans la base de donn??es.</p>

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
          <b>Vous pouvez s??lectionner une ann??e pr??cise :</b> (Facultatif)
        </p>
        <br>
        <p align='center'>
          <?php
            deroulant('Year',$all_years,'    ',true);
          ?>
        </p>
        <br>
        <p align='center'>
          <b>Vous pouvez s??lectionner les donn??es retourn??es <u>pour cette ann??e</u> :</b> 
          (N'en cocher aucune retournera toutes les donn??es)
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
          <button class="w3-button w3-block w3-padding-large w3-red w3-margin-bottom" type="submit" name=submit onclick="w3_close()">Je valide ces crit??res de recherche</button>
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
            <li>Blomqvist, Linus, et al. ???Does the Shoe Fit? Real versus Imagined Ecological Footprints.??? <i>PLoS Biology</i>, vol. 11, no. 11, 2013, <a href='https://doi.org/10.1371/journal.pbio.1001700'>https://doi.org/10.1371/journal.pbio.1001700</a>.</li><br>
            <li>Fiala, Nathan. ???Measuring Sustainability: Why the Ecological Footprint Is Bad Economics and Bad Environmental Science.??? <i>Ecological Economics</i>, vol. 67, no. 4, 2008, pp. 519???525., <a href='https://doi.org/10.1016/j.ecolecon.2008.07.023'>https://doi.org/10.1016/j.ecolecon.2008.07.023</a>.</li><br>
            <li>
                Giampietro, Mario, and Andrea Saltelli. ???Footprints to Nowhere.??? <i>Ecological Indicators</i>, vol. 46, 2014, pp. 610???621., <a href='https://doi.org/10.1016/j.ecolind.2014.01.030'>https://doi.org/10.1016/j.ecolind.2014.01.030</a>. 
            </li><br>
            <li>
                Van den Bergh, Jeroen C.J.M, and Fabio Grazi. ???Ecological Footprint Policy? Land Use as an Environmental Indicator.??? <i>Journal of Industrial Ecology</i>, vol. 18, no. 1, 2014, pp. 10???19., <a href='https://doi.org/10.1111/jiec.12045'>https://doi.org/10.1111/jiec.12045</a>.
            </li><br>
            <li>Van den Bergh, Jeroen C.J.M., and Fabio Grazi. ???Reply to the First Systematic Response by the Global Footprint Network to Criticism: A Real Debate Finally???? <i>Ecological Indicators</i>, vol. 58, 2015, pp. 458???463., <a href=''>https://doi.org/10.1016/j.ecolind.2015.05.007</a>.</li><br>
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
    <p class="w3-right">Ce site et l'ensemble de son contenu ont ??t?? produits par Marianne MAUREL, Ulysse RICHARD et Mano?? M??vellec.</p><br>
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
