<?php

namespace app\oauth;

use League\OAuth2\Server\Entity\AccessTokenEntity;
use League\OAuth2\Server\Entity\AuthCodeEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Entity\SessionEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\SessionInterface;
use yii\db\Query;

class SessionStorage extends AbstractStorage implements SessionInterface
{
    /**
     * {@inheritdoc}
     */
    public function getByAccessToken(AccessTokenEntity $accessToken)
    {
        $result = (new Query())
            ->from('oauth_session')
            ->select(['oauth_session.id', 'oauth_session.owner_type', 'oauth_session.owner_id', 'oauth_session.client_id', 'oauth_session.client_redirect_uri'])
            ->leftJoin('oauth_access_token', 'oauth_access_token.session_id = oauth_session.id')
            ->where(['oauth_access_tokens.access_token' => $accessToken->getId()])
            ->all();

        if (count($result) === 1) {
            $session = new SessionEntity($this->server);
            $session->setId($result[0]['id']);
            $session->setOwner($result[0]['owner_type'], $result[0]['owner_id']);

            return $session;
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function getByAuthCode(AuthCodeEntity $authCode)
    {
        $result = (new Query())
            ->from('oauth_session')
            ->select(['oauth_session.id', 'oauth_session.owner_type', 'oauth_session.owner_id', 'oauth_session.client_id', 'oauth_session.client_redirect_uri'])
            ->leftJoin('oauth_auth_code', 'oauth_auth_code.session_id = oauth_session.id')
            ->where('oauth_auth_code.auth_code', $authCode->getId())
            ->all();

        if (count($result) === 1) {
            $session = new SessionEntity($this->server);
            $session->setId($result[0]['id']);
            $session->setOwner($result[0]['owner_type'], $result[0]['owner_id']);

            return $session;
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function getScopes(SessionEntity $session)
    {
        $result = (new Query())
            ->from('oauth_session')
            ->select('oauth_scope.*')
            ->leftJoin('oauth_session_scope', 'oauth_session.id = oauth_session_scope.session_id')
            ->leftJoin('oauth_scope', 'oauth_scope.id = oauth_session_scope.scope')
            ->where(['oauth_session.id' => $session->getId()])
            ->all();

        $scopes = [];

        foreach ($result as $scope) {
            $scopes[] = (new ScopeEntity($this->server))->hydrate([
                'id'            =>  $scope['id'],
                'description'   =>  $scope['description'],
            ]);
        }

        return $scopes;
    }

    /**
     * {@inheritdoc}
     */
    public function create($ownerType, $ownerId, $clientId, $clientRedirectUri = null)
    {
        Yii::$app->db->createCommand()->insert('oauth_session', [
            'owner_type'  =>    $ownerType,
            'owner_id'    =>    $ownerId,
            'client_id'   =>    $clientId,
        ]);

        $id = Yii::$app->db->getLastInsertID();
        return $id;
    }

    /**
     * {@inheritdoc}
     */
    public function associateScope(SessionEntity $session, ScopeEntity $scope)
    {
        Yii::$app->db->createCommand()->insert('oauth_session_scope', [
            'session_id'    =>  $session->getId(),
            'scope'         =>  $scope->getId(),
        ]);
    }
}
