<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$client = new SoapClient('http://www.cbr.ru/DailyInfoWebServ/DailyInfo.asmx?wsdl');

$result = $client->EnumValutes(true);

$val = simplexml_load_string($result->EnumValutesResult->any);

foreach ($val->ValuteData->EnumValutes as  $value) {
	echo $value->Vname . '  |  ' . $value->Vnom . '  |  ' . $value->VcharCode .  "<br>";
	
};
