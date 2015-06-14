<?php

namespace app\oauth;

use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\ScopeInterface;
use yii\db\Query;

class ScopeStorage extends AbstractStorage implements ScopeInterface
{
    /**
     * {@inheritdoc}
     */
    public function get($scope, $grantType = null, $clientId = null)
    {
        $result = (new Query())
            ->from('oauth_scope')
            ->where(['id' => $scope])
            ->all();

        if (count($result) === 0) {
            return;
        }

        return (new ScopeEntity($this->server))->hydrate([
            'id'            =>  $result[0]['id'],
            'description'   =>  $result[0]['description'],
        ]);
    }
}
