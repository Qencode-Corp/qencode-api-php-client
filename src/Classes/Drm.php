<?php


namespace Qencode\Classes;

class Drm
{
    private $username;
    private $password;
    private $lastResponseRaw;
    private $lastResponse;
    private $curlConnectTimeout = 20;
    private $curlTimeout = 20;
    const USER_AGENT = 'Qencode PHP API SDK 1.1';
    const FPS_DRM_KEYGENERATOR_URI_TEMPLATE = 'https://cpix.ezdrm.com/KeyGenerator/cpix.aspx?k=%s&u=%s&p=%s&c=resourcename&m=2';
    const CENC_DRM_KEYGENERATOR_URI_TEMPLATE = 'https://cpix.ezdrm.com/KeyGenerator/cpix.aspx?k=%s&u=%s&p=%s&c=resourcename&m=1';
    const DRM_KEY_URL_TEMPLATE = 'skd://fps.ezdrm.com/;%s';

    public function __construct($username, $password)
    {
       $this->username = $username;
       $this->password = $password;
    }

    public function get_guid() {
       return $this->_get_guid();
    }

    public function fps_drm($uid = null) {
        $asset_id = $uid ? $uid : $this->_get_guid();
        $url = sprintf(self::FPS_DRM_KEYGENERATOR_URI_TEMPLATE, $asset_id, $this->username, $this->password);
        $response = $this->post($url, array());
        $xml = new  xmlToArrayParser($response);
        $data = $xml->array;
        //print_r($data);
        $kid = $data['cpix:CPIX']['cpix:ContentKeyList']['cpix:ContentKey']['attrib']['kid'];
        $iv = $data['cpix:CPIX']['cpix:ContentKeyList']['cpix:ContentKey']['attrib']['explicitIV'];
        $key = $data['cpix:CPIX']['cpix:ContentKeyList']['cpix:ContentKey']['cpix:Data']['pskc:Secret']['pskc:PlainValue'];
        $key_hex = bin2hex(base64_decode($key));
        $iv_hex = bin2hex(base64_decode($iv));
        $key_url = sprintf(self::DRM_KEY_URL_TEMPLATE,  $kid);
        $payload = array('AssetID'=>$asset_id);
        $res = array('key'=>$key_hex, 'iv'=>$iv_hex, 'key_url'=>$key_url);
        return array('data'=>$res, 'payload'=>$payload);
    }

    public function cenc_drm($uid = null) {
        $asset_id = $uid ? $uid : $this->_get_guid();
        $url = sprintf(self::CENC_DRM_KEYGENERATOR_URI_TEMPLATE, $asset_id, $this->username, $this->password);
        $response = $this->post($url, array());
        $xml = new  xmlToArrayParser($response);
        $data = $xml->array;
        //print_r($data);
        $key_id = $data['cpix:CPIX']['cpix:ContentKeyList']['cpix:ContentKey']['attrib']['kid'];
        $key = $data['cpix:CPIX']['cpix:ContentKeyList']['cpix:ContentKey']['cpix:Data']['pskc:Secret']['pskc:PlainValue'];
        $pssh = $data['cpix:CPIX']['cpix:DRMSystemList']['cpix:DRMSystem'][0]['cpix:PSSH'];
        $key_id_hex =  str_replace('-', '', $key_id);
        $key_hex = bin2hex(base64_decode($key));
        $key_url = sprintf(self::DRM_KEY_URL_TEMPLATE,  $key_id);
        $payload = array('AssetID'=>$asset_id);
        $res = array('key'=>$key_hex, 'key_id'=>$key_id_hex, 'pssh'=>$pssh, 'key_url'=>$key_url);
        return array('data'=>$res, 'payload'=>$payload);
    }

    private function _get_guid() {
        $data = PHP_MAJOR_VERSION < 7 ? openssl_random_pseudo_bytes(16) : random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // Set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // Set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    private function post($url, $params = [], $arrays = null)
    {
        return $this->_request('POST', $url, $params, $arrays);
    }

    private function _request($method, $url, array $params = [], $arrays = null)
    {

        if (!empty($params) & is_array($params)) {
            $params = http_build_query($params);
        }
        if (is_array($arrays)) {
            foreach ($arrays as $key => $value) {
                $encoded_value = json_encode($value);
                $encoded_value = preg_replace('/,\s*"[^"]+":null|"[^"]+":null,?/', '', $encoded_value);
                $params .= '&'.$key.'='.$encoded_value;
            }
        }
        //echo $url;
        //echo "\n";
        //echo $params."\n\n";

        $curl = curl_init($url);
        //curl_setopt($curl, CURLOPT_USERPWD, $this->key);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 3);

        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->curlConnectTimeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->curlTimeout);

        curl_setopt($curl, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));

        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);


        $this->lastResponseRaw = curl_exec($curl);

        $errorNumber = curl_errno($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($errorNumber) {
            throw new QencodeException('CURL: ' . $error, $errorNumber);
        }

        //echo $this->lastResponseRaw;
        //echo "\n\n";
        $this->lastResponse = $response = $this->lastResponseRaw;
        //print_r($response);
        return $response;
    }
}

class xmlToArrayParser {
    /** The array created by the parser can be assigned to any variable: $anyVarArr = $domObj->array.*/
    public  $array = array();
    public  $parse_error = false;
    private $parser;
    private $pointer;

    /** Constructor: $domObj = new xmlToArrayParser($xml); */
    public function __construct($xml) {
        $this->pointer =& $this->array;
        $this->parser = xml_parser_create("UTF-8");
        xml_set_object($this->parser, $this);
        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
        xml_set_element_handler($this->parser, "tag_open", "tag_close");
        xml_set_character_data_handler($this->parser, "cdata");
        $this->parse_error = xml_parse($this->parser, ltrim($xml))? false : true;
    }

    /** Free the parser. */
    public function __destruct() { xml_parser_free($this->parser);}

    /** Get the xml error if an an error in the xml file occured during parsing. */
    public function get_xml_error() {
        if($this->parse_error) {
            $errCode = xml_get_error_code ($this->parser);
            $thisError =  "Error Code [". $errCode ."] \"<strong style='color:red;'>" . xml_error_string($errCode)."</strong>\",
                            at char ".xml_get_current_column_number($this->parser) . "
                            on line ".xml_get_current_line_number($this->parser)."";
        }else $thisError = $this->parse_error;
        return $thisError;
    }

    private function tag_open($parser, $tag, $attributes) {
        $this->convert_to_array($tag, 'attrib');
        $idx=$this->convert_to_array($tag, 'cdata');
        if(isset($idx)) {
            $this->pointer[$tag][$idx] = Array('@idx' => $idx,'@parent' => &$this->pointer);
            $this->pointer =& $this->pointer[$tag][$idx];
        }else {
            $this->pointer[$tag] = Array('@parent' => &$this->pointer);
            $this->pointer =& $this->pointer[$tag];
        }
        if (!empty($attributes)) { $this->pointer['attrib'] = $attributes; }
    }

    /** Adds the current elements content to the current pointer[cdata] array. */
    private function cdata($parser, $cdata) { $this->pointer['cdata'] = trim($cdata); }

    private function tag_close($parser, $tag) {
        $current = & $this->pointer;
        if(isset($this->pointer['@idx'])) {unset($current['@idx']);}

        $this->pointer = & $this->pointer['@parent'];
        unset($current['@parent']);

        if(isset($current['cdata']) && count($current) == 1) { $current = $current['cdata'];}
        else if(empty($current['cdata'])) {unset($current['cdata']);}
    }

    /** Converts a single element item into array(element[0]) if a second element of the same name is encountered. */
    private function convert_to_array($tag, $item) {
        if(isset($this->pointer[$tag][$item])) {
            $content = $this->pointer[$tag];
            $this->pointer[$tag] = array((0) => $content);
            $idx = 1;
        }else if (isset($this->pointer[$tag])) {
            $idx = count($this->pointer[$tag]);
            if(!isset($this->pointer[$tag][0])) {
                foreach ($this->pointer[$tag] as $key => $value) {
                    unset($this->pointer[$tag][$key]);
                    $this->pointer[$tag][0][$key] = $value;
                }}}else $idx = null;
        return $idx;
    }
}