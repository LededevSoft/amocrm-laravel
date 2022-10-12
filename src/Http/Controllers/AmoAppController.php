<?php

namespace LebedevSoft\AmoCRM\Http\Controllers;

use LebedevSoft\AmoCRM\Libs\AmoCRM;
use LebedevSoft\AmoCRM\Libs\AmoOAuth;
use LebedevSoft\AmoCRM\Models\AmoApps;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AmoAppController extends Controller
{
    public
    function amoApp()
    {
        $db_apps = new AmoApps();
        $app_id = config("amo.app_id");
        $app = $db_apps->where("id", $app_id)->first();
        if ($app) {
            $amo_auth = new AmoOAuth($app_id);
            $url = $amo_auth->getRedirectUrl(bin2hex(random_bytes(16)));
            echo '<div class="container">
                <button type="button"
                    class="btn btn-lg btn-block btn-outline-primary"
                    onclick="location=\'' . $url . '\'">
                        Подключить AmoCRM
                </button>
              </div>';
        } else {
            dd("Add amo app to DB");
        }

    }

    public
    function amoAuth(Request $request)
    {
        if (isset($request["code"])) {
            $amo_apps = new AmoApps();
            $app_id = config("amo.app_id");
            $amo_auth = new AmoOAuth($app_id);
            $amo_auth->getAccessToken($request["code"]);
            $amo = new AmoCRM($app_id);
            $info = $amo->accountInfo("users_groups");
            $update_param = [
                "account_name" => $info["name"],
                "account_id" => $info["id"],
            ];
            $amo_apps->where("id", $app_id)->update($update_param);
            return redirect()->route('home');
        } else {
            abort(503);
        }
    }
}
