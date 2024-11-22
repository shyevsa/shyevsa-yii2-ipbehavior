<?php

namespace shyevsa\ipbehavior\tests;

class IpBehaviorStringTest extends TestCase
{
    public function testInsert()
    {
        $this->assertEquals('127.0.0.5', \Yii::$app->request->userIP);

        $post = new PostStr();
        $post->id = 1;
        $post->title = 'test';
        if (!$post->save()) {
            var_dump($post->errors);
        }

        $post = PostStr::findOne(1);
        $this->assertEquals('127.0.0.5', $post->createdFrom);
        $this->assertEquals('127.0.0.5', $post->updatedFrom);
        $this->assertEquals('127.0.0.5', $post->created_ip);
        $this->assertEquals('127.0.0.5', $post->updated_ip);
    }

    public function testUpdate()
    {
        $db = \Yii::$app->db;
        $db->createCommand()->insert('post', [
            'id' => 1,
            'title' => 'test',
            'created_ip' => '127.0.0.1',
            'updated_ip' => '127.0.0.2',
        ])->execute();

        $this->assertEquals('127.0.0.5', \Yii::$app->request->userIP);

        $post = PostStr::findOne(1);
        $this->assertEquals('127.0.0.1', $post->createdFrom);
        $this->assertEquals('127.0.0.2', $post->updatedFrom);
        $this->assertEquals('127.0.0.1', $post->created_ip);
        $this->assertEquals('127.0.0.2', $post->updated_ip);


        $post->title = 'test2';
        if (!$post->save()) {
            var_dump($post->errors);
        }

        $post->refresh();
        $this->assertEquals('127.0.0.1', $post->createdFrom);
        $this->assertEquals('127.0.0.5', $post->updatedFrom);
        $this->assertEquals('127.0.0.1', $post->created_ip);
        $this->assertEquals('127.0.0.5', $post->updated_ip);
    }
}
