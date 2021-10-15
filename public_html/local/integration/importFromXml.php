<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
if(CModule::IncludeModule("iblock") && CModule::IncludeModule("catalog") && CModule::IncludeModule('highloadblock')){
	

	$arHLBlock5 = Bitrix\Highloadblock\HighloadBlockTable::getById(5)->fetch();
	$obEntity5 = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($arHLBlock5);
	$strEntityDataClass5 = $obEntity5->getDataClass();
	$arHLBlock6 = Bitrix\Highloadblock\HighloadBlockTable::getById(6)->fetch();
	$obEntity6 = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($arHLBlock6);
	$strEntityDataClass6 = $obEntity6->getDataClass();




	$xml = simplexml_load_file($_SERVER["DOCUMENT_ROOT"] .'/local/integration/data/data.xml');

	$el = new CIBlockElement;
	$ibpenum = new CIBlockProperty;

	

	foreach ($xml->product as $product) {
		
		//img

		foreach ($product->IMAGES->OPTION as $picture)	{
			if ($picture['is_main']=="Y") 
				$arFields["PREVIEW_PICTURE"]=CFile::MakeFileArray($picture);
			else
				{
					$properImg = CFile::MakeFileArray($picture);
				}
		}

		$properte["IMAGES"] = $properImg;
		
		// sycno

		foreach ($product->SYKNO->VARIANT as $sykno) {
			$rsData = $strEntityDataClass5::getList(array(
				'select' => array('ID','UF_XML_ID'),
				'filter' => array('UF_XML_ID'=> (string)$sykno),
   			));
			
			if ($arItem = $rsData->Fetch())
			{
				$arPropSykno[]=$arItem["UF_XML_ID"];
				
   			}
			else
			{
				$arElementFields = array(
					'UF_NAME' => $sykno["VALUE"],
					'UF_XML_ID' => $sykno,
				);
   				$obResult = $strEntityDataClass5::add($arElementFields);
   				$arPropSykno[]= (string)$sykno;
				pre("Новое значение свойства <br>");
			}
		}
		$properte["SYKNO"] = $arPropSykno;

		// vicrasca

		foreach ($product->VIKRASKA->VARIANT as $vicrasca) {
			$rsData = $strEntityDataClass6::getList(array(
				'select' => array('ID','UF_XML_ID'),
				'filter' => array('UF_XML_ID'=> (string)$vicrasca),
   			));
			
			if ($arItem = $rsData->Fetch())
			{
				$arPropVic[]=$arItem["UF_XML_ID"];
				
   			}
			else
			{
				$arElementFields = array(
					'UF_NAME' => $vicrasca["VALUE"],
					'UF_XML_ID' => $vicrasca,
				);
   				$obResult = $strEntityDataClass6::add($arElementFields);
   				$arPropVic[]= (string)$vicrasca;
				pre("Новое значение свойства <br>");
			}
		}

		
		$properte["VIKRASKA"] = $arPropVic;

		$arFields = [
			"ACTIVE" => "Y", 
			"IBLOCK_ID" => 4,
			"NAME" => $product->NAME,
			"CODE" => $product->CODE,
			"DETAIL_TEXT" => $product->DESCRIPTION,
			"PROPERTY_VALUES" => $properte,
		];
		
		// добовление товара

		if ($prodId = $el->Add($arFields)) {
		pre('Добавлен товар с id' . $prodId);
		$arFields = [
			"ID" => $prodId,
			"QUANTITY" => 0,
		];

		// добавление торгового катаога

		CCatalogProduct::Add($arFields);

		// обход офферов

		foreach ($product->OFFERS->OFFER as $offer) {

			
			$arPropOffer = [
				'SIZE_FIELD' => $offer->SIZE_FIELD,
			    'GAME_TYPE' => $offer->GAME_TYPE,
			    'TABLE_MATERIAL' => $offer->TABLE_MATERIAL,
			    'TABLE_TYPE' => $offer->TABLE_TYPE,
			    'QTY_LEGS' => $offer->QTY_LEGS,
			    "CML2_LINK"=>$prodId,
			];

			
			$arOffer = [
				"IBLOCK_ID" => 5,
				"NAME" => $product->NAME,
				"CODE" => $offer->ART,
				"PROPERTY_VALUES"=>$arPropOffer,
			];

			
			if ($offerId = $el->Add($arOffer)) {
				pre('Добавлен оффер с id' . $offerId);
				$arOffer = [
					"ID" => $offerId,
					"QUANTITY" => 50,
					"WEIGHT" => $offer->VES,
				];

				CCatalogProduct::Add($arOffer);
				
				//загрузка цены
				$arPrice=array(
					"PRODUCT_ID"=>$offerId,
					"CATALOG_GROUP_ID" => 1,
					"PRICE"=>(string)$offer->PRICE,
					"CURRENCY" => "RUB",
				);
				if (!CPrice::Add($arPrice))
				{
					$ex = $APPLICATION->GetException();
	      			echo $ex->GetString();
	      		}

	      	} else {
            	pre("Ошибка оффера");
            	pre($el->LAST_ERROR);
           	}

		}

		} else {
			pre('Ошибка');
			pre($el->LAST_ERROR);
			continue;
		}
	}
}