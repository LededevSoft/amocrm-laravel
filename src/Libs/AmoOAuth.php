<?php


namespace LebedevSoft\AmoCRM\Libs;

use LebedevSoft\AmoCRM\Models\AmoApps;
use Illuminate\Database\Eloquent\Model;

class AmoOAuth
{
    private $api_data;
    private $db_apps;

    /**
     * AmoCRM constructor.
     * @param $app_id - id інтеграції, збереженої в БД
     */
    public function __construct($app_id)
    {
        $this->db_apps = new AmoApps();
        $api_data = $this->db_apps->getAuthData($app_id);
        if ($api_data) {
            $this->api_data = $api_data;
        } else {
            dd("Not app in DB");
        }
    }

    public function getRedirectUrl($state)
    {
        return "https://www.amocrm.ru/oauth?client_id=" . $this->api_data["client_id"] . "&state=$state&mode=popup";
    }

    //Получение Access Token, Refresh Token
    public function getAccessToken($authCode)
    {
        $link = 'https://' . $this->api_data["base_domain"] . '/oauth2/access_token';
        $auth_data = [
            'client_id' => $this->api_data["client_id"],
            'client_secret' => $this->api_data["client_secret"],
            'grant_type' => 'authorization_code',
            'code' => $authCode,
            'redirect_uri' => $this->api_data["redirect_url"],
        ];
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-oAuth-client/1.0');
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($auth_data));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        $out = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        $code = (int)$code;
        $errors = [
            400 => 'Bad request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not found',
            500 => 'Internal server error',
            502 => 'Bad gateway',
            503 => 'Service unavailable',
        ];
        try {
            if ($code < 200 || $code > 204) {
                throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undefined error', $code);
            }
        } catch (\Exception $e) {
            die('Ошибка: ' . $e->getMessage() . PHP_EOL . 'Код ошибки: ' . $e->getCode());
        }
        $response = json_decode($out, true);
        $response["auth_id"] = $this->api_data["auth_id"];
        $this->saveToken($response);
    }

    private function saveToken($token_data)
    {
        $update_param = [
            "access_token" => $token_data["access_token"],
            "refresh_token" => $token_data["refresh_token"]
        ];
        if (isset($token_data["expires_in"])) {
            $update_param["expires"] = date("Y-m-d H:i:s", time() + $token_data["expires_in"]);
        } elseif (isset($token_data["expired_in"])) {
            $update_param["expires"] = date("Y-m-d H:i:s", $token_data["expired_in"]);
        }
        $this->db_apps->where("id", $token_data["auth_id"])->update($update_param);
    }
}
