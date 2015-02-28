<?php
namespace SocialiteProviders\xREL;

use Guzzle\Http\Exception\BadResponseException;
use Laravel\Socialite\One\User;
use League\OAuth1\Client\Credentials\TokenCredentials;
use League\OAuth1\Client\Server\Server as BaseServer;

class Server extends BaseServer
{
    /**
     * {@inheritDoc}
     */
    public function urlTemporaryCredentials()
    {
        return 'http://api.xrel.to/api/oauth/temp_token';
    }

    /**
     * {@inheritDoc}
     */
    public function urlAuthorization()
    {
        return 'http://api.xrel.to/api/oauth/authorize';
    }

    /**
     * {@inheritDoc}
     */
    public function urlTokenCredentials()
    {
        return 'http://api.xrel.to/api/oauth/access_token';
    }

    /**
     * {@inheritDoc}
     */
    public function urlUserDetails()
    {
        return 'http://api.xrel.to/api/user/get_authd_user.json';
    }

    /**
     * {@inheritDoc}
     */
    public function userDetails($data, TokenCredentials $tokenCredentials)
    {
        $user           = new User();
        $user->id       = $data['id'];
        $user->nickname = $data['name'];
        $user->avatar   = array_get($data, 'avatar_url');
        $user->extra    = array_diff_key($data, array_flip([
            'id', 'name', 'avatar_url',
        ]));

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function userUid($data, TokenCredentials $tokenCredentials)
    {
        return $data['id'];
    }

    /**
     * {@inheritDoc}
     */
    public function userEmail($data, TokenCredentials $tokenCredentials)
    {
        return;
    }

    /**
     * {@inheritDoc}
     */
    public function userScreenName($data, TokenCredentials $tokenCredentials)
    {
        return $data['name'];
    }

    /**
     * {@inheritDoc}
     */
    protected function fetchUserDetails(TokenCredentials $tokenCredentials, $force = true)
    {
        if (!$this->cachedUserDetailsResponse || $force == true) {
            $url = $this->urlUserDetails();

            $client = $this->createHttpClient();

            $header              = $this->protocolHeader('GET', $url, $tokenCredentials);
            $authorizationHeader = ['Authorization' => $header];
            $headers             = $this->buildHttpClientHeaders($authorizationHeader);

            try {
                $response = $client->get($url, $headers)->send();
            } catch (BadResponseException $e) {
                $response   = $e->getResponse();
                $body       = $response->getBody();
                $statusCode = $response->getStatusCode();

                throw new \Exception(
                    "Received error [$body] with status code [$statusCode] when retrieving token credentials."
                );
            }

            $this->cachedUserDetailsResponse = $this->parseJson($response->getBody());
        }

        return $this->cachedUserDetailsResponse;
    }

    /**
     * Parse the JSON Response.
     *
     * @param string $data
     *
     * @return string
     */
    private function parseJson($data)
    {
        return json_decode(trim(substr($data, 10, -2)), true)['payload'];
    }
}
