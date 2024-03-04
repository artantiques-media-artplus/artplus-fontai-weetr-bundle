<?php
namespace Fontai\Bundle\WeetrBundle\Service;

use GuzzleHttp\Client;


class Weetr
{
  protected $clientId;
  protected $apiKey;
  protected $apiPassword;
  protected $url;

  public function __construct(
    int $clientId,
    string $apiKey,
    string $apiPassword,
    string $url
  )
  {
    $this->clientId = $clientId;
    $this->apiKey = $apiKey;
    $this->apiPassword = $apiPassword;
    $this->url = $url;
  }

  protected function sendCommand(string $command, array $data)
  {
    $client = new Client();
    $response = $client->request(
      'POST',
      sprintf('%s/%s', $this->url, $command),
      [
        'form_params' => array_merge(
          $data,
          [
            'hash' => sha1(md5(sprintf('%d:%s:%s', $this->clientId, sha1($this->apiPassword), $this->apiKey))),
            'user' => $this->clientId
          ]
        )
      ]
    );

    return $response->getBody()->__toString();
  }

  public function changeGroup(string $email, int $newGroup, $oldGroup = 'all')
  {
    $data = $this->sendCommand(
      'changeGroup',
      [
        'email'    => $email,
        'oldGroup' => $oldGroup,
        'newGroup' => $newGroup
      ]
    );

    return @json_decode($data, TRUE);
  }
  
  public function addEmail(string $email, int $group)
  {
    return $this->sendCommand(
      'addEmail',
      [
        'email' => $email,
        'list'  => $group
      ]
    );
  }
  
  public function deactivateEmail($email)
  {
    return $this->sendCommand(
      'deactivateEmail',
      [
        'email' => $email
      ]
    );
  }
  
  public function getEmailStatus($email)
  {
    $data = $this->sendCommand(
      'getEmailStatus',
      [
        'email' => is_array($email) ? $email : [$email]
      ]
    );

    return @json_decode($data, TRUE);
  }
}