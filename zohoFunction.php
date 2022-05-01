<?php

function curl($url , $para)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $para);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $response_info = curl_getinfo($ch);
    curl_close($ch);
    $response_body = substr($response, $response_info['header_size']);
    $crmData = json_decode($response_body,true) ;
    return $crmData ;
}

function getContact($authtoken,$id)
{
    $url = "https://crm.zoho.com/crm/private/json/Contacts/getRecordById?" ;
    $para = "authtoken=".$authtoken."&scope=crmapi&id=".$id ;
    $customerDatas = curl($url,$para) ;
    $customerDatas = $customerDatas['response']['result']['Contacts']['row']['FL'] ;

    $data = array() ;
    foreach ($customerDatas as $customerData)
    {
        if ($customerData["val"] == "Full Name")
        {
            $data['FullName'] = $customerData["content"] ;
        }
        elseif ($customerData["val"] == "First Name")
        {
            $data['FirstName'] = $customerData["content"] ;
        }
        elseif ($customerData["val"] == "Last Name")
        {
            $data['LastName'] = $customerData["content"] ;
        }
        elseif ($customerData["val"] == "Mobile")
        {
            $data['Mobile'] = $customerData["content"] ;
        }
        elseif ($customerData["val"] == "Email")
        {
            $data['Email'] = $customerData["content"] ;
        }
        elseif ($customerData["val"] == "Mailing Street")
        {
            $data['MailingStreet'] = $customerData["content"] ;
        }
        elseif ($customerData["val"] == "Mailing City")
        {
            $data['MailingCity'] = $customerData["content"] ;
        }
        elseif ($customerData["val"] == "Mailing State")
        {
            $data['MailingState'] = $customerData["content"] ;
        }
        elseif ($customerData["val"] == "Mailing Zip")
        {
            $data['MailingZip'] = $customerData["content"] ;
        }
        elseif ($customerData["val"] == "Mailing Country")
        {
            $data['MailingCountry'] = $customerData["content"] ;
        }
        elseif ($customerData["val"] == "QBID")
        {
            $data['QBID'] = $customerData["content"] ;
        }
    }
    return $data ;
}
function getProduct($authtoken,$id)
{
    $url = "https://crm.zoho.com/crm/private/json/Products/getRecordById?" ;
    $para = "authtoken=".$authtoken."&scope=crmapi&id=".$id ;
    $productDatas = curl($url,$para) ;
    $productDatas = $productDatas['response']['result']['Products']['row']['FL'] ;

    $data = array() ;
    foreach ($productDatas as $productData)
    {
        if ($productData["val"] == "Product Name")
        {
            $data['ProductName'] = $productData["content"] ;
        }
        elseif ($productData["val"] == "Description")
        {
            $data['Description'] = $productData["content"] ;
        }
        elseif ($productData["val"] == "Unit Price")
        {
            $data['UnitPrice'] = $productData["content"] ;
        }
        elseif ($productData["val"] == "Qty in Stock")
        {
            $data['QtyInStock'] = $productData["content"] ;
        }
        elseif ($productData["val"] == "QBID")
        {
            $data['QBID'] = $productData["content"] ;
        }
    }
    return $data ;
}



function getInvoice($authtoken,$id)
{
    $url = "https://crm.zoho.com/crm/private/json/Invoices/getRecordById?" ;
    $para = "authtoken=".$authtoken."&scope=crmapi&id=".$id ;
    $invoiceDatas = curl($url,$para) ;
    

    $invoiceDatas = $invoiceDatas['response']['result']['Invoices']['row']['FL'] ;


    $data = array() ;

    foreach ($invoiceDatas as $invoiceData)
    {
        if ($invoiceData["val"] == "Product Details")
        {
            $data['products'] = $invoiceData["product"] ;
        }
        elseif ($invoiceData["val"] == "CONTACTID")
        {
            $contactQBID = getContact("d3dedcd83e42875f6f97163144701f20",$invoiceData["content"]);
            $data['contactQBID'] = $contactQBID["QBID"] ;
        }
    }
    return $data ;
}