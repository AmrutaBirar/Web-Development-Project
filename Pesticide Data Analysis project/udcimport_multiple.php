<?php

	//make database connection
	
	include('connect.php');
	include('getcsvvalues.php');
	$pdo = connect();
	
	
	$filecount = count($_FILES['udcfileToUpload']['tmp_name']);
	
		
	for($i=0;$i<$filecount;$i++) {
	
			$file_name = $_FILES['udcfileToUpload']['name'][$i];
		$file_type=$_FILES['udcfileToUpload']['type'][$i];	

		$target_dir = "files/";
		$target_file = $target_dir . $file_name;
		//echo $target_file;
		$file_tmp =$_FILES['udcfileToUpload']['tmp_name'][$i];
		move_uploaded_file($file_tmp,$target_file);
		
		
		
		}
		
		
	
		if (is_dir($target_dir)) {
    		if ($dh = opendir($target_dir)) {
        		while (($file = readdir($dh)) !== false) { //use while loop to read navigate through each file in the directory
            		
					if(preg_match("/udc/", $file)){ //if the file name is udc only then do the operation. this is to avoid the hidden and DS file store
						$txt_file = $target_dir . $file;
						echo "<br>Exporting $txt_file <br>";	
						
						//first truncate our staging UDC table
						$truncate = "TRUNCATE TABLE RAWUSEDATACHEMICAL";
						$truncatequery = $pdo->prepare($truncate);
						$truncatequery->execute();
						
						//read the contents of the UDC file into an array
				
						$fp = fopen($txt_file, "r");
						
						$read_limit = 8192000;//size in bytes to load into memory
						$size = filesize($txt_file);
						
						while ($size > 0)
						{
				  			$rlen = ($size > $read_limit) ? $read_limit : $size; //read length
  							//$buffer = fread($fh, $rlen);
							//$fp=str_replace("'", "\'", $fp);
							while (false !== ($line = fgets($fp))) 
							{
								$line=str_replace("'", "\'", $line);
								//echo $line;
    							// Process $line, e.g split it into values since it is CSV.
    		    				$strings = getCSVValues($line);

    							// Do stuff: Run MySQL updates, ...
			
			 					try{	  
			   
										$strings=str_replace("'", "\'", $strings);
				       					$udc_query = "INSERT INTO RAWUSEDATACHEMICAL(
														USE_NO,
														PRODNO,
														CHEM_CODE,
														PRODCHEM_PCT,
														LBS_CHM_USED,
														LBS_PRD_USED,
														AMT_PRD_USED ,
														UNIT_OF_MEAS,
														ACRE_PLANTED,
														UNIT_PLANTED,
														ACRE_TREATED,
														UNIT_TREATED,
														APPLIC_CNT,
														APPLIC_DT,
														APPLIC_TIME,
														COUNTY_CD,
														BASE_LN_MER ,
														TOWNSHIP,
														TSHIP_DIR,
														`RANGE`,
														RANGE_DIR,
														SECTION,
														SITE_LOC_ID,
														GROWER_ID,
														LICENSE_NO,
														PLANTING_SEQ,
														AER_GND_IND,
														SITE_CODE,
														QUALIFY_CD,
														BATCH_NO,
														DOCUMENT_NO,
														SUMMARY_CD,
														RECORD_ID
														) VALUES";
						
											$udc_query .= "('$strings[0]'
															, '$strings[1]'
															, '$strings[2]'
															, '$strings[3]'
															, '$strings[4]'
															, '$strings[5]'
															, '$strings[6]'
															, '$strings[7]'
															, '$strings[8]'
															, '$strings[9]'
															, '$strings[10]'
															, '$strings[11]'
															, '$strings[12]'
															, '$strings[13]'
															, '$strings[14]'
															, '$strings[15]'
															, '$strings[16]'
															, '$strings[17]'
															, '$strings[18]'
															, '$strings[19]'
															, '$strings[20]'
															, '$strings[21]'
															, '$strings[22]'
															, '$strings[23]]'
															, '$strings[24]'
															, '$strings[25]'
															, '$strings[26]'
															, '$strings[27]'
															, '$strings[28]'
															, '$strings[29]'
															, '$strings[30]'
															, '$strings[31]'
															, '$strings[32]'
															)";
											
											$query = $pdo->prepare($udc_query);
											$query->execute();
				  
				
									}//try end
				
								catch(PDOException $e)
							    {
							    echo $sql . "<br>" . $e->getMessage();
							    }
							    
							    
				
				
				
						}
						$size -= $rlen;
				} //end of file buffer	
				
				
							try{
								//clean up the data in the raw table
								$cleandata = "delete from RAWUSEDATACHEMICAL where use_no = '0';";//delete rows where prodno = 0
								$cleanquery = $pdo->prepare($cleandata);
								$cleanquery->execute();
								}
								catch(PDOException $e)
							    {
							    echo $sql . "<br>" . $e->getMessage(). "Data Cleanup";
							    }
								
								
							//insert into the final table, only if data does not exist, 
										//this is to prevent duplication of the data into the final table
										try{
										$udcfinalinsert = "
													INSERT INTO USEDATACHEMICAL (
																USE_NO,
																PRODNO,
																CHEM_CODE,
																PRODCHEM_PCT,
																LBS_CHM_USED,
																LBS_PRD_USED,
																AMT_PRD_USED ,
																UNIT_OF_MEAS,
																ACRE_PLANTED,
																UNIT_PLANTED,
																ACRE_TREATED,
																UNIT_TREATED,
																APPLIC_CNT,
																APPLIC_DT,
																APPLIC_TIME,
																COUNTY_CD,
																BASE_LN_MER ,
																TOWNSHIP,
																TSHIP_DIR,
																`RANGE`,
																RANGE_DIR,
																SECTION,
																SITE_LOC_ID,
																GROWER_ID,
																LICENSE_NO,
																PLANTING_SEQ,
																AER_GND_IND,
																SITE_CODE,
																QUALIFY_CD,
																BATCH_NO,
																DOCUMENT_NO,
																SUMMARY_CD,
																RECORD_ID
													
													
													)
													select 
																USE_NO,
																PRODNO,
																CHEM_CODE,
																PRODCHEM_PCT,
																LBS_CHM_USED,
																LBS_PRD_USED,
																AMT_PRD_USED ,
																UNIT_OF_MEAS,
																ACRE_PLANTED,
																UNIT_PLANTED,
																ACRE_TREATED,
																UNIT_TREATED,
																APPLIC_CNT,
																str_to_date(APPLIC_DT,'%m/%d/%Y'),
																str_to_date(APPLIC_TIME,'%h:%i %p'),
																COUNTY_CD,
																BASE_LN_MER ,
																TOWNSHIP,
																TSHIP_DIR,
																`RANGE`,
																RANGE_DIR,
																SECTION,
																SITE_LOC_ID,
																GROWER_ID,
																LICENSE_NO,
																PLANTING_SEQ,
																AER_GND_IND,
																SITE_CODE,
																QUALIFY_CD,
																BATCH_NO,
																DOCUMENT_NO,
																SUMMARY_CD,
																RECORD_ID 
													from RAWUSEDATACHEMICAL;";
																
										$udcfinalinsertquery = $pdo->prepare($udcfinalinsert);
										$udcfinalinsertquery->execute();
																
				
											
											}
											catch(PDOException $e)
										    {
										    echo $sql . "<br>" . $e->getMessage(). "Data Insertion Error";
										    }
				echo "<br>Data Loaded into USE DATA CHEMICAL table for file : ",$file,"<br>";
				
					
					//INSERT RECORD IN FILE - AUDIT TABLE
				$auditinsert = "Insert into FILEAUDIT(FILENAME,FILETYPE,UPLOADDATE)
								select '$file','UDC', now()";
											
				$auditinsertquery = $pdo->prepare($auditinsert);
				$auditinsertquery->execute();
				
										
				fclose($fp);
				unlink($txt_file);		
						
						
			}
			
						
        }
        closedir($dh);
    }
}


					


$pdo=null;
    
     
?>
<hr>
<input type="button" value="Go back" onclick="window.location.href = 'index.php'" />