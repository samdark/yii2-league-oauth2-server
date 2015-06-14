<?php

namespace app\oauth;

use League\OAuth2\Server\Entity\RefreshTokenEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\RefreshTokenInterface;
use yii\db\Query;

class RefreshTokenStorage extends AbstractStorage implements RefreshTokenInterface
{
    /**
     * {@inheritdoc}
     */
    public function get($token)
    {
        $result = (new Query())
            ->from('oauth_refresh_token')
            ->where(['refresh_token' => $token])
            ->all();

        if (count($result) === 1) {
            $token = (new RefreshTokenEntity($this->server))
                        ->setId($result[0]['refresh_token'])
                        ->setExpireTime($result[0]['expire_time'])
                        ->setAccessTokenId($result[0]['access_token']);

            return $token;
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function create($token, $expireTime, $accessToken)
    {
        \Yii::$app->db->createCommand()->insert('oauth_refresh_token', [
            'refresh_token'     =>  $token,
            'access_token'    =>  $accessToken,
            'expire_time'   =>  $expireTime,
        ])->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(RefreshTokenEntity $token)
    {
        \Yii::$app->db->createCommand()->delete('oauth_refresh_token', ['refresh_token' => $token->getId()])->execute();
    }
}
