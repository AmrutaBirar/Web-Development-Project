<?php
				require_once ('src/jpgraph.php');
					require_once ('src/jpgraph_line.php');
					require_once ('src/jpgraph_bar.php');
					include('connect.php');
				    $pdo = connect();
					$year_array = array();
					$acre_array = array();
					$site_array = array();
					
try{
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['analyzebtn2'])){ //for 2nd query
					$chemname = $_POST["chemicalist"];
					$year = $_POST["yearlist"];
					
	                $query = $pdo->prepare
					("select year(udc.applic_dt) as 'YEAR',s.site_name as 'SITE',c.chemname,sum(udc.acre_treated) as 'ACRES TREATED'
						from usedatachemical udc
						inner join site s on s.site_code = udc.site_code
						inner join chemical c on c.chem_code = udc.chem_code
						where c.chemname like '%".$chemname."%' and year(udc.applic_dt) = '".$year."'
						group by s.site_name
						order by sum(udc.acre_treated) desc limit 5;");
				
			    	 $query->execute();
			         $rows_count = $query->rowCount();
					  if (($query->rowCount())>0){
							
							while ($row = $query->fetch(PDO::FETCH_ASSOC)){
								
								$acre_array[] = $row['ACRES TREATED'];
								$site_array[] = $row['SITE']; 
							
							}
							
							
						}
					  else {
					  	
					  	$acre_array = array(0,0);
						$site_array = array(0,0); 
					  
					  }	
					
					

						$datay=$acre_array; //yaxis
						
						
						// Create the graph. These two calls are always required
						$graph = new Graph(700,500,'auto');
						$graph->SetScale("textlin");
						
						$theme_class=new OrangeTheme;
				
						$graph->SetTheme($theme_class);
						
						// set major and minor tick positions manually
						//$graph->yaxis->SetTickPositions(array(0,30,60,90,120,150), array(15,45,75,105,135));
						$graph->SetBox(false);
						
						
						//$graph->ygrid->SetColor('gray');
						$graph->ygrid->SetFill(false);
						$graph->xaxis->SetTickLabels($site_array);
						$graph->xaxis->SetLabelMargin(15);
						//$graph->xaxis->SetLabelAlign('right','center','right');
						$graph->xaxis->SetLabelAngle(30);
						//$graph->xaxis->SetFont(FF_ARIAL,FS_NORMAL,11); 
						$graph->yaxis->HideLine(false);
						$graph->yaxis->HideTicks(false,false);
						$graph->img->SetMargin(200,50,50,400); 
						// Create the bar plots
						$b1plot = new BarPlot($datay);
						
						// ...and add it to the graPH
						$graph->Add($b1plot);
						if ($rows_count > 0){
							$b1plot->SetLegend('Crops with the highest use of '.$chemname.' in '.$year);
						}else{
							$b1plot->SetLegend('No data found for your selection');
						}
						
						$b1plot->SetColor("white");
						$b1plot->SetFillGradient("orange","orange",GRAD_LEFT_REFLECTION);
						$b1plot->SetWidth(15);
						/*if ($rows_count > 0){
							$graph->title->Set('Crops with the highest use of '.$chemname.' in '.$year);
						}else{
							$graph->title->Set('No data found for your selection');
						}
						//$graph->title->Set("Bar Gradient(Left reflection)");
						//$graph->legend->SetFrameWeight(3);
						$graph->legend->SetAbsPos(10,10,'right','top');
						// store the graph
						$graph->Stroke('files/query2.png');
				
						header('Location:index.php');
						exit;
		
		}//end 2nd query's if	
		$pdo = null;
		
		}//end of try
			
		catch(PDOException $e)
				{
				 echo $sql . "<br>" . $e->getMessage();
				  
				 }
				
				
?>