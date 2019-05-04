<?php
			
					include('connect.php');
					include('demo_count_graph.php');
				    $pdo = connect();
					try{
						
					$year = array();
					$acre = array();
				if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['analyzebtn'])){
					$chemname = $_POST["chemicalist"];
					$sitename = $_POST["sitelist"];
					
	                $query = $pdo->prepare
					("select year(udc.APPLIC_DT) as 'YEAR',s.site_name as 'SITE',c.CHEMNAME as 'CHEMICAL',sum(udc.ACRE_TREATED) as 'ACRES TREATED'
					 from usedatachemical udc 
					 inner join chemical c on udc.chem_code = c.chem_code
					 inner join site s on udc.site_code = s.site_code
					 where c.chemname like '%".$chemname."%' and s.site_name like '%".$sitename."%'
                     group by  year(udc.APPLIC_DT);");
				
			    	 $query->execute();
			         
					  if (($query->rowCount())>0){
							echo "<table><th>Year</th><th>Site</th><th>Chemical</th><th>Acres Treated</th><br>";
							while ($row = $query->fetch(PDO::FETCH_ASSOC)){
								echo "<tr>";
								echo "<td>".$row['YEAR']."</td>"."<td>".$row['SITE']."</td>"."<td>".$row['CHEMICAL']."</td>"."<td>".$row['ACRES TREATED']."</td>";
								echo "</tr>";
								$year[] = $row['YEAR'];
								$acre[] = $row['ACRES TREATED']; 
							
							}
							echo "</table>";
						}
					  else {
					  	echo "No data";
					  }		
					//  $graph = create_graph($acres,$year);	//calling graph creation function
					//  echo $graph;
				}
					
			$pdo = null;
			}
			catch(PDOException $e)
				    {
				    echo $sql . "<br>" . $e->getMessage();
				    }
			
	?>