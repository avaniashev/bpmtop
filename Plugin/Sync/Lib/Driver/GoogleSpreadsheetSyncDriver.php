<?php
App::uses('BaseSyncDriver', 'Sync.Lib/Driver');
class GoogleSpreadsheetSyncDriver extends BaseSyncDriver
{
    public $tokenPath = 'token.json';

    public function getConfigFields()
    {
        $sheets = [];
        if (!empty($this->config['file_id'])){
            $service = $this->getService();
            $response = $service->spreadsheets->get($this->config['file_id']);
            /* @var Google\Service\Sheets\Spreadsheet $response */
            foreach ($response->getSheets() as $sheet){
                /* @var Google\Service\Sheets\Sheet $sheet */
                $sheets[$sheet->getProperties()->getTitle()] = $sheet->getProperties()->getTitle();
            }

        }
        return [
            'Config.file_id' => ['type' => 'text'],
            'Config.sheet' => ['type' => 'select', 'options' => $sheets],
        ];
    }

    function getClient()
    {
        $client = $this->createClient();

        // Load previously authorized token from a file, if it exists.
        // The file token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first
        // time.
        $tokenPath = $this->tokenPath;
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();
                header('location: '.$authUrl);
                exit();

                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
//                $authCode = trim(fgets(STDIN));
//
//                // Exchange authorization code for an access token.
//                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
//                $client->setAccessToken($accessToken);

                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            }
            $this->saveToken($client);
        }
        return $client;
    }

    public function getRemoteFields($type = 'list')
    {
        $rows = $this->getService()->spreadsheets_values->get($this->config['file_id'], $this->config['sheet'].'!1:1');
        return $rows->values[0];
    }

    /**
     * @return \Google\Service\Sheets
     * @throws Exception
     */
    public function getService()
    {
        $client = $this->getClient();
        $service = new Google\Service\Sheets($client);
        return $service;
    }

    public function receiveData($data, $idField = null)
    {
        $rowNum = 0;
        if ($idField && !empty($data[$idField])){
            $letter = $this->getLetterByCalIndex($idField);
            $cells = $this->getService()->spreadsheets_values->get($this->config['file_id'], $this->config['sheet']."!{$letter}2:{$letter}");
            /* @var Google\Service\Sheets\ValueRange $cells */
            foreach ($cells->values as $i => $v){
                if (!empty($v[0]) && $v[0] == $data[$idField]){
                    $rowNum = $i + 2;
                }
            }
        }

        $d = array_fill(0, max(array_keys($data)), '');
        foreach ($data as $key => $val) {
            $d[$key] = empty($val) ? '' : $val;
        }
        $vr = new \Google\Service\Sheets\ValueRange();
        $vr->setValues([array_values($d)]);
        $vr->setMajorDimension('ROWS');
        if ($rowNum == 1){
            throw new CakeException("No write to header");
        }
        if ($rowNum){
            $res = $this->getService()->spreadsheets_values->update(
                $this->config['file_id'],
                $this->config['sheet']."!A$rowNum:$rowNum",
                $vr,
                [
                    'valueInputOption' => 'USER_ENTERED',
                ],
            );
        } else {
            $res = $this->getService()->spreadsheets_values->append(
                $this->config['file_id'],
                $this->config['sheet'].'!A2:2',
                $vr,
                [
                    'valueInputOption' => 'USER_ENTERED',
                ],
            );
        }

//        debug($res);
    }

    /**
     * @return \Google\Client
     * @throws \Google\Exception
     */
    public function createClient(): \Google\Client
    {
        $client = new Google\Client();
        $client->setApplicationName('Google Sheets API PHP Quickstart');
        $client->setScopes([
            'https://www.googleapis.com/auth/spreadsheets',
            'https://www.googleapis.com/auth/drive',
            'https://www.googleapis.com/auth/drive.file',
        ]);
        $client->setAuthConfig(ROOT . '/app/Config/credentials.json');
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');
        return $client;
    }

    /**
     * @param \Google\Client $client
     * @return void
     */
    public function saveToken(\Google\Client $client): void
    {
// Save the token to a file.
        if (!file_exists(dirname($this->tokenPath))) {
            mkdir(dirname($this->tokenPath), 0700, true);
        }
        file_put_contents($this->tokenPath, json_encode($client->getAccessToken()));
    }

    public function setupListener($connectionId, $type)
    {
//        try {
//            $channelId = "sync_{$connectionId}_{$type}_{$this->config['file_id']}";
//            $url = Router::url(['plugin' => 'sync', 'controller' => 'sync_webhook', 'action' => 'process', $connectionId, $type], true);
//            debug($url);
//            debug($channelId);
//            $files = new \Google\Service\Drive($this->getClient());
//            $channel = new \Google\Service\Drive\Channel();
//            $channel->id = $channelId;
//            $channel->type = "webhook";
//            $channel->address = $url;
//            $res = $files->files->watch($this->config['file_id'], $channel);
//        } catch (Exception $e){
//            debug($e);
//        }
    }

    public function sync($connection, $type)
    {
        $filename = TMP . 'sp_hashes/' . md5($this->config['file_id'] . '_' . $this->config['sheet']) . '.hashes';
        $hashes = unserialize(file_get_contents($filename));
        if (empty($hashes)){
            $hashes = [];
        }
        $changed = false;
        $data = $this->getService()->spreadsheets_values->get($this->config['file_id'], $this->config['sheet'].'!A2:ZZ1000');
        foreach ($data->values as $i => $row){
            try {
                $hash = md5(serialize($row));
                if (empty($hashes[$i]) || $hashes[$i] != $hash){
                    // Send to remote
//                debug($row);
                    $createdId = $this->sendUpdateToRemote($connection, $type, $row);
                    if ($createdId){
                        // Update id in the
                        $remoteType = $type == 'src' ? 'dst' : 'src';
                        $field = ClassRegistry::init('Sync.SyncConnectionField')->find('first', [
                            'conditions' => [
                                'sync_connection_id' => $connection['SyncConnection']['id'],
                                $remoteType.'_field' => $createdId['idField'],
                            ]
                        ]);
                        debug($field);
                        debug($createdId);
                        if ($field){
                            $col = $this->getLetterByCalIndex($field['SyncConnectionField'][$type.'_field']);
                            $vr = new \Google\Service\Sheets\ValueRange();
                            $vr->setValues([[$createdId['idValue']]]);
                            $vr->setMajorDimension('ROWS');
                            $rowNum = $i + 2;
                            $res = $this->getService()->spreadsheets_values->update(
                                $this->config['file_id'],
                                $this->config['sheet']."!{$col}{$rowNum}",
                                $vr,
                                [
                                    'valueInputOption' => 'USER_ENTERED',
                                ],
                            );
                            debug($res);
                        }
                    }
                    $hashes[$i] = $hash;
                    $changed = true;
                }
            } catch (Exception $e){
                SyncLog::log($e->getMessage());
            }

        }

        if ($changed){
            debug($hashes);
            file_put_contents($filename, serialize($hashes));
        }

    }

    /**
     * @param mixed $idField
     * @return array
     */
    public function getLetterByCalIndex($idField)
    {
        $azCount = ord('Z') - ord('A') + 1;
        $count = floor($idField / $azCount);
        $letter = $count > 0 ? chr(ord('A') + $count - 1) : '';
        $i = $idField % $azCount;
        $letter .= chr(ord('A') + $i);
        return $letter;
    }
}