<?php

namespace app\oauth;

use League\OAuth2\Server\Entity\AuthCodeEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\AuthCodeInterface;
use yii\db\Query;

class AuthCodeStorage extends AbstractStorage implements AuthCodeInterface
{
    /**
     * {@inheritdoc}
     */
    public function get($code)
    {
        $result = (new Query())
            ->from('oauth_auth_code')
            ->where(['auth_code' => $code])
            ->andWhere('expire_time >= ' . time())
            ->all();

        if (count($result) === 1) {
            $token = new AuthCodeEntity($this->server);
            $token->setId($result[0]['auth_code']);
            $token->setRedirectUri($result[0]['client_redirect_uri']);
            $token->setExpireTime($result[0]['expire_time']);

            return $token;
        }

        return;
    }

    public function create($token, $expireTime, $sessionId, $redirectUri)
    {
        \Yii::$app->db->createCommand()->insert('oauth_auth_code', [
            'auth_code'     =>  $token,
            'client_redirect_uri'  =>  $redirectUri,
            'session_id'    =>  $sessionId,
            'expire_time'   =>  $expireTime,
        ])->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function getScopes(AuthCodeEntity $token)
    {
        $result = (new Query())
            ->from('oauth_auth_code_scope')
            ->select(['oauth_scope.id', 'oauth_scope.description'])
            ->leftJoin('oauth_scope', 'oauth_auth_code_scope.scope = oauth_scope.id')
            ->where(['auth_code' => $token->getId()])
            ->all();

        $response = [];

        if (count($result) > 0) {
            foreach ($result as $row) {
                $scope = (new ScopeEntity($this->server))->hydrate([
                    'id'            =>  $row['id'],
                    'description'   =>  $row['description'],
                ]);
                $response[] = $scope;
            }
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function associateScope(AuthCodeEntity $token, ScopeEntity $scope)
    {
        \Yii::$app->db->createCommand()->insert('oauth_auth_code_scope', [
            'auth_code' =>  $token->getId(),
            'scope'     =>  $scope->getId(),
        ])->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(AuthCodeEntity $token)
    {
        \Yii::$app->db->createCommand()->delete('oauth_auth_code', ['auth_code' => $token->getId()])->execute();
    }
}
