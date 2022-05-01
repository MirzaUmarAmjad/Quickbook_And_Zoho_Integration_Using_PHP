<?php

//Zoho crm product record ID
$id = "3894120000000222098" ;
$authtoken = "d3dedcd83e42875f6f97163144701f20" ;


require "vendor/autoload.php" ;
require "zohoFunction.php" ;

use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\PlatformService\PlatformService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Item;


//fetch refresh token from the file
$myfile = fopen("refreshToken.txt", "r") or die("Unable to open file!");
$refreshTokenFromFile =  fgets($myfile);
fclose($myfile);

$dataService = DataService::Configure(array(
    'auth_mode' => 'oauth2',
    'ClientID' => "Q0lyVljeNt2iwIqjdqAOlKLYFn4MRnKzyCd3r4JcDIiPqoVPLi",
    'ClientSecret' => "0kArxaqbehRuE8bZkySFaIuoLpjZu4DrWAXyiCiQ",
    'refreshTokenKey' => $refreshTokenFromFile,
    'QBORealmID' => "123146389610429",
    'baseUrl' => "Production"
));



$dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");
$OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
//generate the access token
$accessToken = $OAuth2LoginHelper->refreshToken();
//fetch the refresh token from json which is generated for access token
$refreshTokenforSaveInFile = $accessToken->getRefreshToken() ;
//save the new refresh token
$myfile = fopen("refreshToken.txt", "w") or die("Unable to open file!");
fwrite($myfile, $refreshTokenforSaveInFile);
fclose($myfile);

$getProduct = getProduct($authtoken,$id);

$dateTime = new \DateTime('NOW');
$Item = Item::create([
      "Name" => $getProduct["ProductName"],
      "Description" => $getProduct["Description"],
      "Active" => true,
      "FullyQualifiedName" => $getProduct["ProductName"],
      "Taxable" => true,
      "UnitPrice" => $getProduct["UnitPrice"],
      "Type" => "Inventory",
      "IncomeAccountRef"=> [
        "value"=> 79,
        "name" => "Landscaping Services:Job Materials:Fountains and Garden Lighting"
      ],
      "PurchaseDesc"=> $getProduct["Description"],
      "PurchaseCost"=> $getProduct["UnitPrice"],
      "ExpenseAccountRef"=> [
        "value"=> 80,
        "name"=> "Cost of Goods Sold"
      ],
      "AssetAccountRef"=> [
        "value"=> 81,
        "name"=> "Inventory Asset"
      ],
      "TrackQtyOnHand" => true,
      "QtyOnHand"=> $getProduct["QtyInStock"],
      "InvStartDate"=> $dateTime
]);


$resultingObj = $dataService->Add($Item);
$error = $dataService->getLastError();
if ($error) {
    echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
    echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
    echo "The Response message is: " . $error->getResponseBody() . "\n";
}
else {
    $url = "https://crm.zoho.com/crm/private/xml/Products/updateRecords?" ;
    $para = "authtoken=".$authtoken."&scope=crmapi&id=".$id."&xmlData=<Products><row no='1'><FL val='QBID'>".$resultingObj->Id."</FL></row></Products>" ;
    curl($url,$para) ;
}

 ?>
