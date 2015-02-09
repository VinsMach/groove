
<?php
$csv = new arrayToCsv();
$arr_intestazione = array(array("desc_home","IMG","prezzo_da","content","luogo","nome_te","nome_evento","orario_inizio","data_inizio","data_fine","prezzo","link"),array());

$file = './report.csv';
$current = file_get_contents($file);
$current_txt = $csv->convert($arr_intestazione);
file_put_contents($file, $current_txt);


class arrayToCsv{
	protected $delimiter;
	protected $text_separator;
	protected $replace_text_separator;
	protected $line_delimiter;
	
	public function __construct($delimiter = ",", $text_separator = '"', $replace_text_separator = "'", $line_delimiter = "\n"){
		$this->delimiter              = $delimiter;
		$this->text_separator         = $text_separator;
		$this->replace_text_separator = $replace_text_separator;
		$this->line_delimiter         = $line_delimiter;
	}
	
	public function convert($input) {
		$lines = array();
		foreach ($input as $v) {
			$lines[] = $this->convertLine($v);
		}
		return implode($this->line_delimiter, $lines);
	}
	
	private function convertLine($line) {
		$csv_line = array();
		foreach ($line as $v) {
			$csv_line[] = is_array($v) ? 
					$this->convertLine($v) : 
					$this->text_separator . str_replace($this->text_separator, $this->replace_text_separator, $v) . $this->text_separator;
		}
		return implode($this->delimiter, $csv_line);
	}

}

 
$fin_array = array();
$fin_array_2 = array();
$i=0;
	
function source_crawling($link){
	
	$curl = curl_init($link);
 	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
 	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
 	curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/8.0.552.224 Safari/534.10');
 	$html = curl_exec($curl);
 	curl_close($curl);

 	if (!$html) {
    	die("something's wrong 1 !");
 	}

 	$dom = new DOMDocument();
 	@$dom->loadHTML($html);

 	$xpath = new DOMXPath($dom);
 	$scores = array();
 	$tableRows = $xpath->query('//tr[contains(@class,"line")]');
 	$next_page = "";

 	$node_page = $xpath->query("//ul[contains(@class,'footerPager')]/li[contains(@class,'current')]/following-sibling::*[1]/a[contains(text(),'2')]/@href");
 	
 	if($node_page->item(0)){
 		$next_page=$node_page->item(0)->nodeValue;
 	}
 	
 	$csv = new arrayToCsv();
 	echo $i++;
 	
 	foreach ($tableRows as $row) {
    	$match = array();
	
		$img = "";
		$nodelist = $xpath->query('td[contains(@class,"taImage")]//img/@src', $row); // find your image
	
		if($nodelist->item(0)); // gets the 1st image
		$img = $nodelist->item(0)->nodeValue;
	
		if($img){
			$match['IMG'] ="".$img;
		}
	
		$match['titolo'] = $xpath->query('td//a', $row)->item(1)->getAttribute('title');
		$match['data'] = trim($xpath->query('td//dl', $row)->item(0)->textContent);
		$match['contenuto'] = trim($xpath->query('td[contains(@class,"taCommunity")]//p', $row)->item(0)->textContent);
		$match['prezzo'] = substr(trim($xpath->query('td[contains(@class,"taPrice")]//dt', $row)->item(1)->textContent),strpos(trim($xpath->query('td[contains(@class,"taPrice")]//dt', $row)->item(1)->textContent),'€'));
		$match['URL'] = "".$xpath->query('td//a', $row)->item(1)->getAttribute('href');
		$fin_array=source_crawling_link($match);
		//$scores[] = $match;
		
		$text_app .= $csv->convert($fin_array);
	}
	
	file_put_contents('./report.csv', $text_app, FILE_APPEND);
	
	if ($next_page !=""){
		echo "".$next_page."\n";
		source_crawling("".$next_page);
	}
	else {
		//return $scores;
		return;
	}
	
}

function source_crawling_link($arr_list){
	
	$curl = curl_init($arr_list['URL']);
 	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
 	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
 	curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/8.0.552.224 Safari/534.10');
 	$html = curl_exec($curl);
 	curl_close($curl);

 	if (!$html) {
    	die("something's wrong 2 !");
 	}

	$dom = new DOMDocument();
 	@$dom->loadHTML($html);

 	$xpath = new DOMXPath($dom);
	$scores = array();
	$tableRows = $xpath->query('//tr[contains(@valign,"middle")]');
	$cont2= trim($xpath->query("//div[contains(@class,'eveJs') and not(contains(@class,'defBox'))]/text()[2]", $row)->item(0)->nodeValue);
	$cont1= trim($xpath->query("//div[contains(@class,'eveJs') and not(contains(@class,'defBox'))]/text()[1]", $row)->item(0)->nodeValue);
	$cont3= trim($xpath->query("//div[contains(@class,'eveJs') and not(contains(@class,'defBox'))]/text()[3]", $row)->item(0)->nodeValue);

  	download_image(str_replace("60x60","222x222",trim($arr_list['IMG'])),str_replace(" ","-",trim($arr_list['titolo'])).".jpg");
 	
 	foreach ($tableRows as $row) {
    	$match = array();
	
		$match['contenuto_home']	= trim($arr_list['contenuto']);
	
		$match['IMG']	= "-";
		$match['prezzo_da_a']	= trim($arr_list['prezzo']);
	
		$title_app=explode("-", trim($arr_list['titolo']));
		$match['conten_desc']  = trim($cont1 ." " . $cont2 . " ".$cont3);
		$match['luogo'] = trim($xpath->query('td[contains(@class,"col-location")]/span/text()[1]', $row)->item(0)->nodeValue);
		$match['teatro'] = trim($xpath->query('td[contains(@class,"col-location")]/span/text()[2]', $row)->item(0)->nodeValue);
		$match['Nome_Evento']	= trim($title_app[0]).' '.$match['teatro']. ' '.  $match['luogo'];
		$match['orario'] = trim($xpath->query('td[contains(@class,"col-date")]//text()[2]', $row)->item(0)->nodeValue);
		$data = trim(strstr(trim($xpath->query('td[contains(@class,"col-date")]//text()[1]', $row)->item(0)->nodeValue),' '));
		$data_app =explode("/", $data);
		$match['data_inizio']  = "20".$data_app[2].'-'.$data_app[1].'-'.$data_app[0];
		$match['data_fine'] = "20".$data_app[2].'-'.$data_app[1].'-'.$data_app[0];
		$match['prezzo'] = trim(strstr(trim($xpath->query("td[contains(@class,'col-date')]/following-sibling::td[1]//span/text()", $row)->item(0)->nodeValue),' '));
		
		$scores[] = $match;
		//$fin_array_2=$match;
	
	}
	
	return $scores;
}

function download_image($url,$name_file){
	$img = './img/';
	file_put_contents($img.$name_file, file_get_contents($url));
}

?>
	