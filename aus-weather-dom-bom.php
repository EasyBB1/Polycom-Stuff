<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
   <head>
      <meta charset="UTF-8">
      <title></title>
   </head>
   <body>
      <?php
      /*
       * Script written by EasyBB of Whirlpool forums.
       * This script displays Australian weather information on Polycom phone
       * idle screen. Datasource is Bureau of Meteorology, Australia.
       * Script uses PHP DOM html parser and is generally available by default.
       * Please post your comments at http://whrl.pl/ReieJd
       * Please don't run the script too frequently; I recommend min 1 hr.
       */
      
      // Fill in your State and City below; string must be within the quotes.
         $state = 'ACT';
         $city = 'Canberra Central';
         $fcdays = 4; // Max Forecast Days- depends on the size of device screen.

            /****************************************/
            /* DO NOT EDIT ANYTHING BELOW THIS LINE */
            /****************************************/
         $state_scr = str_replace(' ', '', trim(strtolower($state)));
         $city_scr = str_replace(' ', '-', trim(strtolower($city)));
         
         $url = 'http://www.bom.gov.au/places/'.$state_scr.'/'.$city_scr;
         
         // Create new DOM object
         $doc = new DOMDocument();
         $doc->preserveWhiteSpace = false;
      
         // Fix/mask html load errors
	   ini_set('user_agent', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36');
	   libxml_use_internal_errors(true);
         
         // Load the html URL
         $doc->loadHTMLFile($url) or exit('URL error: '.$url);
         
         // Create new Xpath object
         $xpath = new DOMXPath($doc);
         
         // Get current temperature
         $query_ct = '//li[@class="summary"][@id="summary-1"]/ul/li[@class="airT"]';
         $cts = $xpath->query($query_ct);
         foreach($cts as $ct)
         {
            $cur_temp = $ct->nodeValue;
         }
         
         // Get current temperature last updated time
         $query_at = '//div[@class="wrapper"]/ul/li';
         $ats = $xpath->query($query_at);
         foreach ($ats as $at)
         {
            $nodes = $at->childNodes;
            foreach ($nodes as $node)
            {
               if ($node->nodeName == 'h3')
               {
                  $time_ar = explode(' ', trim($node->nodeValue));              
                  break;
               }
            }
         }
         $reading_at = $time_ar[count($time_ar)-1];

         // Get forecast days
         $query_dt = '//dl[@class="forecast-summary"]/dt[@class="date"]/a';
         $dates = $xpath->query($query_dt);
         foreach ($dates as $date)
         {
            $ex = explode(' ', trim($date->nodeValue));
            $dates_ar[] = $ex[0];
         }
         $dates_ar[0] = 'Today';
         
         // Get short forecast
         $query_fc = '//dl[@class="forecast-summary"]/dd[@class="image"]/a/img';
         $forecasts = $xpath->query($query_fc);
         foreach ($forecasts as $fc)
         {
            $fc_ar[] = trim($fc->getAttribute('alt'));
         }
         
         // Get minimum temperature for forecast days
         $query_min = '//dl[@class="forecast-summary"]/dd[@class="min"]';
         $mins = $xpath->query($query_min);
         foreach ($mins as $min)
         {
            $min_ar[] = trim($min->nodeValue);
         }
         if (count($min_ar) < count($dates_ar))
         {
            array_unshift($min_ar, '- °C');
         }
         
         // Get maximum temperature for forecast days
         $query_max = '//dl[@class="forecast-summary"]/dd[@class="max"]';
         $maxs = $xpath->query($query_max);
         foreach ($maxs as $max)
         {
            $max_ar[] = trim($max->nodeValue);
         }
         if (count($max_ar) < count($dates_ar))
         {
            array_unshift($max_ar, '- °C');
         }
         
         // Generate html to display
         echo 'Weather: '. $city . ', ' . $state . '<br />';
         echo 'Current: ' . $cur_temp.' @ '. $reading_at .'<br />';
         //echo 'Day : Outlook  | Temp-Min / Max  <br />';
         for ($i = 0; $i <= $fcdays - 1; $i ++)
         {
            echo $dates_ar[$i].': '.$fc_ar[$i].' | '.$min_ar[$i].' / '.$max_ar[$i].'<br />';
         }

      ?>
   </body>
</html>
