<?PHP
  // Original PHP code by Chirp Internet: www.chirp.com.au
  // Please acknowledge use of this code by including this header.


  getDirContents(".",0);

  function getDirContents($dir,$level=0){
        $results = array();
        $files = scandir($dir);
        foreach($files as $key => $value){
          if(!is_dir($dir. DIRECTORY_SEPARATOR .$value)){
            $path_parts = pathinfo($value);
            if ($path_parts['extension'] == "html") {
              parsePage($dir . DIRECTORY_SEPARATOR . $value,$level);
            }
          } else if (is_dir($dir. DIRECTORY_SEPARATOR .$value) && $value != "." && $value != "..") {
            getDirContents($dir. DIRECTORY_SEPARATOR .$value,$level+1);
          }
        }
  }

  //parsePage("news/index.html",1);

  function parsePage($url,$level) {
    $input = @file_get_contents($url) or die("Could not access file: $url");
    $output = $input;
    $regexp = "href=(\"??)([^\" >]*?)";
    if(preg_match_all("/$regexp/siU", $input, $matches, PREG_SET_ORDER)) {
      foreach($matches as $match) {
        $output = processLink($match[2],$output,$level);
      }
    }
    $regexp = "src=(\"??)([^\" >]*?)";
    if(preg_match_all("/$regexp/siU", $input, $matches, PREG_SET_ORDER)) {
      foreach($matches as $match) {
        $output = processLink($match[2],$output);
      }
    }
    $regexp = "content=(\"??)([^\" >]*?)";
    if(preg_match_all("/$regexp/siU", $input, $matches, PREG_SET_ORDER)) {
      foreach($matches as $match) {
        $output = processLink($match[2],$output);
      }
    }

    $output = replaceWithRelative($output,$level);

    file_put_contents($url, $output);
  }

  function replaceWithRelative($output,$level) {

    $replace = "";
    for ($i=0;$i<=$level;$i++) {
      if ($i>0) {
        $replace = $replace . "../";
      }
    }
    echo $replace;

    //Catch all remaining
    $output = str_replace("www.theodi.org", "oldsite.theodi.org", $output);
    $output = str_replace("\"theodi.org", "\"oldsite.theodi.org", $output);

    $output = str_replace("http://theodi.org/", "http://oldsite.theodi.org/", $output);
    $output = str_replace("https://theodi.org/", "https://oldsite.theodi.org/", $output);
    $output = str_replace("//theodi.org/", "//oldsite.theodi.org/", $output);
    
    $output = str_replace("https://www.theodi.org/", "https://oldsite.theodi.org/", $output);
    $output = str_replace("//www.theodi.org/", "//oldsite.theodi.org/", $output);

    //Make relative
    $output = str_replace("http://oldsite.theodi.org/", $replace, $output);
    $output = str_replace("https://oldsite.theodi.org/", $replace, $output);
    $output = str_replace("//oldsite.theodi.org/", $replace, $output);
    $output = preg_replace('/(href=")(\/)([^\/])/i', '${1}' . $replace . '${3}', $output);
    $output = preg_replace('/(src=")(\/)([^\/])/i', '${1}' . $replace . '${3}', $output);

    return $output;
  }

  

  function processLink($link,$output) {
    echo "Processing " . $link . "\n";
    if (strpos($link,"//theodi.org") !== false) {
      echo "Found theodi.org" . "\n";
      $newlink = str_replace("//theodi.org", "//oldsite.theodi.org", $link); 
      echo "New link " . $newlink . "\n\n";
    } else if (strpos($link,"//www.theodi.org") !== false) {
      echo "Found theodi.org" . "\n";
      $newlink = str_replace("//www.theodi.org", "//oldsite.theodi.org", $link); 
      echo "New link " . $newlink . "\n\n";
    } else if (strpos($link,"static.theodi.org") !== false) {
      echo "Found static.theodi.org" . "\n";
      downloadFile($link);
      $newlink = str_replace("static.theodi.org", "oldsite.theodi.org", $link); 
      $output = replaceLink($link,$newlink,$output);
      echo "\n";
    } else if (strpos($link,"rackcdn") !== false) {
      echo "Found rackcdn" . "\n";
      downloadFile($link);
      $newlink = str_replace(getTld($link), "oldsite.theodi.org", $link); 
      $output = replaceLink($link,$newlink,$output);
    } else {
      echo "NOT PROCESSING" . "\n\n";
    }
    return $output;
  }


  function getTld($link) {
    $parts = explode("/", $link);
    return $parts[2];
  }

  function downloadFile($link) {
    if (substr($link,0,2) == "//") {
      $link = "http:" . $link;
    }
    $cmd = "wget -nc -nH -x " . $link;
    exec($cmd);
  }

  function replaceLink($old,$new,$output) {
    $output = str_replace($old, $new, $output);
    return $output;
  }

?>
