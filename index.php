<!DOCTYPE html>
<html>
  <head>
    <title>Ikea's discounts</title>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
    <link href="./bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
  </head>
  <body>
<?php

class GrabIkeaSale
{
	private $_name;
	private $_url;
	private $_data = array();
	
	public function __construct ($data)
	{
		$this->_name = $data["name"];
		$this->_url = $data["url"];
	}
	
	public function importData ()
	{
		$doc = new DOMDocument();
		$load = @$doc->loadHTMLFile($this->_url);
		
		$elements = $doc->getElementsByTagName('div');
		if (!is_null($elements)) {
			foreach ($elements as $element) {
				if ($element->getAttribute("class") == "productPadding") {
					$item = array();
					
					$img = $element->getElementsByTagName('img')->item(0);
					$item["image"] = $img->getAttribute("src");
										
					$nodes = $element->getElementsByTagName('span');
					foreach ($nodes as $node) {
						if ($node->getAttribute("class") == "prodName prodNameTrolocalStore") { 
							$item["name"] = $node->nodeValue;
						}
						if ($node->getAttribute("class") == "prodDesc") { 
							$item["description"] = $node->nodeValue;
						}
						if ($node->getAttribute("class") == "prodOldPrice") { 
							$item["oldPrice"] = $node->nodeValue;
						}
						if ($node->getAttribute("class") == "prodPrice") { 
							$item["price"] = $node->nodeValue;
						}
					}
					
					if (!empty($item["name"])) {
						$this->_data[$item["name"] . $item["description"] . $item["oldPrice"]] = $item;
					}
				}
			}
		}
	}
	
	public function getData ()
	{
		return $this->_data;
	}
}

class GrabIkeaData
{
	private $_pages = array(
		"belaya_dacha" => array("name" => "Белая Дача", "url" => "http://www.ikea.com/ru/ru/store/belaya_dacha/sale"),
		"teply_stan" => array("name" => "Теплый Стан", "url" => "http://www.ikea.com/ru/ru/store/teply_stan/sale"),
		"khimki" => array("name" => "Химки", "url" => "http://www.ikea.com/ru/ru/store/khimki/offers")
	);
	private $_data = array();
	private $_output = array();

	public function __construct ()
	{
		if (empty($this->_data)) {
			$this->collectData();
		}
	}

	public function collectData()
	{
		foreach ($this->_pages as $k => $p) {
			$sale = new GrabIkeaSale($p);
			$sale->importData();
			$this->_data[$k] = $sale->getData();
		}
	}

	private function prepareToOutput ()
	{
		foreach ($this->_pages as $k => $p) {
			foreach ($this->_data[$k] as $kitem => $item) {
				if (empty($this->_output[$kitem])) {
					$this->_output[$kitem] = array(
						"name" => '<img src="http://www.ikea.com' . $item["image"] . '" border="0" />' . $item["name"],
						"description" => $item["description"],
						"oldPrice" => str_replace(array(".", "–", " ", " "), "", trim($item["oldPrice"])),
						"price" => array()
					);
				}
				$this->_output[$kitem]["price"][$k] = str_replace(array(".", "–", " ", " "), "", trim($item["price"]));
			}
		}
	}

	public function displayData ()
	{
		if (empty($this->_output)) {
			$this->prepareToOutput();
		}

		echo '<table class="table table-condensed table-hover">';
		echo "<caption>Скидки в магазинах ИКЕА Москва</caption><thead><tr>";
		echo "<th>Название</th>";
		echo "<th>Описание</th>";
		echo "<th>Старая цена</th>";
		foreach ($this->_pages as $p) {
			echo "<th>" . $p["name"] . "</th>";
		}
		echo "</tr></thead><tbody>";
		foreach ($this->_output as $item) {
			echo "<tr>";
			echo "<td>" . $item["name"] . "</td>";
			echo "<td>" . $item["description"] . "</td>";
			echo '<td class="muted">' . $item["oldPrice"] . "</td>";
			$min = array_values($item["price"]);
			sort($min, SORT_NUMERIC);
			
			foreach ($this->_pages as $k => $p) {
				echo "<td" . (!empty($item["price"][$k]) && $item["price"][$k] == $min[0] ? " class='text-error'" : "") . ">";
				if (!empty($item["price"][$k])) {
					echo $item["price"][$k];
					echo ' <span class="label label-success">' . (100 - round(($item["price"][$k] * 100) / $item["oldPrice"])) . '%</span>';
				} else {
					echo "&mdash;";
				}
				echo "</td>";
			}	
			echo "</tr>";
		}
		echo "</tbody></table>";
	}
}

$discounts = new GrabIkeaData();
$discounts->collectData();
$discounts->displayData();
?>
 </body>
</html>