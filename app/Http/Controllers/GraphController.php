<?php
namespace App\Http\Controllers;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use App\Http\Controllers\Controller;
use GuzzleHttp;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GraphController
{

    private $graphClient, $clientID, $clientSecret, $tenantID, $accessToken, $controlFile;

    public function __construct()
    {
        $this->graph = new Graph();
        $this->controlFile = "appInfo.json";
        $this->clientID = '55dc7aba-5f5f-42c1-825c-1a0cf5ba31c6';
        $this->clientSecret = 'bfbMj9TguqrLmAh65n6msht';
        $this->tenantID = 'e3e50d9b-724a-45af-9ba9-22a1aff3e8f0';
        $this->SMSFolderID = 'AAMkAGQ1MWM5ZDkzLTFhMDUtNGM5Zi05MGJkLTVmOTljOWM1NjQ4ZQAuAAAAAADFAKbYI361QLDrunfzzf1EAQB8_bw1MgiBQZ8X2z14nk2fAAANpH_3AAA=';
        $this->accessToken = $this->getToken();
        $this->setToken($this->accessToken);
    }

    function getToken()
    {
     $file = Storage::get($this->controlFile);
     $file = json_decode($file);
     return $file->access_token;

    }

    function generateToken()
    {
        $guzzle = new GuzzleHttp\Client;
        $url = 'https://login.microsoftonline.com/' . $this->tenantID . '/oauth2/token?api-version=1.0';
        $token = json_decode($guzzle->post($url, [
                'form_params' => [
                    'client_id' => $this->clientID,
                    'client_secret' => $this->clientSecret,
                    'resource' => 'https://graph.microsoft.com/',
                    'grant_type' => 'client_credentials',
                ],
            ])->getBody()->getContents());

        $accessToken = $token->access_token;
        $tokenTime = new DateTime('now', new \DateTimeZone('America/Chicago'));

        $currentTime = $tokenTime->format("D-M-y H:i:s");
        echo $currentTime;
        $expireTime = $tokenTime->modify("+55 minutes");
        echo $expireTime->format("D-M-y H:i:s");

        Storage::put($this->controlFile, json_encode(array("access_token" => $accessToken)));
        return $accessToken;
    }


    function setToken($accessToken)
    {

        $this->graph->setAccessToken($accessToken);
    }



    public function getName()
    {




        $user = $this->graph->createRequest("GET", "/users/dbdd8a07-7c10-4209-b4e9-70da90d1ad45")
                      ->setReturnType(Model\User::class)
                      ->execute();

        var_dump($user);

        echo "Hello, I am {$user->getGivenName()} ";
    }

    public function createSubscription()
    {
        $date = new DateTime('now');
        $date = $date->modify("+1 day");
        $date = $date->format(DateTime::ATOM);

        $request = array("changeType" => "created" , "notificationUrl" => "https://f5213eae.ngrok.io/success",
            "resource" => "users/dbdd8a07-7c10-4209-b4e9-70da90d1ad45/mailFolders/AAMkAGQ1MWM5ZDkzLTFhMDUtNGM5Zi05MGJkLTVmOTljOWM1NjQ4ZQAuAAAAAADFAKbYI361QLDrunfzzf1EAQB8_bw1MgiBQZ8X2z14nk2fAAANpH_3AAA=/messages",
            "expirationDateTime" => $date, "clientState" => "subscription-identifier");


        $message = $this->graph->createRequest("POST", "/subscriptions")
                         ->setReturnType(Model\Subscription::class)
                         ->attachBody($request)
                         ->execute();


        var_dump($message);


    }

    public function createSubscriptionCurl()
    {
        $s = curl_init();
        $url = "https://graph.microsoft.com/v1.0/subscriptions";
        $date = new DateTime('now');
        $date = $date->modify("+1 day");
        $date = $date->format(DateTime::ATOM);

        $request = array("changeType" => "created" , "notificationUrl" => "https://f5213eae.ngrok.io/success",
            "resource" => "users/dbdd8a07-7c10-4209-b4e9-70da90d1ad45/mailFolders/AAMkAGQ1MWM5ZDkzLTFhMDUtNGM5Zi05MGJkLTVmOTljOWM1NjQ4ZQAuAAAAAADFAKbYI361QLDrunfzzf1EAQB8_bw1MgiBQZ8X2z14nk2fAAANpH_3AAA=/messages",
            "expirationDateTime" => $date, "clientState" => "subscription-identifier");


        $request = json_encode($request);
        var_dump($request);
        curl_setopt($s,CURLOPT_URL,$url);
        //curl_setopt($s,CURLOPT_POST, true);
        curl_setopt($s,CURLOPT_POSTFIELDS,$request);
        curl_setopt($s,CURLOPT_HTTPHEADER,array("Authorization: Bearer {$this->accessToken}"));
        curl_setopt($s, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($s,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($s,CURLOPT_FOLLOWLOCATION, 1);

        $response = curl_exec($s);
        echo($response);


    }

    public function getValidationToken(Request $request)
    {
        header("Content-Type: text/plain");
        return $request->query('validationToken');
    }

}


?>