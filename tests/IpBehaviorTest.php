<?php

namespace shyevsa\ipbehavior\tests;

use shyevsa\ipbehavior\IpBehavior;

class IpBehaviorTest extends TestCase
{

    public function testBlob2ip() {
        $this->assertEquals('127.0.0.1', IpBehavior::blob2ip("\x7f\x00\x00\x01"));
        $this->assertEquals('::1', IpBehavior::blob2ip("\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x01"));
    }

    public function testIp2blob() {
        $this->assertEquals("\x7f\x00\x00\x01", IpBehavior::ip2blob('127.0.0.1'));
        $this->assertEquals("\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x01", IpBehavior::ip2blob('::1'));
    }

    public function testInsert() {
        $this->assertEquals('127.0.0.5', \Yii::$app->request->userIP);

        $post = new Post();
        $post->id = 1;
        $post->title = 'test';
        if (!$post->save()) {
            var_dump($post->errors);
        }

        $post = Post::findOne(1);
        $this->assertEquals('127.0.0.5', $post->createdFrom);
        $this->assertEquals('127.0.0.5', $post->updatedFrom);
    }

    public function testUpdate() {
        $db = \Yii::$app->db;
        $db->createCommand()->insert('post', [
            'id' => 1,
            'title' => 'test',
            'created_from' => "\x7f\x00\x00\x01",
            'updated_from' => "\x7f\x00\x00\x02",
        ])->execute();

        $this->assertEquals('127.0.0.5', \Yii::$app->request->userIP);

        $post = Post::findOne(1);
        $this->assertEquals('127.0.0.1', $post->createdFrom);
        $this->assertEquals('127.0.0.2', $post->updatedFrom);

        $post->title = 'test2';
        if (!$post->save()) {
            var_dump($post->errors);
        }

        $post->refresh();
        $this->assertEquals('127.0.0.1', $post->createdFrom);
        $this->assertEquals('127.0.0.5', $post->updatedFrom);
    }
}
