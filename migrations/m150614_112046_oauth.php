<?php

use yii\db\Schema;
use yii\db\Migration;

class m150614_112046_oauth extends Migration
{
    protected $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

    public function up()
    {
        $this->createTable('oauth_client', [
            'id' => Schema::TYPE_STRING . ' PRIMARY KEY',
            'secret' => Schema::TYPE_STRING . ' NOT NULL',
            'name' => Schema::TYPE_STRING . ' NOT NULL',
        ], $this->tableOptions);

        $this->createTable('oauth_client_redirect_uri', [
            'id' => Schema::TYPE_PK,
            'client_id' => Schema::TYPE_STRING . ' NOT NULL',
            'redirect_uri' => Schema::TYPE_STRING . ' NOT NULL',
        ], $this->tableOptions);

        $this->addForeignKey('fk-oauth_client_redirect_uri-client_id', 'oauth_client_redirect_uri', 'client_id', 'oauth_client', 'id', 'CASCADE');

        $this->createTable('oauth_scope', [
            'id' => Schema::TYPE_STRING . ' PRIMARY KEY',
            'description' => Schema::TYPE_STRING . ' NOT NULL',
        ], $this->tableOptions);

        $this->db->schema->refresh();

        $this->insert('oauth_scope', [
            'id'            =>  'basic',
            'description'   =>  'Basic details about your account',
        ]);

        $this->insert('oauth_scope', [
            'id'            =>  'email',
            'description'   =>  'Your email address',
        ]);

        $this->insert('oauth_scope', [
            'id'            =>  'photo',
            'description'   =>  'Your photo',
        ]);


        $this->createTable('oauth_session', [
            'id' => Schema::TYPE_BIGPK,
            'owner_type' => Schema::TYPE_STRING . ' NOT NULL',
            'owner_id' => Schema::TYPE_STRING . ' NOT NULL',
            'client_id' => Schema::TYPE_STRING . ' NOT NULL',
            'client_redirect_uri' => Schema::TYPE_STRING,
        ], $this->tableOptions);

        $this->addForeignKey('fk-oauth_session-client_id', 'oauth_session', 'client_id', 'oauth_client', 'id', 'CASCADE');

        $this->createTable('oauth_access_token', [
            'access_token' => Schema::TYPE_STRING . ' PRIMARY KEY',
            'session_id' => Schema::TYPE_BIGINT . ' NOT NULL',
            'expire_time' => Schema::TYPE_INTEGER . ' NOT NULL',
        ], $this->tableOptions);

        $this->addForeignKey('fk-oauth_access_token-session_id', 'oauth_access_token', 'session_id', 'oauth_session', 'id', 'CASCADE');


        $this->createTable('oauth_refresh_token', [
            'refresh_token' => Schema::TYPE_STRING . ' PRIMARY KEY',
            'expire_time' => Schema::TYPE_INTEGER . ' NOT NULL',
            'access_token' => Schema::TYPE_STRING . ' NOT NULL',
        ], $this->tableOptions);
        $this->addForeignKey('fk-oauth_refresh_token-access_token', 'oauth_refresh_token', 'access_token', 'oauth_access_token', 'access_token', 'CASCADE');


        $this->createTable('oauth_auth_code', [
            'auth_code' => Schema::TYPE_STRING . ' PRIMARY KEY',
            'session_id' => Schema::TYPE_BIGINT . ' NOT NULL',
            'expire_time' => Schema::TYPE_INTEGER . ' NOT NULL',
            'client_redirect_uri' => Schema::TYPE_STRING . ' NOT NULL',
        ], $this->tableOptions);

        $this->addForeignKey('fk-oauth_auth_code-session_id', 'oauth_auth_code', 'session_id', 'oauth_session', 'id', 'CASCADE');

        $this->createTable('oauth_access_token_scope', [
            'id' => Schema::TYPE_BIGPK,
            'access_token' => Schema::TYPE_STRING . ' NOT NULL',
            'scope' => Schema::TYPE_STRING . ' NOT NULL',
        ], $this->tableOptions);

        $this->addForeignKey('fk-oauth_access_token_scope-access_token', 'oauth_access_token_scope', 'access_token', 'oauth_access_token', 'access_token', 'CASCADE');
        $this->addForeignKey('fk-oauth_access_token_scope-scope', 'oauth_access_token_scope', 'scope', 'oauth_scope', 'id', 'CASCADE');

        $this->createTable('oauth_auth_code_scope', [
            'id' => Schema::TYPE_PK,
            'auth_code' => Schema::TYPE_STRING . ' NOT NULL',
            'scope' => Schema::TYPE_STRING . ' NOT NULL',
        ], $this->tableOptions);

        $this->addForeignKey('fk-oauth_auth_code_scope-auth_code', 'oauth_auth_code_scope', 'auth_code', 'oauth_auth_code', 'auth_code', 'CASCADE');
        $this->addForeignKey('fk-oauth_auth_code_scope-scope', 'oauth_auth_code_scope', 'scope', 'oauth_scope', 'id', 'CASCADE');

        $this->createTable('oauth_session_scope', [
            'id' => Schema::TYPE_BIGPK,
            'session_id' => Schema::TYPE_BIGINT . ' NOT NULL',
            'scope' => Schema::TYPE_STRING . ' NOT NULL',
        ], $this->tableOptions);

        $this->addForeignKey('fk-oauth_session_scope-session_id', 'oauth_session_scope', 'session_id', 'oauth_session', 'id', 'CASCADE');
        $this->addForeignKey('fk-oauth_session_scope-scope', 'oauth_session_scope', 'scope', 'oauth_scope', 'id', 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('oauth_session_scope');
        $this->dropTable('oauth_auth_code_scope');
        $this->dropTable('oauth_access_token_scope');
        $this->dropTable('oauth_auth_code');
        $this->dropTable('oauth_refresh_token');
        $this->dropTable('oauth_access_token');
        $this->dropTable('oauth_session');
        $this->dropTable('oauth_scope');
        $this->dropTable('oauth_client_redirect_uri');
        $this->dropTable('oauth_client');
    }
}
