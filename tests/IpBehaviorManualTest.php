<?php

namespace shyevsa\ipbehavior\tests;

class IpBehaviorManualTest extends TestCase
{
    public function testInsert()
    {
        $this->assertEquals('127.0.0.5', \Yii::$app->request->userIP);

        $post = new PostManual();
        $post->id = 1;
        $post->title = 'test';
        $post->attributes = [
            'createdFrom' => '127.0.0.25',
            'updatedFrom' => '127.0.0.30',
        ];
        if (!$post->save()) {
            var_dump($post->errors);
        }

        $post = PostManual::findOne(1);
        $this->assertEquals('127.0.0.25', $post->createdFrom);
        $this->assertEquals('127.0.0.30', $post->updatedFrom);
        $this->assertEquals("\x7F\x00\x00\x19", $post->created_from);
        $this->assertEquals("\x7F\x00\x00\x1E", $post->updated_from);
    }

    public function testUpdate()
    {
        $db = \Yii::$app->db;
        $db->createCommand()->insert('post', [
            'id' => 1,
            'title' => 'test',
            'created_from' => "\x7F\x00\x00\x01",
            'updated_from' => "\x7F\x00\x00\x02",
        ])->execute();

        $this->assertEquals('127.0.0.5', \Yii::$app->request->userIP);

        $post = PostManual::findOne(1);
        $this->assertEquals('127.0.0.1', $post->createdFrom);
        $this->assertEquals('127.0.0.2', $post->updatedFrom);
        $this->assertEquals("\x7F\x00\x00\x01", $post->created_from);
        $this->assertEquals("\x7F\x00\x00\x02", $post->updated_from);


        $post->title = 'test2';
        $post->updatedFrom = "";
        $post->createdFrom = '78de:d961:7af9:2bf1:4dd2:d676:00b6:0ea2';
        if (!$post->save()) {
            var_dump($post->errors);
        }

        $post->refresh();
        $this->assertEquals('78de:d961:7af9:2bf1:4dd2:d676:b6:ea2', $post->createdFrom);
        $this->assertEquals(null, $post->updatedFrom);
        $this->assertEquals("\x78\xde\xd9\x61\x7a\xf9\x2b\xf1\x4d\xd2\xd6\x76\x00\xb6\x0e\xa2", $post->created_from);
        $this->assertEquals(null, $post->updated_from);
    }
}
