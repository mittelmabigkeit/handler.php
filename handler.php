<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
?>
<?
$fp = fopen("hash.txt", 'r+');
$fp_old = file_get_contents("hash.txt");
fclose($fp);
$filename = $_SERVER["DOCUMENT_ROOT"]."/partnumber/upload.csv";
$hash = hash_file("md5",$filename,FALSE);
$fo = fopen("hash.txt", "w");
fwrite($fo, $hash);
fclose($fo);

if($fp_old!==$hash){
	echo "хэш отличается, записываю";
	require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/csv_data.php");
	$csvFile = new CCSVData('R', true);
	$csvFile->LoadFile($_SERVER["DOCUMENT_ROOT"]."/partnumber/upload.csv");
	$csvFile->SetDelimiter(";");
	$bFirstHeaderTmp = $csvFile->GetFirstHeader();  
	while ($arRes = $csvFile->Fetch()) { 
		$arRes = $GLOBALS["APPLICATION"]->ConvertCharsetArray($arRes, "WINDOWS-1251", SITE_CHARSET);


		$arSelect = Array("ID", "PROPERTY_PARTNUMBER");
		$arFilter = Array("IBLOCK_ID" => 23, "PROPERTY_PARTNUMBER" => $arRes["0"]);
		$res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>9999), $arSelect);
		while($ob = $res->GetNextElement()){ 
			$arFields = $ob->GetFields();
		}


		$el = new CIBlockElement;
		$PROPS = array();
		$PROPS["PARTNUMBER"] = $arRes["0"]; 
		$PROPS["PARTDESCRIPTION"] = $arRes["1"];
		$PROPS["DEALERPRICE"] = $arRes["2"];
		$PROPS["L_SM"] = $arRes["3"];
		$PROPS["H_SM"] = $arRes["4"];
		$PROPS["W_SM"] = $arRes["5"];
		$PROPS["GROSSWGHT_KGS"] = $arRes["6"];
		$arLoadProductArray = Array(
			"IBLOCK_ID" => 23,
			"NAME" => $arRes["0"], 
			"PROPERTY_VALUES"=> $PROPS,
		  );


		if ($arRes["0"] == $arFields["PROPERTY_PARTNUMBER_VALUE"])
			$el->Update($arFields["ID"], $arLoadProductArray);
		else
			$el->Add($arLoadProductArray);

		}
}else{
	echo "хэш равен, ничего записывать не буду";
}
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>