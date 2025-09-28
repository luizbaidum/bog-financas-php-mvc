<?php

namespace MF\API;

use Exception;
use MF\API\ProtectedInfos\GitHubInfos;

class GitHub {
    public function getRepoRelease() {
        try {

            $infos = new GitHubInfos();

            $owner = $infos->getOwner();
            $repo = $infos->getRepo();
            $token = $infos->getToken();

            $url = "https://api.github.com/repos/$owner/$repo/releases";

            $headers = [
                'Accept: application/vnd.github+json',
                'Authorization: Bearer ' . $token,
                'X-GitHub-Api-Version: 2022-11-28',
                'User-Agent: ' . $repo
            ];

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . '\ProtectedInfos\cacert.pem');
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_VERBOSE, true);

            $response = curl_exec($ch);
            curl_close($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            $arr_decode = json_decode($response, true);

            if (curl_errno($ch)) {
                throw new Exception('Erro cURL: ' . curl_error($ch), $httpCode);
            }

            $ret = [
                'status'  => $httpCode,
                'tag'     => $arr_decode[0]['tag_name'],
                'name'    => $arr_decode[0]['name']
            ];

            return $ret;
        } catch (\Exception $e) {
            //TODO APLICAR LOG ERROR
            $ret = [
                'tag'    => $e->getMessage(),
                'status' => $httpCode
            ];
        }
    }
}