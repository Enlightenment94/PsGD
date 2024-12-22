<?php
/*
vrc095
vrc100s
vrc100rp
*/

class CodingIndexTab{

/*
vintage
comporary
*/

	public $collections = ['v' => 'v', 'c' => 'c'];

/*
PS/GD
rc/r - rings Corundum  / rings
pc/p - pendants Corundum  / pendants
ec/e - earrings Corundum / earrings
*/

	public $categories = ['rc', 'pc'. 'ec'];

/*
Z rings
PS/GD
am/am - ametyst/ amethyst
ab/ab - bursztyn/amber
aq/aq - akwamaryn/aquamarine
ax/ax - Aleksandrytem / alexandrite
bt/bt - Ruby emerald/blue topaz  (a.b.c.d)
d/d   - diament/diamond
zi/z  - cyrkonia/zircon 
em/em - szmaragdem / Emerald
rb/rb - rubin/Ruby
sp/sp - szafir/ Sapphire
yp/yp - peridotem/yellow peridot
st/   - ???/
n/ns  - bez kamienia / no stones
-/os  - /other stones ??????
pr/pr - perła/pearl

Z pendatns
to samo +
*/

	public $corundum = ['am','ab','aq','ax','bt','d','zi','em','rb','sp','yp','st','n','pr'];

/*
PS/GD KalikyPanel
w/  - +14k białe złoto
    - +14k czerwone białe złoto

r/r - -14k czerwone złoto !!! ???

yw/ - +14k żółte białe złoto
y/  - +14k żółte złoto
		- +14k czerwone różowe złoto
    - +14k żółte, białe, czerwone złoto
    - -14kt żółte, białe złoto
    - -8K białe złoto
    - -8K żółte złoto

s/w - 925 Srebro !!!

rp/r - -925 Srebro pozłacane (czerwone złoto)
yp/  - -925 Srebro pozłacane (żółte złoto)
     - -Oryginalny wyrób Vintage 14k różowego i białego złota
     - -Oryginalny wyrób Vintage 14k z różowego złota
*/

	public $metals = ['s','w', 'r', 'yw', 'y', 's', 'rp', 'yp'];
}

class ParseIndex {
	public $codingIndexTab;
	public $collection;
	public $category;
	public $model;
	public $metal;
	public $corundum;

	public function __construct(){
		$this->codingIndexTab = new CodingIndexTab();
	}

	/*
	vrc189r-zl
	{collections x(1)}{category (xy/x)}{model [CYFRY](xyz)}{metal (x/xy ??)}-{corundum (???)}
	*/
	public function parsePrestaIndex($index){
		echo " INDEX ... ";
		echo $index;

		$this->collection = '';
		$this->category = '';
		$this->model = '';
		$this->metal = '';
		$this->corundum = '';

		$len = strlen($index);
		//Pakujemy kolekcje
		$this->collection = $index[0];
		if($this->collection ==  'v' || $this->collection == 'c'){
			$this->category = "";
			//Pakujemy kategorie
			for($i = 1 ; $i < $len; $i++){				
				if(ctype_digit($index[$i])){
					break;
				}
				$this->category .=  $index[$i];
			}

			//Pakujemy model
			$j = 0;
			for($i; $i < $len; $i++){
				if($j == 3){
					break;
				}
				$this->model .= $index[$i];
				$j++;
			}

			//Pakujemy metal
			for($i; $i < $len; $i++){
				if($index[$i] == '-'){
					break;
				}
				$this->metal .= $index[$i];
			}

			//Pakujemy kamień
			$i++;
			for($i; $i < $len; $i++){
				$this->corundum .= $index[$i];
			}
		}
	}

	public function printClass(){
		echo "collection:" . $this->collection . "\n";
		echo "category:  " . $this->category . "\n";
		echo "model:     " . $this->model . "\n";
		echo "metal:     " . $this->metal . "\n"; 
		echo "corundum:  " . $this->corundum . "\n";
	}

	public function validateCorundum(){
		if($this->corundum == ''){
			return -1;
		}
		$flag = 0;
		foreach ($this->codingIndexTab->corundum as $c) {
			if($this->corundum == $c){
				$flag = 1;
			}
		}
		return $flag;
	}

	public function validateMetal(){
		if($this->metal == ''){
			return -1;
		}
		$flag = 0;
		foreach ($this->codingIndexTab->metal as $m) {
			if($this->corundum == $m){
				$flag = 1;
			}
		}
		return $flag;
	}

	public function parseToGoogleDirver(){
		$tempCorundum = $this->corundum;
		$tempCategory = $this->category;
		$tempMetal = $this->metal;
		if($this->corundum == "zi" ){
			$tempCorundum = "z";
		}

		if($this->corundum == "n" ){		
			$tempCorundum = "ns";
		}


		//Kategorie
		if($this->category == "rc"){
			$tempCategory = "r";
		}

		if($this->category == "ec"){
			$tempCategory = "e";
		}

		if($this->category == "pc"){
			$tempCategory = "p";
		}


		//Metale
		if($this->metal == "s"){
			$tempMetal = "w"; //<- Czy aby napewno
		}

		if($this->metal == "rp"){
			$tempMetal = "r"; //<- Czy aby napewno
		}

		return $this->collection . $tempCategory . $tempCorundum .  $this->model . $tempMetal;
	}
}
