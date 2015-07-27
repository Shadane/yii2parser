<?php
namespace app\components;


use linslin\yii2\curl\Curl;
use Yii;

class MyCurl extends Curl
{
    private function _httpRequest($method, $url, $raw = false)
    {
        //Init
        $body = '';

        //set request type and writer function
        $this->setOption(CURLOPT_CUSTOMREQUEST, strtoupper($method));

        //check if method is head and set no body
        if ($method === 'HEAD') {
            $this->setOption(CURLOPT_NOBODY, true);
            $this->unsetOption(CURLOPT_WRITEFUNCTION);
        } else {
            $this->setOption(CURLOPT_WRITEFUNCTION, function ($curl, $data) use (&$body) {
                $body .= $data;
                return mb_strlen($data, '8bit');
            });
        }


        //setup error reporting and profiling
        Yii::trace('Start sending cURL-Request: '.$url.'\n', __METHOD__);
        Yii::beginProfile($method.' '.$url.'#'.md5(serialize($this->getOption(CURLOPT_POSTFIELDS))), __METHOD__);

        /**
         * proceed curl
         */
        $curl = curl_init($url);
        curl_setopt_array($curl, $this->getOptions());
        $body = curl_exec($curl);
//Добавленный мной кусок кода, который отвечает за попытки перезапроса при ошибке соединения
        $retry = 0;
        while(curl_errno($curl) == 28 && $retry < 5){
            Yii::info('Curl request timeout(errno 28): '.(5-$retry).' retries left', 'parseInfo');
            $body = curl_exec($curl);
            $retry++;
        }
//
        //check if curl was successful
        if ($body === false) {
            throw new Exception('curl request failed: ' . curl_error($curl) , curl_errno($curl));
        }

        //retrieve response code
        $this->responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $this->response = $body;

        //stop curl
        curl_close($curl);

        //end yii debug profile
        Yii::endProfile($method.' '.$url .'#'.md5(serialize($this->getOption(CURLOPT_POSTFIELDS))), __METHOD__);

        //check responseCode and return data/status
        if ($this->responseCode >= 200 && $this->responseCode < 300) { // all between 200 && 300 is successful
            if ($this->getOption(CURLOPT_CUSTOMREQUEST) === 'HEAD') {
                return true;
            } else {
                $this->response = $raw ? $this->response : Json::decode($this->response);
                return $this->response;
            }
        } elseif ($this->responseCode >= 400 && $this->responseCode <= 510) { // client and server errors return false.
            return false;
        } else { //any other status code or custom codes
            return true;
        }
    }
}