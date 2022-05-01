<?php

//Zoho crm invoice record ID
$id = "3894120000000222110" ;
$authtoken = "d3dedcd83e42875f6f97163144701f20" ;


require "vendor/autoload.php" ;
require "zohoFunction.php" ;

use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\PlatformService\PlatformService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Invoice;
use QuickBooksOnline\API\Facades\Line;


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

$getInvoice = getInvoice($authtoken,$id);

$lineArray = array();
// create three Lines
$i = 0;
foreach ($getInvoice["products"] as $key) {

  $productData = getProduct($authtoken,$key['FL'][0]['content']);
  $productQBID = $productData["QBID"] ;

   $LineObj = Line::create([
       "Description" => $key['FL'][11]['content'],
       "Amount" => $key['FL'][9]['content'],
       "DetailType" => "SalesItemLineDetail",
       "SalesItemLineDetail" => [
           "ItemRef" => [
               "value" => $productQBID,
               "name" => $key['FL'][1]['content']
           ],
           "UnitPrice" => $key['FL'][2]['content'],
           "Qty" => $key['FL'][3]['content'],
           "TaxCodeRef" => [
               "value" => "NON"
           ]
       ]
   ]);
   $lineArray[] = $LineObj;
}


//Add a new Invoice
$theResourceObj = Invoice::create([
     "Line" =>  $lineArray,
    "CustomerRef"=> [
     "value"=> $getInvoice["contactQBID"]
     ]
]);
$resultingObj = $dataService->Add($theResourceObj);
$error = $dataService->getLastError();
if ($error) {
    echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
    echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
    echo "The Response message is: " . $error->getResponseBody() . "\n";
}
else {
    echo "Created Id={$resultingObj->Id}. Reconstructed response body:\n\n";
    $xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($resultingObj, $urlResource);
    echo $xmlBody . "\n";
}

 ?>
