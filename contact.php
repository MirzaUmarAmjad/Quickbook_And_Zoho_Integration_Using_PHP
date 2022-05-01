<?php

//Zoho crm contact record ID
$id = "3894120000000222092" ;
$authtoken = "d3dedcd83e42875f6f97163144701f20" ;


require "vendor/autoload.php" ;
require "zohoFunction.php" ;

use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\PlatformService\PlatformService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Customer;


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

$getContact = getContact($authtoken,$id);

// Add a customer
$customerObj = Customer::create([
  "BillAddr" => [
     "Line1"=>  $getContact['MailingStreet'],
     "City"=>  $getContact['MailingCity'],
     "Country"=>  $getContact['MailingCountry'],
     "PostalCode"=>  $getContact['MailingZip']
 ],
 // "Notes" =>  "Here are other details.",
 // "Title"=>  "Mr",
 "GivenName"=>  $getContact['FirstName'],
 // "MiddleName"=>  "1B",
 "FamilyName"=>  $getContact['LastName'],
 // "Suffix"=>  "Jr",
 // "FullyQualifiedName"=>  "Evil King",
 // "CompanyName"=>  "King Evial",
 "DisplayName"=>  $getContact['FullName'],
 "PrimaryPhone"=>  [
     "FreeFormNumber"=>  $getContact['Mobile']
 ],
 "PrimaryEmailAddr"=>  [
     "Address" => $getContact['Email']
 ]
]);
$resultingCustomerObj = $dataService->Add($customerObj);
$error = $dataService->getLastError();
if ($error) {
    echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
    echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
    echo "The Response message is: " . $error->getResponseBody() . "\n";
} else {
    $url = "https://crm.zoho.com/crm/private/xml/Contacts/updateRecords?" ;
    $para = "authtoken=".$authtoken."&scope=crmapi&id=".$id."&xmlData=<Contacts><row no='1'><FL val='QBID'>".$resultingCustomerObj->Id."</FL></row></Contacts>" ;
    curl($url,$para) ;
}

 ?>
