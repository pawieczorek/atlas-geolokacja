<?php

//poniższ aplikacja integruje dane demograficzne (miasto, populacja) z danymi geolokacyjnymi
//(szerość i długość geograficzna)
//dane demograficzne znajdują się w bazie mysql w przykładowej tabeli citiespoland
//kulumny: miasto (text), populacja(int)
//dane geolokalizacyjne pochodzą z Google Maps Geocoding API i są odpowiednio umieszczane
//w kolumnach lat(decimal(10,8)), lng(decimal 11,8)) 
//przebieg wykonywania kodu jest spowolniony przez komendę usleep (100000); ze względu
//limit sekundowy zapytań do Google Maps Geocoding API
//
//P.Wieczorek 2016



class geocodeClass {


		public function getCoordinates($city, $country){
		 
				$cityCopy = $city;
				
				$city = urlencode($city);
				 
				$url = "http://maps.google.com/maps/api/geocode/json?address={$city}&components=country:{$country}";
			 
				$resp_json = file_get_contents($url);
				 
				$resp = json_decode($resp_json, true);
			 
				
				if($resp['status']=='OK'){
			 
						$lat = $resp['results'][0]['geometry']['location']['lat'];
						$lng = $resp['results'][0]['geometry']['location']['lng'];
						$formatted_address = $resp['results'][0]['formatted_address'];
						 
						
						if($lat && $lng && $formatted_address){
						 
								$data_arr = array();            
								 
								array_push(
									$data_arr, 
										$lat, 
										$lng, 
										$formatted_address
									);
									
								echo $cityCopy.' ==> '.$resp['status'];
								echo '<br />';
								 
								return $data_arr;
								 
								}//if
						else
								{
									return false;
								}
						 
						}//if
				else
						{
							echo $addressCopy.' ==> '.$resp['status'];
							echo '<br />';
							
							return false;
						}
						
		}//getCoordinates


		public function addCoordinates(&$arrayOfCities, $country){
			
				foreach($arrayOfCities as &$row) {						
						
						usleep (100000);
						
						$cooordinates= array();
						$cooordinates=$this->getCoordinates($row['miasto'], $country);
						if ($cooordinates!=false)
								{
									
								$row['lat']=$cooordinates[0];
								$row['lng']=$cooordinates[1];

								}//if	
						else
								{
									
								$row['lat']=null;
								$row['lng']=null;	
								
								}
						
				}//foreach 	
				
				return $arrayOfCities;	
		
		}//addCoordinates
		

}//geocodeClass

class PDOClass{ 
		
		var $mysql_host; 
		var $port; 
		var $username;
		var $password;
		var $database; 


		public function setMysql_host($mysql_host){
			
				$this -> mysql_host	= $mysql_host;
					
				}//
				
		public function setPort($port){
			
				$this -> port = $port;
					
				}//		
				
		public function setUsername($username){
			
				$this -> username = $username;	
					
				}//

		public function setPassword($password){
			
				$this -> password = $password;	
					
				}//

		public function setDatabase($database){
			
				$this -> database = $database;	
					
				}//	
				
		public function getCitiesNames($table){		
		 
				 $result=array();
				 
				 try
						   {
							   
							  $pdo = new PDO('mysql:host='.$this->mysql_host.';dbname='.$this->database.';port='.$this->port, $this->username, $this->password );
							  
							  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
							  $pdo ->query('SET CHARACTER SET utf8');
							  $pdo ->query('SET NAMES utf8');
							  
							  $stmt = $pdo->query('SELECT miasto FROM '.$table);
							  
							  echo '<ul>';
							  
							  foreach($stmt as $row)
									  { 
										  $result[]=array('miasto'=>$row['miasto']);
									  }
							  $stmt->closeCursor();
							  
							  echo '</ul>';
						   
						   }//try
						   
				 catch(PDOException $e)
				 
						   {
							  echo 'Połączenie nie mogło zostać utworzone: ' . $e->getMessage();
						   }		
								
				 return $result;	
					
					
				}//getCitiesNames	
				
		
		
		public function upDateBase($table, $dataArray){
		
				try
						{
					
						$pdo = new PDO('mysql:host='.$this->mysql_host.';dbname='.$this->database.';port='.$this->port, $this->username, $this->password );
						$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
						$pdo ->query('SET CHARACTER SET utf8');
						$pdo ->query('SET NAMES utf8');
				
						$stmt = $pdo -> prepare('UPDATE '.$table.' SET lat = :lat, 
																	lng = :lng 
																	WHERE miasto = :miasto'); 
						
						$ilosc = 0;
						
						foreach($dataArray as $row)
						
								{
									
								if ($row['lat']!=null)
									
										{
											
										$stmt -> bindValue(':miasto', $row['miasto'], PDO::PARAM_STR); 
										$stmt -> bindValue(':lat', $row['lat'], PDO::PARAM_STR);
										$stmt -> bindValue(':lng', $row['lng'], PDO::PARAM_STR);
										
										$ilosc += $stmt -> execute(); 
										
										}
										
								}//foreach
				
						if($ilosc > 0)
							
								{
								echo '<br />';	
								echo 'Zmodyfikowano: '.$ilosc.' rekordow';
								}
								
						else
								{
								echo 'Wystapil blad podczas dodawania rekordow!';
								}
					
						}//try
						
				catch(PDOException $e)
				
						{
							echo 'Wystapil blad biblioteki PDO: ' . $e->getMessage();
						}
				
		}//upDateBase		
				
}//PDOClass



ini_set('max_execution_time', 2000);


$pdo = new PDOClass;

$pdo -> setMysql_host('sql.wyrzykowskikornacki.home.pl'); 
$pdo -> setPort('3306'); 
$pdo -> setUsername('10535339_wieczor');
$pdo -> setPassword('spinoza2015');
$pdo -> setDatabase('10535339_wieczor');
$tableName = 'citiespoland';

$arrayOfCities = $pdo ->getCitiesNames($tableName);

$geocode= new geocodeClass;

$geocode->addCoordinates($arrayOfCities, 'pl');

echo '<br />';

foreach($arrayOfCities as $row)

		{	
		echo '<li>'.$row['miasto'].'  '.$row['lat'].'  '.$row['lng'].'</li>';	  
		}

$pdo -> upDateBase($tableName, $arrayOfCities);






?>