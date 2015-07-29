<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head></head>
    <body>
		<?php
        /***************** EasyBB - Whirlpool Forum ****************************
        This script is for displaying weather forecast on the idle screen
        of Polycom Soundpoint phones using inbuilt Microbrowser. Weather information
        is based upon Australian postcode information. Be aware that some postcodes
        cross state boundaries, so displayed 'location' may be a neighbouring city.
        Do not set the phone to update too frequently; recommended minimum 3600s.
        Test the script first on your computer to see errors if any.
        This script is provided as is, without any warranty expressed or implied.
        ************************************************************************/
        //Enter your postcode below with in the single quotes.
        $postcode = '2600'; //Postcode must be 4 digits long; prefix 0 if needed.
        //Enter your custom City and State names below, if and only if necessary.
        //This will only change the display; weather info is still based on postcode.
        $mycity = ''; //Fill in to override displayed city name.
        $mystate = ''; //Fill in to override displayed state.
            /****************************************/
            /* DO NOT EDIT ANYTHING BELOW THIS LINE */
            /****************************************/
        function checkPostcode($postcode){
            if (is_numeric($postcode)){
                if (strlen($postcode) != 4){
                    exit("Only 4 digit postcodes are accepted, eg: 0870, 2605.");
                }else{
                    return true;
                }
            }else{
                exit("Only digits are allowed as postcode.");
            }
        }
        // Gets data from a URL
		function getData($url) {
			$ch = curl_init();
			$timeout = 5;
			$userAgent = 'Mozilla/5.0 (X11; Linux i586; rv:31.0) Gecko/20100101 Firefox/31.0';
			curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			curl_setopt($ch, CURLOPT_FAILONERROR, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_AUTOREFERER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			$data = curl_exec($ch);
			curl_close($ch);
			return $data;
		}
        function getState($postcode){
			$url = 'http://www.auspost.com.au/postcode/'.$postcode;
            //$data = file_get_contents($url) or exit("Unable to open url: <br/>".$url);
            $data = getData($url) or exit("Unable to open url: <br/>".$url);
            //echo $data;
            $regex = "/<td class=\"first\"><a class=\"result(?:.*)\" href=\"\/(?:.*)\/(?:.*)\/(.*?)\/(?:.*)\">".$postcode."<\/a>/";
            preg_match($regex, $data, $state);
            $states = array('act','nsw','nt','qld','sa','tas','vic','wa');
            if (in_array($state[1], $states,true)){
                return $state[1];
            }else{
                exit("Error finding state.");
            }
        }
        if (checkPostcode($postcode)){
            $state = getState($postcode);
        }else{
            exit("There is an issue. Please check regex.");
        }
        function getWeather($state, $postcode, $mycity, $mystate){
            //Generate url using state and postcode
            $url = 'https://weather.yahoo.com.au/local-forecast/'.$state.'/'.$postcode;
            //$data = file_get_contents($url) or exit("Unable to open weather url: <br/>".$url);
            $data = getData($url) or exit("Unable to open weather url: <br/>".$url);
            // Get weather location name
            $regex = "/<span id='qualified_location_name' style='display: none;'>(.*?)<\/span>/";
            preg_match($regex,$data,$loc);
            $autoloc = $loc[1];
            if (empty($autoloc)){
                exit("Unable to fetch weather for postcode: ".$postcode);
            }
            $myloc = $mycity.",".$mystate;
            $mycity = trim($mycity);
            $mystate = trim($mystate);
            if ((!empty($mycity)) && (!empty($mystate))) {
                if ($autoloc != $myloc){
                    $loc[1] = $myloc;
                }
            }
            // Get current temperature
            $regex = "/<span class='current'>(.*?)°<\/span>/";
            preg_match($regex,$data,$curr);
            // Get forecast days
            $regex = "/<tr>\n<th class='empty'>(?:.*)<\/th>\n<th colspan='2' scope='col'>(.*?)<\/th>\n<th colspan='2' scope='col'>(.*?)<\/th>\n<th colspan='2' scope='col'>(.*?)<\/th>/";
            preg_match($regex,$data,$day);
            // Get minimum temperature for forecast days	
            $regex = "/<tr>\n<th scope='row'>Minimum<\/th>\n<td colspan='2'>\n<span class='min'>(.*?)°<\/span>\n<\/td>\n<td colspan='2'>\n<span class='min'>(.*?)°<\/span>\n<\/td>\n<td colspan='2'>\n<span class='min'>(.*?)°<\/span>/";
            preg_match($regex,$data,$min);
            // Get maximum temperature for forecast days
            $regex = "/<tr>\n<th scope='row'>Maximum<\/th>\n<td colspan='2'>\n<span class='max'>(.*?)°<\/span>\n<\/td>\n<td colspan='2'>\n<span class='max'>(.*?)°<\/span>\n<\/td>\n<td colspan='2'>\n<span class='max'>(.*?)°<\/span>/";
            preg_match($regex,$data,$max);
            // Get outlook for forecast days
            $regex = "/<th scope=\'row\'>Summary<\/th>\n<td colspan=\'2\'>\n<img (?:.*?) title=\"(.*?)\" \/>\n<\/td>\n<td colspan=\'2\'>\n<img (?:.*?) title=\"(.*?)\" \/>\n<\/td>\n<td colspan=\'2\'>\n<img (?:.*?) title=\"(.*?)\" \/>/";
            preg_match($regex,$data,$outlook);
            // Generate html to display on screen
            $res = $loc[1]." ".$postcode."<br/>";
            $res .= "Current Temp: ".$curr[1]." deg Celsius <br/>";
            $res .= "Forecast(Outlook/Temp-min/max):<br/>";
            for($i = 1; $i <= count($day)-1; $i++){
				$res .= $day[$i].": ".$outlook[$i]." / ".$min[$i]." / ".$max[$i]."<br/>";
			}
            return $res;
        }
        echo getWeather($state, $postcode, $mycity, $mystate);
        ?>
    </body>
</html>
