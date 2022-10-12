<?php


namespace LebedevSoft\AmoCRM\Libs;

use LebedevSoft\AmoCRM\Models\AmoApps;
use LebedevSoft\AmoCRM\Models\AmoLogs;
use Illuminate\Database\Eloquent\Model;

class AmoCRM
{
    private $db_apps;
    private $db_logs;
    /**
     * @var string access token from amoCRM
     */
    private $access_token;
    /**
     * @var string full domain amocrm portal, example adminka.amocrm.ru
     */
    private $domain;
    /**
     * @var array with amocrm integration data (client_id, client_secret)
     */
    private $api_data;

    /**
     * @var array with contact and lead custom fields list
     */
    private $cfs_list;

    /**
     * AmoCRM constructor.
     * @param $app_id - id інтеграції, збереженої в БД
     */
    function __construct($app_id)
    {
        $this->db_logs = new AmoLogs("amo_lib");
        $this->db_apps = new AmoApps();
        $api_data = $this->db_apps->getApiData($app_id);
        if (($api_data) && (!empty($api_data["access_token"]))) {
            $this->api_data = $api_data;
            $this->domain = $api_data["base_domain"];
            $this->access_token = $api_data["access_token"];
        } else {
            $this->db_logs->addLog("construct", "error", ["error" => "Not access token"]);
            dd("Not access token");
        }
    }

    private function getCURL($url)
    {
        $error_codes = [101, 110, 111, 112, 113];
        $headers = ["Accept: application/json",
            "Authorization: Bearer " . $this->access_token
        ];
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, "amoCRM-oAuth-client/1.0");
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $out = curl_exec($curl);
        curl_close($curl);
        $res = json_decode($out, true);
        if (((isset($res["response"]["error_code"])) && (in_array($res["response"]["error_code"], $error_codes))) || ((isset($res["status"])) && ($res["status"] == 401))) {
            $this->db_logs->addLog("getCurl", "error", ["path" => parse_url($url, PHP_URL_PATH), "params" => parse_url($url, PHP_URL_QUERY), "response" => $res]);
            $this->refreshToken();
            return $this->getCURL($url);
        } else {
            $this->db_logs->addLog("getCurl", "success", ["path" => parse_url($url, PHP_URL_PATH), "params" => parse_url($url, PHP_URL_QUERY), "response" => $res]);
            return $res;
        }
    }

    private function postCURL($url, $data)
    {
        $error_codes = [101, 110, 111, 112, 113];
        $headers = ["Content-Type: application/json",
            "Authorization: Bearer " . $this->access_token
        ];
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, "amoCRM-oAuth-client/1.0");
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $out = curl_exec($curl);
        curl_close($curl);
        $res = json_decode($out, true);
        if (((isset($res["response"]["error_code"])) && (in_array($res["response"]["error_code"], $error_codes))) || ((isset($res["status"])) && ($res["status"] == 401))) {
            $this->db_logs->addLog("postCURL", "error", ["path" => parse_url($url, PHP_URL_PATH), "params" => $data, "response" => $res]);
            $this->refreshToken();
            return $this->postCURL($url, $data);
        } else {
            $this->db_logs->addLog("postCURL", "success", ["path" => parse_url($url, PHP_URL_PATH), "params" => $data, "response" => $res]);
            return $res;
        }
    }

    private function patchCURL($url, $data)
    {
        $error_codes = [101, 110, 111, 112, 113];
        $headers = ["Content-Type: application/json",
            "Authorization: Bearer " . $this->access_token
        ];
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, "amoCRM-oAuth-client/1.0");
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $out = curl_exec($curl);
        curl_close($curl);
        $res = json_decode($out, true);
        if (((isset($res["response"]["error_code"])) && (in_array($res["response"]["error_code"], $error_codes))) || ((isset($res["status"])) && ($res["status"] == 401))) {
            $this->db_logs->addLog("patchCURL", "error", ["path" => parse_url($url, PHP_URL_PATH), "params" => $data, "response" => $res]);
            $this->refreshToken();
            return $this->patchCURL($url, $data);
        } else {
            $this->db_logs->addLog("patchCURL", "success", ["path" => parse_url($url, PHP_URL_PATH), "params" => $data, "response" => $res]);
            return $res;
        }
    }

    private function deleteCURL($url)
    {
        $error_codes = [101, 110, 111, 112, 113];
        $headers = ["Content-Type: application/json",
            "Authorization: Bearer " . $this->access_token
        ];
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, "amoCRM-oAuth-client/1.0");
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $out = curl_exec($curl);
        curl_close($curl);
        $res = json_decode($out, true);
        if (((isset($res["response"]["error_code"])) && (in_array($res["response"]["error_code"], $error_codes))) || ((isset($res["status"])) && ($res["status"] == 401))) {
            $this->db_logs->addLog("deleteCURL", "error", ["path" => parse_url($url, PHP_URL_PATH), "params" => null, "response" => $res]);
            $this->refreshToken();
            return $this->deleteCURL($url);
        } else {
            $this->db_logs->addLog("deleteCURL", "success", ["path" => parse_url($url, PHP_URL_PATH), "params" => null, "response" => $res]);
            return $res;
        }
    }

    /**
     * @param string|null $param з потрібними параметрами (users_groups, task_types)
     * @return mixed
     */
    public function accountInfo(string $param = null)
    {
        $link = "https://" . $this->domain . "/api/v4/account?with=" . $param;
        return $this->getCURL($link);
        //return $response;
    }

    public function addCompany($company)
    {
        $link = "https://" . $this->domain . "/api/v4/companies";
        $response = $this->postCURL($link, $company);
        if (isset($response["_embedded"])) {
            if (sizeof($response["_embedded"]["companies"]) == 1) {
                $res = $response["_embedded"]["companies"][0];
            } else {
                $res = $response["_embedded"]["companies"];
            }
        } else {
            $res = $response;
        }
        return $res;
    }

    public function addContact($contact)
    {
        $link = "https://" . $this->domain . "/api/v4/contacts";
        $response = $this->postCURL($link, $contact);
        if (isset($response["_embedded"])) {
            if (sizeof($response["_embedded"]["contacts"]) == 1) {
                $res = $response["_embedded"]["contacts"][0];
            } else {
                $res = $response["_embedded"]["contacts"];
            }
        } else {
            $res = $response;
        }
        return $res;
    }

    public function addCustomer($customer)
    {
        $link = "https://" . $this->domain . "/api/v4/customers";
        $response = $this->postCURL($link, $customer);
        if (isset($response["_embedded"])) {
            if (sizeof($response["_embedded"]["customers"]) == 1) {
                $res = $response["_embedded"]["customers"][0];
            } else {
                $res = $response["_embedded"]["customers"];
            }
        } else {
            $res = $response;
        }
        return $res;
    }

    public function addCustomerTransactions($customer_id, $transaction)
    {
        $link = "https://" . $this->domain . "/api/v4/customers/" . $customer_id . "/transactions";
        $response = $this->postCURL($link, $transaction);
        if (isset($response["_embedded"])) {
            if (sizeof($response["_embedded"]["transactions"]) == 1) {
                $res = $response["_embedded"]["transactions"][0];
            } else {
                $res = $response["_embedded"]["transactions"];
            }
        } else {
            $res = $response;
        }
        return $res;
    }

    public
    function deleteCustomerTransaction($transaction_id)
    {
        $link = "https://" . $this->domain . "/api/v4/customers/transactions/".$transaction_id;
        $response = $this->deleteCURL($link);
        return $response;
    }

    public function addLead($lead_data)
    {
        $link = "https://" . $this->domain . "/api/v4/leads";
        $response = $this->postCURL($link, $lead_data);
        if (isset($response["_embedded"])) {
            if (sizeof($response["_embedded"]["leads"]) == 1) {
                $res = $response["_embedded"]["leads"][0];
            } else {
                $res = $response["_embedded"]["leads"];
            }
        } else {
            $res = $response;
        }
        return $res;
    }

    public function addNote($entity_type, $note_data)
    {
        $link = "https://" . $this->domain . "/api/v4/" . $entity_type . "/notes";
        $response = $this->postCURL($link, $note_data);
        if (isset($response["_embedded"])) {
            $res = $response["_embedded"];
        } else {
            $res = null;
        }
        return $res;
    }

    public function updateNote($entity_type, $note_data, $entity_id = null, $id_note = null)
    {
        $link = "https://" . $this->domain . "/api/v4/" . $entity_type . "/notes";
        if (!empty($id_note) && !empty($entity_id)){
            $link = "https://" . $this->domain . "/api/v4/" . $entity_type ."/" . $entity_id . "/notes/" . $id_note;
        } else if (!empty($entity_id)){
            $link = "https://" . $this->domain . "/api/v4/" . $entity_type ."/" . $entity_id . "/notes";
        }

        $response = $this->patchCURL($link, $note_data);

        return !empty($response) ? $response : null;
    }

    public function addTask($task_data)
    {
        $link = "https://" . $this->domain . "/api/v4/tasks";
        $Response = $this->postCURL($link, $task_data);
        if (isset($Response["_embedded"])) {
            $res = $Response;
        } else {
            $res = null;
        }
        return $res;
    }

    public function getCompanies($query = null, $get_params = null)
    {
        if (!empty($query)) {
            $link = "https://" . $this->domain . "/api/v4/companies?query=" . $query . "&with=leads,customers,contacts";
        } else {
            $link = "https://" . $this->domain . "/api/v4/companies?with=leads,customers,contacts";
        }
        if ($get_params) {
            $link .= "&" . http_build_query($get_params);
        }
        return $this->getCURL($link);
    }

    public function getCompanyFromID($company_id)
    {
        $link = "https://" . $this->domain . "/api/v4/companies/" . $company_id . "?with=leads,customers,contacts";
        return $this->getCURL($link);
    }

    public function getContacts($query = null, $get_params = null)
    {
        if (!empty($query)) {
            $link = "https://" . $this->domain . "/api/v4/contacts?query=" . $query . "&with=leads";
        } else {
            $link = "https://" . $this->domain . "/api/v4/contacts?with=leads";
        }
        if ($get_params) {
            $link .= "&" . http_build_query($get_params);
        }
        return $this->getCURL($link);
    }

    public function getContactFromID($contact_id)
    {
        $link = "https://" . $this->domain . "/api/v4/contacts/" . $contact_id . "?with=leads";
        return $this->getCURL($link);
    }

    public function getCustomers($query = null, $get_params = null)
    {
        if (!empty($query)) {
            $link = "https://" . $this->domain . "/api/v4/customers?query=" . $query . "&with=contacts,companies,catalog_elements";
        } else {
            $link = "https://" . $this->domain . "/api/v4/customers?with=contacts,companies,catalog_elements";
        }
        if ($get_params) {
            $link .= "&" . http_build_query($get_params);
        }
        return $this->getCURL($link);
    }

    public function getCustomerFromID($customer_id)
    {
        $link = "https://" . $this->domain . "/api/v4/customers/" . $customer_id . "?with=contacts,companies,catalog_elements";
        return $this->getCURL($link);
    }

    /**
     * @param string $entity сутність, з якої необхідно отримати кастомні поля, можливі варіанти - leads, contacts, companies, customers, customers/segments
     * @return null
     */
    public function getCustomFields(string $entity)
    {
        $field_list = null;
        $get = true;
        $p = 1;
        while ($get) {
            $link = "https://" . $this->domain . "/api/v4/" . $entity . "/custom_fields?page=$p";
            $res = $this->getCURL($link);
            if (isset($res["_embedded"]["custom_fields"])) {
                foreach ($res["_embedded"]["custom_fields"] as $field) {
                    $field_list[] = $field;
                }
            }
            if ((isset($res["_page_count"])) && ($res["_page_count"] > 1) && ($res["_page"] < $res["_page_count"])) {
                $p++;
            } else {
                $get = false;

            }
        }
        return $field_list;
    }

    public function getEvents($params = null)
    {
        $link = "https://" . $this->domain . "/api/v4/events";
        if (!empty($params)) {
            $link .= "?" . http_build_query($params);
        }
        return $this->getCURL($link);
    }

    public function getLeads($query = null, $get_params = null, $deleted = null)
    {
        if (!empty($query)) {
            if (!empty($deleted)) {
                $link = "https://" . $this->domain . "/api/v4/leads?query=" . $query . "&with=loss_reason,contacts,catalog_elements,source_id,only_deleted";
            } else {
                $link = "https://" . $this->domain . "/api/v4/leads?query=" . $query . "&with=loss_reason,contacts,catalog_elements,source_id";
            }
        } else {
            if (!empty($deleted)) {
                $link = "https://" . $this->domain . "/api/v4/leads?with=loss_reason,contacts,catalog_elements,only_deleted";
            } else {
                $link = "https://" . $this->domain . "/api/v4/leads?with=loss_reason,contacts,catalog_elements";
            }
        }
        if ($get_params) {
            $link .= "&" . http_build_query($get_params);
        }
        return $this->getCURL($link);
    }

    public function getLeadFromID($lead_id)
    {
        $link = "https://" . $this->domain . "/api/v4/leads/" . $lead_id . "?with=loss_reason,contacts,catalog_elements,source_id";
        return $this->getCURL($link);
    }

    public function getNotes($entity, $params = null, $entity_id = null)
    {

        if ($entity_id) {
            $link = "https://" . $this->domain . "/api/v4/$entity/$entity_id/notes";
        } else {
            $link = "https://" . $this->domain . "/api/v4/$entity/notes";
        }
        if (!empty($params)) {
            $link .= "?" . http_build_query($params);
        }
        return $this->getCURL($link);
    }

    public function getPipelines($pipeline_id = null)
    {
        if (!empty($pipeline_id)) {
            $link = 'https://' . $this->domain . '/api/v4/leads/pipelines/' . $pipeline_id;
            $response = $this->getCURL($link);
            return $response['_embedded']['statuses'];
        } else {
            $link = 'https://' . $this->domain . '/api/v4/leads/pipelines';
            $response = $this->getCURL($link);
            return $response['_embedded']['pipelines'];
        }

    }

    public function getTags($entity)
    {
        $tag_list = null;
        $get = true;
        $p = 1;
        while ($get) {
            $link = "https://" . $this->domain . "/api/v4/" . $entity . "/tags?limit=250&page=$p";
            $res = $this->getCURL($link);
            if (isset($res["_embedded"]["tags"])) {
                foreach ($res["_embedded"]["tags"] as $tag) {
                    $tag_list[] = $tag;
                }
            }
            if (isset($res["_links"]["next"])) {
                $p++;
            } else {
                $get = false;

            }
        }
        return $tag_list;
    }

    public function getTasks($params = null)
    {
        $link = "https://" . $this->domain . "/api/v4/tasks";
        if (!empty($params)) {
            $link .= "?" . http_build_query($params);
        }
        return $this->getCURL($link);
    }

    /**
     * Отримуємо список користувачів акаунту
     * @return null
     */
    public function getUsers()
    {
        $user_list = null;
        $get = true;
        $p = 1;
        while ($get) {
            $link = "https://" . $this->domain . "/api/v4/users?with=role,group,uuid&page=$p";
            $res = $this->getCURL($link);
            if (isset($res["_embedded"]["users"])) {
                foreach ($res["_embedded"]["users"] as $user) {
                    $user_list[] = $user;
                }
            }
            if ((isset($res["_page_count"])) && ($res["_page_count"] > 1) && ($res["_page"] < $res["_page_count"])) {
                $p++;
            } else {
                $get = false;

            }
        }
        return $user_list;
    }

    /**
     * @param string $entity_type сутність, до якої прив'язуємо іншу сутніть, можливі варіанти - leads, contacts, companies, customers
     * @param integer $entity_id ID сутності, до якої прив'язуємо іншу сутніть
     * @param array $link_data дані сутності, яку прив'язуємо
     * @return mixed
     */
    public
    function linkEntity($entity_type, $entity_id, $link_data)
    {
        $link = "https://" . $this->domain . "/api/v4/" . $entity_type . "/" . $entity_id . "/link";
        return $this->postCURL($link, $link_data);
    }

    public
    function linkEntities($entity_type, $link_data)
    {
        $link = "https://" . $this->domain . "/api/v4/$entity_type/link";
        return $this->postCURL($link, $link_data);
    }

    public function setCustomField($field_id, $field_data)
    {
        $cf_data = null;
        if (empty($this->cfs_list)) {
            $this->setCustomFieldsList();
        }

        if (!empty($this->cfs_list)) {
            if (isset($this->cfs_list[$field_id])) {
                if (!empty($field_data)) {
                    switch ($this->cfs_list[$field_id]["type"]) {
                        case "multiselect":
                            $values = null;
                            foreach ($field_data as $val) {
                                $values[] = [
                                    "enum_id" => $val
                                ];
                            }
                            $cf_data = [
                                "field_id" => $field_id,
                                "values" => $values
                            ];
                            break;
                        case "multitext":
                        case "smart_address":
                            $values = null;
                            foreach ($field_data as $val) {
                                $values[] = [
                                    "value" => $val["value"],
                                    "enum_code" => $val["enum"]
                                ];
                            }
                            $cf_data = [
                                "field_id" => $field_id,
                                "values" => $values
                            ];
                            break;
                        default:
                            $cf_data = [
                                "field_id" => $field_id,
                                "values" => [
                                    [
                                        "value" => $field_data
                                    ]
                                ]
                            ];
                            break;
                    }
                } else {
                    $cf_data = [
                        "field_id" => $field_id,
                        "values" => $field_data
                    ];
                }
            }
        }
        return $cf_data;
    }

    private function setCustomFieldsList()
    {
        $cfs_list = null;
        $contact_cfs = $this->getCustomFields("contacts");
        if (!empty($contact_cfs)) {
            foreach ($contact_cfs as $cf) {
                $cfs_list[$cf["id"]] = [
                    "name" => $cf["name"],
                    "type" => $cf["type"]
                ];
            }
        }
        $lead_cfs = $this->getCustomFields("leads");
        if (!empty($lead_cfs)) {
            foreach ($lead_cfs as $cf) {
                $cfs_list[$cf["id"]] = [
                    "name" => $cf["name"],
                    "type" => $cf["type"]
                ];
            }
        }
        $lead_cfs = $this->getCustomFields("companies");
        if (!empty($lead_cfs)) {
            foreach ($lead_cfs as $cf) {
                $cfs_list[$cf["id"]] = [
                    "name" => $cf["name"],
                    "type" => $cf["type"]
                ];
            }
        }
        $lead_cfs = $this->getCustomFields("customers");
        if (!empty($lead_cfs)) {
            foreach ($lead_cfs as $cf) {
                $cfs_list[$cf["id"]] = [
                    "name" => $cf["name"],
                    "type" => $cf["type"]
                ];
            }
        }
        if (!empty($cfs_list)) {
            $this->cfs_list = $cfs_list;
        }
    }

    public
    function updateCompany($company_data, $company_id = null)
    {
        if ($company_id) {
            $link = "https://" . $this->domain . "/api/v4/companies/" . $company_id;
        } else {
            $link = "https://" . $this->domain . "/api/v4/companies";
        }
        return $this->patchCURL($link, $company_data);
    }

    public
    function updateContact($contact_data, $contact_id = null)
    {
        if ($contact_id) {
            $link = "https://" . $this->domain . "/api/v4/contacts/" . $contact_id;
        } else {
            $link = "https://" . $this->domain . "/api/v4/contacts";
        }
        return $this->patchCURL($link, $contact_data);
    }

    public
    function updateCustomer($customer_data, $customer_id = null)
    {
        if ($customer_id) {
            $link = "https://" . $this->domain . "/api/v4/customers/" . $customer_id;
        } else {
            $link = "https://" . $this->domain . "/api/v4/customers";
        }
        return $this->patchCURL($link, $customer_data);
    }

    public
    function updateLead($lead_data, $lead_id = null)
    {
        if ($lead_id) {
            $link = "https://" . $this->domain . "/api/v4/leads/" . $lead_id;
        } else {
            $link = "https://" . $this->domain . "/api/v4/leads";
        }
        return $this->patchCURL($link, $lead_data);
    }

    private function updateToken($token_data)
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

    private
    function refreshToken()
    {
        $amo_url = "https://" . $this->domain . "/oauth2/access_token";
		print_r($amo_url);
        $data = [
            "client_id" => $this->api_data["client_id"],
            "client_secret" => $this->api_data["client_secret"],
            "grant_type" => "refresh_token",
            "refresh_token" => $this->api_data["refresh_token"],
            "redirect_uri" => $this->api_data["redirect_url"],
        ];
		print_r($data);
        $new_token = $this->refreshCURL($amo_url, $data);
		print_r($new_token);
        if (($new_token["status"] == "success") && !empty($new_token["data"])) {
            $expired_in = time() + $new_token["data"]["expires_in"] - 300;
			
            $new_auth_data = [
                "auth_id" => $this->api_data["auth_id"],
                "access_token" => $new_token["data"]["access_token"],
                "refresh_token" => $new_token["data"]["refresh_token"],
                "expired_in" => $expired_in
            ];
            $this->access_token = $new_auth_data["access_token"];
            $this->updateToken($new_auth_data);
        } elseif ($new_token["status"] == "error") {
            dd($new_token["error_data"]);
        }
    }

    private
    function refreshCURL($url, $auth_data)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, "amoCRM-oAuth-client/1.0");
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ["Content-Type:application/json"]);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($auth_data));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        $out = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        $code = (int)$code;
        $errors = [
            400 => "Bad request",
            401 => "Unauthorized",
            403 => "Forbidden",
            404 => "Not found",
            500 => "Internal server error",
            502 => "Bad gateway",
            503 => "Service unavailable",
        ];
        try {
            if ($code < 200 && $code > 204) {
                $this->db_logs->addLog("refreshCURL", "error", ["code" => $code, "response" => json_decode($out, true)]);
                throw new Exception(isset($errors[$code]) ? $errors[$code] : "Undefined error", $code);
            }
        } catch (\Exception $e) {
            $this->db_logs->addLog("refreshCURL", "error", ["code" => $code, "response" => json_decode($out, true)]);
            return [
                "status" => "error",
                "error_data" => "Ошибка: " . $e->getMessage() . PHP_EOL . "Код ошибки: " . $e->getCode()
            ];
        }
        $response = json_decode($out, true);
        $this->db_logs->addLog("refreshCURL", "success", ["code" => $code, "response" => $response]);
        return [
            "status" => "success",
            "data" => $response
        ];
    }

}
