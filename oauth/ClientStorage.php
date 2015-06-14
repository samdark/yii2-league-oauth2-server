<?php

namespace app\oauth;

use League\OAuth2\Server\Entity\ClientEntity;
use League\OAuth2\Server\Entity\SessionEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\ClientInterface;
use yii\db\Query;

class ClientStorage extends AbstractStorage implements ClientInterface
{
    /**
     * {@inheritdoc}
     */
    public function get($clientId, $clientSecret = null, $redirectUri = null, $grantType = null)
    {
        $query = (new Query())
            ->from('oauth_client')
            ->select('oauth_client.*')
            ->where(['oauth_client.id' => $clientId]);


        if ($clientSecret !== null) {
            $query->andWhere(['oauth_client.secret' => $clientSecret]);
        }

        if ($redirectUri) {
            $query->leftJoin('oauth_client_redirect_uri', 'oauth_client.id = oauth_client_redirect_uri.client_id')
                  ->addSelect(['oauth_client.*', 'oauth_client_redirect_uri.*'])
                  ->andWhere(['oauth_client_redirect_uri.redirect_uri' => $redirectUri]);
        }

        $result = $query->all();

        if (count($result) === 1) {
            $client = new ClientEntity($this->server);
            $client->hydrate([
                'id'    =>  $result[0]['id'],
                'name'  =>  $result[0]['name'],
            ]);

            return $client;
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function getBySession(SessionEntity $session)
    {
        $result = (new Query())
            ->from('oauth_client')
            ->select(['oauth_client.id', 'oauth_client.name'])
            ->leftJoin('oauth_session', 'oauth_client.id = oauth_session.client_id')
            ->where(['oauth_session.id' => $session->getId()])
            ->all();

        if (count($result) === 1) {
            $client = new ClientEntity($this->server);
            $client->hydrate([
                'id'    =>  $result[0]['id'],
                'name'  =>  $result[0]['name'],
            ]);

            return $client;
        }

        return;
    }
}
