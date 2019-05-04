<?php 

//make database connection
include('connect.php');
include('getcsvvalues.php');
$pdo = connect();


//Upload the file to a specific directory for operation
$target_dir = "files/";
$target_file = $target_dir . basename($_FILES["udcfileToUpload"]["name"]);

$uploadOk = 1;
$fileType = pathinfo($target_file,PATHINFO_EXTENSION);


	// Allow .txt file formats
	if($fileType != "txt") {
	    echo "Sorry, only text files allowed for upload.<br>";
	    $uploadOk = 0;
	}
	// Check if $uploadOk is set to 0 by an error
	if ($uploadOk == 0) {
	    echo "Sorry, your file was not uploaded!<br>";
	//  try to upload file
	} else {
	    if (move_uploaded_file($_FILES["udcfileToUpload"]["tmp_name"], $target_file)) {
	        echo "The file ". basename( $_FILES["udcfileToUpload"]["name"]). " has been uploaded.<br>";
			
			$txt_file =  $target_dir . basename( $_FILES["udcfileToUpload"]["name"]);
			echo $txt_file;
			//Writing to databases after getting contents of the file
			//first truncate our staging CASNO table
			$truncate = "TRUNCATE TABLE RAWUSEDATACHEMICAL";
			$truncatequery = $pdo->prepare($truncate);
			$truncatequery->execute();
			
			//read the contents of the CASNO file into an array
				
				$file = file_get_contents($txt_file);
				
			    $dataStrings = explode("\n", $file);
				
				
			   
			 try{	  
			    $i = 1;
			    foreach ( $dataStrings as $data ) ++$i;
			    {
			
				    for ( $j = 1; $j < $i; ++$j )
				    {
				        $strings = getCSVValues( $dataStrings[$j] );//call the csv value function, this helps parse the commas in the data as well
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
				    }	
					
				 }
				}//try end
				
				catch(PDOException $e)
			    {
			    echo $sql . "<br>" . $e->getMessage();
			    }
				
				
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
			    
			   /* try{
			    //remove entries from application table if data is being inserted again for the same file
			    //this is to prevent duplication
			    $dedupe = "DELETE udc from USEDATACHEMICAL udc 
							INNER JOIN RAWUSEDATACHEMICAL rudc on udc.USE_NO=rudc.USE_NO;";
				$dedupequery = $pdo->prepare($dedupe);
				$dedupequery->execute();			
				}
				
				catch(PDOException $e)
			    {
			    echo $sql . "<br>" . $e->getMessage(). "Data deduplication";
			    }*/
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
				
				echo "<br>Data Loaded into USE DATA CHEMICAL table";
				$filename =  basename( $_FILES["udcfileToUpload"]["name"]);
				//INSERT RECORD IN FILE - AUDIT TABLE
				$auditinsert = "Insert into FILEAUDIT(FILENAME,FILETYPE,UPLOADDATE)
								select '$filename','UDC', now()";
				
				$auditinsertquery = $pdo->prepare($auditinsert);
				$auditinsertquery->execute();
				
				}
				catch(PDOException $e)
			    {
			    echo $sql . "<br>" . $e->getMessage(). "Data Insertion Error";
			    }
				//delete temp file once its uploaded
				unlink($txt_file);
				$pdo=null;
				
	    } else {
	        echo "Error uploading your file!<br>";
	    }
	}

?>
	<input type="button" value="Go back" onclick="window.location.href = 'index.php'" />
