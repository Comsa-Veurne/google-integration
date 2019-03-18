<?php
namespace Comsa\GoogleIntegration;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;

class Connector {
    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * @var string
     */
    private $redirectUri;

    /**
     * @var string
     */
    private $urlAuthorize;

    /**
     * @var string
     */
    private $urlAccessToken;

    /**
     * @var string
     */
    private $urlResourceOwnerDetails;

    /**
     * Connector constructor.
     * @param string $clientId
     * @param string $clientSecret
     * @param string $redirectUri
     * @param string $urlAuthorize
     * @param string $urlAccessToken
     * @param string $urlResourceOwnerDetails
     */
    public function __construct(string $clientId, string $clientSecret, string $redirectUri, string $urlAuthorize, string $urlAccessToken, string $urlResourceOwnerDetails)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;
        $this->urlAuthorize = $urlAuthorize;
        $this->urlAccessToken = $urlAccessToken;
        $this->urlResourceOwnerDetails = $urlResourceOwnerDetails;
    }

    public function connect()
    {
        $provider = new GenericProvider([
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'redirectUri' => $this->redirectUri,
            'urlAuthorize' => $this->urlAuthorize,
            'urlAccessToken' => $this->urlAccessToken,
            'urlResourceOwnerDetails' => $this->urlResourceOwnerDetails
        ]);

        if (!isset($_GET['code'])) {
            $authorizationUrl = $provider->getAuthorizationUrl();
            $_SESSION['oauth2state'] = $provider->getState();
            header('Location: ' . $authorizationUrl);
            exit;
        } elseif (empty($_GET['state']) || (isset($_SESSION['oauth2state']) && $_GET['state'] !== $_SESSION['oauth2state'])) {
            if (isset($_SESSION['oauth2state'])) {
                unset($_SESSION['oauth2state']);
            }
            exit('Invalid state');
        } else {
            try {
                // Try to get an access token using the authorization code grant.
                $accessToken = $provider->getAccessToken('authorization_code', [
                    'code' => $_GET['code']
                ]);

                // We have an access token, which we may use in authenticated
                // requests against the service provider's API.
                echo 'Access Token: ' . $accessToken->getToken() . "<br>";
                echo 'Refresh Token: ' . $accessToken->getRefreshToken() . "<br>";
                echo 'Expired in: ' . $accessToken->getExpires() . "<br>";
                echo 'Already expired? ' . ($accessToken->hasExpired() ? 'expired' : 'not expired') . "<br>";

                // Using the access token, we may look up details about the
                // resource owner.
                $resourceOwner = $provider->getResourceOwner($accessToken);

                var_export($resourceOwner->toArray());

                // The provider provides a way to get an authenticated API request for
                // the service, using the access token; it returns an object conforming
                // to Psr\Http\Message\RequestInterface.
                $request = $provider->getAuthenticatedRequest(
                    'GET',
                    'http://brentertainment.com/oauth2/lockdin/resource',
                    $accessToken
                );

            } catch (IdentityProviderException $e) {
                exit($e->getMessage());
            }
        }
    }
}