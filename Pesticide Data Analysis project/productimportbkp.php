<?php 

//make database connection
include('connect.php');
include('getcsvvalues.php');
$pdo = connect();


//Upload the file to a specific directory for operation
$target_dir = "files/";
$target_file = $target_dir . basename($_FILES["prodfileToUpload"]["name"]);
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
	    if (move_uploaded_file($_FILES["prodfileToUpload"]["tmp_name"], $target_file)) {
	        echo "The file ". basename( $_FILES["prodfileToUpload"]["name"]). " has been uploaded.<br>";
	    } else {
	        echo "Error uploading your file!<br>";
	    }
	}
$txt_file =  $target_dir . basename( $_FILES["prodfileToUpload"]["name"]);
echo $txt_file;
//Writing to databases after getting contents of the file
//first truncate our staging county table
$truncate = "TRUNCATE TABLE RAWPRODUCT";
$truncatequery = $pdo->prepare($truncate);
$truncatequery->execute();

//read the contents of the County file into an array
    $input = file_get_contents($txt_file, "r");
	$filearray = explode("\n", $input);
	//echo $filearray[0];
	
/*try{
$setmaxpacket = "set GLOBAL max_allowed_packet = 1073741824";
$setmaxpacketquery = $pdo->prepare($setmaxpacket);
$setmaxpacketquery->execute();
}

catch(PDOException $e)
    {
    echo $sql . "<br>" . $e->getMessage();
    }
	*/
	$count = count($filearray);
	
try{	
//if array is not empty, create the insert statements until we reach the end of array	
	if ($count > 0)
	{		$i=0;
		
	
	foreach($filearray as $row)
	{
		$product_query = "INSERT INTO RAWPRODUCT(PRODNO,MFG_FIRMNO,REG_FIRMNO,LABEL_SEQ_NO,
		REVISION_NO,FUT_FIRMNO,PRODSTAT_IND,PRODUCT_NAME,SHOW_REGNO,AER_GRND_IND,AGRICCOM_SW,
		 CONFID_SW,DENSITY,FORMULA_CD,FULL_EXP_DT,FULL_ISS_DT,FUMIGANT_SW,GEN_PEST_IND,
		LASTUP_DT,MFG_REF_SW,PROD_INAC_DT,REG_DT,REG_TYPE_IND,RODENT_SW,SIGNLWRD_IND,SOILAPPL_SW,
		SPECGRAV_SW,SPEC_GRAVITY,CONDREG_SW) VALUES";
		$product = explode(',', $row);
		$product[7]= str_replace("'", "\'", $product[7]);
		//$product=str_replace(",","\,",$product);
		$product_query .= "('$product[0]', '$product[1]', '$product[2]', '$product[3]', '$product[4]'
		, '$product[5]', '$product[6]', '$product[7]', '$product[8]', '$product[9]', '$product[10]'
		, '$product[11]', '$product[12]', '$product[13]', '$product[14]', '$product[15]', '$product[16]'
		, '$product[17]', '$product[18]', '$product[19]', '$product[20]', '$product[21]', '$product[22]'
		, '$product[23]', '$product[24]', '$product[25]', '$product[26]', '$product[27]', '$product[28]')";
		//$product_query .= $i < $count ?  ',' :  '';
		//echo $product_query;
		$i++;
		$query = $pdo->prepare($product_query);
		$query->execute();
	}
//echo $product_query;
	}
	//execute the insert statement to load data into the staging product table
	//echo $product_query;
	
	}

catch(PDOException $e)
    {
    echo $sql . "<br>" . $e->getMessage();
    }
	
	$pdo=null;
	/*
	//clean up the data in the raw table
	$cleandata = "delete from RAWCOUNTY where ceil(county)=0";//ceil is used to check for integers
	$cleanquery = $pdo->prepare($cleandata);
	$cleanquery->execute();
	
	//insert into the final table, only if data does not exist, 
	//this is to prevent duplication of the data into the final table
	$countyfinalinsert = "insert into COUNTY (COUNTY_CD,COUNTY_NAME)
							select county,county_name	from RAWCOUNTY
							where  COUNTY not in (select COUNTY_CD	 from COUNTY)";
							
	$countyfinalinsertquery = $pdo->prepare($countyfinalinsert);
	$countyfinalinsertquery->execute();
	
	echo "<br>Data Loaded into County table";
	$filename =  basename( $_FILES["fileToUpload"]["name"]);
	//INSERT RECORD IN FILE - AUDIT TABLE
	$auditinsert = "Insert into FILEAUDIT(FILENAME,FILETYPE,UPLOADDATE)
					select '$filename','county', now()";
	
	$auditinsertquery = $pdo->prepare($auditinsert);
	$auditinsertquery->execute();
	
	//remove the file after it is uploaded the database after 10 days-- research the UNLINK function
	//show number of rows imported and the last file uploaded on the index page - this can be done by fetching data from a status table
	


	//delete temp file once its uploaded
	unlink($txt_file);

*/






	
?>
	<input type="button" value="Go back" onclick="window.location.href = 'index.php'" />
