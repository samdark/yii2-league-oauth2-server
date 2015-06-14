<?php

namespace app\oauth;

use League\OAuth2\Server\Entity\AccessTokenEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\AccessTokenInterface;
use yii\db\Query;

class AccessTokenStorage extends AbstractStorage implements AccessTokenInterface
{
    /**
     * {@inheritdoc}
     */
    public function get($token)
    {
        $result = (new Query())
            ->from('oauth_access_token')
            ->where(['access_token' => $token])
            ->all();

        if (count($result) === 1) {
            $token = (new AccessTokenEntity($this->server))
                        ->setId($result[0]['access_token'])
                        ->setExpireTime($result[0]['expire_time']);

            return $token;
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function getScopes(AccessTokenEntity $token)
    {
        $result = (new Query())
            ->from('oauth_access_token_scope')
            ->select(['oauth_scope.id', 'oauth_scope.description'])
            ->leftJoin('oauth_scope', 'oauth_access_token_scope.scope = oauth_scope.id')
            ->where('access_token', $token->getId())
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
    public function create($token, $expireTime, $sessionId)
    {
        \Yii::$app->db->createCommand()->insert('oauth_access_token', [
            'access_token'  =>  $token,
            'session_id'    =>  $sessionId,
            'expire_time'   =>  $expireTime,
        ])->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function associateScope(AccessTokenEntity $token, ScopeEntity $scope)
    {
        \Yii::$app->db->createCommand()->insert('oauth_access_token_scope', [
            'access_token'  =>  $token->getId(),
            'scope' =>  $scope->getId(),
        ])->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(AccessTokenEntity $token)
    {
        \Yii::$app->db->createCommand()
            ->delete('oauth_access_token', ['access_token' => $token->getId()])
            ->execute();
    }
}
